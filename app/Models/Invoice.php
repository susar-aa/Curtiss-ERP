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

            $stockStatus = $invoiceData['stock_status'] ?? 'deducted';
            $jeStatus = ($stockStatus === 'reserved') ? 'Draft' : 'Posted';

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

            $stockStatus = $invoiceData['stock_status'] ?? 'deducted';
            $uuid = $invoiceData['uuid'] ?? null;
            $this->db->query("INSERT INTO invoices (invoice_number, uuid, customer_id, rep_route_id, invoice_date, due_date, payment_term_id, total_amount, global_discount_val, global_discount_type, notes, journal_entry_id, created_by, status, stock_status) 
                              VALUES (:invoice_number, :uuid, :customer_id, :rep_route_id, :invoice_date, :due_date, :payment_term_id, :total_amount, :global_discount_val, :global_discount_type, :notes, :journal_entry_id, :created_by, 'Unpaid', :stock_status)");
            $this->db->bind(':invoice_number', $invoiceData['invoice_number']);
            $this->db->bind(':uuid', $uuid);
            $this->db->bind(':customer_id', $invoiceData['customer_id']);
            $this->db->bind(':rep_route_id', $invoiceData['rep_route_id'] ?? null);
            $this->db->bind(':invoice_date', $invoiceData['invoice_date']);
            $this->db->bind(':due_date', $invoiceData['due_date']);
            $this->db->bind(':payment_term_id', $invoiceData['payment_term_id'] ?? null);
            $this->db->bind(':total_amount', $invoiceData['subtotal']);
            $this->db->bind(':global_discount_val', $invoiceData['global_discount_val']);
            $this->db->bind(':global_discount_type', $invoiceData['global_discount_type']);
            $this->db->bind(':notes', $invoiceData['notes']);
            $this->db->bind(':journal_entry_id', $journalEntryId);
            $this->db->bind(':created_by', $userId);
            $this->db->bind(':stock_status', $stockStatus);
            $this->db->execute();
            $invoiceId = $this->db->lastInsertId();

            require_once '../app/Models/FIFO.php';
            $fifo = new FIFO();

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

                if ($stockStatus === 'reserved') {
                    // Update Reserved Quantities
                    if ($itemId) {
                        $this->db->query("UPDATE items SET quantity_reserved = quantity_reserved + :qty WHERE id = :id");
                        $this->db->bind(':qty', $item['quantity']);
                        $this->db->bind(':id', $itemId);
                        $this->db->execute();
                    }
                    if ($varId) {
                        $this->db->query("UPDATE item_variation_options SET quantity_reserved = quantity_reserved + :qty WHERE id = :id");
                        $this->db->bind(':qty', $item['quantity']);
                        $this->db->bind(':id', $varId);
                        $this->db->execute();
                    }

                    // Log reserved stock placement in ledger
                    require_once '../app/Models/StockLedger.php';
                    $ledger = new StockLedger();
                    $this->db->query("SELECT warehouse_id, cost_price FROM items WHERE id = :id");
                    $this->db->bind(':id', $itemId);
                    $itemRow = $this->db->single();
                    $whId = $itemRow ? $itemRow->warehouse_id : null;
                    $itemCost = $itemRow ? floatval($itemRow->cost_price > 0 ? $itemRow->cost_price : 0.00) : 0.00;
                    $remarks = 'Sales Order - Reserved Stock Placed (Qty: ' . $item['quantity'] . ')';
                    $ledger->logMovement($itemId, $varId, 0, $item['quantity'], 'Reserved Stock Placement', $invoiceData['invoice_number'], $whId, $userId, $remarks, $itemCost);
                } else {
                    // Direct creation deducts from Physical stock immediately (with unsigned underflow safety)
                    if ($itemId) {
                        require_once '../app/Models/Item.php';
                        $itemModel = new Item();
                        $itemModel->updateStockDelta($itemId, -$item['quantity']);
                    }
                    if ($varId) {
                        $this->db->query("UPDATE item_variation_options SET quantity_on_hand = GREATEST(0, CAST(quantity_on_hand AS SIGNED) - :qty) WHERE id = :id");
                        $this->db->bind(':qty', $item['quantity']);
                        $this->db->bind(':id', $varId);
                        $this->db->execute();
                    }

                    // Deplete via FIFO batches & capture unit cost
                    $avgCost = $fifo->depleteStock($itemId, $varId, $item['quantity'], $invoiceItemId, null);

                    // Log stock movement in ledger
                    require_once '../app/Models/StockLedger.php';
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

            require_once '../app/Models/FIFO.php';
            $fifo = new FIFO();

            foreach ($oldItems as $oldItem) {
                $itemId = $oldItem->item_id;
                $varId = $oldItem->variation_option_id ?? null;

                if ($oldStockStatus === 'reserved') {
                    // Reverse the reservation: Subtract from quantity_reserved
                    if ($itemId) {
                        $this->db->query("UPDATE items SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :qty) WHERE id = :id");
                        $this->db->bind(':qty', $oldItem->quantity);
                        $this->db->bind(':id', $itemId);
                        $this->db->execute();
                    }
                    if ($varId) {
                        $this->db->query("UPDATE item_variation_options SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :qty) WHERE id = :id");
                        $this->db->bind(':qty', $oldItem->quantity);
                        $this->db->bind(':id', $varId);
                        $this->db->execute();
                    }
                } else {
                    // Reverse the physical deduction: Add back to quantity_on_hand
                    if ($itemId) {
                        require_once '../app/Models/Item.php';
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
                    require_once '../app/Models/StockLedger.php';
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
            if ($jid) {
                $this->db->query("DELETE FROM transactions WHERE journal_entry_id = :jid");
                $this->db->bind(':jid', $jid);
                $this->db->execute();

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
            }

            // 4. UPDATE TOP-LEVEL RECORD & PRESERVE stock_status
            $this->db->query("UPDATE invoices SET 
                                customer_id = :customer_id, 
                                invoice_date = :invoice_date, 
                                due_date = :due_date, 
                                payment_term_id = :payment_term_id,
                                total_amount = :total_amount, 
                                global_discount_val = :global_discount_val, 
                                global_discount_type = :global_discount_type, 
                                notes = :notes,
                                stock_status = :stock_status
                              WHERE id = :id");
            $this->db->bind(':customer_id', $invoiceData['customer_id']);
            $this->db->bind(':invoice_date', $invoiceData['invoice_date']);
            $this->db->bind(':due_date', $invoiceData['due_date']);
            $this->db->bind(':payment_term_id', $invoiceData['payment_term_id'] ?? null);
            $this->db->bind(':total_amount', $invoiceData['subtotal']);
            $this->db->bind(':global_discount_val', $invoiceData['global_discount_val']);
            $this->db->bind(':global_discount_type', $invoiceData['global_discount_type']);
            $this->db->bind(':notes', $invoiceData['notes']);
            $this->db->bind(':stock_status', $oldStockStatus);
            $this->db->bind(':id', $invoiceId);
            $this->db->execute();

            // 5. INSERT REVISED ITEMS & APPLY STOCK RESERVATIONS OR DEDUCTIONS
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

                if ($oldStockStatus === 'reserved') {
                    // Update Reserved Quantities
                    if ($itemId) {
                        $this->db->query("UPDATE items SET quantity_reserved = quantity_reserved + :qty WHERE id = :id");
                        $this->db->bind(':qty', $item['quantity']);
                        $this->db->bind(':id', $itemId);
                        $this->db->execute();
                    }
                    if ($varId) {
                        $this->db->query("UPDATE item_variation_options SET quantity_reserved = quantity_reserved + :qty WHERE id = :id");
                        $this->db->bind(':qty', $item['quantity']);
                        $this->db->bind(':id', $varId);
                        $this->db->execute();
                    }
                } else {
                    // Deduct from Main Product Quantity on Hand directly since the invoice is now finalized (unsigned underflow safety)
                    if ($itemId) {
                        require_once '../app/Models/Item.php';
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
                    require_once '../app/Models/StockLedger.php';
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

            require_once '../app/Models/FIFO.php';
            $fifo = new FIFO();

            foreach ($oldItems as $oldItem) {
                $itemId = $oldItem->item_id;
                $varId = $oldItem->variation_option_id ?? null;

                if ($oldStockStatus === 'reserved') {
                    if ($itemId) {
                        $this->db->query("UPDATE items SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :qty) WHERE id = :id");
                        $this->db->bind(':qty', $oldItem->quantity);
                        $this->db->bind(':id', $itemId);
                        $this->db->execute();
                    }
                    if ($varId) {
                        $this->db->query("UPDATE item_variation_options SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :qty) WHERE id = :id");
                        $this->db->bind(':qty', $oldItem->quantity);
                        $this->db->bind(':id', $varId);
                        $this->db->execute();
                    }

                    // Log stock movement in ledger (HIGH-6)
                    require_once '../app/Models/StockLedger.php';
                    $ledger = new StockLedger();
                    $this->db->query("SELECT warehouse_id, cost_price FROM items WHERE id = :id");
                    $this->db->bind(':id', $itemId);
                    $itemRow = $this->db->single();
                    $whId = $itemRow ? $itemRow->warehouse_id : null;
                    $itemCost = $itemRow ? floatval($itemRow->cost_price > 0 ? $itemRow->cost_price : 0.00) : 0.00;
                    $remarks = 'Invoice Deleted - Reserved Stock Released (Qty: ' . $oldItem->quantity . ')';
                    $ledger->logMovement($itemId, $varId, $oldItem->quantity, 0, 'Reserved Stock Release', $oldInvoice->invoice_number, $whId, $userId, $remarks, $itemCost);
                } else {
                    if ($itemId) {
                        require_once '../app/Models/Item.php';
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

                    require_once '../app/Models/StockLedger.php';
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