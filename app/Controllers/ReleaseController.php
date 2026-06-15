<?php
class ReleaseController extends Controller {
    private $releaseModel;

    public function __construct() {
        // We only bypass auth for the api method
        $url = $this->parseUrl();
        $isApi = isset($url[1]) && strpos($url[1], 'api_') === 0;

        if (!$isApi) {
            if (!isset($_SESSION['user_id'])) {
                header('Location: ' . APP_URL . '/auth/login');
                exit;
            }
            if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Manager') {
                die("Access Denied: You do not have permission to view this module.");
            }
        }
        $this->releaseModel = $this->model('AppRelease');
    }

    private function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }

    public function index() {
        $releases = $this->releaseModel->getAllReleases();
        
        // Suggest next version
        $suggestedVersion = '1.0.0';
        if (!empty($releases)) {
            $latest = $releases[0]; // Ordered by major, minor, patch DESC
            $parts = explode('.', $latest->version);
            if (count($parts) === 3) {
                $patch = intval($parts[2]) + 1;
                $suggestedVersion = $parts[0] . '.' . $parts[1] . '.' . $patch;
            }
        }

        $data = [
            'title' => 'App Release Management',
            'content_view' => 'release/index',
            'releases' => $releases,
            'suggested_version' => $suggestedVersion,
            'error' => $_SESSION['release_error'] ?? '',
            'success' => $_SESSION['release_success'] ?? ''
        ];

        unset($_SESSION['release_error']);
        unset($_SESSION['release_success']);

        $this->view('layouts/main', $data);
    }

    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/release');
            exit;
        }

        $version = trim($_POST['version'] ?? '');
        $releaseNotes = trim($_POST['release_notes'] ?? '');
        $forceUpdate = isset($_POST['force_update']) ? 1 : 0;
        $isLatest = isset($_POST['is_latest']) ? 1 : 0;

        // 1. Validate version format (Semantic Versioning: X.Y.Z)
        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            $_SESSION['release_error'] = "Invalid version format. Must follow MAJOR.MINOR.PATCH format (e.g. 1.0.1).";
            header('Location: ' . APP_URL . '/release');
            exit;
        }

        $parts = explode('.', $version);
        $major = intval($parts[0]);
        $minor = intval($parts[1]);
        $patch = intval($parts[2]);

        // 2. Validate file upload
        if (!isset($_FILES['apk']) || $_FILES['apk']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['release_error'] = "Please select a valid APK file to upload.";
            header('Location: ' . APP_URL . '/release');
            exit;
        }

        $fileTmpPath = $_FILES['apk']['tmp_name'];
        $fileName = $_FILES['apk']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileExtension !== 'apk') {
            $_SESSION['release_error'] = "Invalid file type. Only .apk files are allowed.";
            header('Location: ' . APP_URL . '/release');
            exit;
        }

        // Create releases folder if not exists
        $uploadDir = '../public/releases';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newFileName = 'app-v' . $version . '.apk';
        $destPath = $uploadDir . '/' . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $apkRelativePath = 'public/releases/' . $newFileName;

            $releaseData = [
                'version' => $version,
                'major' => $major,
                'minor' => $minor,
                'patch' => $patch,
                'release_notes' => $releaseNotes,
                'apk_path' => $apkRelativePath,
                'force_update' => $forceUpdate,
                'is_latest' => $isLatest
            ];

            try {
                $id = $this->releaseModel->addRelease($releaseData);
                if ($id) {
                    $_SESSION['release_success'] = "New release v{$version} uploaded and registered successfully.";
                    
                    if ($isLatest) {
                        // Copy to latest.apk
                        $latestPath = $uploadDir . '/latest.apk';
                        copy($destPath, $latestPath);
                    }
                } else {
                    $_SESSION['release_error'] = "Failed to save release to database. Version might already exist.";
                    unlink($destPath); // Remove the file
                }
            } catch (Exception $e) {
                $_SESSION['release_error'] = "Error registering release: " . $e->getMessage();
                if (file_exists($destPath)) {
                    unlink($destPath);
                }
            }
        } else {
            $_SESSION['release_error'] = "Failed to upload APK file. Check directory permissions.";
        }

        header('Location: ' . APP_URL . '/release');
        exit;
    }

    public function set_latest($id) {
        $release = $this->releaseModel->getReleaseById($id);
        if ($release) {
            if ($this->releaseModel->setLatestRelease($id)) {
                // Copy the versioned file to latest.apk
                $uploadDir = '../public/releases';
                $versionedFile = $uploadDir . '/app-v' . $release->version . '.apk';
                $latestPath = $uploadDir . '/latest.apk';
                if (file_exists($versionedFile)) {
                    copy($versionedFile, $latestPath);
                }
                $_SESSION['release_success'] = "Release v{$release->version} marked as latest.";
            } else {
                $_SESSION['release_error'] = "Failed to update database.";
            }
        } else {
            $_SESSION['release_error'] = "Release not found.";
        }
        header('Location: ' . APP_URL . '/release');
        exit;
    }

    public function delete($id) {
        $release = $this->releaseModel->getReleaseById($id);
        if ($release) {
            $uploadDir = '../public/releases';
            $versionedFile = $uploadDir . '/app-v' . $release->version . '.apk';
            
            // Delete versioned file
            if (file_exists($versionedFile)) {
                unlink($versionedFile);
            }

            $wasLatest = $release->is_latest;

            if ($this->releaseModel->deleteRelease($id)) {
                $_SESSION['release_success'] = "Release v{$release->version} deleted successfully.";

                // If deleted release was the latest one, set the next highest version as latest
                if ($wasLatest) {
                    $latestPath = $uploadDir . '/latest.apk';
                    if (file_exists($latestPath)) {
                        unlink($latestPath);
                    }

                    $remaining = $this->releaseModel->getAllReleases();
                    if (!empty($remaining)) {
                        $newLatest = $remaining[0];
                        $this->releaseModel->setLatestRelease($newLatest->id);
                        
                        $newLatestFile = $uploadDir . '/app-v' . $newLatest->version . '.apk';
                        if (file_exists($newLatestFile)) {
                            copy($newLatestFile, $latestPath);
                        }
                    }
                }
            } else {
                $_SESSION['release_error'] = "Failed to delete release from database.";
            }
        } else {
            $_SESSION['release_error'] = "Release not found.";
        }
        header('Location: ' . APP_URL . '/release');
        exit;
    }

    // Version API for Mobile App (Bypasses Session Login checks)
    public function api_latest_version() {
        header('Content-Type: application/json');
        
        $latest = $this->releaseModel->getLatestRelease();
        if ($latest) {
            // Process release notes into an array of strings
            $notes = [];
            if (!empty($latest->release_notes)) {
                $rawNotes = explode("\n", $latest->release_notes);
                foreach ($rawNotes as $line) {
                    $trimmed = trim($line);
                    if ($trimmed !== '') {
                        $notes[] = $trimmed;
                    }
                }
            }

            echo json_encode([
                'latestVersion' => $latest->version,
                'apkUrl' => APP_URL . '/releases/latest.apk',
                'forceUpdate' => (bool)$latest->force_update,
                'releaseNotes' => $notes
            ]);
        } else {
            // Suggest default if no releases uploaded yet
            echo json_encode([
                'latestVersion' => '1.0.0',
                'apkUrl' => APP_URL . '/releases/latest.apk',
                'forceUpdate' => false,
                'releaseNotes' => ['Initial release']
            ]);
        }
        exit;
    }
}
