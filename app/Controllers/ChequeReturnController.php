<?php
class ChequeReturnController extends Controller {
    private $chequeModel;
    private $coaModel;
    
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        $this->chequeModel = $this->model('Cheque');
        $this->coaModel = $this->model('ChartOfAccount');
    }

    public function index() {
        $search = $_GET['search'] ?? '';
        $db = new Database();
        
        // Fetch only customer cheques that are Pending and in Cheque in Hand (bank_account_id is NULL)
        $query = "SELECT ch.*, c.name as customer_name, c.id as customer_id
                  FROM cheques ch 
                  JOIN customers c ON ch.customer_id = c.id
                  WHERE ch.status = 'Pending' AND ch.bank_account_id IS NULL";
                  
        if (!empty($search)) {
            $query .= " AND (ch.cheque_number LIKE :search OR ch.bank_name LIKE :search OR c.name LIKE :search)";
            $db->query($query);
            $db->bind(':search', "%$search%");
        } else {
            $db->query($query);
        }
        
        $cheques = $db->resultSet();
        
        // Fetch expense accounts for the return charge
        $accounts = $this->coaModel->getAccounts() ?: [];
        $expenseAccounts = array_filter($accounts, function($a) {
            return $a->account_type == 'Expense' || strpos(strtolower($a->account_name), 'bank charge') !== false;
        });

        $data = [
            'title' => 'Cheque Returns',
            'content_view' => 'cheque_returns/index',
            'cheques' => $cheques,
            'search' => $search,
            'expense_accounts' => $expenseAccounts,
            'error' => $_GET['error'] ?? '',
            'success' => $_GET['success'] ?? ''
        ];

        $this->view('layouts/main', $data);
    }

    public function processReturn() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cheque_id'])) {
            $chequeId = intval($_POST['cheque_id']);
            $reason = trim($_POST['return_reason']);
            if ($reason === 'Other') {
                $reason = trim($_POST['other_reason']);
            }
            $returnDate = $_POST['return_date'];
            $charge = floatval($_POST['return_charge'] ?? 0);
            $chargeAccountId = intval($_POST['charge_account_id'] ?? 0);
            $userId = $_SESSION['user_id'];

            if (empty($reason) || empty($returnDate)) {
                header('Location: ' . APP_URL . '/chequereturn?error=Reason and Date are required.');
                exit;
            }

            if ($this->chequeModel->returnCustomerCheque($chequeId, $reason, $returnDate, $charge, $chargeAccountId, $userId)) {
                $this->logActivity('Cheque Returned', 'Banking', "Marked Cheque ID $chequeId as Returned. Reason: $reason");
                header('Location: ' . APP_URL . '/chequereturn?success=Cheque successfully marked as returned.');
                exit;
            } else {
                header('Location: ' . APP_URL . '/chequereturn?error=Failed to return cheque. It may have already been returned or cleared.');
                exit;
            }
        }
        header('Location: ' . APP_URL . '/chequereturn');
        exit;
    }
}
