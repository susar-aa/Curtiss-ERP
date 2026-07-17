<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/Models/JournalEntry.php';
require_once dirname(__DIR__) . '/Models/PettyCashTransaction.php';
require_once dirname(__DIR__) . '/Models/AuditLog.php';

class RouteExpenseService {
    private Database $db;
    private JournalEntry $journal;
    private PettyCashTransaction $pettyCash;
    private AuditLog $audit;

    public function __construct() {
        $this->db = new Database();
        $this->journal = new JournalEntry();
        $this->pettyCash = new PettyCashTransaction();
        $this->audit = new AuditLog();
    }

    /**
     * Get available route collections cash (Cash payments received - cash expenses recorded)
     */
    public function getAvailableRouteCash(int $routeId): float {
        // Resolve all bound routes (secondary, etc.) to get accurate total cash collections
        $this->db->query("SELECT route_binding_id FROM rep_daily_routes WHERE id = :rid");
        $this->db->bind(':rid', $routeId);
        $row = $this->db->single();
        $rids = [$routeId];
        if ($row && $row->route_binding_id) {
            $this->db->query("SELECT id FROM rep_daily_routes WHERE route_binding_id = :bid");
            $this->db->bind(':bid', $row->route_binding_id);
            $rows = $this->db->resultSet() ?: [];
            foreach ($rows as $r) {
                $rids[] = intval($r->id);
            }
            $rids = array_unique($rids);
        }
        $ridsStr = implode(',', $rids);

        // Fetch sum of cash customer payments
        $this->db->query("SELECT COALESCE(SUM(amount), 0.0) as amt FROM customer_payments WHERE rep_route_id IN ($ridsStr) AND payment_method = 'Cash'");
        $paySum = floatval($this->db->single()->amt ?? 0.0);

        // Fetch sum of cash expenses recorded so far
        $this->db->query("SELECT COALESCE(SUM(amount), 0.0) as amt FROM route_expenses WHERE rep_route_id IN ($ridsStr) AND payment_source = 'Collected Cash'");
        $expSum = floatval($this->db->single()->amt ?? 0.0);

        return max(0.0, $paySum - $expSum);
    }

    /**
     * Record a new route expense, post journal entry, create PC transaction if needed
     */
    public function recordExpense(array $data, int $userId): string|bool {
        $routeId = intval($data['rep_route_id']);
        $amount = floatval($data['amount']);
        $type = trim($data['expense_type']);
        $source = trim($data['payment_source']);
        $desc = trim($data['description']);
        $receipt = !empty($data['receipt_number']) ? trim($data['receipt_number']) : null;
        $date = !empty($data['expense_date']) ? trim($data['expense_date']) : date('Y-m-d H:i:s');

        if ($routeId <= 0) return "Invalid Route ID.";
        if ($amount <= 0) return "Expense amount must be greater than zero.";
        if (empty($type)) return "Expense type is required.";
        if (empty($source)) return "Payment source is required.";

        // Retrieve route data
        $this->db->query("SELECT * FROM rep_daily_routes WHERE id = :rid");
        $this->db->bind(':rid', $routeId);
        $route = $this->db->single();
        if (!$route) return "Route not found.";

        // Fetch assigned vehicle (if any)
        $vehicleNumber = null;
        $this->db->query("SELECT vehicle_number FROM deliveries WHERE rep_route_id = :rid ORDER BY id DESC LIMIT 1");
        $this->db->bind(':rid', $routeId);
        $del = $this->db->single();
        if ($del) {
            $vehicleNumber = $del->vehicle_number;
        }

        // Fetch rep user ID
        $repUserId = intval($route->user_id);

        // Validate balances
        if ($source === 'Petty Cash') {
            $availPC = $this->pettyCash->getAvailableBalance();
            if ($amount > $availPC) {
                return "Expense amount (Rs. " . number_format($amount, 2) . ") exceeds available Petty Cash balance (Rs. " . number_format($availPC, 2) . ").";
            }
        } elseif ($source === 'Collected Cash') {
            $availRC = $this->getAvailableRouteCash($routeId);
            if ($amount > $availRC) {
                return "Expense amount (Rs. " . number_format($amount, 2) . ") exceeds available Route Collected Cash (Rs. " . number_format($availRC, 2) . ").";
            }
        } else {
            return "Invalid Payment Source.";
        }

        // Get or create Chart of Account expense account
        $expenseAccountId = $this->getOrCreateExpenseAccount($type, $vehicleNumber);
        if (!$expenseAccountId) return "Failed to map or create Chart of Accounts expense sub-account.";

        // Determine reference and description
        $ref = 'RT-EXP-' . str_pad((string)$routeId, 5, '0', STR_PAD_LEFT) . '-' . time();
        $journalDesc = "Route Expense [{$type}] for Route #RT-" . str_pad((string)$routeId, 5, '0', STR_PAD_LEFT) . " (" . ($vehicleNumber ?: 'No Vehicle') . ") - " . $desc;

        // Post Journal Entry
        $lines = [];
        $creditAccountId = 0;
        
        if ($source === 'Petty Cash') {
            $creditAccountId = $this->pettyCash->getPettyCashAccountId();
        } else {
            // Collected Cash: use Driver Transit Collections clearing account (1090)
            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1090' LIMIT 1");
            $clearingRow = $this->db->single();
            if ($clearingRow) {
                $creditAccountId = intval($clearingRow->id);
            } else {
                // Auto create 1090 if it somehow doesn't exist
                $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, balance, parent_id) 
                                  VALUES ('1090', 'Driver Transit Collections (Temp)', 'Asset', 0.00, NULL)");
                $this->db->execute();
                $creditAccountId = intval($this->db->lastInsertId());
            }
        }

