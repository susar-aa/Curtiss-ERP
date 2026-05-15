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
        $this->db->query("SELECT i.*, c.name as customer_name, c.email, c.phone, c.address, c.whatsapp,
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

            // 1. Calculate Subtotal with individual Item Discounts
            $subTotal = 0;
            foreach ($items as &$item) {
                $itemGross = $item['qty'] * $item['price'];
                $itemDisc = 0;
                
                if (isset($item['disc_type']) && $item['disc_type'] === '%') {
                    $itemDisc = $itemGross * ($item['disc_val'] / 100);
                } else {
                    $itemDisc = $item['disc_val'] ?? 0;
                }
                
                $item['calculated_total'] = $itemGross - $itemDisc;
                $subTotal += $item['calculated_total'];
            }

            // Calculate Global Bill Discount
            $globalDisc = 0;
            if (isset($invoiceData['global_discount_type']) && $invoiceData['global_discount_type'] === '%') {
                $globalDisc = $subTotal * (($invoiceData['global_discount_val'] ?? 0) / 100);
            } else {
                $globalDisc = $invoiceData['global_discount_val'] ?? 0;
            }

            $netSubTotal = $subTotal - $globalDisc;
            if ($netSubTotal < 0) $netSubTotal = 0;

            // 2. Handle Tax Calculations
            $taxAmount = 0;
            $taxRateId = null;

            if ($taxData && !empty($taxData['tax_rate_id'])) {
                $this->db->query("SELECT * FROM tax_rates WHERE id = :tid");
                $this->db->bind(':tid', $taxData['tax_rate_id']);
                $taxConfig = $this->db->single();

                if ($taxConfig) {
                    $taxAmount = ($netSubTotal * $taxConfig->rate_percentage) / 100;
                    $taxRateId = $taxConfig->id;
                }
            }

            $grandTotal = $netSubTotal + $taxAmount;

            // 3. Post Journal Entry Header
            $desc = "Invoice created: " . $invoiceData['invoice_number'];
            $this->db->query("INSERT INTO journal_entries (entry_date, reference, description, created_by, status) 
                              VALUES (:date, :ref, :desc, :user, 'Posted')");
            $this->db->bind(':date', $invoiceData['date']);
            $this->db->bind(':ref', $invoiceData['invoice_number']);
            $this->db->bind(':desc', $desc);
            $this->db->bind(':user', $userId);
            $this->db->execute();
            
            $journalId = $this->db->lastInsertId();

            // 4. Post Journal Lines (Debit AR, Credit Revenue)
            $lines = [
                ['account_id' => $arAccountId, 'debit' => $grandTotal, 'credit' => 0], 
                ['account_id' => $revenueAccountId, 'debit' => 0, 'credit' => $netSubTotal]
            ];

            foreach ($lines as $line) {
                $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) 
                                  VALUES (:jid, :aid, :deb, :cred)");
                $this->db->bind(':jid', $journalId);
                $this->db->bind(':aid', $line['account_id']);
                $this->db->bind(':deb', $line['debit']);
                $this->db->bind(':cred', $line['credit']);
                $this->db->execute();

                // Update ledger balances mathematically
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
            $this->db->query("INSERT INTO invoices (invoice_number, customer_id, invoice_date, due_date, tax_rate_id, total_amount, global_discount_val, global_discount_type, tax_amount, journal_entry_id, created_by) 
                              VALUES (:inv_num, :cust_id, :idate, :ddate, :tid, :total, :gdisc_val, :gdisc_type, :tamt, :jid, :uid)");
            
            $this->db->bind(':inv_num', $invoiceData['invoice_number']);
            $this->db->bind(':cust_id', $invoiceData['customer_id']);
            $this->db->bind(':idate', $invoiceData['date']);
            $this->db->bind(':ddate', $invoiceData['due_date']);
            $this->db->bind(':tid', $taxRateId);
            $this->db->bind(':total', $subTotal); 
            $this->db->bind(':gdisc_val', $invoiceData['global_discount_val'] ?? 0);
            $this->db->bind(':gdisc_type', $invoiceData['global_discount_type'] ?? 'Rs');
            $this->db->bind(':tamt', $taxAmount);
            $this->db->bind(':jid', $journalId);
            $this->db->bind(':uid', $userId);
            $this->db->execute();
            
            $invoiceId = $this->db->lastInsertId();

            // 6. Create Invoice Items and Deduct Inventory Stock
            foreach ($items as $item) {
                $this->db->query("INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, discount_value, discount_type, total) 
                                  VALUES (:iid, :desc, :qty, :price, :disc_val, :disc_type, :total)");
                $this->db->bind(':iid', $invoiceId);
                $this->db->bind(':desc', $item['desc']);
                $this->db->bind(':qty', $item['qty']);
                $this->db->bind(':price', $item['price']);
                $this->db->bind(':disc_val', $item['disc_val'] ?? 0);
                $this->db->bind(':disc_type', $item['disc_type'] ?? 'Rs');
                $this->db->bind(':total', $item['calculated_total']);
                $this->db->execute();

                // Deduct Inventory Quantities
                if (!empty($item['item_selection'])) {
                    // String structure: item_id|variation_value_id|is_mix
                    $parts = explode('|', $item['item_selection']);
                    $itemId = $parts[0] ?? null;
                    $varId = isset($parts[1]) && $parts[1] !== 'MIX' && $parts[1] !== '0' ? $parts[1] : null;

                    // Deduct from Main Product Quantity
                    if ($itemId) {
                        $this->db->query("UPDATE items SET quantity_on_hand = quantity_on_hand - :qty WHERE id = :id");
                        $this->db->bind(':qty', $item['qty']);
                        $this->db->bind(':id', $itemId);
                        $this->db->execute();
                    }
                    
                    // Deduct from Specific Variation Quantity
                    if ($varId) {
                        $this->db->query("UPDATE item_variation_options SET quantity_on_hand = quantity_on_hand - :qty WHERE id = :id");
                        $this->db->bind(':qty', $item['qty']);
                        $this->db->bind(':id', $varId);
                        $this->db->execute();
                    }
                }
            }

            $this->db->commit();
            return $invoiceId;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Invoice Creation Error: " . $e->getMessage());
            return false;
        }
    }
}