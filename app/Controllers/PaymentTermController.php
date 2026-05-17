<?php
class PaymentTermController extends Controller {
    private $termModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->termModel = $this->model('PaymentTerm');
    }

    public function index() {
        $data = [
            'title' => 'Payment Terms',
            'content_view' => 'payment_terms/index',
            'terms' => $this->termModel->getAllTerms(),
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] == 'add') {
                if ($this->termModel->addTerm(['name' => trim($_POST['name']), 'days_due' => intval($_POST['days_due'])])) {
                    header('Location: ' . APP_URL . '/paymentterm?success=1'); exit;
                } else { $data['error'] = 'Failed to create payment term.'; }
            } elseif ($_POST['action'] == 'edit') {
                if ($this->termModel->updateTerm(['id' => $_POST['term_id'], 'name' => trim($_POST['name']), 'days_due' => intval($_POST['days_due'])])) {
                    header('Location: ' . APP_URL . '/paymentterm?success=1'); exit;
                } else { $data['error'] = 'Failed to update payment term.'; }
            } elseif ($_POST['action'] == 'delete') {
                if ($this->termModel->deleteTerm($_POST['term_id'])) {
                    header('Location: ' . APP_URL . '/paymentterm?success=1'); exit;
                } else { $data['error'] = 'Failed to delete term. It may be linked to existing invoices.'; }
            }
        }

        if(isset($_GET['success'])) $data['success'] = "Payment Terms updated successfully!";
        
        $this->view('layouts/main', $data);
    }
}