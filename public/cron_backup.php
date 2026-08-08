<?php
// CRON Script for Automated System Backup
// Can be run via CLI or via HTTP with a secure token: cron_backup.php?token=YOUR_SECRET_TOKEN

// Example token - In production this should be in .env
define('CRON_TOKEN', 'curtiss_secure_backup_2026');

$isCli = (php_sapi_name() === 'cli');
$token = $_GET['token'] ?? '';

if (!$isCli && $token !== CRON_TOKEN) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: Invalid token.";
    exit;
}

// Bootstrap the application
require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/Database.php';

// Increase limits
ini_set('memory_limit', '512M');
set_time_limit(300);

echo "Starting Automated Backup...\n";

try {
    $db = new Database();
    $pdo = $db->getDbHandler();
    
    $rootDir = dirname(__DIR__);
    $backupDir = $rootDir . '/writable/backups/';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0777, true);
    }

    $timestamp = date('Ymd_His');
    $sqlFilename = 'db_' . DB_NAME . '_' . $timestamp . '.sql';
    $sqlFilePath = $backupDir . $sqlFilename;
    $zipFilename = 'backup_' . DB_NAME . '_' . $timestamp . '.zip';
    $zipFilePath = $backupDir . $zipFilename;

    echo "1/2 Generating Database Dump...\n";
    $fp = fopen($sqlFilePath, 'w');
    if (!$fp) throw new Exception("Unable to create temporary SQL file.");

    fwrite($fp, "-- Curtiss ERP System Backup (Automated)\n");
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

        $rowCount = 0;
        $batchSize = 200;
        $insertSql = "";

        while ($row = $dataResult->fetch(PDO::FETCH_NUM)) {
            if ($rowCount % $batchSize == 0) {
                if ($rowCount > 0) {
                    fwrite($fp, $insertSql . ";\n");
                }
                $insertSql = "INSERT INTO `$table` VALUES (";
            } else {
                $insertSql .= "),(";
            }

            for ($j = 0; $j < $columnCount; $j++) {
                if (isset($row[$j])) {
                    $escaped = str_replace(array("\x00", "\n", "\r", "\\", "'", "\x1a"), array('\\0', '\\n', '\\r', '\\\\', "\\'", '\\Z'), $row[$j]);
                    $insertSql .= "'" . $escaped . "'";
                } else {
                    $insertSql .= "NULL";
                }
                if ($j < ($columnCount - 1)) $insertSql .= ",";
            }
            $rowCount++;
        }
        if ($rowCount > 0) {
            fwrite($fp, $insertSql . ");\n");
        }
        fwrite($fp, "\n");
    }
    fwrite($fp, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($fp);

    echo "2/2 Generating Full System ZIP Archive...\n";
    $zip = new ZipArchive();
    if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        throw new Exception("Cannot create ZIP archive.");
    }

    $zip->addFile($sqlFilePath, $sqlFilename);

    if (file_exists($rootDir . '/.env')) {
        $zip->addFile($rootDir . '/.env', '.env');
    }

    $uploadsDir = $rootDir . '/public/uploads';
    if (is_dir($uploadsDir)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($uploadsDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = 'public/uploads/' . ltrim(str_replace('\\', '/', substr($filePath, strlen(realpath($uploadsDir)))), '/');
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
    $zip->close();
    
    if (file_exists($sqlFilePath)) {
        unlink($sqlFilePath);
    }

    echo "SUCCESS: Full system backup $zipFilename generated successfully.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
