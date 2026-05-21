<?php

class DriverAppRouter {
    protected $currentController = 'DriverDashboardController';
    protected $currentMethod = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->getUrl();

        // 1. Look for controllers inside the new driver_app folder
        if (isset($url[0]) && file_exists('../driver_app/Controllers/Driver' . ucwords($url[0]) . 'Controller.php')) {
            $this->currentController = 'Driver' . ucwords($url[0]) . 'Controller';
            unset($url[0]);
        }

        require_once '../driver_app/Controllers/' . $this->currentController . '.php';
        $this->currentController = new $this->currentController;

        // Re-index the array so the next item (the method) is always at position [0]
        $url = $url ? array_values($url) : [];

        // 2. Check for the method
        if (isset($url[0])) {
            if (method_exists($this->currentController, $url[0])) {
                $this->currentMethod = $url[0];
                unset($url[0]);
            }
        }

        // 3. Get params
        $this->params = $url ? array_values($url) : [];

        // 4. Call the method
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    public function getUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $urlArray = explode('/', $url);
            
            if (isset($urlArray[0]) && $urlArray[0] === 'driver') {
                array_shift($urlArray); 
            }
            
            return $urlArray;
        }
        return [];
    }
}
