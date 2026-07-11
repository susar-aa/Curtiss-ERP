<?php
if (DIRECTORY_SEPARATOR === '\\' || (isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false || strpos($_SERVER['HTTP_HOST'], '::1') !== false))) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Global Exception and Error Logger
function global_exception_handler(Throwable $exception) {
    $logFile = dirname(__DIR__) . '/app_errors.log';
    $errMessage = "Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    $logContent = "[" . date('Y-m-d H:i:s') . "] " . $errMessage . "\n" . $exception->getTraceAsString() . "\n\n";
    @file_put_contents($logFile, $logContent, FILE_APPEND);

    // If it's an API/AJAX request, return structured JSON
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || 
              (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
              isset($_GET['api_sync']);
              
    if ($isAjax) {
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode([
            'success' => false,
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    } else {
        http_response_code(500);
        $showDetails = (DIRECTORY_SEPARATOR === '\\' || (isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false)));
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Internal Server Error</title>
            <style>
                body { font-family: -apple-system, sans-serif; background: #f0f2f5; color: #333; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
                .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); max-width: 800px; width: 100%; }
                h3 { color: #ff3b30; margin-top: 0; }
                p { font-size: 14px; color: #666; line-height: 1.5; }
                pre { background: #f8f9fa; padding: 15px; border-radius: 6px; overflow-x: auto; font-size: 12px; border: 1px solid #e1e4e8; max-height: 300px; }
                .btn { display: inline-block; background: #0066cc; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-size: 14px; margin-top: 15px; }
            </style>
        </head>
        <body>
            <div class='card'>
                <h3>Application Error (HTTP 500)</h3>
                <p>An unexpected error occurred while processing your request. This issue has been logged to <strong>app_errors.log</strong>.</p>
                <?php if ($showDetails): ?>
                    <p><strong>Error Message:</strong> <?php echo htmlspecialchars($exception->getMessage()); ?></p>
                    <p><strong>File:</strong> <?php echo htmlspecialchars($exception->getFile()); ?>:<?php echo $exception->getLine(); ?></p>
                    <pre><?php echo htmlspecialchars($exception->getTraceAsString()); ?></pre>
                <?php endif; ?>
                <a href='javascript:history.back()' class='btn'>Go Back</a>
            </div>
        </body>
        </html>
        <?php
    }
    exit;
}

function global_error_handler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

set_exception_handler('global_exception_handler');
set_error_handler('global_error_handler');


// Global CORS Headers
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-User-ID');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'suzxlabs.com') !== false) {
    $isSecure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '.suzxlabs.com',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

session_start();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../vendor/autoload.php';
require_once '../config/database.php';
require_once '../core/Cache.php';
require_once '../core/Database.php';
require_once '../core/Controller.php';
require_once '../core/App.php';

$app = new App();