        $lines[] = [
            'account_id' => $expenseAccountId,
            'debit' => $amount,
            'credit' => 0.0,
            'description' => $journalDesc
        ];
        $lines[] = [
            'account_id' => $creditAccountId,
            'debit' => 0.0,
            'credit' => $amount,
            'description' => $journalDesc
        ];

        $postRes = $this->journal->postEntry(date('Y-m-d', strtotime($date)), $ref, $journalDesc, $lines, $userId);
        if ($postRes !== true) {
            return $postRes ?: "Failed to post journal entry.";
        }

        // Get Journal Entry ID
        $this->db->query("SELECT id FROM journal_entries WHERE reference = :ref LIMIT 1");
        $this->db->bind(':ref', $ref);
        $je = $this->db->single();
        $journalEntryId = $je ? intval($je->id) : null;

        // If Petty Cash, record Petty Cash transaction
        $pettyCashTxId = null;
        if ($source === 'Petty Cash') {
            try {
                // Get Representative user details or name first to prevent overwriting active query
                $this->db->query("SELECT first_name, last_name FROM employees WHERE email = (SELECT email FROM users WHERE id = :uid)");
                $this->db->bind(':uid', $repUserId);
                $emp = $this->db->single();
                $repName = $emp ? $emp->first_name . ' ' . $emp->last_name : 'Rep User';

                $this->db->beginTransaction();
                $this->db->query("INSERT INTO petty_cash_transactions (transaction_date, type, amount, reference, description, paid_to, account_id, status, created_by, approved_by, approved_at, journal_entry_id) 
                                  VALUES (:date, 'expense', :amount, :ref, :desc, :paid_to, :acc_id, 'Approved', :uid, :uid, NOW(), :jid)");
                $this->db->bind(':date', date('Y-m-d', strtotime($date)));
                $this->db->bind(':amount', $amount);
                $this->db->bind(':ref', $ref);
                $this->db->bind(':desc', $journalDesc);
                $this->db->bind(':paid_to', $repName);
                $this->db->bind(':acc_id', $expenseAccountId);
                $this->db->bind(':uid', $userId);
                $this->db->bind(':jid', $journalEntryId);
                $this->db->execute();
                
                $pettyCashTxId = intval($this->db->lastInsertId());
                $this->db->commit();
            } catch (Exception $ex) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                return "Petty Cash Entry Error: " . $ex->getMessage();
            }
        }

