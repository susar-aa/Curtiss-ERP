<?php
class DashboardController extends Controller {
    private $dashboardModel;

    public function __construct() {
        // Ensure user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        $this->dashboardModel = $this->model('Dashboard');
    }

    public function index() {
        $revenue = $this->dashboardModel->getTotalRevenue();
        $expenses = $this->dashboardModel->getTotalExpenses();
        $profit = $revenue - $expenses;
        $ar = $this->dashboardModel->getTotalAR();
        $recent_activity = $this->dashboardModel->getRecentActivity(6);

        $data = [
            'title' => 'Dashboard Overview',
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'revenue' => $revenue,
            'expenses' => $expenses,
            'profit' => $profit,
            'ar' => $ar,
            'recent_activity' => $recent_activity,
            'content_view' => 'dashboard/index'
        ];
        
        $this->view('layouts/main', $data);
    }
}