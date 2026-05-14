<?php
class HrmController extends Controller {
    private $employeeModel;
    private $payrollModel;
    private $coaModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->employeeModel = $this->model('Employee');
        $this->payrollModel = $this->model('Payroll');
        $this->coaModel = $this->model('ChartOfAccount');
    }

    public function index() {
        $data = [
            'title' => 'HRM & Employees',
            'content_view' => 'hrm/index',
            'employees' => $this->employeeModel->getAllEmployees(),
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_employee') {
            $empData = [
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'email' => trim($_POST['email']),
                'phone' => trim($_POST['phone']),
                'department' => trim($_POST['department']),
                'job_title' => trim($_POST['job_title']),
                'base_salary' => floatval($_POST['base_salary']),
                'hire_date' => $_POST['hire_date']
            ];

            if ($this->employeeModel->addEmployee($empData)) {
                $data['success'] = 'Employee added successfully.';
                $data['employees'] = $this->employeeModel->getAllEmployees();
            } else {
                $data['error'] = 'Failed to add employee.';
            }
        }

        $this->view('layouts/main', $data);
    }

    public function payroll() {
        $accounts = $this->coaModel->getAccounts();
        $expenses = array_filter($accounts, function($a) { return $a->account_type == 'Expense'; });
        $banks = array_filter($accounts, function($a) { return $a->account_type == 'Asset'; });
        
        $activeEmployees = $this->employeeModel->getActiveEmployees();
        $totalEstimatedGross = 0;
        foreach($activeEmployees as $emp) {
            $totalEstimatedGross += $emp->base_salary;
        }

        $data = [
            'title' => 'Payroll Processing',
            'content_view' => 'hrm/payroll',
            'payroll_runs' => $this->payrollModel->getAllPayrollRuns(),
            'expenses' => $expenses,
            'banks' => $banks,
            'active_employees_count' => count($activeEmployees),
            'estimated_gross' => $totalEstimatedGross,
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'run_payroll') {
            $periodStart = $_POST['period_start'];
            $periodEnd = $_POST['period_end'];
            $runDate = $_POST['run_date'];
            $wageExpenseAccId = $_POST['wage_expense_account_id'];
            $bankAccId = $_POST['bank_account_id'];
            
            // Using base salary for simplicity in this phase
            $gross = $totalEstimatedGross; 

            if ($gross <= 0) {
                $data['error'] = 'No active employees with a salary found.';
            } else {
                if ($this->payrollModel->processPayroll($periodStart, $periodEnd, $runDate, $gross, $wageExpenseAccId, $bankAccId, $_SESSION['user_id'])) {
                    $data['success'] = 'Payroll processed and posted to ledger successfully!';
                    $data['payroll_runs'] = $this->payrollModel->getAllPayrollRuns(); // Refresh
                } else {
                    $data['error'] = 'Database Error: Failed to process payroll.';
                }
            }
        }

        $this->view('layouts/main', $data);
    }
}