        // Insert into route_expenses
        try {
            $this->db->beginTransaction();
            $this->db->query("INSERT INTO route_expenses (rep_route_id, vehicle_number, rep_user_id, expense_date, expense_type, amount, description, payment_source, receipt_number, petty_cash_transaction_id, journal_entry_id, created_by, created_at)
                              VALUES (:rep_route_id, :vehicle_number, :rep_user_id, :expense_date, :expense_type, :amount, :description, :payment_source, :receipt_number, :petty_cash_transaction_id, :journal_entry_id, :created_by, NOW())");
            
            $this->db->bind(':rep_route_id', $routeId);
            $this->db->bind(':vehicle_number', $vehicleNumber);
            $this->db->bind(':rep_user_id', $repUserId);
            $this->db->bind(':expense_date', $date);
            $this->db->bind(':expense_type', $type);
            $this->db->bind(':amount', $amount);
            $this->db->bind(':description', $desc);
            $this->db->bind(':payment_source', $source);
            $this->db->bind(':receipt_number', $receipt);
            $this->db->bind(':petty_cash_transaction_id', $pettyCashTxId);
            $this->db->bind(':journal_entry_id', $journalEntryId);
            $this->db->bind(':created_by', $userId);
            $this->db->execute();
            
            $expenseId = intval($this->db->lastInsertId());
            $this->db->commit();

            // Log Audit
            $auditDesc = "Recorded route expense [{$type}] of Rs. " . number_format($amount, 2) . " for route #RT-" . str_pad((string)$routeId, 5, '0', STR_PAD_LEFT) . " using source [{$source}].";
            $this->audit->logAction($userId, 'CREATE', 'accounting', $auditDesc, $expenseId, null, $data);

            return true;
        } catch (Exception $ex) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return "Route Expense Save Error: " . $ex->getMessage();
        }
    }

    /**
     * Delete route expense, voids journal entry, removes PC transaction
     */
    public function deleteExpense(int $expenseId, int $userId): string|bool {
        if ($expenseId <= 0) return "Invalid Expense ID.";

        $this->db->query("SELECT * FROM route_expenses WHERE id = :id");
        $this->db->bind(':id', $expenseId);
        $expense = $this->db->single();
        if (!$expense) return "Expense record not found.";

        // Void Journal Entry (which automatically reverses account balances!)
        if ($expense->journal_entry_id) {
            $voidRes = $this->journal->voidEntry(intval($expense->journal_entry_id));
            if (!$voidRes) {
                return "Failed to void linked journal entry. It may be in a closed financial year.";
            }
        }

        // Delete Petty Cash transaction if any
        if ($expense->petty_cash_transaction_id) {
            try {
                $this->db->beginTransaction();
                $this->db->query("DELETE FROM petty_cash_transactions WHERE id = :id");
                $this->db->bind(':id', $expense->petty_cash_transaction_id);
                $this->db->execute();
                $this->db->commit();
            } catch (Exception $ex) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                return "Failed to delete linked petty cash transaction.";
            }
        }

        // Delete from route_expenses
        try {
            $this->db->beginTransaction();
            $this->db->query("DELETE FROM route_expenses WHERE id = :id");
            $this->db->bind(':id', $expenseId);
            $this->db->execute();
            $this->db->commit();

            // Log Audit
            $auditDesc = "Deleted route expense [{$expense->expense_type}] of Rs. " . number_format(floatval($expense->amount), 2) . " for route #RT-" . str_pad((string)$expense->rep_route_id, 5, '0', STR_PAD_LEFT);
            $this->audit->logAction($userId, 'DELETE', 'accounting', $auditDesc, $expenseId, $expense, null);

            return true;
        } catch (Exception $ex) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return "Failed to delete route expense record: " . $ex->getMessage();
        }
    }

    /**
     * Map or create Chart of Account expense accounts dynamically
     */
    public function getOrCreateExpenseAccount(string $type, ?string $vehicleNumber = null): ?int {
        $accountName = '';
        $parentCode = '';
        $parentName = '';
        
        switch ($type) {
            case 'Fuel':
                $accountName = "Vehicle Expense - " . ($vehicleNumber ?: 'General');
                $parentCode = '6700';
                $parentName = 'Travel Transport (Fuel)';
                break;
            case 'Vehicle Maintenance':
                $accountName = "Vehicle Maintenance - " . ($vehicleNumber ?: 'General');
                $parentCode = '6800';
                $parentName = 'Vehicle Maintenance';
                break;
            case 'Meals':
                $accountName = "Meals Expense";
                $parentCode = '6100';
                $parentName = 'Staff Meals & Entertainment';
                break;
            case 'Accommodation':
                $accountName = "Accommodation Expense";
                $parentCode = '6200';
                $parentName = 'Accommodation';
                break;
            case 'Parking':
                $accountName = "Parking Expense";
                $parentCode = '6300';
                $parentName = 'Parking & Tolls';
                break;
            case 'Toll Charges':
                $accountName = "Toll Charges Expense";
                $parentCode = '6300';
                $parentName = 'Parking & Tolls';
                break;
            default:
                $accountName = "Other Route Expense";
                $parentCode = '6900';
                $parentName = 'Miscellaneous Expenses';
                break;
        }
        
        // 1. Check if account already exists
        $this->db->query("SELECT id FROM chart_of_accounts WHERE account_name = :name LIMIT 1");
        $this->db->bind(':name', $accountName);
        $row = $this->db->single();
        if ($row) {
            return intval($row->id);
        }
        
        // 2. Ensure parent exists
        $this->db->query("SELECT id, account_code FROM chart_of_accounts WHERE account_code = :code LIMIT 1");
        $this->db->bind(':code', $parentCode);
        $parent = $this->db->single();
        if (!$parent) {
            // Find parent by name
            $this->db->query("SELECT id, account_code FROM chart_of_accounts WHERE account_name = :name LIMIT 1");
            $this->db->bind(':name', $parentName);
            $parent = $this->db->single();
        }
        
        if (!$parent) {
            // Create parent
            $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, parent_id) VALUES (:code, :name, 'Expense', NULL)");
            $this->db->bind(':code', $parentCode);
            $this->db->bind(':name', $parentName);
            $this->db->execute();
            $parentId = intval($this->db->lastInsertId());
        } else {
            $parentId = intval($parent->id);
            $parentCode = $parent->account_code;
        }
        
        // 3. Create sub-account under parent
        $this->db->query("SELECT COUNT(*) as cnt FROM chart_of_accounts WHERE parent_id = :pid");
        $this->db->bind(':pid', $parentId);
        $childCount = intval($this->db->single()->cnt ?? 0);
        $subAccountCode = strval(intval($parentCode) + $childCount + 1);
        
        $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, parent_id) VALUES (:code, :name, 'Expense', :pid)");
        $this->db->bind(':code', $subAccountCode);
        $this->db->bind(':name', $accountName);
        $this->db->bind(':pid', $parentId);
        $this->db->execute();
        
        return intval($this->db->lastInsertId());
    }
}
