<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Setup mock session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'Admin';
$_SESSION['permissions'] = ['petty_cash' => ['view', 'create_edit']];

require_once '../vendor/autoload.php';
require_once '../config/database.php';
require_once '../core/Cache.php';
require_once '../core/Database.php';
require_once '../core/Controller.php';
require_once '../app/Controllers/PettyCashController.php';

// Mock checkPermission for Controller class if needed
class MockPettyCashController extends PettyCashController {
    public function checkPermission($module, $action = 'view') {
        return true; // Bypass checks
    }
}

try {
    $controller = new MockPettyCashController();
    ob_start();
    $controller->index();
    $html = ob_get_clean();
    echo "RENDER_SUCCESS: Size " . strlen($html) . " bytes\n";
    
    $ids = ['settingsModal', 'allocateModal', 'expenseModal', 'reimburseModal'];
    foreach ($ids as $id) {
        echo "ID '$id': " . (strpos($html, 'id="' . $id . '"') !== false ? 'YES' : 'NO') . "\n";
    }
    
    file_put_contents('rendered_petty_cash.html', $html);
    echo "Saved output to rendered_petty_cash.html\n";
} catch (Throwable $e) {
    echo "RENDER_FAILED: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
