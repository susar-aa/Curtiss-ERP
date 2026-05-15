<?php
/* STREAMING_CHUNK:Simplifying the Dashboard Controller... */
class DashboardController extends Controller {

    public function __construct() {
        // Ensure user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
    }

    public function index() {
        $data = [
            'title' => 'Workflow Dashboard',
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'content_view' => 'dashboard/index'
        ];
        
        $this->view('layouts/main', $data);
    }
}
