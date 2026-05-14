<?php
class Invoice {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllInvoices() {
        $this->db->query("SELECT i.*, c.name as customer_name 
                          FROM invoices i 
                          JOIN customers c ON i.customer_id = c.id 
                          ORDER BY i.created_at DESC");
        return $this->db->resultSet();
    }

    public function getInvoiceById($id) {
        $this->db->query("SELECT i.*, c.name as customer_name, c.email, c.phone, c.address, 
                                 t.tax_name, t.rate_percentage 
                          FROM invoices i 
                          JOIN customers c ON i.customer_id = c.id 
                          LEFT JOIN tax_rates t ON i.tax_rate_id = t.id
                          WHERE i.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getInvoiceItems($id) {
        $this->db->query("SELECT * FROM invoice_items WHERE invoice_id = :id");
        $this->db->bind(':id', $id);
        return $this->db->resultSet();
    }

    public function createInvoiceWithAccounting($invoiceData, $items, $arAccountId, $revenueAccountId, $userId, $taxData = null) {
        try {
            $this->db->beginTransaction();

            // 1. Calculate Subtotal
            $subTotal = 0;
            foreach ($items as $item) {
                $subTotal += ($item['qty'] * $item['price']);
            }

            // 2. Handle Tax Calculations
            $taxAmount = 0;
            $taxLiabilityAccountId = null;
            $taxRateId = null;

            if ($taxData && !empty($taxData['tax_rate_id'])) {
                // Fetch the specific tax configuration
                $this->db->query("SELECT * FROM tax_rates WHERE id = :tid");
                $this->db->bind(':tid', $taxData['tax_rate_id']);
                $taxConfig = $this->db->single();

                if ($taxConfig) {
                    $taxAmount = ($subTotal * $taxConfig->rate_percentage) / 100;
                    $taxLiabilityAccountId = $taxConfig->liability_account_id;
                    $taxRateId = $taxConfig->id;
                }
            }

            $grandTotal = $subTotal + $taxAmount;

            // 3. Post Journal Entry Header
            $desc = "Auto-generated entry for Invoice #" . $invoiceData['invoice_number'];
            $this->db->query("INSERT INTO journal_entries (entry_date, reference, description, created_by, status) 
                              VALUES (:date, :ref, :desc, :user, 'Posted')");
            $this->db->bind(':date', $invoiceData['date']);
            $this->db->bind(':ref', $invoiceData['invoice_number']);
            $this->db->bind(':desc', $desc);
            $this->db->bind(':user', $userId);
            $this->db->execute();
            
            $journalId = $this->db->lastInsertId();

            // 4. Post Journal Lines (Debit AR, Credit Revenue, Credit Tax Liability)
            $lines = [
                ['account_id' => $arAccountId, 'debit' => $grandTotal, 'credit' => 0], // AR gets full amount including tax
                ['account_id' => $revenueAccountId, 'debit' => 0, 'credit' => $subTotal] // Revenue only gets subtotal
            ];

            if ($taxAmount > 0 && $taxLiabilityAccountId) {
                $lines[] = ['account_id' => $taxLiabilityAccountId, 'debit' => 0, 'credit' => $taxAmount]; // Liability gets tax amount
            }

            foreach ($lines as $line) {
                $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) 
                                  VALUES (:jid, :aid, :deb, :cred)");
                $this->db->bind(':jid', $journalId);
                $this->db->bind(':aid', $line['account_id']);
                $this->db->bind(':deb', $line['debit']);
                $this->db->bind(':cred', $line['credit']);
                $this->db->execute();

                // Update Chart of Accounts balances instantly
                $this->db->query("SELECT account_type FROM chart_of_accounts WHERE id = :id");
                $this->db->bind(':id', $line['account_id']);
                $acc = $this->db->single();

                $sql = "UPDATE chart_of_accounts SET balance = balance ";
                if (in_array($acc->account_type, ['Asset', 'Expense'])) {
                    $sql .= "+ :debit - :credit ";
                } else {
                    $sql .= "- :debit + :credit ";
                }
                $sql .= "WHERE id = :id";
                
                $this->db->query($sql);
                $this->db->bind(':debit', $line['debit']);
                $this->db->bind(':credit', $line['credit']);
                $this->db->bind(':id', $line['account_id']);
                $this->db->execute();
            }

            // 5. Create Invoice Header
            $this->db->query("INSERT INTO invoices (invoice_number, customer_id, invoice_date, due_date, tax_rate_id, total_amount, tax_amount, journal_entry_id, created_by) 
                              VALUES (:inv_num, :cust_id, :idate, :ddate, :tid, :total, :tamt, :jid, :uid)");
            $this->db->bind(':inv_num', $invoiceData['invoice_number']);
            $this->db->bind(':cust_id', $invoiceData['customer_id']);
            $this->db->bind(':idate', $invoiceData['date']);
            $this->db->bind(':ddate', $invoiceData['due_date']);
            $this->db->bind(':tid', $taxRateId);
            $this->db->bind(':total', $subTotal); // Store subtotal in total_amount column for historical consistency
            $this->db->bind(':tamt', $taxAmount);
            $this->db->bind(':jid', $journalId);
            $this->db->bind(':uid', $userId);
            $this->db->execute();
            
            $invoiceId = $this->db->lastInsertId();

            // 6. Create Invoice Items
            foreach ($items as $item) {
                $itemTotal = $item['qty'] * $item['price'];
                $this->db->query("INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total) 
                                  VALUES (:iid, :desc, :qty, :price, :total)");
                $this->db->bind(':iid', $invoiceId);
                $this->db->bind(':desc', $item['desc']);
                $this->db->bind(':qty', $item['qty']);
                $this->db->bind(':price', $item['price']);
                $this->db->bind(':total', $itemTotal);
                $this->db->execute();
            }

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }
}