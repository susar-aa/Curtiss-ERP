<?php
class RepController {
    
    // Load model from the isolated rep_app/Models folder
    public function model($model) {
        require_once '../rep_app/Models/' . $model . '.php';
        return new $model();
    }

    // Load view from the isolated rep_app/Views folder
    public function view($view, $data = []) {
        if (file_exists('../rep_app/Views/' . $view . '.php')) {
            require_once '../rep_app/Views/' . $view . '.php';
        } else {
            die("Mobile View does not exist: " . $view);
        }
    }
}