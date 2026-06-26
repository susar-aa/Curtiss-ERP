<?php
class App {
    protected $controller = 'DashboardController';
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // Return 204 No Content for favicon.ico requests to prevent routing errors
        if (isset($url[0]) && strtolower($url[0]) === 'favicon.ico') {
            http_response_code(204);
            return;
        }

        // Strip the mobile 'rep' or 'driver' prefix if present
        $isMobileApi = false;
        $mobilePrefix = '';
        if (isset($url[0]) && (strtolower($url[0]) === 'rep' || strtolower($url[0]) === 'driver')) {
            $isMobileApi = true;
            $mobilePrefix = ucfirst(strtolower($url[0]));
            array_shift($url);
        }

        // Check if this is an API sync request
        $isApiSync = $isMobileApi || isset($_GET['api_sync']) || (isset($url[1]) && (strpos($url[1], 'api_') === 0 || strpos($url[1], 'sync_') === 0));

        // Allow public access to sales/show
        $isPublicInvoice = false;
        if (isset($url[0]) && strtolower($url[0]) === 'sales' && isset($url[1]) && strtolower($url[1]) === 'show' && isset($url[2])) {
            $isPublicInvoice = true;
        }

        // Check if user is logged in. If not, force routing to AuthController (unless it is auth controller, an API sync request, or a public invoice view)
        if (!isset($_SESSION['user_id']) && !$isApiSync && !$isPublicInvoice && (isset($url[0]) ? strtolower($url[0]) !== 'auth' : true)) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || 
                      (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
                      (strpos($_SERVER['REQUEST_URI'], '/fetch_data') !== false) ||
                      (strpos($_SERVER['REQUEST_URI'], '/quick_view') !== false);

            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'session_expired' => true,
                    'message' => 'Session Expired. Please login again.'
                ]);
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            }

            $this->controller = 'AuthController';
            $this->method = 'login';
        } else {

            if (isset($url[0])) {
                $cleanControllerName = str_replace('-', '', ucwords($url[0], '-'));
                
                // Case-insensitive controller file matching for cross-platform compatibility (Linux / Plesk)
                $matchedController = null;
                $controllersDir = '../app/Controllers/';
                if (is_dir($controllersDir)) {
                    $files = scandir($controllersDir);
                    
                    // 1. Try prepending the mobile prefix if applicable (e.g. DriverDashboardController)
                    if ($isMobileApi && !empty($mobilePrefix)) {
                        $prefixedControllerName = $mobilePrefix . $cleanControllerName . 'Controller';
                        $targetLowerPrefixed = strtolower($prefixedControllerName . '.php');
                        foreach ($files as $file) {
                            if (strtolower($file) === $targetLowerPrefixed) {
                                $matchedController = pathinfo($file, PATHINFO_FILENAME);
                                break;
                            }
                        }
                    }
                    
                    // 2. Fallback to normal controller name if no prefixed controller matches
                    if ($matchedController === null) {
                        $controllerName = $cleanControllerName . 'Controller';
                        $targetLower = strtolower($controllerName . '.php');
                        foreach ($files as $file) {
                            if (strtolower($file) === $targetLower) {
                                $matchedController = pathinfo($file, PATHINFO_FILENAME);
                                break;
                            }
                        }
                    }
                }

                if ($matchedController !== null) {
                    $this->controller = $matchedController;
                    unset($url[0]);
                } else {
                    $controllerName = $cleanControllerName . 'Controller';
                    // Force an error to show exactly what file is missing
                    die("<div style='padding:20px; font-family:sans-serif; color:red;'>
                            <h3>MVC Routing Error 404</h3>
                            <p>The router is looking for a file that does not exist.</p>
                            <p>Missing File: <strong>app/Controllers/" . $controllerName . ".php</strong></p>
                         </div>");
                }
            }
        }

        // Require the controller
        require_once '../app/Controllers/' . $this->controller . '.php';

        // Instantiate controller class
        $this->controller = new $this->controller;

        // Check for second part of url (method)
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            }
        }

        // Get params
        $this->params = $url ? array_values($url) : [];

        // Call a callback with array of params
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    public function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}