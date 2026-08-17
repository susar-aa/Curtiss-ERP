<?php
declare(strict_types=1);

class Payroll {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllPayrollRuns(): array {
        $this->db->query("SELECT p.*, u.username 
                          FROM payroll_runs p 
                          LEFT JOIN users u ON p.created_by = u.id 
                          ORDER BY p.run_date DESC");
        return $this->db->resultSet() ?: [];
    }

    public function getPayrollRunById($id) {
        $this->db->query("SELECT p.*, u.username 
                          FROM payroll_runs p 
                          LEFT JOIN users u ON p.created_by = u.id 
                          WHERE p.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getSlipsByRunId($runId) {
        $this->db->query("SELECT ps.*, e.first_name, e.last_name, e.employee_code, e.department, e.job_title
                          FROM payroll_slips ps
                          JOIN employees e ON ps.employee_id = e.id
                          WHERE ps.payroll_run_id = :run_id");
        $this->db->bind(':run_id', $runId);
        return $this->db->resultSet() ?: [];
    }

    public function previewPayroll($periodStart, $periodEnd) {
        // Fetch all active employees
        $this->db->query("SELECT * FROM employees WHERE status = 'Active'");
        $employees = $this->db->resultSet();

        $slips = [];
        $totalGross = 0;
        $totalNet = 0;
        $totalDeductions = 0;

        foreach ($employees as $emp) {
            $base = floatval($emp->base_salary);
            // Default components for now, can be expanded to fetch from employee contract
            $allowances = 0;
            $commissions = 0;
            $overtime = 0;
            $other_deductions = 0;

            // Calculate active loan deductions
            $loan_deduction = 0;
            $this->db->query("SELECT id, principal_amount, repayment_amount,
                             COALESCE((SELECT SUM(principal_amount) FROM employee_loan_repayments WHERE employee_loan_id = el.id), 0) as paid
                             FROM employee_loans el
                             WHERE employee_id = :emp_id AND status = 'Active'");
            $this->db->bind(':emp_id', $emp->id);
            $loans = $this->db->resultSet();

            $loanDeductionDetails = [];
            foreach ($loans as $loan) {
                $balance = floatval($loan->principal_amount) - floatval($loan->paid);
                if ($balance > 0) {
                    $deduction = min(floatval($loan->repayment_amount), $balance);
                    $loan_deduction += $deduction;
                    $loanDeductionDetails[] = [
                        'loan_id' => $loan->id,
                        'amount' => $deduction
                    ];
                }
            }

            $gross = $base + $allowances + $commissions + $overtime;
            $net = $gross - $loan_deduction - $other_deductions;

            $totalGross += $gross;
            $totalNet += $net;
            $totalDeductions += $loan_deduction + $other_deductions;

            $slips[] = [
                'employee_id' => $emp->id,
                'employee_name' => $emp->first_name . ' ' . $emp->last_name,
                'base_salary' => $base,
                'allowances' => $allowances,
                'commissions' => $commissions,
                'overtime' => $overtime,
                'loan_deduction' => $loan_deduction,
                'other_deductions' => $other_deductions,
                'gross_salary' => $gross,
                'net_salary' => $net,
                'loan_details' => $loanDeductionDetails
            ];
        }

        return [
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'total_gross' => $totalGross,
            'total_net' => $totalNet,
            'total_deductions' => $totalDeductions,
            'slips' => $slips
        ];
    }

    public function savePayrollRun($periodStart, $periodEnd, $runDate, $totalGross, $slips, $userId) {
        try {
            $this->db->beginTransaction();

            // Insert Run
            $this->db->query("INSERT INTO payroll_runs (run_date, period_start, period_end, total_gross, created_by, status) 
                              VALUES (:rdate, :pstart, :pend, :gross, :uid, 'Draft')");
            $this->db->bind(':rdate', $runDate);
            $this->db->bind(':pstart', $periodStart);
            $this->db->bind(':pend', $periodEnd);
            $this->db->bind(':gross', $totalGross);
            $this->db->bind(':uid', $userId);
            $this->db->execute();
            $runId = $this->db->lastInsertId();

            // Insert Slips
            foreach ($slips as $s) {
                $this->db->query("INSERT INTO payroll_slips 
                    (payroll_run_id, employee_id, basic_salary, allowances, commissions, overtime, loan_deduction, other_deductions, gross_salary, net_salary, status)
                    VALUES (:run_id, :emp_id, :base, :allowances, :commissions, :overtime, :loan_ded, :other_ded, :gross, :net, 'Draft')");
                
                $this->db->bind(':run_id', $runId);
                $this->db->bind(':emp_id', $s['employee_id']);
                $this->db->bind(':base', $s['base_salary']);
                $this->db->bind(':allowances', $s['allowances']);
                $this->db->bind(':commissions', $s['commissions']);
                $this->db->bind(':overtime', $s['overtime']);
                $this->db->bind(':loan_ded', $s['loan_deduction']);
                $this->db->bind(':other_ded', $s['other_deductions']);
                $this->db->bind(':gross', $s['gross_salary']);
                $this->db->bind(':net', $s['net_salary']);
                $this->db->execute();

                // Store loan deduction details temporarily if we want to link them later?
                // For simplicity, we calculate them again on pay, or we could store JSON in other_deductions/notes.
            }

            $this->db->commit();
            return $runId;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function approvePayroll($runId, $wageExpenseAccId, $salaryPayableAccId, $employeeLoansAccId, $userId) {
        $run = $this->getPayrollRunById($runId);
        if (!$run || $run->status != 'Draft') return false;

        $slips = $this->getSlipsByRunId($runId);
        
        $totalGross = 0;
        $totalNet = 0;
        $totalLoanDeductions = 0;

        foreach ($slips as $s) {
            $totalGross += floatval($s->gross_salary);
            $totalNet += floatval($s->net_salary);
            $totalLoanDeductions += floatval($s->loan_deduction);
        }

        try {
            $this->db->beginTransaction();

            $desc = "Payroll Approval: " . date('M d', strtotime($run->period_start)) . " to " . date('M d, Y', strtotime($run->period_end));
            $reference = "PR-APP-" . $runId;
            
            $lines = [
                ['account_id' => $wageExpenseAccId, 'debit' => $totalGross, 'credit' => 0, 'description' => 'Wage Expense'],
                ['account_id' => $salaryPayableAccId, 'debit' => 0, 'credit' => $totalNet, 'description' => 'Salary Payable (Net)']
            ];

            if ($totalLoanDeductions > 0 && $employeeLoansAccId) {
                $lines[] = ['account_id' => $employeeLoansAccId, 'debit' => 0, 'credit' => $totalLoanDeductions, 'description' => 'Employee Loan Deductions'];
            } else if ($totalLoanDeductions > 0 && !$employeeLoansAccId) {
                // Fallback to salary payable if no loan account defined
                $lines[1]['credit'] += $totalLoanDeductions;
            }

            require_once APP_ROOT . '/app/Models/JournalEntry.php';
            $journalModel = new JournalEntry();

            $postResult = $journalModel->postEntry(date('Y-m-d'), $reference, $desc, $lines, $userId);
            if ($postResult !== true) {
                throw new Exception("Failed to post journal entry");
            }

            $this->db->query("SELECT id FROM journal_entries WHERE reference = :ref");
            $this->db->bind(':ref', $reference);
            $jeRow = $this->db->single();
            $journalId = $jeRow ? intval($jeRow->id) : null;

            $this->db->query("UPDATE payroll_runs SET status = 'Approved', journal_entry_id = :jid WHERE id = :id");
            $this->db->bind(':jid', $journalId);
            $this->db->bind(':id', $runId);
            $this->db->execute();

            $this->db->query("UPDATE payroll_slips SET status = 'Approved' WHERE payroll_run_id = :id");
            $this->db->bind(':id', $runId);
            $this->db->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function payPayroll($runId, $bankAccId, $salaryPayableAccId, $paymentDate, $userId) {
        $run = $this->getPayrollRunById($runId);
        if (!$run || $run->status != 'Approved') return false;

        $slips = $this->getSlipsByRunId($runId);
        $totalNet = 0;

        foreach ($slips as $s) {
            $totalNet += floatval($s->net_salary);
        }

        try {
            $this->db->beginTransaction();

            $desc = "Payroll Payment: " . date('M d', strtotime($run->period_start)) . " to " . date('M d, Y', strtotime($run->period_end));
            $reference = "PR-PAY-" . $runId;
            
            $lines = [
                ['account_id' => $salaryPayableAccId, 'debit' => $totalNet, 'credit' => 0, 'description' => 'Salary Payable Settlement'],
                ['account_id' => $bankAccId, 'debit' => 0, 'credit' => $totalNet, 'description' => 'Bank Payment']
            ];

            require_once APP_ROOT . '/app/Models/JournalEntry.php';
            $journalModel = new JournalEntry();

            $postResult = $journalModel->postEntry($paymentDate, $reference, $desc, $lines, $userId);
            if ($postResult !== true) {
                throw new Exception("Failed to post journal entry");
            }

            $this->db->query("UPDATE payroll_runs SET status = 'Paid' WHERE id = :id");
            $this->db->bind(':id', $runId);
            $this->db->execute();

            $this->db->query("UPDATE payroll_slips SET status = 'Paid', payment_date = :pdate WHERE payroll_run_id = :id");
            $this->db->bind(':pdate', $paymentDate);
            $this->db->bind(':id', $runId);
            $this->db->execute();

            // Process Loan Repayments
            require_once APP_ROOT . '/app/Models/EmployeeLoan.php';
            $loanModel = new EmployeeLoan();

            foreach ($slips as $s) {
                if (floatval($s->loan_deduction) > 0) {
                    $deductionLeft = floatval($s->loan_deduction);
                    $activeLoans = $loanModel->getActiveLoansByEmployeeId($s->employee_id);
                    foreach ($activeLoans as $loan) {
                        if ($deductionLeft <= 0) break;
                        $balance = $loan->principal_balance;
                        if ($balance > 0) {
                            $payAmount = min($balance, $deductionLeft);
                            $loanModel->addRepayment([
                                'employee_loan_id' => $loan->id,
                                'payroll_slip_id' => $s->id,
                                'payment_date' => $paymentDate,
                                'principal_amount' => $payAmount,
                                'interest_amount' => 0,
                                'notes' => 'Payroll Auto Deduction',
                                'created_by' => $userId
                            ]);
                            $deductionLeft -= $payAmount;
                        }
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }
}