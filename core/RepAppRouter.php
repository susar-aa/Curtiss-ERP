<?php
class RepAppRouter {
    protected $currentController = 'RepDashboardController';
    protected $currentMethod = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->getUrl();

        // 1. Look for controllers inside the new rep_app folder
        if (isset($url[0]) && file_exists('../rep_app/Controllers/' . ucwords($url[0]) . 'Controller.php')) {
            $this->currentController = ucwords($url[0]) . 'Controller';
            unset($url[0]);
        }

        require_once '../rep_app/Controllers/' . $this->currentController . '.php';
        $this->currentController = new $this->currentController;

        // NEW FIX: Re-index the array so the next item (the method) is always at position [0]
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
            
            if (isset($urlArray[0]) && $urlArray[0] === 'rep') {
                array_shift($urlArray); 
            }
            
            return $urlArray;
        }
        return [];
    }
}