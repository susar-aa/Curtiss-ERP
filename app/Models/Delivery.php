<?php

class Delivery {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function createDelivery($data) {
        $this->db->beginTransaction();

        try {
            // 1. Insert into deliveries table
            $this->db->query("INSERT INTO deliveries (rep_route_id, secondary_rep_route_id, delivery_date, vehicle_number, driver_name, partner_name, selected_credit_invoices) 
                              VALUES (:rep_route_id, :secondary_rep_route_id, :delivery_date, :vehicle_number, :driver_name, :partner_name, :selected_credit_invoices)");
            $this->db->bind(':rep_route_id', $data['rep_route_id']);
            $this->db->bind(':secondary_rep_route_id', $data['secondary_rep_route_id'] ?? null);
            $this->db->bind(':delivery_date', $data['delivery_date']);
            $this->db->bind(':vehicle_number', $data['vehicle_number']);
            $this->db->bind(':driver_name', $data['driver_name']);
            $this->db->bind(':partner_name', $data['partner_name']);
            $this->db->bind(':selected_credit_invoices', $data['selected_credit_invoices'] ?? null);
            $this->db->execute();
            $deliveryId = $this->db->lastInsertId();

            // 2. Update status of rep_daily_routes to 'Delivery Arranged'
            $rids = [intval($data['rep_route_id'])];
            if (!empty($data['secondary_rep_route_id'])) {
                $rids[] = intval($data['secondary_rep_route_id']);
            }
            $rids = $this->resolveAllBoundRouteIds($rids);
            $ridsStr = implode(',', array_map('intval', $rids));

            $this->db->query("UPDATE rep_daily_routes SET status = 'Delivery Arranged' WHERE id IN ($ridsStr)");
            $this->db->execute();

            $this->db->commit();
            return $deliveryId;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getAllDeliveries() {
        $this->db->query("
            SELECT d.*, r.route_name, r.start_time, r.user_id as user_id, COALESCE(e.first_name, u.username) as first_name, COALESCE(e.last_name, '') as last_name,
                COALESCE(inv.bill_count, 0) as bill_count,
                COALESCE(inv.total_sales, 0.00) as total_sales
            FROM deliveries d
            JOIN rep_daily_routes r ON d.rep_route_id = r.id
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN employees e ON u.employee_id = e.id
            LEFT JOIN (
                SELECT rep_route_id, 
                       COUNT(*) as bill_count,
                       SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as total_sales
                FROM invoices 
                WHERE status != 'Voided' AND rep_route_id IS NOT NULL
                GROUP BY rep_route_id
            ) inv ON inv.rep_route_id = r.id
            ORDER BY d.delivery_date DESC, d.created_at DESC
        ");
        return $this->db->resultSet();
    }

    public function getDeliveryById($id) {
        $this->db->query("
            SELECT d.*, r.route_name, r.start_time, r.user_id as user_id, COALESCE(e.first_name, u.username) as first_name, COALESCE(e.last_name, '') as last_name,
                COALESCE(inv.bill_count, 0) as bill_count,
                COALESCE(inv.total_sales, 0.00) as total_sales
            FROM deliveries d
            JOIN rep_daily_routes r ON d.rep_route_id = r.id
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN employees e ON u.employee_id = e.id
            LEFT JOIN (
                SELECT rep_route_id, 
                       COUNT(*) as bill_count,
                       SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as total_sales
                FROM invoices 
                WHERE status != 'Voided' AND rep_route_id IS NOT NULL
                GROUP BY rep_route_id
            ) inv ON inv.rep_route_id = r.id
            WHERE d.id = :id
        ");
        $this->db->bind(':id', $id);
        $delivery = $this->db->single();
        if ($delivery) {
            $dynDebit = [];
            $dynCredit = [];
            
            $rids = [intval($delivery->rep_route_id)];
            if (!empty($delivery->secondary_rep_route_id)) {
                $rids[] = intval($delivery->secondary_rep_route_id);
            }
            $rids = $this->resolveAllBoundRouteIds($rids);
            $ridsStr = !empty($rids) ? implode(',', array_map('intval', $rids)) : '0';
            
            // 1. Fetch invoice draft JEs
            $this->db->query("SELECT id FROM invoices WHERE rep_route_id IN ($ridsStr) AND status != 'Voided'");
            $routeInvoices = $this->db->resultSet() ?: [];
            foreach ($routeInvoices as $inv) {
                $ref = "INV-SALES-DRAFT-" . $inv->id;
                $this->db->query("SELECT id FROM journal_entries WHERE reference = :ref AND status = 'Draft' LIMIT 1");
                $this->db->bind(':ref', $ref);
                $je = $this->db->single();
                if ($je) {
                    $this->db->query("SELECT account_id FROM transactions WHERE journal_entry_id = :jid AND debit > 0 LIMIT 1");
                    $this->db->bind(':jid', $je->id);
                    $tDeb = $this->db->single();
                    if ($tDeb) {
                        $dynDebit["inv_" . $inv->id] = intval($tDeb->account_id);
                    }
                    
                    $this->db->query("SELECT account_id FROM transactions WHERE journal_entry_id = :jid AND credit > 0 LIMIT 1");
                    $this->db->bind(':jid', $je->id);
                    $tCred = $this->db->single();
                    if ($tCred) {
                        $dynCredit["inv_" . $inv->id] = intval($tCred->account_id);
                    }
                }
            }
            
            // 2. Fetch payment draft JEs
            $this->db->query("SELECT id FROM customer_payments WHERE rep_route_id IN ($ridsStr)");
            $routePayments = $this->db->resultSet() ?: [];
            foreach ($routePayments as $pay) {
                $ref = "PMT-BAL-DRAFT-" . $pay->id;
                $this->db->query("SELECT id FROM journal_entries WHERE reference = :ref AND status = 'Draft' LIMIT 1");
                $this->db->bind(':ref', $ref);
                $je = $this->db->single();
                if ($je) {
                    $this->db->query("SELECT account_id FROM transactions WHERE journal_entry_id = :jid AND debit > 0 LIMIT 1");
                    $this->db->bind(':jid', $je->id);
                    $tDeb = $this->db->single();
                    if ($tDeb) {
                        $dynDebit["pay_" . $pay->id] = intval($tDeb->account_id);
                    }
                    
                    $this->db->query("SELECT account_id FROM transactions WHERE journal_entry_id = :jid AND credit > 0 LIMIT 1");
                    $this->db->bind(':jid', $je->id);
                    $tCred = $this->db->single();
                    if ($tCred) {
                        $dynCredit["pay_" . $pay->id] = intval($tCred->account_id);
                    }
                }
            }

            if (!empty($dynDebit) || !empty($dynCredit)) {
                $delivery->accounting_entries_json = json_encode([
                    'debit' => $dynDebit,
                    'credit' => $dynCredit
                ]);
            }
        }
        return $delivery;
    }

    public function getDeliveryInvoices($routeId, $secondaryRouteId = null) {
        $rids = [intval($routeId)];
        if ($secondaryRouteId) {
            $rids[] = intval($secondaryRouteId);
        }
        $rids = $this->resolveAllBoundRouteIds($rids);
        $ridsStr = implode(',', array_map('intval', $rids));

        $this->db->query("
            SELECT i.*, c.name as customer_name,
            (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as true_grand_total
            FROM invoices i
            JOIN customers c ON i.customer_id = c.id
            WHERE i.rep_route_id IN ($ridsStr)
            ORDER BY i.created_at ASC
        ");
        return $this->db->resultSet();
    }

    public function getDeliveryCreditInvoices($routeId, $secondaryRouteId = null) {
        // Fetch the delivery first to check if there are selected credit invoices
        $this->db->query("SELECT selected_credit_invoices FROM deliveries WHERE rep_route_id = :rid ORDER BY id DESC LIMIT 1");
        $this->db->bind(':rid', $routeId);
        $del = $this->db->single();
        
        $selectedIds = [];
        if ($del && !empty($del->selected_credit_invoices)) {
            $selectedIds = json_decode($del->selected_credit_invoices, true);
        }
        
        if (!empty($selectedIds) && is_array($selectedIds)) {
            $idList = implode(',', array_map('intval', $selectedIds));
            $this->db->query("
                SELECT i.*, c.name as customer_name,
                (i.total_amount - COALESCE(CASE WHEN i.global_discount_type = '%' THEN (i.total_amount * i.global_discount_val / 100) ELSE i.global_discount_val END, 0) + COALESCE(i.tax_amount, 0)) as true_grand_total
                FROM invoices i
                JOIN customers c ON i.customer_id = c.id
                WHERE i.id IN ($idList) AND i.status = 'Unpaid'
                ORDER BY i.invoice_date ASC, i.id ASC
            ");
            return $this->db->resultSet();
        }

        // Only show ticked bills. If none are ticked, return empty array.
        return [];
    }

    public function getDeliverySpreadsheetData($routeId, $secondaryRouteId = null) {
        $rids = [intval($routeId)];
        if ($secondaryRouteId) {
            $rids[] = intval($secondaryRouteId);
        }
        $rids = $this->resolveAllBoundRouteIds($rids);
        $ridsStr = implode(',', array_map('intval', $rids));

        $this->db->query("
            SELECT ii.description as item_name, SUM(ii.quantity) as total_qty 
            FROM invoice_items ii 
            JOIN invoices i ON ii.invoice_id = i.id 
            WHERE i.rep_route_id IN ($ridsStr) AND i.status != 'Voided' 
            GROUP BY ii.description 
            ORDER BY ii.description ASC
        ");
        return $this->db->resultSet();
    }

    public function getDeliveryBalancingData($deliveryId) {
        $delivery = $this->getDeliveryById($deliveryId);
        if (!$delivery) return null;

        $rids = [intval($delivery->rep_route_id)];
        if (!empty($delivery->secondary_rep_route_id)) {
            $rids[] = intval($delivery->secondary_rep_route_id);
        }
        $rids = $this->resolveAllBoundRouteIds($rids);
        $ridsStr = implode(',', array_map('intval', $rids));

        // Ensure 1605 account exists in chart of accounts
        try {
            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1605'");
            if (!$this->db->single()) {
                $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1600' LIMIT 1");
                $parentRow = $this->db->single();
                $pId = $parentRow ? $parentRow->id : null;

                $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, balance, parent_id) 
                                  VALUES ('1605', 'Temporary Bank Account', 'Asset', 0.00, :pid)");
                $this->db->bind(':pid', $pId);
                $this->db->execute();
            }
        } catch (Exception $e) {}

        // Ensure 1010 account exists for cheques
        try {
            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1010'");
            if (!$this->db->single()) {
                $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, balance, parent_id) 
                                  VALUES ('1010', 'Cheque in Hand', 'Asset', 0.00, NULL)");
                $this->db->execute();
            }
        } catch (Exception $e) {}

        // Ensure 1090 account exists
        try {
            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1090'");
            if (!$this->db->single()) {
                $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, balance, parent_id) 
                                  VALUES ('1090', 'Driver Transit Collections (Temp)', 'Asset', 0.00, NULL)");
                $this->db->execute();
            }
        } catch (Exception $e) {}

        // 1. Fetch Invoices stats (today's Cash Sales vs today's Credit Sales)
        $this->db->query("
            SELECT id, customer_id, delivery_status, status, total_amount, global_discount_val, global_discount_type, tax_amount 
            FROM invoices 
            WHERE rep_route_id IN ($ridsStr) AND status != 'Voided'
        ");
        $routeInvoices = $this->db->resultSet();

        $cash_sales = 0.0;
        $cheque_sales = 0.0;
        $bank_sales = 0.0;
        $credit_sales = 0.0;

        // Group dispatches by customer
        $custDispatches = [];
        foreach ($routeInvoices as $inv) {
            $amt = $this->getTrueGrandTotal($inv);
            $isDelivered = ($inv->delivery_status !== 'Cancelled' && $inv->delivery_status !== 'Postponed');
            if ($isDelivered) {
                $custDispatches[$inv->customer_id] = ($custDispatches[$inv->customer_id] ?? 0.0) + $amt;
            }
        }

        // For each customer who received a delivery, calculate their cash & credit sales
        foreach ($custDispatches as $cid => $dispatchAmt) {
            // Fetch customer payments on this route.
            $this->db->query("SELECT payment_method, COALESCE(SUM(amount), 0) as amt FROM customer_payments WHERE rep_route_id IN ($ridsStr) AND customer_id = :cid GROUP BY payment_method");
            $this->db->bind(':cid', $cid);
            $pays = $this->db->resultSet() ?: [];
            
            $totPaid = 0.0;
            $pMap = ['Cash' => 0.0, 'Cheque' => 0.0, 'Bank Transfer' => 0.0];
            foreach ($pays as $p) {
                $amt = floatval($p->amt);
                $totPaid += $amt;
                if (isset($pMap[$p->payment_method])) {
                    $pMap[$p->payment_method] += $amt;
                }
            }

            $pool = $dispatchAmt;
            // Allocate in order: Cash, then Bank Transfer, then Cheque
            $cCash = min($pool, $pMap['Cash']);
            $cash_sales += $cCash;
            $pool -= $cCash;

            $cBank = min($pool, $pMap['Bank Transfer']);
            $bank_sales += $cBank;
            $pool -= $cBank;

            $cCheque = min($pool, $pMap['Cheque']);
            $cheque_sales += $cCheque;
            $pool -= $cCheque;

            $credit_sales += $pool;
        }

        // 2. Fetch Driver Collections logged today
        $this->db->query("
            SELECT 
                COALESCE(SUM(CASE WHEN payment_method = 'Cash' THEN amount ELSE 0 END), 0) as cash_collections,
                COALESCE(SUM(CASE WHEN payment_method = 'Cheque' THEN amount ELSE 0 END), 0) as cheque_collections,
                COALESCE(SUM(CASE WHEN payment_method = 'Bank Transfer' THEN amount ELSE 0 END), 0) as bank_collections
            FROM customer_payments 
            WHERE rep_route_id IN ($ridsStr)
        ");
        $collectionsStats = $this->db->single();

        // 3. Fetch Stock summary
        $this->db->query("
            SELECT 
                MAX(ii.item_id) as item_id, 
                MAX(ii.variation_option_id) as variation_option_id,
                TRIM(ii.description) as item_name,
                SUM(ii.loaded_quantity) as loaded_qty,
                SUM(CASE WHEN i.delivery_status != 'Cancelled' AND i.delivery_status != 'Postponed' THEN ii.quantity ELSE 0 END) as delivered_qty,
                (SUM(ii.loaded_quantity) - SUM(CASE WHEN i.delivery_status != 'Cancelled' AND i.delivery_status != 'Postponed' THEN ii.quantity ELSE 0 END)) as remaining_qty
            FROM invoice_items ii
            JOIN invoices i ON ii.invoice_id = i.id
            WHERE i.rep_route_id IN ($ridsStr) AND i.status != 'Voided'
            GROUP BY TRIM(ii.description)
            ORDER BY TRIM(ii.description) ASC
        ");
        $stockItems = $this->db->resultSet();

        // 4. Fetch cheques collected on this delivery
        $this->db->query("
            SELECT c.*, cust.name as customer_name
            FROM cheques c
            JOIN customers cust ON c.customer_id = cust.id
            WHERE c.rep_route_id IN ($ridsStr)
        ");
        $chequesCollected = $this->db->resultSet();

        // 5. Fetch all ledger accounts
        $this->db->query("SELECT id, account_code, account_name FROM chart_of_accounts ORDER BY account_code ASC");
        $allAccounts = $this->db->resultSet() ?: [];

        // 6. Fetch bank accounts for selector
        $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1600'");
        $parent = $this->db->single();
        $parentId = $parent ? $parent->id : 0;
        $this->db->query("SELECT id, account_code, account_name FROM chart_of_accounts WHERE parent_id = :pid ORDER BY account_code ASC");
        $this->db->bind(':pid', $parentId);
        $bankAccounts = $this->db->resultSet() ?: [];

        // Fetch route collections payments
        $this->db->query("
            SELECT cp.*, cust.name as customer_name
            FROM pending_collections cp
            JOIN customers cust ON cp.customer_id = cust.id
            WHERE cp.route_id IN ($ridsStr)
        ");
        $payments = $this->db->resultSet() ?: [];

        return [
            'delivery' => $delivery,
            'cash_sales' => floatval($cash_sales),
            'cheque_sales' => floatval($cheque_sales),
            'bank_sales' => floatval($bank_sales),
            'credit_sales' => floatval($credit_sales),
            'cash_collections' => floatval($collectionsStats->cash_collections),
            'cheque_collections' => floatval($collectionsStats->cheque_collections),
            'bank_collections' => floatval($collectionsStats->bank_collections),
            'stock_items' => $stockItems,
            'cheques' => $chequesCollected,
            'all_accounts' => $allAccounts,
            'bank_accounts' => $bankAccounts,
            'payments' => $payments
        ];
    }

    public function finalizeDelivery($deliveryId, $adminUserId, $selectedPaymentIds = [], $selectedInvoiceIds = [], $debitAccounts = [], $creditAccounts = [], $returnedItems = [], $vehicleNumber = null, $driverName = null, $partnerName = null) {
        $this->db->beginTransaction();
        try {
            $delivery = $this->getDeliveryById($deliveryId);
            if (!$delivery) {
                throw new Exception("Delivery not found");
            }

            // Enforce that return stock verification must be saved first (CRIT-4)
            if ($delivery->return_stock_json === null || $delivery->return_stock_json === '') {
                throw new Exception("Cannot finalize delivery: Return stock verification has not been saved yet.");
            }
            
            // Merge draft mappings from deliveries.accounting_entries_json if empty
            if (empty($debitAccounts) || empty($creditAccounts)) {
                $this->db->query("SELECT accounting_entries_json FROM deliveries WHERE id = :id");
                $this->db->bind(':id', $deliveryId);
                $delRow = $this->db->single();
                if ($delRow && !empty($delRow->accounting_entries_json)) {
                    $draft = json_decode($delRow->accounting_entries_json, true);
                    if (is_array($draft)) {
                        if (empty($debitAccounts) && isset($draft['debit']) && is_array($draft['debit'])) {
                            $debitAccounts = $draft['debit'];
                        }
                        if (empty($creditAccounts) && isset($draft['credit']) && is_array($draft['credit'])) {
                            $creditAccounts = $draft['credit'];
                        }
                    }
                }
            }

            if ($delivery->status === 'Finalized') {
                throw new Exception("Delivery is already finalized");
            }

            if ($vehicleNumber !== null && $driverName !== null) {
                $this->db->query("UPDATE deliveries 
                                  SET vehicle_number = :v, driver_name = :d, partner_name = :p 
                                  WHERE id = :id");
                $this->db->bind(':v', $vehicleNumber);
                $this->db->bind(':d', $driverName);
                $this->db->bind(':p', $partnerName);
                $this->db->bind(':id', $deliveryId);
                $this->db->execute();
            }

            // Ensure 1605 account exists in chart of accounts
            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1605'");
            if (!$this->db->single()) {
                $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1600' LIMIT 1");
                $parentRow = $this->db->single();
                $pId = $parentRow ? $parentRow->id : null;

                $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, balance, parent_id) 
                                  VALUES ('1605', 'Temporary Bank Account', 'Asset', 0.00, :pid)");
                $this->db->bind(':pid', $pId);
                $this->db->execute();
            }

            // Ensure 1010 account exists for cheques
            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1010'");
            if (!$this->db->single()) {
                $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, balance, parent_id) 
                                  VALUES ('1010', 'Cheque in Hand', 'Asset', 0.00, NULL)");
                $this->db->execute();
            }

            // 1. Update statuses to Finalized
            $this->db->query("UPDATE deliveries SET status = 'Finalized' WHERE id = :id");
            $this->db->bind(':id', $deliveryId);
            $this->db->execute();

            $rids = [intval($delivery->rep_route_id)];
            if (!empty($delivery->secondary_rep_route_id)) {
                $rids[] = intval($delivery->secondary_rep_route_id);
            }
            $rids = $this->resolveAllBoundRouteIds($rids);
            $ridsStr = implode(',', array_map('intval', $rids));

            $this->db->query("UPDATE rep_daily_routes SET status = 'Finalized' WHERE id IN ($ridsStr)");
            $this->db->execute();

            // 2. Stock deductions & reservation releases

            $this->db->query("SELECT id, invoice_number, total_amount, global_discount_val, global_discount_type, tax_amount, journal_entry_id, delivery_status, stock_status FROM invoices WHERE rep_route_id IN ($ridsStr) AND status != 'Voided'");
            $invoices = $this->db->resultSet();

            require_once '../app/Models/FIFO.php';
            $fifo = new FIFO();

            foreach ($invoices as $invoice) {
                $this->db->query("SELECT * FROM invoice_items WHERE invoice_id = :iid");
                $this->db->bind(':iid', $invoice->id);
                $items = $this->db->resultSet();

                $isDelivered = ($invoice->delivery_status !== 'Cancelled' && $invoice->delivery_status !== 'Postponed');
                if ($isDelivered) {
                    if ($invoice->stock_status === 'reserved') {
                        foreach ($items as $item) {
                            $qty = floatval($item->quantity);
                            $loadedQty = floatval($item->loaded_quantity);
                            $itemId = $item->item_id;
                            $varId = $item->variation_option_id;

                            if (!$itemId && !empty($item->description)) {
                                $this->db->query("SELECT id FROM items WHERE name = :name LIMIT 1");
                                $this->db->bind(':name', $item->description);
                                $rowItem = $this->db->single();
                                if ($rowItem) $itemId = $rowItem->id;
                            }

                            if ($itemId) {
                                require_once '../app/Models/Item.php';
                                $itemModel = new Item();
                                $itemModel->updateStockDelta($itemId, -$qty);

                                $this->db->query("UPDATE items SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :loadedQty) WHERE id = :id");
                                $this->db->bind(':loadedQty', $loadedQty);
                                $this->db->bind(':id', $itemId);
                                $this->db->execute();
                            }
                            if ($varId) {
                                $this->db->query("UPDATE item_variation_options SET quantity_on_hand = GREATEST(0, CAST(quantity_on_hand AS SIGNED) - :qty) WHERE id = :id");
                                $this->db->bind(':qty', $qty);
                                $this->db->bind(':id', $varId);
                                $this->db->execute();

                                $this->db->query("UPDATE item_variation_options SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :loadedQty) WHERE id = :id");
                                $this->db->bind(':loadedQty', $loadedQty);
                                $this->db->bind(':id', $varId);
                                $this->db->execute();
                            }

                            $fifo->depleteStock($itemId, $varId, $qty, $item->id, null);

                            // Log stock movement in ledger (depletion)
                            require_once '../app/Models/StockLedger.php';
                            $ledger = new StockLedger();
                            $this->db->query("SELECT warehouse_id, cost_price FROM items WHERE id = :id");
                            $this->db->bind(':id', $itemId);
                            $itemRow = $this->db->single();
                            $whId = $itemRow ? $itemRow->warehouse_id : null;
                            $itemCost = $itemRow ? floatval($itemRow->cost_price > 0 ? $itemRow->cost_price : 0.00) : 0.00;
                            
                            $this->db->query("SELECT invoice_number FROM invoices WHERE id = :iid");
                            $this->db->bind(':iid', $invoice->id);
                            $invRow = $this->db->single();
                            $invNum = $invRow ? $invRow->invoice_number : '';
                            $ledger->logMovement($itemId, $varId ?: null, 0, $qty, 'Sales Invoice', $invNum, $whId, $adminUserId, 'Delivery Finalized - Stock Deducted', $itemCost);
                        }
                        $this->db->query("UPDATE invoices SET stock_status = 'deducted' WHERE id = :iid");
                        $this->db->bind(':iid', $invoice->id);
                        $this->db->execute();
                    }
                } else {
                    if ($invoice->stock_status === 'reserved') {
                        foreach ($items as $item) {
                            $loadedQty = floatval($item->loaded_quantity);
                            $itemId = $item->item_id;
                            $varId = $item->variation_option_id;

                            if (!$itemId && !empty($item->description)) {
                                $this->db->query("SELECT id FROM items WHERE name = :name LIMIT 1");
                                $this->db->bind(':name', $item->description);
                                $rowItem = $this->db->single();
                                if ($rowItem) $itemId = $rowItem->id;
                            }

                            if ($itemId) {
                                $this->db->query("UPDATE items SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :loadedQty) WHERE id = :id");
                                $this->db->bind(':loadedQty', $loadedQty);
                                $this->db->bind(':id', $itemId);
                                $this->db->execute();
                            }
                            if ($varId) {
                                $this->db->query("UPDATE item_variation_options SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :loadedQty) WHERE id = :id");
                                $this->db->bind(':loadedQty', $loadedQty);
                                $this->db->bind(':id', $varId);
                                $this->db->execute();
                            }
                        }
                        $this->db->query("UPDATE invoices SET stock_status = 'returned' WHERE id = :iid");
                        $this->db->bind(':iid', $invoice->id);
                        $this->db->execute();
                    }
                }
            }

            // 2b. Counted returns stock adjustment is now processed inside api_save_return_stock() to avoid double deduction/adjustment.


            // 3. Redundant sales JE creation loop removed to prevent revenue double-counting (CRIT-1).
            // Invoices already have their posted JEs and account balances updated at creation time in Invoice::createInvoiceWithAccounting().

            // 4. Financial Clearance collections balancing
            $this->db->query("
                SELECT * 
                FROM pending_collections 
                WHERE route_id IN ($ridsStr) AND status = 'Pending'
            ");
            $routePayments = $this->db->resultSet() ?: [];

            if (!empty($routePayments)) {
                $this->db->query("SELECT id, account_code FROM chart_of_accounts WHERE account_code IN ('1000', '1010', '1600', '1605', '1200', '1090')");
                $accounts = $this->db->resultSet();
                $accMap = [];
                foreach ($accounts as $a) { $accMap[$a->account_code] = $a->id; }

                $cashAcc = $accMap['1000'] ?? null;
                $chequeAcc = $accMap['1010'] ?? null;
                $tempBankAcc = $accMap['1605'] ?? ($accMap['1600'] ?? null);
                $arAcc = $accMap['1200'] ?? null;
                $transitAcc = $accMap['1090'] ?? null;

                foreach ($routePayments as $pay) {
                    $payId = intval($pay->id);
                    if (!empty($selectedPaymentIds) && !in_array($payId, $selectedPaymentIds)) {
                        continue;
                    }

                    $amount = floatval($pay->adjusted_amount !== null ? $pay->adjusted_amount : $pay->amount);
                    if ($amount <= 0) continue;

                    // Clean up any existing draft or incomplete journal entries for this payment to prevent collisions on retry
                    $this->db->query("SELECT id FROM journal_entries WHERE reference IN (:ref, :ref2)");
                    $this->db->bind(':ref', "PMT-BAL-" . $payId);
                    $this->db->bind(':ref2', "PMT-BAL-DRAFT-" . $payId);
                    $oldRows = $this->db->resultSet() ?: [];
                    foreach ($oldRows as $oldRow) {
                        $this->db->query("DELETE FROM transactions WHERE journal_entry_id = :jid");
                        $this->db->bind(':jid', $oldRow->id);
                        $this->db->execute();

                        $this->db->query("DELETE FROM journal_entries WHERE id = :id");
                        $this->db->bind(':id', $oldRow->id);
                        $this->db->execute();
                    }

                    $debAccId = isset($debitAccounts["pay_" . $payId]) ? intval($debitAccounts["pay_" . $payId]) : (isset($debitAccounts[$payId]) ? intval($debitAccounts[$payId]) : intval($pay->debit_account_id ?? 0));
                    if (!$debAccId) {
                        $method = $pay->payment_method;
                        if ($method === 'Cash') {
                            $debAccId = $cashAcc;
                        } elseif ($method === 'Cheque') {
                            $debAccId = $chequeAcc;
                        } elseif ($method === 'Bank Transfer') {
                            $debAccId = $tempBankAcc;
                        }
                    }
                    if (!$debAccId) continue;

                    $credAccId = isset($creditAccounts["pay_" . $payId]) ? intval($creditAccounts["pay_" . $payId]) : (isset($creditAccounts[$payId]) ? intval($creditAccounts[$payId]) : intval($pay->credit_account_id ?? 0));
                    if (!$credAccId) {
                        $credAccId = $transitAcc ?: $arAcc;
                    }

                    // Insert new Posted Journal Entry first to prevent partial updates with no rollback on failure
                    $this->db->query("INSERT INTO journal_entries (entry_date, reference, description, created_by, status) 
                                      VALUES (CURDATE(), :ref, :desc, :uid, 'Posted')");
                    $this->db->bind(':ref', "PMT-BAL-" . $payId);
                    $this->db->bind(':desc', "Finalized Delivery Collection (" . $pay->payment_method . ")");
                    $this->db->bind(':uid', $adminUserId);
                    $this->db->execute();
                    $payJid = $this->db->lastInsertId();

                    // Insert into customer_payments officially to credit customer subledger
                    $this->db->query("INSERT INTO customer_payments (customer_id, amount, unallocated_amount, payment_date, payment_method, reference, journal_entry_id, rep_route_id, created_by, status) 
                                      VALUES (:cid, :amt, :uamt, CURDATE(), :method, :ref, :jid, :rid, :uid, 'Active')");
                    $this->db->bind(':cid', $pay->customer_id);
                    $this->db->bind(':amt', $amount);
                    $this->db->bind(':uamt', $amount);
                    $this->db->bind(':method', $pay->payment_method);
                    $this->db->bind(':ref', $pay->cheque_number ? $pay->cheque_number : ($pay->reference ? $pay->reference : "Route Payment"));
                    $this->db->bind(':jid', $payJid);
                    $this->db->bind(':rid', $pay->route_id);
                    $this->db->bind(':uid', $adminUserId);
                    $this->db->execute();
                    $insertedPaymentId = $this->db->lastInsertId();

                    // If it is a cheque, register it in the cheques table as well
                    if ($pay->payment_method === 'Cheque') {
                        $this->db->query("INSERT INTO cheques (customer_id, bank_name, cheque_number, amount, banking_date, status, rep_route_id, created_by) 
                                          VALUES (:cid, :bn, :cn, :amt, :bdate, 'Pending', :rid, :uid)");
                        $this->db->bind(':cid', $pay->customer_id);
                        $this->db->bind(':bn', $pay->bank_name ?? 'Unknown');
                        $this->db->bind(':cn', $pay->cheque_number ?? 'Unknown');
                        $this->db->bind(':amt', $amount);
                        $this->db->bind(':bdate', $pay->cheque_date ?: date('Y-m-d'));
                        $this->db->bind(':rid', $pay->route_id);
                        $this->db->bind(':uid', $adminUserId);
                        $this->db->execute();
                    }

                    // Update pending_collections status to Finalized
                    $this->db->query("UPDATE pending_collections SET status = 'Finalized', is_verified = 1, verified_by = :vby, verified_at = NOW() WHERE id = :id");
                    $this->db->bind(':vby', $adminUserId);
                    $this->db->bind(':id', $payId);
                    $this->db->execute();

                    // Debit Asset Account
                    $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:jid, :aid, :deb, 0)");
                    $this->db->bind(':jid', $payJid);
                    $this->db->bind(':aid', $debAccId);
                    $this->db->bind(':deb', $amount);
                    $this->db->execute();

                    $this->db->query("UPDATE chart_of_accounts SET balance = balance + :amt WHERE id = :aid");
                    $this->db->bind(':amt', $amount);
                    $this->db->bind(':aid', $debAccId);
                    $this->db->execute();

                    // Credit cleared transit account
                    $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:jid, :aid, 0, :cred)");
                    $this->db->bind(':jid', $payJid);
                    $this->db->bind(':aid', $credAccId);
                    $this->db->bind(':cred', $amount);
                    $this->db->execute();

                    $this->db->query("UPDATE chart_of_accounts SET balance = balance - :amt WHERE id = :aid");
                    $this->db->bind(':amt', $amount);
                    $this->db->bind(':aid', $credAccId);
                    $this->db->execute();

                    // FIFO allocation
                    require_once __DIR__ . '/Payment.php';
                    $paymentModel = new Payment();
                    $paymentModel->settleCustomerInvoicesWithCreditNonTransactional($pay->customer_id, $adminUserId);
                }
            }

            // Clean up any remaining DRAFT journal entries and transactions for this route
            foreach ($invoices as $invoice) {
                $dbRef = "INV-SALES-DRAFT-" . $invoice->id;
                $this->db->query("SELECT id FROM journal_entries WHERE reference = :ref AND status = 'Draft'");
                $this->db->bind(':ref', $dbRef);
                $oldRows = $this->db->resultSet();
                foreach ($oldRows as $oldRow) {
                    $this->db->query("DELETE FROM transactions WHERE journal_entry_id = :jid");
                    $this->db->bind(':jid', $oldRow->id);
                    $this->db->execute();

                    $this->db->query("DELETE FROM journal_entries WHERE id = :id");
                    $this->db->bind(':id', $oldRow->id);
                    $this->db->execute();
                }
            }
            if (!empty($routePayments)) {
                foreach ($routePayments as $pay) {
                    $dbRef = "PMT-BAL-DRAFT-" . $pay->id;
                    $this->db->query("SELECT id FROM journal_entries WHERE reference = :ref AND status = 'Draft'");
                    $this->db->bind(':ref', $dbRef);
                    $oldRows = $this->db->resultSet();
                    foreach ($oldRows as $oldRow) {
                        $this->db->query("DELETE FROM transactions WHERE journal_entry_id = :jid");
                        $this->db->bind(':jid', $oldRow->id);
                        $this->db->execute();

                        $this->db->query("DELETE FROM journal_entries WHERE id = :id");
                        $this->db->bind(':id', $oldRow->id);
                        $this->db->execute();
                    }

                    if (!in_array(intval($pay->id), $selectedPaymentIds)) {
                        $this->db->query("UPDATE customer_payments SET journal_entry_id = NULL WHERE id = :pid AND journal_entry_id IN (SELECT id FROM journal_entries WHERE status = 'Draft')");
                        $this->db->bind(':pid', $pay->id);
                        $this->db->execute();
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("finalizeDelivery Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getTrueGrandTotal($invoice) {
        $subTotal = floatval($invoice->total_amount ?? 0);
        $globalDiscVal = floatval($invoice->global_discount_val ?? 0);
        $globalDiscType = $invoice->global_discount_type ?? 'Rs';
        $globalDisc = ($globalDiscType === '%') ? ($subTotal * $globalDiscVal / 100) : $globalDiscVal;
        return max(0, $subTotal - $globalDisc) + floatval($invoice->tax_amount ?? 0);
    }

    public function resolveAllBoundRouteIds($rids) {
        if (empty($rids)) return [];
        $rids = array_map('intval', $rids);
        $ridsStr = implode(',', array_map('intval', $rids));
        
        $this->db->query("SELECT DISTINCT route_binding_id FROM rep_daily_routes WHERE id IN ($ridsStr) AND route_binding_id IS NOT NULL");
        $bindings = $this->db->resultSet();
        
        if (!empty($bindings)) {
            $bindingIds = [];
            foreach ($bindings as $b) {
                $bindingIds[] = intval($b->route_binding_id);
            }
            $bindingIdsStr = implode(',', array_map('intval', $bindingIds));
            
            $this->db->query("SELECT id FROM rep_daily_routes WHERE route_binding_id IN ($bindingIdsStr)");
            $allRoutes = $this->db->resultSet();
            foreach ($allRoutes as $r) {
                $rids[] = intval($r->id);
            }
        }
        return array_unique($rids);
    }
}
