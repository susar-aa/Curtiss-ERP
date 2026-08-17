<?php
class EmployeeLoanController extends Controller {
    private $employeeLoanModel;
    private $employeeModel;
    private $coaModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        
        $this->employeeLoanModel = $this->model('EmployeeLoan');
        $this->employeeModel = $this->model('Employee');
        $this->coaModel = $this->model('ChartOfAccount');
    }

    public function index() {
        $this->checkPermission('hrm', 'view');
        $data = [
            'title' => 'Employee Loans',
            'content_view' => 'employee_loans/index',
            'loans' => $this->employeeLoanModel->getAllLoans()
        ];
        
        if (isset($_SESSION['flash_success'])) {
            $data['success'] = $_SESSION['flash_success'];
            unset($_SESSION['flash_success']);
        }
        if (isset($_SESSION['flash_error'])) {
            $data['error'] = $_SESSION['flash_error'];
            unset($_SESSION['flash_error']);
        }

        $this->view('layouts/main', $data);
    }

    public function create() {
        $this->checkPermission('hrm', 'create_edit');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'employee_id' => intval($_POST['employee_id']),
                'loan_number' => trim($_POST['loan_number']),
                'principal_amount' => floatval($_POST['principal_amount']),
                'interest_rate' => floatval($_POST['interest_rate'] ?? 0),
                'loan_start_date' => $_POST['loan_start_date'],
                'loan_term_months' => intval($_POST['loan_term_months']),
                'repayment_frequency' => $_POST['repayment_frequency'],
                'repayment_amount' => floatval($_POST['repayment_amount']),
                'notes' => trim($_POST['notes'])
            ];
            
            if ($this->employeeLoanModel->addLoan($data)) {
                $_SESSION['flash_success'] = 'Employee loan application created successfully.';
                header('Location: ' . APP_URL . '/employeeloan');
                exit;
            } else {
                $_SESSION['flash_error'] = 'Failed to create employee loan application.';
                header('Location: ' . APP_URL . '/employeeloan/create');
                exit;
            }
        } else {
            $data = [
                'title' => 'New Employee Loan',
                'content_view' => 'employee_loans/create',
                'employees' => $this->employeeModel->getActiveEmployees()
            ];
            $this->view('layouts/main', $data);
        }
    }

    public function show($id) {
        $this->checkPermission('hrm', 'view');
        $loan = $this->employeeLoanModel->getLoanById($id);
        if (!$loan) {
            $_SESSION['flash_error'] = 'Employee loan not found.';
            header('Location: ' . APP_URL . '/employeeloan');
            exit;
        }
        
        $data = [
            'title' => 'Employee Loan Details: ' . htmlspecialchars($loan->employee_name),
            'content_view' => 'employee_loans/show',
            'loan' => $loan,
            'repayments' => $this->employeeLoanModel->getRepayments($id),
            'banks' => array_filter($this->coaModel->getAccounts(), function($a) { return $a->account_type == 'Asset'; })
        ];

        if (isset($_SESSION['flash_success'])) {
            $data['success'] = $_SESSION['flash_success'];
            unset($_SESSION['flash_success']);
        }
        if (isset($_SESSION['flash_error'])) {
            $data['error'] = $_SESSION['flash_error'];
            unset($_SESSION['flash_error']);
        }

        $this->view('layouts/main', $data);
    }

    public function approve($id) {
        $this->checkPermission('hrm', 'create_edit');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->employeeLoanModel->updateStatus($id, 'Approved')) {
                $_SESSION['flash_success'] = 'Loan approved successfully.';
            } else {
                $_SESSION['flash_error'] = 'Failed to approve loan.';
            }
        }
        header('Location: ' . APP_URL . '/employeeloan/show/' . $id);
        exit;
    }

    public function disburse($id) {
        $this->checkPermission('hrm', 'create_edit');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $loan = $this->employeeLoanModel->getLoanById($id);
            if ($loan && $loan->status == 'Approved') {
                $bankAccountId = intval($_POST['bank_account_id']);
                
                // Accounting for Employee Loan Disbursement (Company gives money to Employee)
                $employeeLoansAcc = $this->coaModel->getAccountByCode('1400'); // Assuming 1400 is Employee Advances
                if (!$employeeLoansAcc) {
                    $this->coaModel->addAccount(['account_code' => '1400', 'account_name' => 'Employee Advances', 'account_type' => 'Asset', 'account_category' => 'Current Assets']);
                    $employeeLoansAcc = $this->coaModel->getAccountByCode('1400');
                }

                $desc = "Employee Loan Disbursement: " . $loan->employee_name;
                $reference = "EL-DISB-" . $loan->id;
                
                $lines = [
                    ['account_id' => $employeeLoansAcc->id, 'debit' => $loan->principal_amount, 'credit' => 0, 'description' => 'Loan Given'],
                    ['account_id' => $bankAccountId, 'debit' => 0, 'credit' => $loan->principal_amount, 'description' => 'Bank Payment']
                ];

                require_once __DIR__ . '/../Models/JournalEntry.php';
                $journalModel = new JournalEntry();

                $postResult = $journalModel->postEntry(date('Y-m-d'), $reference, $desc, $lines, $_SESSION['user_id']);
                
                if ($postResult === true) {
                    $this->employeeLoanModel->updateStatus($id, 'Active');
                    $_SESSION['flash_success'] = 'Loan disbursed and journal entry created successfully.';
                } else {
                    $_SESSION['flash_error'] = 'Failed to post journal entry.';
                }
            }
        }
        header('Location: ' . APP_URL . '/employeeloan/show/' . $id);
        exit;
    }

    public function repay($id) {
        $this->checkPermission('hrm', 'create_edit');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $loan = $this->employeeLoanModel->getLoanById($id);
            if ($loan && $loan->status == 'Active') {
                $bankAccountId = intval($_POST['bank_account_id']);
                $principal = floatval($_POST['principal_amount']);
                $interest = floatval($_POST['interest_amount'] ?? 0);
                
                // Manual repayment directly to Bank
                $employeeLoansAcc = $this->coaModel->getAccountByCode('1400');
                
                $desc = "Manual Loan Repayment: " . $loan->employee_name;
                $reference = "EL-REP-" . time();
                
                $lines = [
                    ['account_id' => $bankAccountId, 'debit' => ($principal + $interest), 'credit' => 0, 'description' => 'Bank Receipt'],
                    ['account_id' => $employeeLoansAcc->id, 'debit' => 0, 'credit' => $principal, 'description' => 'Loan Principal Repayment']
                ];

                require_once __DIR__ . '/../Models/JournalEntry.php';
                $journalModel = new JournalEntry();
                $postResult = $journalModel->postEntry(date('Y-m-d'), $reference, $desc, $lines, $_SESSION['user_id']);

                if ($postResult === true) {
                    $this->employeeLoanModel->addRepayment([
                        'employee_loan_id' => $loan->id,
                        'payment_date' => $_POST['payment_date'],
                        'principal_amount' => $principal,
                        'interest_amount' => $interest,
                        'notes' => trim($_POST['notes']),
                        'created_by' => $_SESSION['user_id']
                    ]);
                    $_SESSION['flash_success'] = 'Manual repayment processed successfully.';
                } else {
                    $_SESSION['flash_error'] = 'Failed to post journal entry.';
                }
            }
        }
        header('Location: ' . APP_URL . '/employeeloan/show/' . $id);
        exit;
    }

    public function delete($id) {
        $this->checkPermission('hrm', 'delete');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->employeeLoanModel->deleteLoan($id)) {
                $_SESSION['flash_success'] = 'Employee loan application deleted.';
            } else {
                $_SESSION['flash_error'] = 'Cannot delete loan because it is not in Pending status.';
            }
        }
        header('Location: ' . APP_URL . '/employeeloan');
        exit;
    }
}
