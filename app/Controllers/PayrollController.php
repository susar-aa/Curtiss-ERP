<?php
class PayrollController extends Controller {
    private $payrollModel;
    private $employeeModel;
    private $coaModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->payrollModel = $this->model('Payroll');
        $this->employeeModel = $this->model('Employee');
        $this->coaModel = $this->model('ChartOfAccount');
    }

    public function index() {
        $this->checkPermission('hrm', 'view');

        $data = [
            'title' => 'Payroll Processing',
            'content_view' => 'hrm/payroll',
            'payroll_runs' => $this->payrollModel->getAllPayrollRuns(),
            'error' => '',
            'success' => ''
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

    public function preview() {
        $this->checkPermission('hrm', 'create_edit');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $periodStart = $_POST['period_start'];
            $periodEnd = $_POST['period_end'];
            
            $previewData = $this->payrollModel->previewPayroll($periodStart, $periodEnd);
            
            $data = [
                'title' => 'Payroll Preview',
                'content_view' => 'hrm/payroll_preview',
                'preview' => $previewData,
                'run_date' => $_POST['run_date'] ?? date('Y-m-d')
            ];
            
            $this->view('layouts/main', $data);
        } else {
            header('Location: ' . APP_URL . '/payroll');
            exit;
        }
    }

    public function run() {
        $this->checkPermission('hrm', 'create_edit');
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'run_payroll') {
            $periodStart = $_POST['period_start'];
            $periodEnd = $_POST['period_end'];
            $runDate = $_POST['run_date'];
            
            // Re-calculate to save
            $previewData = $this->payrollModel->previewPayroll($periodStart, $periodEnd);
            
            if (empty($previewData['slips'])) {
                $_SESSION['flash_error'] = 'No active employees to process payroll.';
                header('Location: ' . APP_URL . '/payroll');
                exit;
            }

            $runId = $this->payrollModel->savePayrollRun($periodStart, $periodEnd, $runDate, $previewData['total_gross'], $previewData['slips'], $_SESSION['user_id']);
            
            if ($runId) {
                $this->logActivity('Payroll Created', 'HRM', "Created draft payroll for period $periodStart to $periodEnd.");
                $_SESSION['flash_success'] = 'Payroll Draft created successfully.';
                header('Location: ' . APP_URL . '/payroll/show/' . $runId);
            } else {
                $_SESSION['flash_error'] = 'Failed to create payroll draft.';
                header('Location: ' . APP_URL . '/payroll');
            }
        } else {
            header('Location: ' . APP_URL . '/payroll');
        }
        exit;
    }

    public function show($id) {
        $this->checkPermission('hrm', 'view');
        
        $run = $this->payrollModel->getPayrollRunById($id);
        if (!$run) {
            header('Location: ' . APP_URL . '/payroll');
            exit;
        }

        $slips = $this->payrollModel->getSlipsByRunId($id);
        $accounts = $this->coaModel->getAccounts();
        $expenses = array_filter($accounts, function($a) { return $a->account_type == 'Expense'; });
        $banks = array_filter($accounts, function($a) { return $a->account_type == 'Asset' && (stripos($a->account_name, 'bank') !== false || stripos($a->account_name, 'cash') !== false); });
        $liabilities = array_filter($accounts, function($a) { return $a->account_type == 'Liability'; });
        
        if (empty($banks)) {
            $banks = array_filter($accounts, function($a) { return $a->account_type == 'Asset'; });
        }

        $data = [
            'title' => 'Payroll Run Details',
            'content_view' => 'hrm/payroll_show',
            'run' => $run,
            'slips' => $slips,
            'expenses' => $expenses,
            'banks' => $banks,
            'liabilities' => $liabilities,
            'error' => '',
            'success' => ''
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
            $wageExpenseAccId = intval($_POST['wage_expense_account_id']);
            $salaryPayableAccId = intval($_POST['salary_payable_account_id']);
            
            // Find Employee Advances Account (1400)
            $empLoansAcc = $this->coaModel->getAccountByCode('1400');
            $empLoansAccId = $empLoansAcc ? $empLoansAcc->id : null;
            
            if ($this->payrollModel->approvePayroll($id, $wageExpenseAccId, $salaryPayableAccId, $empLoansAccId, $_SESSION['user_id'])) {
                $_SESSION['flash_success'] = 'Payroll Approved and Journal Entries Posted.';
            } else {
                $_SESSION['flash_error'] = 'Failed to approve payroll. Make sure it is in Draft status.';
            }
        }
        header('Location: ' . APP_URL . '/payroll/show/' . $id);
        exit;
    }

    public function pay($id) {
        $this->checkPermission('hrm', 'create_edit');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $bankAccId = intval($_POST['bank_account_id']);
            $salaryPayableAccId = intval($_POST['salary_payable_account_id']);
            $paymentDate = $_POST['payment_date'];
            
            if ($this->payrollModel->payPayroll($id, $bankAccId, $salaryPayableAccId, $paymentDate, $_SESSION['user_id'])) {
                $_SESSION['flash_success'] = 'Payroll marked as Paid. Employee loan deductions were applied.';
            } else {
                $_SESSION['flash_error'] = 'Failed to pay payroll. Make sure it is Approved first.';
            }
        }
        header('Location: ' . APP_URL . '/payroll/show/' . $id);
        exit;
    }

    public function payslip($id) {
        $this->checkPermission('hrm', 'view');
        // $id is the slip ID
        // Fetch the slip details
        $this->db = new Database();
        $this->db->query("SELECT ps.*, pr.period_start, pr.period_end, e.first_name, e.last_name, e.job_title, e.department
                          FROM payroll_slips ps
                          JOIN payroll_runs pr ON ps.payroll_run_id = pr.id
                          JOIN employees e ON ps.employee_id = e.id
                          WHERE ps.id = :id");
        $this->db->bind(':id', $id);
        $slip = $this->db->single();
        
        if (!$slip) {
            die("Payslip not found");
        }
        
        $data = [
            'title' => 'Payslip - ' . $slip->first_name . ' ' . $slip->last_name,
            'slip' => $slip
        ];
        
        $this->view('hrm/payslip_print', $data); // A dedicated print view
    }
}
