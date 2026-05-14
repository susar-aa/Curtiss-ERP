<?php
class App {
    protected $controller = 'DashboardController';
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // Check if user is logged in. If not, force routing to AuthController
        if (!isset($_SESSION['user_id']) && (isset($url[0]) ? strtolower($url[0]) !== 'auth' : true)) {
            $this->controller = 'AuthController';
            $this->method = 'login';
        } else {
            // Look in controllers for first value
            if (isset($url[0])) {
                $controllerName = ucwords($url[0]) . 'Controller';
                if (file_exists('../app/Controllers/' . $controllerName . '.php')) {
                    $this->controller = $controllerName;
                    unset($url[0]);
                } else {
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