<?php
class Deposit {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAccountIdByCode($code) {
        $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = :code LIMIT 1");
        $this->db->bind(':code', $code);
        $row = $this->db->single();
        return $row ? intval($row->id) : null;
    }

    public function generateDepositNumber() {
        $this->db->query("SELECT id FROM deposits ORDER BY id DESC LIMIT 1");
        $lastRow = $this->db->single();
        $nextId = $lastRow ? ($lastRow->id + 1) : 1;
        return 'DEP-' . date('Ymd') . '-' . str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);
    }

    public function getAllDeposits() {
        $this->db->query("SELECT d.*, c.account_name as bank_name, u.username as prepared_by_name 
                          FROM deposits d
                          LEFT JOIN chart_of_accounts c ON d.destination_bank_account_id = c.id
                          LEFT JOIN users u ON d.prepared_by = u.id
                          ORDER BY d.deposit_date DESC, d.id DESC");
        return $this->db->resultSet() ?: [];
    }

    public function getDepositById($id) {
        $this->db->query("SELECT d.*, c.account_name as bank_name, c.account_code as bank_code, u.username as prepared_by_name 
                          FROM deposits d
                          LEFT JOIN chart_of_accounts c ON d.destination_bank_account_id = c.id
                          LEFT JOIN users u ON d.prepared_by = u.id
                          WHERE d.id = :id");
        $this->db->bind(':id', intval($id));
        return $this->db->single();
    }

    public function getDepositItems($depositId) {
        $this->db->query("SELECT di.*, ch.cheque_number, ch.bank_name, ch.amount as cheque_amount, ch.banking_date, cust.name as customer_name
                          FROM deposit_items di
                          LEFT JOIN cheques ch ON di.cheque_id = ch.id
                          LEFT JOIN customers cust ON ch.customer_id = cust.id
                          WHERE di.deposit_id = :did");
        $this->db->bind(':did', intval($depositId));
        return $this->db->resultSet() ?: [];
    }

    public function getPendingCheques() {
        $this->db->query("SELECT ch.*, c.name as customer_name 
                          FROM cheques ch 
                          LEFT JOIN customers c ON ch.customer_id = c.id 
                          WHERE ch.status = 'Pending' AND ch.customer_id IS NOT NULL 
                          ORDER BY ch.banking_date ASC");
        return $this->db->resultSet() ?: [];
    }

    public function createDeposit($data, $userId) {
        try {
            $this->db->beginTransaction();

            $depositNumber = $this->generateDepositNumber();
            $cashTotal = 5000 * intval($data['cash_5000'] ?? 0)
                       + 2000 * intval($data['cash_2000'] ?? 0)
                       + 1000 * intval($data['cash_1000'] ?? 0)
                       + 500 * intval($data['cash_500'] ?? 0)
                       + 100 * intval($data['cash_100'] ?? 0)
                       + 50 * intval($data['cash_50'] ?? 0)
                       + 20 * intval($data['cash_20'] ?? 0);

            // Calculate cheque total
            $chequeTotal = 0.00;
            $selectedChequeIds = $data['cheques'] ?? [];
            if (!empty($selectedChequeIds)) {
                $placeholders = implode(',', array_map(function($idx) { return ':cid_' . $idx; }, array_keys($selectedChequeIds)));
                $this->db->query("SELECT SUM(amount) as total FROM cheques WHERE id IN ($placeholders)");
                foreach ($selectedChequeIds as $idx => $cid) {
                    $this->db->bind(':cid_' . $idx, intval($cid));
                }
                $row = $this->db->single();
                $chequeTotal = $row ? floatval($row->total) : 0.00;
            }

            $totalDeposit = $cashTotal + $chequeTotal;

            // Insert into deposits
            $this->db->query("INSERT INTO deposits (
                deposit_number, deposit_date, destination_bank_account_id, status, prepared_by,
                cash_total, cheque_total, total_deposit,
                cash_5000, cash_2000, cash_1000, cash_500, cash_100, cash_50, cash_20
            ) VALUES (
                :dep_num, :dep_date, :bank_id, 'Draft', :uid,
                :cash_tot, :cheque_tot, :total_dep,
                :c5000, :c2000, :c1000, :c500, :c100, :c50, :c20
            )");
            $this->db->bind(':dep_num', $depositNumber);
            $this->db->bind(':dep_date', $data['deposit_date']);
            $this->db->bind(':bank_id', intval($data['destination_bank_account_id']));
            $this->db->bind(':uid', intval($userId));
            $this->db->bind(':cash_tot', $cashTotal);
            $this->db->bind(':cheque_tot', $chequeTotal);
            $this->db->bind(':total_dep', $totalDeposit);
            $this->db->bind(':c5000', intval($data['cash_5000'] ?? 0));
            $this->db->bind(':c2000', intval($data['cash_2000'] ?? 0));
            $this->db->bind(':c1000', intval($data['cash_1000'] ?? 0));
            $this->db->bind(':c500', intval($data['cash_500'] ?? 0));
            $this->db->bind(':c100', intval($data['cash_100'] ?? 0));
            $this->db->bind(':c50', intval($data['cash_50'] ?? 0));
            $this->db->bind(':c20', intval($data['cash_20'] ?? 0));
            $this->db->execute();
            $depositId = $this->db->lastInsertId();

            // Insert cash item if applicable
            if ($cashTotal > 0) {
                $this->db->query("INSERT INTO deposit_items (deposit_id, cheque_id, cash_amount, status) 
                                  VALUES (:did, NULL, :amt, 'Pending')");
                $this->db->bind(':did', $depositId);
                $this->db->bind(':amt', $cashTotal);
                $this->db->execute();
            }

            // Insert cheque items & update cheque statuses
            foreach ($selectedChequeIds as $cid) {
                $this->db->query("INSERT INTO deposit_items (deposit_id, cheque_id, cash_amount, status) 
                                  VALUES (:did, :cid, NULL, 'Pending')");
                $this->db->bind(':did', $depositId);
                $this->db->bind(':cid', intval($cid));
                $this->db->execute();

                // Update cheque status to Deposited to prevent double selection
                $this->db->query("UPDATE cheques SET status = 'Deposited' WHERE id = :cid");
                $this->db->bind(':cid', intval($cid));
                $this->db->execute();
            }

            $this->db->commit();
            return $depositId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("createDeposit Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateDeposit($id, $data, $userId) {
        try {
            $this->db->beginTransaction();

            $deposit = $this->getDepositById($id);
            if (!$deposit || $deposit->status !== 'Draft') {
                throw new Exception("Deposit not found or is not in Draft status.");
            }

            // 1. Revert existing cheques of this deposit back to Pending
            $this->db->query("UPDATE cheques SET status = 'Pending' 
                              WHERE id IN (SELECT cheque_id FROM deposit_items WHERE deposit_id = :did AND cheque_id IS NOT NULL)");
            $this->db->bind(':did', intval($id));
            $this->db->execute();

            // 2. Delete all existing deposit items
            $this->db->query("DELETE FROM deposit_items WHERE deposit_id = :did");
            $this->db->bind(':did', intval($id));
            $this->db->execute();

            // 3. Calculate new totals
            $cashTotal = 5000 * intval($data['cash_5000'] ?? 0)
                       + 2000 * intval($data['cash_2000'] ?? 0)
                       + 1000 * intval($data['cash_1000'] ?? 0)
                       + 500 * intval($data['cash_500'] ?? 0)
                       + 100 * intval($data['cash_100'] ?? 0)
                       + 50 * intval($data['cash_50'] ?? 0)
                       + 20 * intval($data['cash_20'] ?? 0);

            $chequeTotal = 0.00;
            $selectedChequeIds = $data['cheques'] ?? [];
            if (!empty($selectedChequeIds)) {
                $placeholders = implode(',', array_map(function($idx) { return ':cid_' . $idx; }, array_keys($selectedChequeIds)));
                $this->db->query("SELECT SUM(amount) as total FROM cheques WHERE id IN ($placeholders)");
                foreach ($selectedChequeIds as $idx => $cid) {
                    $this->db->bind(':cid_' . $idx, intval($cid));
                }
                $row = $this->db->single();
                $chequeTotal = $row ? floatval($row->total) : 0.00;
            }

            $totalDeposit = $cashTotal + $chequeTotal;

            // 4. Update deposit header
            $this->db->query("UPDATE deposits SET 
                deposit_date = :dep_date,
                destination_bank_account_id = :bank_id,
                cash_total = :cash_tot,
                cheque_total = :cheque_tot,
                total_deposit = :total_dep,
                cash_5000 = :c5000,
                cash_2000 = :c2000,
                cash_1000 = :c1000,
                cash_500 = :c500,
                cash_100 = :c100,
                cash_50 = :c50,
                cash_20 = :c20
                WHERE id = :id");
            $this->db->bind(':id', intval($id));
            $this->db->bind(':dep_date', $data['deposit_date']);
            $this->db->bind(':bank_id', intval($data['destination_bank_account_id']));
            $this->db->bind(':cash_tot', $cashTotal);
            $this->db->bind(':cheque_tot', $chequeTotal);
            $this->db->bind(':total_dep', $totalDeposit);
            $this->db->bind(':c5000', intval($data['cash_5000'] ?? 0));
            $this->db->bind(':c2000', intval($data['cash_2000'] ?? 0));
            $this->db->bind(':c1000', intval($data['cash_1000'] ?? 0));
            $this->db->bind(':c500', intval($data['cash_500'] ?? 0));
            $this->db->bind(':c100', intval($data['cash_100'] ?? 0));
            $this->db->bind(':c50', intval($data['cash_50'] ?? 0));
            $this->db->bind(':c20', intval($data['cash_20'] ?? 0));
            $this->db->execute();

            // 5. Insert items and update cheques
            if ($cashTotal > 0) {
                $this->db->query("INSERT INTO deposit_items (deposit_id, cheque_id, cash_amount, status) 
                                  VALUES (:did, NULL, :amt, 'Pending')");
                $this->db->bind(':did', intval($id));
                $this->db->bind(':amt', $cashTotal);
                $this->db->execute();
            }

            foreach ($selectedChequeIds as $cid) {
                $this->db->query("INSERT INTO deposit_items (deposit_id, cheque_id, cash_amount, status) 
                                  VALUES (:did, :cid, NULL, 'Pending')");
                $this->db->bind(':did', intval($id));
                $this->db->bind(':cid', intval($cid));
                $this->db->execute();

                $this->db->query("UPDATE cheques SET status = 'Deposited' WHERE id = :cid");
                $this->db->bind(':cid', intval($cid));
                $this->db->execute();
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("updateDeposit Error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteDeposit($id) {
        try {
            $this->db->beginTransaction();

            $deposit = $this->getDepositById($id);
            if (!$deposit || $deposit->status !== 'Draft') {
                throw new Exception("Deposit not found or is not in Draft status.");
            }

            // Revert cheques back to Pending
            $this->db->query("UPDATE cheques SET status = 'Pending' 
                              WHERE id IN (SELECT cheque_id FROM deposit_items WHERE deposit_id = :did AND cheque_id IS NOT NULL)");
            $this->db->bind(':did', intval($id));
            $this->db->execute();

            // Delete deposit header (cascades to items)
            $this->db->query("DELETE FROM deposits WHERE id = :id");
            $this->db->bind(':id', intval($id));
            $this->db->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("deleteDeposit Error: " . $e->getMessage());
            return false;
        }
    }

    public function sendToBank($id, $userId) {
        $deposit = $this->getDepositById($id);
        if (!$deposit || $deposit->status !== 'Draft') {
            return "Error: Deposit not found or is not in Draft status.";
        }

        // Retrieve Chart of Account IDs
        $cashInHandId = $this->getAccountIdByCode('1000');
        $chequeInHandId = $this->getAccountIdByCode('1010');
        $transitCashId = $this->getAccountIdByCode('1030');
        $transitChequesId = $this->getAccountIdByCode('1040');

        if (!$cashInHandId || !$chequeInHandId || !$transitCashId || !$transitChequesId) {
            return "Error: Required transit accounts or cash/cheque sub-ledger accounts are missing in the Chart of Accounts.";
        }

        // Post Journal Entry
        $lines = [];
        if ($deposit->cash_total > 0) {
            $lines[] = [
                'account_id' => $transitCashId,
                'debit' => $deposit->cash_total,
                'credit' => 0.00,
                'description' => 'Cash Deposit in Transit'
            ];
            $lines[] = [
                'account_id' => $cashInHandId,
                'debit' => 0.00,
                'credit' => $deposit->cash_total,
                'description' => 'Cash Sent to Bank'
            ];
        }

        if ($deposit->cheque_total > 0) {
            $lines[] = [
                'account_id' => $transitChequesId,
                'debit' => $deposit->cheque_total,
                'credit' => 0.00,
                'description' => 'Cheque Deposit in Transit'
            ];
            $lines[] = [
                'account_id' => $chequeInHandId,
                'debit' => 0.00,
                'credit' => $deposit->cheque_total,
                'description' => 'Cheques Sent to Bank'
            ];
        }

        if (empty($lines)) {
            return "Error: Deposit total must be greater than zero.";
        }

        require_once __DIR__ . '/JournalEntry.php';
        $jeModel = new JournalEntry();
        
        $desc = "Deposit sent to bank: " . $deposit->deposit_number;
        $result = $jeModel->postEntry(
            $deposit->deposit_date,
            $deposit->deposit_number,
            $desc,
            $lines,
            intval($userId)
        );

        if ($result === true) {
            try {
                $this->db->beginTransaction();

                // Get the inserted journal entry ID
                $this->db->query("SELECT id FROM journal_entries WHERE reference = :ref LIMIT 1");
                $this->db->bind(':ref', $deposit->deposit_number);
                $jeRow = $this->db->single();
                $jeId = $jeRow ? intval($jeRow->id) : null;

                // Update Deposit Status
                $this->db->query("UPDATE deposits SET status = 'Sent to Bank', journal_entry_id = :je_id WHERE id = :id");
                $this->db->bind(':je_id', $jeId);
                $this->db->bind(':id', intval($id));
                $this->db->execute();

                $this->db->commit();
                return true;
            } catch (Exception $e) {
                $this->db->rollBack();
                return "Database Error: " . $e->getMessage();
            }
        }

        return $result; // Returns the error string
    }

    public function processDeposit($id, $processedData, $userId) {
        try {
            $deposit = $this->getDepositById($id);
            if (!$deposit || $deposit->status !== 'Sent to Bank') {
                return "Error: Deposit not found or not in Sent to Bank status.";
            }

            $items = $this->getDepositItems($id);
            if (empty($items)) {
                return "Error: No items found in this deposit.";
            }

            // Retrieve account IDs
            $transitCashId = $this->getAccountIdByCode('1030');
            $transitChequesId = $this->getAccountIdByCode('1040');
            $arAccountId = $this->getAccountIdByCode('1200'); // Accounts Receivable Code 1200
            $destinationBankId = intval($deposit->destination_bank_account_id);

            if (!$transitCashId || !$transitChequesId || !$arAccountId) {
                return "Error: Required transit accounts are missing in the Chart of Accounts.";
            }

            $this->db->beginTransaction();

            $acceptedCash = 0.00;
            $clearedChequesTotal = 0.00;
            $clearedChequeIds = [];

            // We will loop through items and process them
            foreach ($items as $item) {
                if ($item->cheque_id === null) {
                    // Cash Item
                    $acceptedCash = floatval($processedData['accepted_cash_amount'] ?? $item->cash_amount);
                    
                    $this->db->query("UPDATE deposit_items 
                                      SET status = 'Passed', cash_amount = :accepted 
                                      WHERE id = :iid");
                    $this->db->bind(':accepted', $acceptedCash);
                    $this->db->bind(':iid', $item->id);
                    $this->db->execute();
                } else {
                    // Cheque Item
                    $chequeId = intval($item->cheque_id);
                    $action = $processedData['cheque_action'][$chequeId] ?? 'Clear';
                    $reason = trim($processedData['rejection_reason'][$chequeId] ?? '');

                    if ($action === 'Clear') {
                        // Clear Cheque
                        $this->db->query("UPDATE deposit_items SET status = 'Passed' WHERE id = :iid");
                        $this->db->bind(':iid', $item->id);
                        $this->db->execute();

                        $this->db->query("UPDATE cheques SET status = 'Cleared' WHERE id = :cid");
                        $this->db->bind(':cid', $chequeId);
                        $this->db->execute();

                        $clearedChequesTotal += floatval($item->cheque_amount);
                        $clearedChequeIds[] = $chequeId;
                    } else {
                        // Return or Reject Cheque
                        $statusText = ($action === 'Reject') ? 'Rejected' : 'Returned';
                        
                        $this->db->query("UPDATE deposit_items 
                                          SET status = :status, rejection_reason = :reason 
                                          WHERE id = :iid");
                        $this->db->bind(':status', $statusText);
                        $this->db->bind(':reason', $reason ?: 'Returned by bank');
                        $this->db->bind(':iid', $item->id);
                        $this->db->execute();

                        $this->db->query("UPDATE cheques SET status = :status WHERE id = :cid");
                        $this->db->bind(':status', $statusText);
                        $this->db->bind(':cid', $chequeId);
                        $this->db->execute();

                        // REVERSE CUSTOMER PAYMENT & Ledger
                        // 1. Fetch corresponding customer payment record
                        // We query by amount, customer, payment method, and matching cheque details
                        $this->db->query("SELECT p.* FROM customer_payments p
                                          JOIN cheques ch ON ch.customer_id = p.customer_id AND ch.amount = p.amount AND ABS(TIMESTAMPDIFF(SECOND, ch.created_at, p.created_at)) < 60
                                          WHERE ch.id = :cid AND p.status = 'Active' LIMIT 1");
                        $this->db->bind(':cid', $chequeId);
                        $payment = $this->db->single();

                        if ($payment) {
                            // Mark payment as Bounced
                            $this->db->query("UPDATE customer_payments 
                                              SET status = 'Bounced', reversed_by = :uid, reversed_at = CURRENT_TIMESTAMP() 
                                              WHERE id = :pid");
                            $this->db->bind(':uid', intval($userId));
                            $this->db->bind(':pid', intval($payment->id));
                            $this->db->execute();

                            // Reverse payment allocations
                            $this->db->query("SELECT invoice_id FROM customer_payment_allocations 
                                              WHERE customer_payment_id = :pid AND is_reversed = 0");
                            $this->db->bind(':pid', intval($payment->id));
                            $allocs = $this->db->resultSet() ?: [];

                            $this->db->query("UPDATE customer_payment_allocations 
                                              SET is_reversed = 1, reversed_by = :uid, reversed_at = CURRENT_TIMESTAMP() 
                                              WHERE customer_payment_id = :pid");
                            $this->db->bind(':uid', intval($userId));
                            $this->db->bind(':pid', intval($payment->id));
                            $this->db->execute();

                            // Revert invoices to Unpaid
                            foreach ($allocs as $a) {
                                $this->db->query("UPDATE invoices SET status = 'Unpaid' WHERE id = :id");
                                $this->db->bind(':id', intval($a->invoice_id));
                                $this->db->execute();
                            }

                            // Post Reversal Journal Entry: Debit Accounts Receivable (1200), Credit Deposit in Transit - Cheques (1040)
                            // This cancels the transit balance and puts the debt back on the customer.
                            $revRef = 'REV-' . $payment->reference;
                            $revDesc = "Reversal: Cheque Returned/Rejected. Cheque #" . $item->cheque_number;
                            
                            $revLines = [
                                [
                                    'account_id' => $arAccountId,
                                    'debit' => floatval($item->cheque_amount),
                                    'credit' => 0.00,
                                    'description' => $revDesc
                                ],
                                [
                                    'account_id' => $transitChequesId,
                                    'debit' => 0.00,
                                    'credit' => floatval($item->cheque_amount),
                                    'description' => $revDesc
                                ]
                            ];

                            // We call postEntry here but wait! We are in a transaction.
                            // To prevent nested transaction issues, we post the entry AFTER this transaction commits, OR we do it manually.
                            // Let's do it manually since we are already inside a database transaction here!
                            // Doing it manually is extremely safe and keeps everything in a single atomic transaction block!
                            
                            $this->db->query("INSERT INTO journal_entries (entry_date, reference, description, created_by, status) 
                                              VALUES (CURRENT_DATE(), :ref, :desc, :uid, 'Posted')");
                            $this->db->bind(':ref', $revRef);
                            $this->db->bind(':desc', $revDesc);
                            $this->db->bind(':uid', intval($userId));
                            $this->db->execute();
                            $revJournalId = $this->db->lastInsertId();

                            foreach ($revLines as $line) {
                                $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit, description) 
                                                  VALUES (:jid, :aid, :deb, :cred, :desc)");
                                $this->db->bind(':jid', $revJournalId);
                                $this->db->bind(':aid', $line['account_id']);
                                $this->db->bind(':deb', $line['debit']);
                                $this->db->bind(':cred', $line['credit']);
                                $this->db->bind(':desc', $line['description']);
                                $this->db->execute();

                                // Update balance
                                $this->db->query("SELECT account_type FROM chart_of_accounts WHERE id = :id FOR UPDATE");
                                $this->db->bind(':id', $line['account_id']);
                                $account = $this->db->single();

                                $balanceUpdateSql = "UPDATE chart_of_accounts SET balance = balance ";
                                if (in_array($account->account_type, ['Asset', 'Expense'])) {
                                    $balanceUpdateSql .= "+ :debit - :credit ";
                                } else {
                                    $balanceUpdateSql .= "- :debit + :credit ";
                                }
                                $balanceUpdateSql .= "WHERE id = :id";

                                $this->db->query($balanceUpdateSql);
                                $this->db->bind(':debit', $line['debit']);
                                $this->db->bind(':credit', $line['credit']);
                                $this->db->bind(':id', $line['account_id']);
                                $this->db->execute();
                            }
                        }
                    }
                }
            }

            // Post Realization Journal Entry for cleared cash & cheques
            $clearedTotal = $acceptedCash + $clearedChequesTotal;
            if ($clearedTotal > 0) {
                $realRef = $deposit->deposit_number . '-REAL';
                $realDesc = "Realization of Deposit: " . $deposit->deposit_number;

                $realLines = [];
                $realLines[] = [
                    'account_id' => $destinationBankId,
                    'debit' => $clearedTotal,
                    'credit' => 0.00,
                    'description' => 'Funds deposited to bank'
                ];

                if ($acceptedCash > 0) {
                    $realLines[] = [
                        'account_id' => $transitCashId,
                        'debit' => 0.00,
                        'credit' => $acceptedCash,
                        'description' => 'Realization of Cash Transit'
                    ];
                }

                if ($clearedChequesTotal > 0) {
                    $realLines[] = [
                        'account_id' => $transitChequesId,
                        'debit' => 0.00,
                        'credit' => $clearedChequesTotal,
                        'description' => 'Realization of Cheques Transit'
                    ];
                }

                // Insert journal entry manually under current transaction
                $this->db->query("INSERT INTO journal_entries (entry_date, reference, description, created_by, status) 
                                  VALUES (:date, :ref, :desc, :uid, 'Posted')");
                $this->db->bind(':date', $deposit->deposit_date);
                $this->db->bind(':ref', $realRef);
                $this->db->bind(':desc', $realDesc);
                $this->db->bind(':uid', intval($userId));
                $this->db->execute();
                $realJournalId = $this->db->lastInsertId();

                foreach ($realLines as $line) {
                    $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit, description) 
                                      VALUES (:jid, :aid, :deb, :cred, :desc)");
                    $this->db->bind(':jid', $realJournalId);
                    $this->db->bind(':aid', $line['account_id']);
                    $this->db->bind(':deb', $line['debit']);
                    $this->db->bind(':cred', $line['credit']);
                    $this->db->bind(':desc', $line['description']);
                    $this->db->execute();

                    // Update balance
                    $this->db->query("SELECT account_type FROM chart_of_accounts WHERE id = :id FOR UPDATE");
                    $this->db->bind(':id', $line['account_id']);
                    $account = $this->db->single();

                    $balanceUpdateSql = "UPDATE chart_of_accounts SET balance = balance ";
                    if (in_array($account->account_type, ['Asset', 'Expense'])) {
                        $balanceUpdateSql .= "+ :debit - :credit ";
                    } else {
                        $balanceUpdateSql .= "- :debit + :credit ";
                    }
                    $balanceUpdateSql .= "WHERE id = :id";

                    $this->db->query($balanceUpdateSql);
                    $this->db->bind(':debit', $line['debit']);
                    $this->db->bind(':credit', $line['credit']);
                    $this->db->bind(':id', $line['account_id']);
                    $this->db->execute();
                }

                // Store realization journal entry ID in deposit
                $this->db->query("UPDATE deposits SET realization_journal_entry_id = :rje_id WHERE id = :id");
                $this->db->bind(':rje_id', $realJournalId);
                $this->db->bind(':id', intval($id));
                $this->db->execute();
            }

            // Update Deposit header status to Completed
            $this->db->query("UPDATE deposits SET 
                status = 'Completed', 
                accepted_cash_amount = :accepted_cash, 
                approval_remarks = :remarks 
                WHERE id = :id");
            $this->db->bind(':accepted_cash', $acceptedCash);
            $this->db->bind(':remarks', trim($processedData['approval_remarks'] ?? ''));
            $this->db->bind(':id', intval($id));
            $this->db->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("processDeposit Error: " . $e->getMessage());
            return "Database Error: " . $e->getMessage();
        }
    }
}
