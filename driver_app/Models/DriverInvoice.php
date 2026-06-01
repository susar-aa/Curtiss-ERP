<?php
class DriverInvoice {
    private $db;

    public function __construct() {
        $this->db = new Database();
        
        // Failsafe DDL: Auto-insert Driver Transit Collections (Temp) account if missing
        try {
            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1090'");
            if (!$this->db->single()) {
                $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, balance, parent_id) 
                                  VALUES ('1090', 'Driver Transit Collections (Temp)', 'Asset', 0.00, NULL)");
                $this->db->execute();
            }
        } catch (Exception $e) {
            // Silently catch errors
        }
    }

    public function getCustomerInvoices($customerId, $routeId) {
        $this->db->query("
            SELECT i.*, 
                (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as true_grand_total
            FROM invoices i
            WHERE i.customer_id = :cid AND i.rep_route_id = :rid AND i.status != 'Voided'
            ORDER BY i.created_at ASC
        ");
        $this->db->bind(':cid', $customerId);
        $this->db->bind(':rid', $routeId);
        return $this->db->resultSet();
    }

    public function getCustomerCreditBills($customerId) {
        $this->db->query("
            SELECT i.*, 
                (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as true_grand_total
            FROM invoices i
            WHERE i.customer_id = :cid AND i.status IN ('Unpaid')
            ORDER BY i.invoice_date ASC
        ");
        $this->db->bind(':cid', $customerId);
        return $this->db->resultSet();
    }

    public function getInvoiceItems($invoiceId) {
        $this->db->query("SELECT * FROM invoice_items WHERE invoice_id = :iid");
        $this->db->bind(':iid', $invoiceId);
        return $this->db->resultSet();
    }

    public function getInvoiceDetails($invoiceId) {
        $this->db->query("SELECT i.*, c.name as customer_name, c.phone, c.address 
                          FROM invoices i
                          JOIN customers c ON i.customer_id = c.id
                          WHERE i.id = :id");
        $this->db->bind(':id', $invoiceId);
        return $this->db->single();
    }

    public function getCustomerDetails($customerId) {
        $this->db->query("SELECT * FROM customers WHERE id = :id");
        $this->db->bind(':id', $customerId);
        return $this->db->single();
    }

    public function getCustomerTotalArrears($customerId, $currentRouteId) {
        $this->db->query("
            SELECT COALESCE(SUM(
                total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)
            ), 0) as arrears
            FROM invoices
            WHERE customer_id = :cid AND status = 'Unpaid' AND (rep_route_id != :rid OR rep_route_id IS NULL)
        ");
        $this->db->bind(':cid', $customerId);
        $this->db->bind(':rid', $currentRouteId);
        $res = $this->db->single();
        return $res ? floatval($res->arrears) : 0.0;
    }

    public function updateInvoiceItemQty($itemId, $newQty) {
        $this->db->beginTransaction();
        try {
            // 1. Fetch item
            $this->db->query("SELECT * FROM invoice_items WHERE id = :id");
            $this->db->bind(':id', $itemId);
            $item = $this->db->single();
            if (!$item) throw new Exception("Invoice item not found.");

            $invoiceId = $item->invoice_id;
            $oldQty = floatval($item->quantity);
            $newQty = floatval($newQty);
            $diffQty = $oldQty - $newQty;

            // 2. Fetch invoice
            $this->db->query("SELECT * FROM invoices WHERE id = :id");
            $this->db->bind(':id', $invoiceId);
            $invoice = $this->db->single();
            if (!$invoice) throw new Exception("Invoice not found.");

            // 3. Update reservation stock in database
            if ($item->item_id) {
                $this->db->query("UPDATE items SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :diff) WHERE id = :id");
                $this->db->bind(':diff', $diffQty);
                $this->db->bind(':id', $item->item_id);
                $this->db->execute();
            }
            if ($item->variation_option_id) {
                $this->db->query("UPDATE item_variation_options SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :diff) WHERE id = :id");
                $this->db->bind(':diff', $diffQty);
                $this->db->bind(':id', $item->variation_option_id);
                $this->db->execute();
            }

            // 4. Update the item
            $rowGross = $newQty * floatval($item->unit_price);
            $rowDisc = ($item->discount_type === '%') ? ($rowGross * floatval($item->discount_value) / 100) : floatval($item->discount_value);
            $newTotal = max(0, $rowGross - $rowDisc);

            $this->db->query("UPDATE invoice_items SET quantity = :qty, total = :total WHERE id = :id");
            $this->db->bind(':qty', $newQty);
            $this->db->bind(':total', $newTotal);
            $this->db->bind(':id', $itemId);
            $this->db->execute();

            // 5. Recalculate invoice totals
            $this->recalculateInvoiceTotals($invoiceId);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("updateInvoiceItemQty error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteInvoiceItem($itemId) {
        $this->db->beginTransaction();
        try {
            // 1. Fetch item
            $this->db->query("SELECT * FROM invoice_items WHERE id = :id");
            $this->db->bind(':id', $itemId);
            $item = $this->db->single();
            if (!$item) throw new Exception("Invoice item not found.");

            $invoiceId = $item->invoice_id;
            $oldQty = floatval($item->quantity);

            // 2. Release full reservation stock
            if ($item->item_id) {
                $this->db->query("UPDATE items SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :diff) WHERE id = :id");
                $this->db->bind(':diff', $oldQty);
                $this->db->bind(':id', $item->item_id);
                $this->db->execute();
            }
            if ($item->variation_option_id) {
                $this->db->query("UPDATE item_variation_options SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :diff) WHERE id = :id");
                $this->db->bind(':diff', $oldQty);
                $this->db->bind(':id', $item->variation_option_id);
                $this->db->execute();
            }

            // 3. Update quantity and total to 0 to preserve audit trail for returns
            $this->db->query("UPDATE invoice_items SET quantity = 0, total = 0 WHERE id = :id");
            $this->db->bind(':id', $itemId);
            $this->db->execute();

            // 4. Recalculate invoice totals
            $this->recalculateInvoiceTotals($invoiceId);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("deleteInvoiceItem error: " . $e->getMessage());
            return false;
        }
    }

    private function recalculateInvoiceTotals($invoiceId) {
        // Fetch invoice
        $this->db->query("SELECT * FROM invoices WHERE id = :id");
        $this->db->bind(':id', $invoiceId);
        $invoice = $this->db->single();
        if (!$invoice) return;

        $oldGrandTotal = $this->getTrueGrandTotal($invoice);

        // Fetch all items
        $this->db->query("SELECT * FROM invoice_items WHERE invoice_id = :iid");
        $this->db->bind(':iid', $invoiceId);
        $items = $this->db->resultSet();

        $subTotal = 0;
        foreach ($items as $item) {
            $subTotal += floatval($item->total);
        }

        $globalDiscVal = floatval($invoice->global_discount_val ?? 0);
        $globalDiscType = $invoice->global_discount_type ?? 'Rs';
        $globalDisc = ($globalDiscType === '%') ? ($subTotal * $globalDiscVal / 100) : $globalDiscVal;
        $netSub = max(0, $subTotal - $globalDisc);

        $taxAmount = 0.0;
        if (!empty($invoice->tax_rate_id)) {
            $this->db->query("SELECT rate_percentage FROM tax_rates WHERE id = :id");
            $this->db->bind(':id', $invoice->tax_rate_id);
            $tr = $this->db->single();
            if ($tr) {
                $taxAmount = ($netSub * floatval($tr->rate_percentage) / 100);
            }
        } else {
            $oldSub = floatval($invoice->total_amount ?? 0);
            if ($oldSub > 0) {
                $taxPercentage = floatval($invoice->tax_amount ?? 0) / $oldSub;
                $taxAmount = $netSub * $taxPercentage;
            }
        }

        $newGrandTotal = $netSub + $taxAmount;

        // Update invoice
        $this->db->query("UPDATE invoices SET total_amount = :subtotal, tax_amount = :tax WHERE id = :id");
        $this->db->bind(':subtotal', $subTotal);
        $this->db->bind(':tax', $taxAmount);
        $this->db->bind(':id', $invoiceId);
        $this->db->execute();

        // Ledger adjustments
        $this->db->query("SELECT id, account_code FROM chart_of_accounts WHERE account_code IN ('1200', '4000')");
        $accounts = $this->db->resultSet();
        $accMap = [];
        foreach($accounts as $a) { $accMap[$a->account_code] = $a->id; }

        $arAcc = $accMap['1200'] ?? null;
        $salesAcc = $accMap['4000'] ?? null;

        if ($arAcc && $salesAcc) {
            // Update chart_of_accounts balances
            $this->db->query("UPDATE chart_of_accounts SET balance = balance - :old_amt + :new_amt WHERE id = :id");
            $this->db->bind(':old_amt', $oldGrandTotal);
            $this->db->bind(':new_amt', $newGrandTotal);
            $this->db->bind(':id', $arAcc);
            $this->db->execute();

            $this->db->query("UPDATE chart_of_accounts SET balance = balance - :old_amt + :new_amt WHERE id = :id");
            $this->db->bind(':old_amt', $oldGrandTotal);
            $this->db->bind(':new_amt', $newGrandTotal);
            $this->db->bind(':id', $salesAcc);
            $this->db->execute();

            // Update journal transactions
            $jid = $invoice->journal_entry_id;
            if ($jid) {
                $this->db->query("UPDATE transactions SET debit = :new_amt WHERE journal_entry_id = :jid AND account_id = :aid AND debit > 0");
                $this->db->bind(':new_amt', $newGrandTotal);
                $this->db->bind(':jid', $jid);
                $this->db->bind(':aid', $arAcc);
                $this->db->execute();

                $this->db->query("UPDATE transactions SET credit = :new_amt WHERE journal_entry_id = :jid AND account_id = :aid AND credit > 0");
                $this->db->bind(':new_amt', $newGrandTotal);
                $this->db->bind(':jid', $jid);
                $this->db->bind(':aid', $salesAcc);
                $this->db->execute();
            }
        }
    }

    private function getTrueGrandTotal($invoice) {
        $subTotal = floatval($invoice->total_amount ?? 0);
        $globalDiscVal = floatval($invoice->global_discount_val ?? 0);
        $globalDiscType = $invoice->global_discount_type ?? 'Rs';
        $globalDisc = ($globalDiscType === '%') ? ($subTotal * $globalDiscVal / 100) : $globalDiscVal;
        return max(0, $subTotal - $globalDisc) + floatval($invoice->tax_amount ?? 0);
    }

    public function checkoutShop($customerId, $routeId, $userId, $collections) {
        $this->db->beginTransaction();
        try {
            // 1. Fetch today's invoices for this customer
            $invoices = $this->getCustomerInvoices($customerId, $routeId);

            // 2. DO NOT deduct physical stock or finalize accounting here. Update delivery status to 'Delivered'
            foreach ($invoices as $invoice) {
                $this->db->query("UPDATE invoices SET delivery_status = 'Delivered' WHERE id = :id");
                $this->db->bind(':id', $invoice->id);
                $this->db->execute();
            }

            // 3. Process collections & save to customer_payments and cheques tables (without posting journal entries yet)
            $cashAmt = floatval($collections['cash'] ?? 0);
            $bankAmt = floatval($collections['bank'] ?? 0);

            $savePaymentRecord = function($amount, $methodStr, $chequeDetails = null) use ($userId, $customerId, $routeId) {
                if ($amount <= 0) return;

                $refCode = "PMT-" . time() . rand(10,99);

                // If PDC Cheque details provided, write to cheques table
                if ($methodStr === 'Cheque' && $chequeDetails) {
                    $this->db->query("INSERT INTO cheques (customer_id, bank_name, cheque_number, amount, banking_date, status, rep_route_id, created_by) 
                                      VALUES (:cid, :bn, :cn, :amt, :bdate, 'Pending', :route_id, :uid)");
                    $this->db->bind(':cid', $customerId);
                    $this->db->bind(':bn', $chequeDetails['bank'] ?? 'Unknown');
                    $this->db->bind(':cn', $chequeDetails['number'] ?? 'Unknown');
                    $this->db->bind(':amt', $amount);
                    $this->db->bind(':bdate', $chequeDetails['date'] ?: date('Y-m-d'));
                    $this->db->bind(':route_id', $routeId);
                    $this->db->bind(':uid', $userId);
                    $this->db->execute();
                }

                // Log Customer Payment History with rep_route_id (leaving journal_entry_id NULL)
                $this->db->query("INSERT INTO customer_payments (customer_id, amount, payment_date, payment_method, reference, journal_entry_id, rep_route_id, created_by) 
                                  VALUES (:cid, :amt, CURDATE(), :method, :ref, NULL, :route_id, :uid)");
                $this->db->bind(':cid', $customerId);
                $this->db->bind(':amt', $amount);
                $this->db->bind(':method', $methodStr);
                $this->db->bind(':ref', $chequeDetails['number'] ?? $refCode);
                $this->db->bind(':route_id', $routeId);
                $this->db->bind(':uid', $userId);
                $this->db->execute();
            };

            $savePaymentRecord($cashAmt, 'Cash');
            $savePaymentRecord($bankAmt, 'Bank Transfer');
            
            if (isset($collections['cheques']) && is_array($collections['cheques'])) {
                foreach ($collections['cheques'] as $chequeObj) {
                    $amt = floatval($chequeObj['amount'] ?? 0);
                    if ($amt > 0) {
                        $savePaymentRecord($amt, 'Cheque', [
                            'bank' => $chequeObj['bank'] ?? 'Unknown',
                            'number' => $chequeObj['number'] ?? 'Unknown',
                            'date' => $chequeObj['date'] ?? date('Y-m-d')
                        ]);
                    }
                }
            } else {
                // Fallback to legacy single cheque details if present
                $chequeAmt = floatval($collections['cheque'] ?? 0);
                if ($chequeAmt > 0) {
                    $chequeDetails = [
                        'bank' => $collections['cheque_bank'] ?? '',
                        'number' => $collections['cheque_number'] ?? '',
                        'date' => $collections['cheque_date'] ?? ''
                    ];
                    $savePaymentRecord($chequeAmt, 'Cheque', $chequeDetails);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            file_put_contents('C:/xampp/htdocs/Curtiss-ERP/sync_debug.log', "[" . date('Y-m-d H:i:s') . "] checkoutShop error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n", FILE_APPEND);
            throw $e;
        }
    }

    public function updateInvoiceDeliveryStatus($invoiceId, $status) {
        $this->db->query("UPDATE invoices SET delivery_status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $invoiceId);
        return $this->db->execute();
    }
}
