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
                          WHERE i.id = :id OR i.invoice_number = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getInvoiceItems($id) {
        $this->db->query("SELECT ii.* FROM invoice_items ii 
                          JOIN invoices i ON ii.invoice_id = i.id 
                          WHERE i.id = :id OR i.invoice_number = :id");
        $this->db->bind(':id', $id);
        return $this->db->resultSet() ?: [];
    }

    public function createInvoiceWithAccounting($invoiceData, $items, $arAccountId, $revenueAccountId, $userId, $taxData = null) {
        try {
            $this->db->beginTransaction();

            // PRE-INSERT STOCK LOCKING & VERIFICATION
            require_once __DIR__ . '/../Services/StockMovementService.php';
            $stockService = new \App\Services\StockMovementService();
            
            $stockDemands = [];
            foreach ($items as $item) {
                $parts = explode('|', $item['item_selection']);
                $stockDemands[] = [
                    'item_id' => $parts[0] ?? null,
                    'variation_option_id' => isset($parts[1]) && $parts[1] !== 'MIX' && $parts[1] !== '0' ? $parts[1] : null,
                    'quantity' => $item['quantity']
                ];
            }
            $stockService->lockAndVerifyAvailability($stockDemands);
            // End Stock Verification

            $stockStatus = $invoiceData['stock_status'] ?? 'deducted';
            $jeStatus = 'Posted';

            // Calculate Item Gross Total and Item Discount Total
            $itemGrossTotal = 0.0;
            $itemDiscountTotal = 0.0;

            foreach ($items as $item) {
                $qty = floatval($item['quantity'] ?? 0);
                $unitPrice = floatval($item['unit_price'] ?? 0);
                $lineGross = $qty * $unitPrice;
                
                $discVal = floatval($item['discount_value'] ?? 0);
                $discType = $item['discount_type'] ?? 'Rs';
                $lineDisc = ($discType === '%') ? ($lineGross * $discVal / 100) : $discVal;

                $itemGrossTotal += $lineGross;
                $itemDiscountTotal += $lineDisc;
            }

            $subtotal = floatval($invoiceData['subtotal'] ?? 0);
            $globalDiscVal = floatval($invoiceData['global_discount_val'] ?? 0);
            $globalDiscType = $invoiceData['global_discount_type'] ?? 'Rs';
            $globalDiscAmount = ($globalDiscType === '%') ? ($subtotal * $globalDiscVal / 100) : $globalDiscVal;

            $totalDiscountAmount = $itemDiscountTotal + $globalDiscAmount;
            $grossRevenue = ($itemGrossTotal > 0) ? $itemGrossTotal : ($subtotal + $itemDiscountTotal);
            $netGrandTotal = floatval($invoiceData['grand_total'] ?? ($subtotal - $globalDiscAmount));

            // Resolve Discounts Allowed Account (Code 4050 / Name Discounts Allowed)
            $discountAccountId = null;
            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '4050' OR account_name LIKE '%Discount%' LIMIT 1");
            $discRow = $this->db->single();
            if ($discRow) {
                $discountAccountId = $discRow->id;
            } else {
                $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, account_category, balance, is_active) VALUES ('4050', 'Discounts Allowed', 'Expense', 'Discounts', 0.00, 1)");
                $this->db->execute();
                $discountAccountId = $this->db->lastInsertId();
            }

            // Resolve COGS Account (Code 5000)
            $cogsAccountId = null;
            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '5000' OR account_name LIKE '%Cost of Goods%' LIMIT 1");
            $cogsRow = $this->db->single();
            if ($cogsRow) {
                $cogsAccountId = $cogsRow->id;
            } else {
                $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, account_category, balance, is_active) VALUES ('5000', 'Cost of Goods Sold (COGS)', 'Expense', 'Cost of Goods Sold', 0.00, 1)");
                $this->db->execute();
                $cogsAccountId = $this->db->lastInsertId();
            }

            // Resolve Inventory Asset Account (Code 1300)
            $inventoryAccountId = null;
            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1300' OR account_name LIKE '%Inventory Asset%' LIMIT 1");
            $invRow = $this->db->single();
            if ($invRow) {
                $inventoryAccountId = $invRow->id;
            } else {
                $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, account_category, balance, is_active) VALUES ('1300', 'Inventory Asset', 'Asset', 'Current Assets', 0.00, 1)");
                $this->db->execute();
                $inventoryAccountId = $this->db->lastInsertId();
            }

            // Resolve Tax Payable Account
            $taxAccountId = null;
            if (!empty($invoiceData['tax_rate_id'])) {
                $this->db->query("SELECT liability_account_id FROM tax_rates WHERE id = :id");
                $this->db->bind(':id', $invoiceData['tax_rate_id']);
                $taxRow = $this->db->single();
                if ($taxRow && !empty($taxRow->liability_account_id)) {
                    $taxAccountId = intval($taxRow->liability_account_id);
                }
            }
            if (empty($taxAccountId)) {
                $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '2110' OR account_name LIKE '%Tax Payable%' OR account_name LIKE '%Sales Tax%' LIMIT 1");
                $taxRow = $this->db->single();
                if ($taxRow) {
                    $taxAccountId = $taxRow->id;
                } else {
                    $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, account_category, balance, is_active) VALUES ('2110', 'VAT / Sales Tax Payable', 'Liability', 'Current Liabilities', 0.00, 1)");
                    $this->db->execute();
                    $taxAccountId = $this->db->lastInsertId();
                }
            }

            $this->db->query("INSERT INTO journal_entries (entry_date, reference, description, created_by, status) 
                              VALUES (:entry_date, :reference, :description, :created_by, :status)");
            $this->db->bind(':entry_date', $invoiceData['invoice_date']);
            $this->db->bind(':reference', $invoiceData['invoice_number']);
            $this->db->bind(':description', 'Invoice Entry - ' . $invoiceData['invoice_number']);
            $this->db->bind(':created_by', $userId);
            $this->db->bind(':status', $jeStatus);
            $this->db->execute();
            $journalEntryId = $this->db->lastInsertId();

            // 1. DEBIT: Accounts Receivable (Asset) = Net Grand Total
            $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:journal_id, :account_id, :debit, 0)");
            $this->db->bind(':journal_id', $journalEntryId);
            $this->db->bind(':account_id', $arAccountId);
            $this->db->bind(':debit', $netGrandTotal);
            $this->db->execute();

            if ($jeStatus === 'Posted') {
                $this->db->updateAccountBalance($arAccountId, $netGrandTotal, 0);
            }

            // 2. DEBIT: Discounts Allowed (Expense / Contra-Revenue) = Total Discount Amount (if > 0)
            if ($totalDiscountAmount > 0.001 && $discountAccountId) {
                $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:journal_id, :account_id, :debit, 0)");
                $this->db->bind(':journal_id', $journalEntryId);
                $this->db->bind(':account_id', $discountAccountId);
                $this->db->bind(':debit', $totalDiscountAmount);
                $this->db->execute();

                if ($jeStatus === 'Posted') {
                    $this->db->updateAccountBalance($discountAccountId, $totalDiscountAmount, 0);
                }
            }

            // 3. CREDIT: Sales Revenue (Revenue) = Gross Revenue
            $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:journal_id, :account_id, 0, :credit)");
            $this->db->bind(':journal_id', $journalEntryId);
            $this->db->bind(':account_id', $revenueAccountId);
            $this->db->bind(':credit', $grossRevenue);
            $this->db->execute();

            if ($jeStatus === 'Posted') {
                $this->db->updateAccountBalance($revenueAccountId, 0, $grossRevenue);
            }

            $taxAmount = isset($invoiceData['tax_amount']) ? floatval($invoiceData['tax_amount']) : ($netGrandTotal - ($grossRevenue - $totalDiscountAmount));

            // 4. CREDIT: Tax Payable (Liability) = Tax Amount (if > 0)
            if ($taxAmount > 0.001 && $taxAccountId) {
                $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:journal_id, :account_id, 0, :credit)");
                $this->db->bind(':journal_id', $journalEntryId);
                $this->db->bind(':account_id', $taxAccountId);
                $this->db->bind(':credit', $taxAmount);
                $this->db->execute();

                if ($jeStatus === 'Posted') {
                    $this->db->updateAccountBalance($taxAccountId, 0, $taxAmount);
                }
            }

            $stockStatus = $invoiceData['stock_status'] ?? 'deducted';
            $uuid = $invoiceData['uuid'] ?? null;
            $this->db->query("INSERT INTO invoices (invoice_number, uuid, customer_id, rep_route_id, invoice_date, due_date, payment_term_id, total_amount, tax_amount, tax_rate_id, global_discount_val, global_discount_type, notes, journal_entry_id, created_by, status, stock_status, customer_vat_number) 
                              VALUES (:invoice_number, :uuid, :customer_id, :rep_route_id, :invoice_date, :due_date, :payment_term_id, :total_amount, :tax_amount, :tax_rate_id, :global_discount_val, :global_discount_type, :notes, :journal_entry_id, :created_by, 'Unpaid', :stock_status, :customer_vat_number)");
            $this->db->bind(':invoice_number', $invoiceData['invoice_number']);
            $this->db->bind(':uuid', $uuid);
            $this->db->bind(':customer_id', $invoiceData['customer_id']);
            $this->db->bind(':rep_route_id', $invoiceData['rep_route_id'] ?? null);
            $this->db->bind(':invoice_date', $invoiceData['invoice_date']);
            $this->db->bind(':due_date', $invoiceData['due_date']);
            $this->db->bind(':payment_term_id', $invoiceData['payment_term_id'] ?? null);
            $this->db->bind(':total_amount', $invoiceData['subtotal']);
            $this->db->bind(':tax_amount', $taxAmount > 0 ? $taxAmount : 0.0);
            $this->db->bind(':tax_rate_id', $invoiceData['tax_rate_id'] ?? null);
            $this->db->bind(':global_discount_val', $invoiceData['global_discount_val']);
            $this->db->bind(':global_discount_type', $invoiceData['global_discount_type']);
            $this->db->bind(':notes', $invoiceData['notes']);
            $this->db->bind(':journal_entry_id', $journalEntryId);
            $this->db->bind(':created_by', $userId);
            $this->db->bind(':stock_status', $stockStatus);
            $this->db->bind(':customer_vat_number', $invoiceData['customer_vat_number'] ?? null);
            $this->db->execute();
            $invoiceId = $this->db->lastInsertId();

            require_once __DIR__ . '/FIFO.php';
            $fifo = new FIFO();
            
            $totalCogs = 0.0;

            foreach ($items as $item) {
                $parts = explode('|', $item['item_selection']);
                $itemId = $parts[0] ?? null;
                $varId = isset($parts[1]) && $parts[1] !== 'MIX' && $parts[1] !== '0' ? $parts[1] : null;

                $this->db->query("INSERT INTO invoice_items (invoice_id, item_id, variation_option_id, description, quantity, loaded_quantity, unit_price, discount_value, discount_type, total) 
                                  VALUES (:invoice_id, :item_id, :var_id, :description, :quantity, :quantity, :unit_price, :discount_value, :discount_type, :total)");
                $this->db->bind(':invoice_id', $invoiceId);
                $this->db->bind(':item_id', $itemId);
                $this->db->bind(':var_id', $varId);
                $this->db->bind(':description', $item['description']);
                $this->db->bind(':quantity', $item['quantity']);
                $this->db->bind(':unit_price', $item['unit_price']);
                $this->db->bind(':discount_value', $item['discount_value']);
                $this->db->bind(':discount_type', $item['discount_type']);
                $this->db->bind(':total', $item['total']);
                $this->db->execute();
                $invoiceItemId = $this->db->lastInsertId();

                // Direct creation deducts from Physical stock immediately (with unsigned underflow safety)
                if ($itemId) {
                    require_once __DIR__ . '/Item.php';
                    $itemModel = new Item();
                    $itemModel->updateStockDelta($itemId, -$item['quantity']);
                }
                if ($varId) {
                    $this->db->query("UPDATE item_variation_options SET quantity_on_hand = CAST(quantity_on_hand AS SIGNED) - :qty WHERE id = :id");
                    $this->db->bind(':qty', $item['quantity']);
                    $this->db->bind(':id', $varId);
                    $this->db->execute();
                }

                // Deplete via FIFO batches & capture unit cost
                $avgCost = $fifo->depleteStock($itemId, $varId, $item['quantity'], $invoiceItemId, null);

                // Log stock movement in ledger
                require_once __DIR__ . '/StockLedger.php';
                $ledger = new StockLedger();
                $this->db->query("SELECT warehouse_id, cost_price FROM items WHERE id = :id");
                $this->db->bind(':id', $itemId);
                $itemRow = $this->db->single();
                $whId = $itemRow ? $itemRow->warehouse_id : null;
                $itemCost = ($avgCost > 0) ? $avgCost : floatval($itemRow->cost_price ?? 0.00);

                $isFreeIssue = (floatval($item['unit_price'] ?? 0) <= 0 
                                || floatval($item['total'] ?? 0) <= 0 
                                || (isset($item['discount_type']) && in_array($item['discount_type'], ['Free Issue', 'Free']))
                                || strpos($item['description'] ?? '', '(Free') !== false);

                $movType = $isFreeIssue ? 'Promotional Free Issue' : 'Sales Invoice';
                $remarks = $isFreeIssue ? 'Free Issue Promotional Stock Deduction' : 'Sales Invoice Direct Deduction';

                $ledger->logMovement($itemId, $varId, 0, $item['quantity'], $movType, $invoiceData['invoice_number'], $whId, $userId, $remarks, $itemCost);
                $totalCogs += ($itemCost * floatval($item['quantity']));
            }

            // 4. DEBIT: COGS (Expense) & CREDIT: Inventory (Asset)
            if ($totalCogs > 0.001 && $cogsAccountId && $inventoryAccountId) {
                // Debit COGS
                $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:journal_id, :account_id, :debit, 0)");
                $this->db->bind(':journal_id', $journalEntryId);
                $this->db->bind(':account_id', $cogsAccountId);
                $this->db->bind(':debit', $totalCogs);
                $this->db->execute();
                
                if ($jeStatus === 'Posted') {
                    $this->db->updateAccountBalance($cogsAccountId, $totalCogs, 0);
                }

                // Credit Inventory
                $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:journal_id, :account_id, 0, :credit)");
                $this->db->bind(':journal_id', $journalEntryId);
                $this->db->bind(':account_id', $inventoryAccountId);
                $this->db->bind(':credit', $totalCogs);
                $this->db->execute();
                
                if ($jeStatus === 'Posted') {
                    $this->db->updateAccountBalance($inventoryAccountId, 0, $totalCogs);
                }
            }

            $this->db->commit();
            return $invoiceId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log("Invoice Creation Error: " . $e->getMessage());
            $_SESSION['invoice_error'] = "SQL Creation Exception: " . $e->getMessage();
            return false;
        }
    }

    public function updateInvoiceWithAccounting($invoiceId, $invoiceData, $items, $arAccountId, $revenueAccountId, $userId) {
        try {
            $this->db->beginTransaction();

            // Fetch current invoice state
            $this->db->query("SELECT * FROM invoices WHERE id = :id");
            $this->db->bind(':id', $invoiceId);
            $oldInvoice = $this->db->single();
            if (!$oldInvoice) throw new Exception("Invoice not found.");

            $oldSub = floatval($oldInvoice->total_amount ?? 0);
            $oldDiscVal = floatval($oldInvoice->global_discount_val ?? 0);
            $oldDiscType = $oldInvoice->global_discount_type ?? 'Rs';
            $oldDisc = ($oldDiscType === '%') ? ($oldSub * $oldDiscVal / 100) : $oldDiscVal;
            $oldGrandTotal = ($oldSub - $oldDisc) + floatval($oldInvoice->tax_amount ?? 0);

            // Determine if the invoice was currently holding reserved stock or physically deducted stock
            $oldStockStatus = isset($oldInvoice->stock_status) ? $oldInvoice->stock_status : 'deducted';

            // 1. REVERT PREVIOUS STOCK ALLOCATIONS
            $this->db->query("SELECT * FROM invoice_items WHERE invoice_id = :id");
            $this->db->bind(':id', $invoiceId);
            $oldItems = $this->db->resultSet();

            require_once __DIR__ . '/FIFO.php';
            $fifo = new FIFO();

            foreach ($oldItems as $oldItem) {
                $itemId = $oldItem->item_id;
                $varId = $oldItem->variation_option_id ?? null;

                // Reverse the physical deduction: Add back to quantity_on_hand
                if ($itemId) {
                    require_once __DIR__ . '/Item.php';
                    $itemModel = new Item();
                    $itemModel->updateStockDelta($itemId, $oldItem->quantity);
                }
                if ($varId) {
                    $this->db->query("UPDATE item_variation_options SET quantity_on_hand = quantity_on_hand + :qty WHERE id = :id");
                    $this->db->bind(':qty', $oldItem->quantity);
                    $this->db->bind(':id', $varId);
                    $this->db->execute();
                }

                // Revert FIFO batch allocations
                $fifo->revertDepletion($oldItem->id, null);

                // Log stock movement in ledger (reversion/addition)
                require_once __DIR__ . '/StockLedger.php';
                $ledger = new StockLedger();
                $this->db->query("SELECT warehouse_id, cost_price FROM items WHERE id = :id");
                $this->db->bind(':id', $itemId);
                $itemRow = $this->db->single();
                $whId = $itemRow ? $itemRow->warehouse_id : null;
                $itemCost = floatval($oldItem->cost_at_sale ?? 0.00);
                if ($itemCost <= 0 && $itemRow) {
                    $itemCost = floatval($itemRow->cost_price > 0 ? $itemRow->cost_price : 0.00);
                }

                $isFreeIssue = (floatval($oldItem->unit_price ?? 0) <= 0 
                                || floatval($oldItem->total ?? 0) <= 0 
                                || (isset($oldItem->discount_type) && in_array($oldItem->discount_type, ['Free Issue', 'Free']))
                                || strpos($oldItem->description ?? '', '(Free') !== false);

                $movType = $isFreeIssue ? 'Promotional Free Issue Reversion' : 'Sales Invoice Reversion';
                $remarks = $isFreeIssue ? 'Invoice Updated - Free Issue Stock Reverted' : 'Invoice Updated - Stock Reverted';

                $ledger->logMovement($itemId, $varId, $oldItem->quantity, 0, $movType, $oldInvoice->invoice_number, $whId, $userId, $remarks, $itemCost);
            }

            // Remove existing item records
            $this->db->query("DELETE FROM invoice_items WHERE invoice_id = :id");
            $this->db->bind(':id', $invoiceId);
            $this->db->execute();

            // 2. ADJUST LEDGER BALANCE & RE-POST REVISED TRANSACTIONS
            $jid = $oldInvoice->journal_entry_id;
            $isPosted = false;
            if ($jid) {
                $this->db->query("SELECT status FROM journal_entries WHERE id = :jid");
                $this->db->bind(':jid', $jid);
                $jeRow = $this->db->single();
                $isPosted = ($jeRow && $jeRow->status === 'Posted');
            }

            if ($isPosted && $jid) {
                // Revert all existing transaction balance impacts
                $this->db->query("SELECT account_id, debit, credit FROM transactions WHERE journal_entry_id = :jid");
                $this->db->bind(':jid', $jid);
                $oldTxns = $this->db->resultSet() ?: [];
                foreach ($oldTxns as $tx) {
                    $aId = intval($tx->account_id);
                    $d = floatval($tx->debit);
                    $c = floatval($tx->credit);
                    if ($d > 0) {
                        $this->db->updateAccountBalance($aId, 0, $d);
                    }
                    if ($c > 0) {
                        $this->db->updateAccountBalance($aId, $c, 0);
                    }
                }
            }

            // 3. RE-POST REVISED JOURNAL ENTRIES
            // Calculate new item-wise and global discounts
            $itemGrossTotal = 0.0;
            $itemDiscountTotal = 0.0;
            foreach ($items as $item) {
                $qty = floatval($item['quantity'] ?? 0);
                $unitPrice = floatval($item['unit_price'] ?? 0);
                $lineGross = $qty * $unitPrice;
                
                $discVal = floatval($item['discount_value'] ?? 0);
                $discType = $item['discount_type'] ?? 'Rs';
                $lineDisc = ($discType === '%') ? ($lineGross * $discVal / 100) : $discVal;

                $itemGrossTotal += $lineGross;
                $itemDiscountTotal += $lineDisc;
            }

            $subtotal = floatval($invoiceData['subtotal'] ?? 0);
            $globalDiscVal = floatval($invoiceData['global_discount_val'] ?? 0);
            $globalDiscType = $invoiceData['global_discount_type'] ?? 'Rs';
            $globalDiscAmount = ($globalDiscType === '%') ? ($subtotal * $globalDiscVal / 100) : $globalDiscVal;

            $totalDiscountAmount = $itemDiscountTotal + $globalDiscAmount;
            $grossRevenue = ($itemGrossTotal > 0) ? $itemGrossTotal : ($subtotal + $itemDiscountTotal);
            $netGrandTotal = floatval($invoiceData['grand_total'] ?? ($subtotal - $globalDiscAmount));
            $taxAmount = isset($invoiceData['tax_amount']) ? floatval($invoiceData['tax_amount']) : ($netGrandTotal - ($grossRevenue - $totalDiscountAmount));

            if ($jid) {
                $this->db->query("DELETE FROM transactions WHERE journal_entry_id = :jid");
                $this->db->bind(':jid', $jid);
                $this->db->execute();


                $discountAccountId = null;
                $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '4050' OR account_name LIKE '%Discount%' LIMIT 1");
                $discRow = $this->db->single();
                if ($discRow) {
                    $discountAccountId = $discRow->id;
                } else {
                    $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, account_category, balance, is_active) VALUES ('4050', 'Discounts Allowed', 'Expense', 'Discounts', 0.00, 1)");
                    $this->db->execute();
                    $discountAccountId = $this->db->lastInsertId();
                }

                // Resolve COGS Account (Code 5000)
                $cogsAccountId = null;
                $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '5000' OR account_name LIKE '%Cost of Goods%' LIMIT 1");
                $cogsRow = $this->db->single();
                if ($cogsRow) {
                    $cogsAccountId = $cogsRow->id;
                } else {
                    $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, account_category, balance, is_active) VALUES ('5000', 'Cost of Goods Sold (COGS)', 'Expense', 'Cost of Goods Sold', 0.00, 1)");
                    $this->db->execute();
                    $cogsAccountId = $this->db->lastInsertId();
                }
    
                // Resolve Inventory Asset Account (Code 1300)
                $inventoryAccountId = null;
                $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1300' OR account_name LIKE '%Inventory Asset%' LIMIT 1");
                $invRow = $this->db->single();
                if ($invRow) {
                    $inventoryAccountId = $invRow->id;
                } else {
                    $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, account_category, balance, is_active) VALUES ('1300', 'Inventory Asset', 'Asset', 'Current Assets', 0.00, 1)");
                    $this->db->execute();
                    $inventoryAccountId = $this->db->lastInsertId();
                }

                // Resolve Tax Payable Account
                $taxAccountId = null;
                if (!empty($invoiceData['tax_rate_id'])) {
                    $this->db->query("SELECT liability_account_id FROM tax_rates WHERE id = :id");
                    $this->db->bind(':id', $invoiceData['tax_rate_id']);
                    $taxRow = $this->db->single();
                    if ($taxRow && !empty($taxRow->liability_account_id)) {
                        $taxAccountId = intval($taxRow->liability_account_id);
                    }
                }
                if (empty($taxAccountId)) {
                    $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '2110' OR account_name LIKE '%Tax Payable%' OR account_name LIKE '%Sales Tax%' LIMIT 1");
                    $taxRow = $this->db->single();
                    if ($taxRow) {
                        $taxAccountId = $taxRow->id;
                    } else {
                        $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, account_category, balance, is_active) VALUES ('2110', 'VAT / Sales Tax Payable', 'Liability', 'Current Liabilities', 0.00, 1)");
                        $this->db->execute();
                        $taxAccountId = $this->db->lastInsertId();
                    }
                }

                // 1. DEBIT: Accounts Receivable (Asset) = Net Grand Total
                $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:journal_id, :account_id, :debit, 0)");
                $this->db->bind(':journal_id', $jid);
                $this->db->bind(':account_id', $arAccountId);
                $this->db->bind(':debit', $netGrandTotal);
                $this->db->execute();
                if ($isPosted) {
                    $this->db->updateAccountBalance($arAccountId, $netGrandTotal, 0);
                }

                // 2. DEBIT: Discounts Allowed (Expense / Contra-Revenue) = Total Discount Amount (if > 0)
                if ($totalDiscountAmount > 0.001 && $discountAccountId) {
                    $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:journal_id, :account_id, :debit, 0)");
                    $this->db->bind(':journal_id', $jid);
                    $this->db->bind(':account_id', $discountAccountId);
                    $this->db->bind(':debit', $totalDiscountAmount);
                    $this->db->execute();
                    if ($isPosted) {
                        $this->db->updateAccountBalance($discountAccountId, $totalDiscountAmount, 0);
                    }
                }

                // 3. CREDIT: Sales Revenue (Revenue) = Gross Revenue
                $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:journal_id, :account_id, 0, :credit)");
                $this->db->bind(':journal_id', $jid);
                $this->db->bind(':account_id', $revenueAccountId);
                $this->db->bind(':credit', $grossRevenue);
                $this->db->execute();
                if ($isPosted) {
                    $this->db->updateAccountBalance($revenueAccountId, 0, $grossRevenue);
                }

                // 4. CREDIT: Tax Payable (Liability) = Tax Amount (if > 0)
                if ($taxAmount > 0.001 && $taxAccountId) {
                    $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:journal_id, :account_id, 0, :credit)");
                    $this->db->bind(':journal_id', $jid);
                    $this->db->bind(':account_id', $taxAccountId);
                    $this->db->bind(':credit', $taxAmount);
                    $this->db->execute();

                    if ($isPosted) {
                        $this->db->updateAccountBalance($taxAccountId, 0, $taxAmount);
                    }
                }
            }

            // 5. UPDATE TOP-LEVEL RECORD & PRESERVE stock_status
            $this->db->query("UPDATE invoices SET 
                                customer_id = :customer_id, 
                                invoice_date = :invoice_date, 
                                due_date = :due_date, 
                                payment_term_id = :payment_term_id,
                                total_amount = :total_amount, 
                                tax_amount = :tax_amount,
                                tax_rate_id = :tax_rate_id,
                                global_discount_val = :global_discount_val, 
                                global_discount_type = :global_discount_type, 
                                notes = :notes,
                                stock_status = :stock_status,
                                customer_vat_number = :customer_vat_number
                              WHERE id = :id");
            $this->db->bind(':customer_id', $invoiceData['customer_id']);
            $this->db->bind(':invoice_date', $invoiceData['invoice_date']);
            $this->db->bind(':due_date', $invoiceData['due_date']);
            $this->db->bind(':payment_term_id', $invoiceData['payment_term_id'] ?? null);
            $this->db->bind(':total_amount', $invoiceData['subtotal']);
            $this->db->bind(':tax_amount', $taxAmount > 0 ? $taxAmount : 0.0);
            $this->db->bind(':tax_rate_id', $invoiceData['tax_rate_id'] ?? null);
            $this->db->bind(':global_discount_val', $invoiceData['global_discount_val']);
            $this->db->bind(':global_discount_type', $invoiceData['global_discount_type']);
            $this->db->bind(':notes', $invoiceData['notes']);
            $this->db->bind(':stock_status', $oldStockStatus);
            $this->db->bind(':customer_vat_number', $invoiceData['customer_vat_number'] ?? null);
            $this->db->bind(':id', $invoiceId);
            $this->db->execute();

            // 5. INSERT REVISED ITEMS & APPLY STOCK RESERVATIONS OR DEDUCTIONS
            $totalCogs = 0.0;
            foreach ($items as $item) {
                $parts = explode('|', $item['item_selection']);
                $itemId = $parts[0] ?? null;
                $varId = isset($parts[1]) && $parts[1] !== 'MIX' && $parts[1] !== '0' ? $parts[1] : null;

                $this->db->query("INSERT INTO invoice_items (invoice_id, item_id, variation_option_id, description, quantity, loaded_quantity, unit_price, discount_value, discount_type, total) 
                                  VALUES (:invoice_id, :item_id, :var_id, :description, :quantity, :quantity, :unit_price, :discount_value, :discount_type, :total)");
                $this->db->bind(':invoice_id', $invoiceId);
                $this->db->bind(':item_id', $itemId);
                $this->db->bind(':var_id', $varId);
                $this->db->bind(':description', $item['description']);
                $this->db->bind(':quantity', $item['quantity']);
                $this->db->bind(':unit_price', $item['unit_price']);
                $this->db->bind(':discount_value', $item['discount_value']);
                $this->db->bind(':discount_type', $item['discount_type']);
                $this->db->bind(':total', $item['total']);
                $this->db->execute();
                $newInvoiceItemId = $this->db->lastInsertId();

                // Deduct from Main Product Quantity on Hand directly since the invoice is now finalized (unsigned underflow safety)
                if ($itemId) {
                    require_once __DIR__ . '/Item.php';
                    $itemModel = new Item();
                    $itemModel->updateStockDelta($itemId, -$item['quantity']);
                }
                if ($varId) {
                    $this->db->query("UPDATE item_variation_options SET quantity_on_hand = GREATEST(0, CAST(quantity_on_hand AS SIGNED) - :qty) WHERE id = :id");
                    $this->db->bind(':qty', $item['quantity']);
                    $this->db->bind(':id', $varId);
                    $this->db->execute();
                }

                // Deplete new items via FIFO batches & capture unit cost
                $avgCost = $fifo->depleteStock($itemId, $varId, $item['quantity'], $newInvoiceItemId, null);

                // Log stock movement in ledger (new deduction)
                require_once __DIR__ . '/StockLedger.php';
                $ledger = new StockLedger();
                $this->db->query("SELECT warehouse_id, cost_price FROM items WHERE id = :id");
                $this->db->bind(':id', $itemId);
                $itemRow = $this->db->single();
                $whId = $itemRow ? $itemRow->warehouse_id : null;
                $itemCost = ($avgCost > 0) ? $avgCost : floatval($itemRow->cost_price ?? 0.00);

                $isFreeIssue = (floatval($item['unit_price'] ?? 0) <= 0 
                                || floatval($item['total'] ?? 0) <= 0 
                                || (isset($item['discount_type']) && in_array($item['discount_type'], ['Free Issue', 'Free']))
                                || strpos($item['description'] ?? '', '(Free') !== false);

                $movType = $isFreeIssue ? 'Promotional Free Issue' : 'Sales Invoice';
                $remarks = $isFreeIssue ? 'Invoice Updated - New Free Issue Stock Deducted' : 'Invoice Updated - New Stock Deducted';

                $ledger->logMovement($itemId, $varId, 0, $item['quantity'], $movType, $invoiceData['invoice_number'], $whId, $userId, $remarks, $itemCost);
                $totalCogs += ($itemCost * floatval($item['quantity']));
            }

            if ($jid && $totalCogs > 0.001 && $cogsAccountId && $inventoryAccountId) {
                // Debit COGS
                $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:journal_id, :account_id, :debit, 0)");
                $this->db->bind(':journal_id', $jid);
                $this->db->bind(':account_id', $cogsAccountId);
                $this->db->bind(':debit', $totalCogs);
                $this->db->execute();
                
                if ($isPosted) {
                    $this->db->updateAccountBalance($cogsAccountId, $totalCogs, 0);
                }

                // Credit Inventory
                $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:journal_id, :account_id, 0, :credit)");
                $this->db->bind(':journal_id', $jid);
                $this->db->bind(':account_id', $inventoryAccountId);
                $this->db->bind(':credit', $totalCogs);
                $this->db->execute();
                
                if ($isPosted) {
                    $this->db->updateAccountBalance($inventoryAccountId, 0, $totalCogs);
                }
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log("Invoice Edit Saving Error: " . $e->getMessage());
            $_SESSION['invoice_error'] = "SQL Edit Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
            return false;
        }
    }

    public function deleteInvoiceWithAccounting($invoiceId, $userId) {
        try {
            $this->db->beginTransaction();

            $oldInvoice = $this->getInvoiceById($invoiceId);
            if (!$oldInvoice) throw new Exception("Invoice not found.");

            $oldSub = floatval($oldInvoice->total_amount ?? 0);
            $oldDiscVal = floatval($oldInvoice->global_discount_val ?? 0);
            $oldDiscType = $oldInvoice->global_discount_type ?? 'Rs';
            $oldDisc = ($oldDiscType === '%') ? ($oldSub * $oldDiscVal / 100) : $oldDiscVal;
            $oldGrandTotal = ($oldSub - $oldDisc) + floatval($oldInvoice->tax_amount ?? 0);

            $oldStockStatus = isset($oldInvoice->stock_status) ? $oldInvoice->stock_status : 'deducted';

            // 1. REVERT STOCK ALLOCATIONS
            $this->db->query("SELECT * FROM invoice_items WHERE invoice_id = :id");
            $this->db->bind(':id', $invoiceId);
            $oldItems = $this->db->resultSet() ?: [];

            require_once __DIR__ . '/FIFO.php';
            $fifo = new FIFO();

            foreach ($oldItems as $oldItem) {
                $itemId = $oldItem->item_id;
                $varId = $oldItem->variation_option_id ?? null;

                if ($itemId) {
                    require_once __DIR__ . '/Item.php';
                    $itemModel = new Item();
                    $itemModel->updateStockDelta($itemId, $oldItem->quantity);
                }
                if ($varId) {
                    $this->db->query("UPDATE item_variation_options SET quantity_on_hand = quantity_on_hand + :qty WHERE id = :id");
                    $this->db->bind(':qty', $oldItem->quantity);
                    $this->db->bind(':id', $varId);
                    $this->db->execute();
                }

                $fifo->revertDepletion($oldItem->id, null);

                require_once __DIR__ . '/StockLedger.php';
                $ledger = new StockLedger();
                $this->db->query("SELECT warehouse_id, cost_price FROM items WHERE id = :id");
                $this->db->bind(':id', $itemId);
                $itemRow = $this->db->single();
                $whId = $itemRow ? $itemRow->warehouse_id : null;
                $itemCost = floatval($oldItem->cost_at_sale ?? 0.00);
                if ($itemCost <= 0 && $itemRow) {
                    $itemCost = floatval($itemRow->cost_price > 0 ? $itemRow->cost_price : 0.00);
                }

                $isFreeIssue = (floatval($oldItem->unit_price ?? 0) <= 0 
                                || floatval($oldItem->total ?? 0) <= 0 
                                || (isset($oldItem->discount_type) && in_array($oldItem->discount_type, ['Free Issue', 'Free']))
                                || strpos($oldItem->description ?? '', '(Free') !== false);

                $movType = $isFreeIssue ? 'Promotional Free Issue Reversion' : 'Sales Invoice Deletion';
                $remarks = $isFreeIssue ? 'Invoice Deleted - Free Issue Stock Reverted' : 'Invoice Deleted - Stock Reverted';

                $ledger->logMovement($itemId, $varId, $oldItem->quantity, 0, $movType, $oldInvoice->invoice_number, $whId, $userId, $remarks, $itemCost);
            }

            // 2. ADJUST LEDGER ACCOUNTS BALANCE & REMOVE JOURNAL ENTRIES
            $jid = $oldInvoice->journal_entry_id;
            $isPosted = false;
            if ($jid) {
                $this->db->query("SELECT status FROM journal_entries WHERE id = :jid");
                $this->db->bind(':jid', $jid);
                $jeRow = $this->db->single();
                $isPosted = ($jeRow && $jeRow->status === 'Posted');
            }

            if ($isPosted && $jid) {
                // Dynamic reversal of all transactions associated with this journal entry
                $this->db->query("SELECT account_id, debit, credit FROM transactions WHERE journal_entry_id = :jid");
                $this->db->bind(':jid', $jid);
                $oldTxns = $this->db->resultSet() ?: [];
                foreach ($oldTxns as $tx) {
                    $aId = intval($tx->account_id);
                    $d = floatval($tx->debit);
                    $c = floatval($tx->credit);
                    if ($d > 0) {
                        $this->db->updateAccountBalance($aId, 0, $d);
                    }
                    if ($c > 0) {
                        $this->db->updateAccountBalance($aId, $c, 0);
                    }
                }
            }

            // 3. REMOVE JOURNAL ENTRIES & TRANSACTIONS
            if ($jid) {
                $this->db->query("DELETE FROM transactions WHERE journal_entry_id = :jid");
                $this->db->bind(':jid', $jid);
                $this->db->execute();

                $this->db->query("DELETE FROM journal_entries WHERE id = :jid");
                $this->db->bind(':jid', $jid);
                $this->db->execute();
            }

            // 4. DELETE ITEMS AND INVOICE
            $this->db->query("DELETE FROM invoice_items WHERE invoice_id = :id");
            $this->db->bind(':id', $invoiceId);
            $this->db->execute();

            $this->db->query("DELETE FROM invoices WHERE id = :id");
            $this->db->bind(':id', $invoiceId);
            $this->db->execute();

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log("Invoice Deletion Saving Error: " . $e->getMessage());
            $_SESSION['invoice_error'] = "SQL Deletion Exception: " . $e->getMessage();
            return false;
        }
    }
}