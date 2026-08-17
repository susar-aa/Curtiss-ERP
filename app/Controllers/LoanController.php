<?php
class LoanController extends Controller {
    private $loanModel;
    private $coaModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        
        $this->loanModel = $this->model('Loan');
        $this->coaModel = $this->model('ChartOfAccount');
    }

    public function index() {
        $data = [
            'title' => 'Bank Loans',
            'content_view' => 'loans/index',
            'stats' => $this->loanModel->getDashboardStats(),
            'loans' => $this->loanModel->getLoans()
        ];
        $this->view('layouts/main', $data);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'lender_name' => trim($_POST['lender_name']),
                'loan_number' => trim($_POST['loan_number']),
                'principal_amount' => floatval($_POST['principal_amount']),
                'interest_rate' => floatval($_POST['interest_rate']),
                'loan_start_date' => $_POST['loan_start_date'],
                'loan_term_months' => intval($_POST['loan_term_months']),
                'repayment_frequency' => $_POST['repayment_frequency'],
                'first_payment_date' => $_POST['first_payment_date'],
                'maturity_date' => $_POST['maturity_date'],
                'liability_account_id' => intval($_POST['liability_account_id']),
                'notes' => trim($_POST['notes'])
            ];
            
            if ($this->loanModel->addLoan($data)) {
                header('Location: ' . APP_URL . '/loan?success=created');
                exit;
            } else {
                header('Location: ' . APP_URL . '/loan/create?error=failed');
                exit;
            }
        } else {
            $accounts = $this->coaModel->getAccounts();
            $liabilities = array_filter($accounts, function($a) { return $a->account_type == 'Liability'; });
            
            $data = [
                'title' => 'Register New Loan',
                'content_view' => 'loans/create',
                'liabilities' => $liabilities
            ];
            $this->view('layouts/main', $data);
        }
    }

    public function edit($id) {
        $loan = $this->loanModel->getLoanById($id);
        if (!$loan) {
            header('Location: ' . APP_URL . '/loan?error=notfound');
            exit;
        }
        
        // Cannot edit if not Pending (or maybe we allow editing notes? For now, restrict to pending to prevent changing principal/rate)
        // Let's allow editing but we only update non-financials if Active. The view will handle this.
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'id' => $id,
                'lender_name' => trim($_POST['lender_name']),
                'loan_number' => trim($_POST['loan_number']),
                'principal_amount' => floatval($_POST['principal_amount']),
                'interest_rate' => floatval($_POST['interest_rate']),
                'loan_start_date' => $_POST['loan_start_date'],
                'loan_term_months' => intval($_POST['loan_term_months']),
                'repayment_frequency' => $_POST['repayment_frequency'],
                'first_payment_date' => $_POST['first_payment_date'],
                'maturity_date' => $_POST['maturity_date'],
                'liability_account_id' => intval($_POST['liability_account_id']),
                'notes' => trim($_POST['notes'])
            ];
            
            if ($this->loanModel->updateLoan($data)) {
                header('Location: ' . APP_URL . '/loan/show/' . $id . '?success=updated');
                exit;
            } else {
                header('Location: ' . APP_URL . '/loan/edit/' . $id . '?error=failed');
                exit;
            }
        } else {
            $accounts = $this->coaModel->getAccounts();
            $liabilities = array_filter($accounts, function($a) { return $a->account_type == 'Liability'; });
            
            $data = [
                'title' => 'Edit Loan',
                'content_view' => 'loans/edit',
                'loan' => $loan,
                'liabilities' => $liabilities
            ];
            $this->view('layouts/main', $data);
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->loanModel->deleteLoan($id)) {
                header('Location: ' . APP_URL . '/loan?success=deleted');
            } else {
                header('Location: ' . APP_URL . '/loan?error=delete_failed_active');
            }
            exit;
        }
    }

    public function show($id) {
        $loan = $this->loanModel->getLoanById($id);
        if (!$loan) {
            header('Location: ' . APP_URL . '/loan?error=notfound');
            exit;
        }
        
        $data = [
            'title' => 'Loan Details: ' . htmlspecialchars($loan->lender_name),
            'content_view' => 'loans/show',
            'loan' => $loan,
            'repayments' => $this->loanModel->getRepayments($id),
            'bank_accounts' => $this->loanModel->getAllBankAccounts()
        ];
        $this->view('layouts/main', $data);
    }

    public function disburse($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $bankAccountId = intval($_POST['bank_account_id']);
            $fees = floatval($_POST['processing_fees']);
            
            if ($this->loanModel->disburseLoan($id, $bankAccountId, $fees, $_SESSION['user_id'])) {
                header('Location: ' . APP_URL . '/loan/show/' . $id . '?success=disbursed');
            } else {
                header('Location: ' . APP_URL . '/loan/show/' . $id . '?error=failed');
            }
            exit;
        }
    }

    public function repay($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'loan_id' => $id,
                'payment_date' => $_POST['payment_date'],
                'principal_amount' => floatval($_POST['principal_amount']),
                'interest_amount' => floatval($_POST['interest_amount']),
                'bank_charges' => floatval($_POST['bank_charges']),
                'bank_account_id' => intval($_POST['bank_account_id']),
                'reference' => trim($_POST['reference']),
                'notes' => trim($_POST['notes']),
                'created_by' => $_SESSION['user_id']
            ];
            
            $data['total_payment'] = $data['principal_amount'] + $data['interest_amount'] + $data['bank_charges'];
            
            if ($this->loanModel->addRepayment($data)) {
                header('Location: ' . APP_URL . '/loan/show/' . $id . '?success=repayment');
            } else {
                header('Location: ' . APP_URL . '/loan/show/' . $id . '?error=failed');
            }
            exit;
        }
    }
}
