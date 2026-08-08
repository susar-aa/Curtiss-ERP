<?php
class BackupController extends Controller {
    private $db;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        // Restrict to Admin only for security reasons
        if ($_SESSION['role'] !== 'Admin') {
            die("Access Denied: Only System Administrators can access backup and restore tools.");
        }
        $this->db = new Database();
    }

    public function index() {
        $backupDir = '../writable/backups/';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        $files = [];
        if ($handle = opendir($backupDir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && pathinfo($entry, PATHINFO_EXTENSION) == 'zip') {
                    $files[] = [
                        'filename' => $entry,
                        'size' => filesize($backupDir . $entry),
                        'date' => filemtime($backupDir . $entry)
                    ];
                }
            }
            closedir($handle);
        }

        // Sort files by date descending
        usort($files, function($a, $b) {
            return $b['date'] - $a['date'];
        });

        $data = [
            'title' => 'Backup & Restore',
            'content_view' => 'admin/backup',
            'files' => $files,
            'error' => '',
            'success' => ''
        ];

        if (isset($_SESSION['flash_success'])) {
            $data['success'] = $_SESSION['flash_success'];
            unset($_SESSION['flash_success']);
        }
        if (isset($_SESSION['flash_error'])) {
            $data['error'] = $_SESSION['flash_error'];
            unset($_SESSION['flash_error']);
        }

        $this->view('layouts/main', $data);
    }

    public function generate() {
        // Increase limits for intensive backup process
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        try {
            $pdo = $this->db->getDbHandler();
            $rootDir = dirname(__DIR__, 2);
            $backupDir = $rootDir . '/writable/backups/';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0777, true);
            }

            $timestamp = date('Ymd_His');
            $sqlFilename = 'db_' . DB_NAME . '_' . $timestamp . '.sql';
            $sqlFilePath = $backupDir . $sqlFilename;
            $zipFilename = 'backup_' . DB_NAME . '_' . $timestamp . '.zip';
            $zipFilePath = $backupDir . $zipFilename;

            // 1. Generate SQL File (Stream to disk to prevent RAM exhaustion)
            $fp = fopen($sqlFilePath, 'w');
            if (!$fp) throw new Exception("Unable to create temporary SQL file.");

            fwrite($fp, "-- Curtiss ERP System Backup\n");
            fwrite($fp, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
            fwrite($fp, "-- Database: " . DB_NAME . "\n\n");
            fwrite($fp, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            $tables = [];
            $result = $pdo->query("SHOW TABLES");
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            foreach ($tables as $table) {
                $structResult = $pdo->query("SHOW CREATE TABLE `$table`");
                $structRow = $structResult->fetch(PDO::FETCH_NUM);
                fwrite($fp, "DROP TABLE IF EXISTS `$table`;\n");
                fwrite($fp, $structRow[1] . ";\n\n");

                $dataResult = $pdo->query("SELECT * FROM `$table`");
                $columnCount = $dataResult->columnCount();

                while ($row = $dataResult->fetch(PDO::FETCH_NUM)) {
                    $insertSql = "INSERT INTO `$table` VALUES(";
                    for ($j = 0; $j < $columnCount; $j++) {
                        if (isset($row[$j])) {
                            $escaped = str_replace(array("\x00", "\n", "\r", "\\", "'", "\x1a"), array('\\0', '\\n', '\\r', '\\\\', "\\'", '\\Z'), $row[$j]);
                            $insertSql .= "'" . $escaped . "'";
                        } else {
                            $insertSql .= "NULL";
                        }
                        if ($j < ($columnCount - 1)) $insertSql .= ",";
                    }
                    $insertSql .= ");\n";
                    fwrite($fp, $insertSql);
                }
                fwrite($fp, "\n");
            }
            fwrite($fp, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($fp);

            // 2. Create ZIP Archive
            $zip = new ZipArchive();
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
                throw new Exception("Cannot create ZIP archive.");
            }

            // Add SQL Dump
            $zip->addFile($sqlFilePath, $sqlFilename);

            // Add .env file
            if (file_exists($rootDir . '/.env')) {
                $zip->addFile($rootDir . '/.env', '.env');
            }

            // Add uploads directory
            $uploadsDir = $rootDir . '/public/uploads';
            if (is_dir($uploadsDir)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($uploadsDir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );
                foreach ($files as $name => $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        // Compute relative path
                        $relativePath = 'public/uploads/' . ltrim(str_replace('\\', '/', substr($filePath, strlen(realpath($uploadsDir)))), '/');
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            }

            $zip->close();

            // Cleanup SQL file
            if (file_exists($sqlFilePath)) {
                unlink($sqlFilePath);
            }

            $this->logActivity('System Backup Generated', 'System', "Backup filename: $zipFilename");
            $_SESSION['flash_success'] = "Full system backup $zipFilename generated successfully.";

        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Backup failed: " . $e->getMessage();
        }

        header('Location: ' . APP_URL . '/backup');
        exit;
    }

    public function download($filename) {
        $filename = basename($filename);
        $filePath = '../writable/backups/' . $filename;

        if (file_exists($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) == 'zip') {
            header('Content-Description: File Transfer');
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            flush();
            readfile($filePath);
            exit;
        } else {
            $_SESSION['flash_error'] = "Backup file not found.";
            header('Location: ' . APP_URL . '/backup');
            exit;
        }
    }

    public function delete($filename) {
        $filename = basename($filename);
        $filePath = '../writable/backups/' . $filename;

        if (file_exists($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) == 'zip') {
            unlink($filePath);
            $this->logActivity('System Backup Deleted', 'System', "Deleted backup file: $filename");
            $_SESSION['flash_success'] = "Backup archive deleted successfully.";
        } else {
            $_SESSION['flash_error'] = "Backup file not found.";
        }
        header('Location: ' . APP_URL . '/backup');
        exit;
    }

    public function restore() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Increase limits for extraction and DB import
            ini_set('memory_limit', '512M');
            set_time_limit(300);
            
            $pdo = $this->db->getDbHandler();
            $zipFilePath = '';

            // Handle file upload or server file restoration
            if (isset($_POST['server_file']) && !empty($_POST['server_file'])) {
                $filename = basename($_POST['server_file']);
                $zipFilePath = '../writable/backups/' . $filename;
            } elseif (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
                $zipFilePath = $_FILES['backup_file']['tmp_name'];
                $fileName = $_FILES['backup_file']['name'];
                if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) != 'zip') {
                    $_SESSION['flash_error'] = "Invalid file type. Only ZIP archives are supported.";
                    header('Location: ' . APP_URL . '/backup');
                    exit;
                }
            } else {
                $_SESSION['flash_error'] = "Please select a backup archive to restore.";
                header('Location: ' . APP_URL . '/backup');
                exit;
            }

            if (file_exists($zipFilePath)) {
                try {
                    $rootDir = dirname(__DIR__, 2);
                    $zip = new ZipArchive();
                    if ($zip->open($zipFilePath) === TRUE) {
                        
                        // 1. Find and Restore Database
                        $sqlFileIndex = -1;
                        for ($i = 0; $i < $zip->numFiles; $i++) {
                            $stat = $zip->statIndex($i);
                            if (pathinfo($stat['name'], PATHINFO_EXTENSION) == 'sql') {
                                $sqlFileIndex = $i;
                                break;
                            }
                        }

                        if ($sqlFileIndex !== -1) {
                            $sqlFileName = $zip->getNameIndex($sqlFileIndex);
                            $fp = $zip->getStream($sqlFileName);
                            if (!$fp) throw new Exception("Failed to read SQL stream from ZIP.");
                            
                            $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
                            
                            $query = '';
                            while (!feof($fp)) {
                                $line = fgets($fp);
                                if (trim($line) == '' || strpos(trim($line), '--') === 0) continue;
                                $query .= $line;
                                if (substr(trim($line), -1) == ';') {
                                    $pdo->exec($query);
                                    $query = '';
                                }
                            }
                            fclose($fp);
                            $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
                        }

                        // 2. Extract Files (Uploads)
                        // Extract specific paths to their original locations
                        for ($i = 0; $i < $zip->numFiles; $i++) {
                            $filename = $zip->getNameIndex($i);
                            
                            if (strpos($filename, 'public/uploads/') === 0) {
                                // Extract to root dir
                                $dest = $rootDir . '/' . $filename;
                                $dir = dirname($dest);
                                if (!is_dir($dir)) mkdir($dir, 0777, true);
                                copy("zip://".$zipFilePath."#".$filename, $dest);
                            }
                            
                            // Restore .env file securely as a backup to avoid overwriting live credentials
                            if ($filename === '.env') {
                                copy("zip://".$zipFilePath."#".$filename, $rootDir . '/.env.restored');
                            }
                        }
                        
                        $zip->close();
                        
                        $this->logActivity('System Restored', 'System', "Database and assets restored from archive.");
                        $_SESSION['flash_success'] = "System restored successfully! (Note: .env file was extracted as .env.restored to prevent overwriting live credentials)";
                    } else {
                        throw new Exception("Failed to open ZIP archive.");
                    }
                } catch (Exception $e) {
                    $_SESSION['flash_error'] = "Restore failed: " . $e->getMessage();
                }
            } else {
                $_SESSION['flash_error'] = "Archive file not found.";
            }
        }
        header('Location: ' . APP_URL . '/backup');
        exit;
    }
}
