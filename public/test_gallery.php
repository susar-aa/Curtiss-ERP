<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

define('APP_URL', 'http://localhost');

// Load core files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../app/Controllers/GalleryController.php';

try {
    $controller = new GalleryController();
    $controller->index();
} catch (Throwable $e) {
    echo "Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
}
