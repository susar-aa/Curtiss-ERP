<?php
class GalleryController extends Controller {

    private $galleryModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        
        require_once dirname(__DIR__) . '/Models/Gallery.php';
        $this->galleryModel = new Gallery();
    }

    public function index() {
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/products';
            $files = [];
            if (is_dir($uploadDir)) {
                $scanned = scandir($uploadDir);
                if (is_array($scanned)) {
                    $files = array_diff($scanned, array('.', '..'));
                }
            }

            $usedImagesMap = $this->galleryModel->getAllUsedImages();
            
            $galleryImages = [];
            
            foreach ($files as $file) {
                $path = $uploadDir . '/' . $file;
                if (is_file($path)) {
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $isUsed = isset($usedImagesMap[$file]);
                        
                        $galleryImages[] = [
                            'filename' => $file,
                            'url' => APP_URL . '/public/uploads/products/' . $file,
                            'path' => 'public/uploads/products/' . $file,
                            'size' => filesize($path),
                            'upload_date' => filemtime($path),
                            'is_used' => $isUsed,
                            'usages' => $isUsed ? $usedImagesMap[$file] : []
                        ];
                    }
                }
            }
            
            // Sort by newest first
            usort($galleryImages, function($a, $b) {
                return $b['upload_date'] <=> $a['upload_date'];
            });

            $data = [
                'title' => 'Image Gallery',
                'images' => $galleryImages,
                'content_view' => 'gallery/index'
            ];
            
            $this->view('layouts/main', $data);
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        
        header('Content-Type: application/json');
        
        $input = file_get_contents('php://input');
        $request = json_decode($input, true);
        
        $filesToDelete = $request['files'] ?? [];
        if (empty($filesToDelete)) {
            echo json_encode(['success' => false, 'message' => 'No files selected for deletion.']);
            exit;
        }
        
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/products';
        $deletedCount = 0;
        
        foreach ($filesToDelete as $file) {
            // Security check against directory traversal
            $file = basename($file);
            $path = $uploadDir . '/' . $file;
            
            if (file_exists($path) && is_file($path)) {
                // Delete from filesystem
                if (unlink($path)) {
                    // Delete from DB references
                    $this->galleryModel->removeImageReferences('public/uploads/products/' . $file);
                    $deletedCount++;
                }
            } else {
                 // Even if not on disk, clean up DB just in case it was a ghost reference
                 $this->galleryModel->removeImageReferences('public/uploads/products/' . $file);
                 $deletedCount++;
            }
        }
        
        echo json_encode(['success' => true, 'message' => "Successfully deleted $deletedCount image(s)."]);
    }
}
