<?php

class Delivery {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function createDelivery($data) {
        $this->db->beginTransaction();

        try {
            // Check if there is already an existing delivery for this rep_route_id
            $this->db->query("SELECT id FROM deliveries WHERE rep_route_id = :rid LIMIT 1");
            $this->db->bind(':rid', $data['rep_route_id']);
            $existing = $this->db->single();

            if ($existing) {
                $deliveryId = intval($existing->id);
                // Update the existing delivery record
                $this->db->query("UPDATE deliveries SET 
                                    secondary_rep_route_id = :secondary_rep_route_id, 
                                    delivery_date = :delivery_date, 
                                    vehicle_number = :vehicle_number, 
                                    driver_name = :driver_name, 
                                    partner_name = :partner_name, 
                                    selected_credit_invoices = :selected_credit_invoices
                                  WHERE id = :id");
                $this->db->bind(':secondary_rep_route_id', $data['secondary_rep_route_id'] ?? null);
                $this->db->bind(':delivery_date', $data['delivery_date']);
                $this->db->bind(':vehicle_number', $data['vehicle_number']);
                $this->db->bind(':driver_name', $data['driver_name']);
                $this->db->bind(':partner_name', $data['partner_name']);
                $this->db->bind(':selected_credit_invoices', $data['selected_credit_invoices'] ?? null);
                $this->db->bind(':id', $deliveryId);
                $this->db->execute();
            } else {
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
            }

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
            throw $e;
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
            
            // 1. Fetch invoice JEs (reading directly from primary journal entry)
            $this->db->query("SELECT id, journal_entry_id FROM invoices WHERE rep_route_id IN ($ridsStr) AND status != 'Voided'");
            $routeInvoices = $this->db->resultSet() ?: [];
            foreach ($routeInvoices as $inv) {
                if (!empty($inv->journal_entry_id)) {
                    $this->db->query("SELECT account_id FROM transactions WHERE journal_entry_id = :jid AND debit > 0 LIMIT 1");
                    $this->db->bind(':jid', $inv->journal_entry_id);
                    $tDeb = $this->db->single();
                    if ($tDeb) {
                        $dynDebit["inv_" . $inv->id] = intval($tDeb->account_id);
                    }
                    
                    $this->db->query("SELECT account_id FROM transactions WHERE journal_entry_id = :jid AND credit > 0 LIMIT 1");
                    $this->db->bind(':jid', $inv->journal_entry_id);
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

    public function getVirtualDeliveryByRouteId($routeId) {
        $this->db->query("
            SELECT 0 as id, r.id as rep_route_id, null as secondary_rep_route_id, 
                   DATE(r.start_time) as delivery_date, '' as vehicle_number, '' as driver_name, '' as partner_name,
                   'Arranged' as status, null as start_meter, null as end_meter, r.created_at,
                   null as accepted_at, null as started_at, null as completed_at, null as cash_denominations,
                   null as selected_credit_invoices, null as reconciliation_json, null as return_stock_json,
                   null as accounting_entries_json, null as return_stock_verified_by, null as return_stock_verified_at,
                   r.route_name, r.start_time, r.user_id as user_id, COALESCE(e.first_name, u.username) as first_name, COALESCE(e.last_name, '') as last_name,
                   COALESCE(inv.bill_count, 0) as bill_count,
                   COALESCE(inv.total_sales, 0.00) as total_sales
            FROM rep_daily_routes r
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
            WHERE r.id = :route_id
        ");
        $this->db->bind(':route_id', $routeId);
        $delivery = $this->db->single();
        if ($delivery) {
            $delivery->id = 0;
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

    public function ensureRequiredAccountsExist() {
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

        // Ensure 4900 Cash Variance account exists
        try {
            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '4900'");
            if (!$this->db->single()) {
                $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, balance, parent_id) 
                                  VALUES ('4900', 'Cash Variance', 'Expense', 0.00, NULL)");
                $this->db->execute();
            }
        } catch (Exception $e) {}
    }

    public function getDeliveryBalancingData($deliveryId, $routeId = null) {
        if ($deliveryId > 0) {
            $delivery = $this->getDeliveryById($deliveryId);
        } else {
            $delivery = $this->getVirtualDeliveryByRouteId($routeId);
        }
        if (!$delivery) return null;

        $rids = [intval($delivery->rep_route_id)];
        if (!empty($delivery->secondary_rep_route_id)) {
            $rids[] = intval($delivery->secondary_rep_route_id);
        }
        $rids = $this->resolveAllBoundRouteIds($rids);
        $ridsStr = implode(',', array_map('intval', $rids));

        $this->ensureRequiredAccountsExist();

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
            // Fetch customer payments on this route (both pending and finalized).
            $this->db->query("
                SELECT payment_method, COALESCE(SUM(amount), 0) as amt 
                FROM (
                    SELECT payment_method, amount FROM customer_payments WHERE rep_route_id IN ($ridsStr) AND customer_id = :cid AND (status IS NULL OR status = 'Active')
                    UNION ALL
                    SELECT payment_method, amount FROM pending_collections WHERE route_id IN ($ridsStr) AND customer_id = :cid2 AND status = 'Pending'
                ) combined_payments
                GROUP BY payment_method
            ");
            $this->db->bind(':cid', $cid);
            $this->db->bind(':cid2', $cid);
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

        // 2. Fetch Driver Collections logged today (both pending and finalized)
        $this->db->query("
            SELECT 
                COALESCE(SUM(CASE WHEN payment_method = 'Cash' THEN amount ELSE 0 END), 0) as cash_collections,
                COALESCE(SUM(CASE WHEN payment_method = 'Cheque' THEN amount ELSE 0 END), 0) as cheque_collections,
                COALESCE(SUM(CASE WHEN payment_method = 'Bank Transfer' THEN amount ELSE 0 END), 0) as bank_collections
            FROM (
                SELECT payment_method, amount FROM customer_payments WHERE rep_route_id IN ($ridsStr) AND (status IS NULL OR status = 'Active')
                UNION ALL
                SELECT payment_method, amount FROM pending_collections WHERE route_id IN ($ridsStr) AND status = 'Pending'
            ) combined_all_payments
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

        // Query route expenses paid from collected cash
        $this->db->query("SELECT COALESCE(SUM(amount), 0.0) as amt FROM route_expenses WHERE rep_route_id IN ($ridsStr) AND payment_source = 'Collected Cash'");
        $collectedCashExpensesTotal = floatval($this->db->single()->amt ?? 0.0);
        $adjustedCashCollections = floatval($collectionsStats->cash_collections) - $collectedCashExpensesTotal;

        return [
            'delivery' => $delivery,
            'cash_sales' => floatval($cash_sales),
            'cheque_sales' => floatval($cheque_sales),
            'bank_sales' => floatval($bank_sales),
            'credit_sales' => floatval($credit_sales),
            'cash_collections' => max(0.0, $adjustedCashCollections),
            'raw_cash_collections' => floatval($collectionsStats->cash_collections),
            'cheque_collections' => floatval($collectionsStats->cheque_collections),
            'bank_collections' => floatval($collectionsStats->bank_collections),
            'stock_items' => $stockItems,
            'cheques' => $chequesCollected,
            'all_accounts' => $allAccounts,
            'bank_accounts' => $bankAccounts,
            'payments' => $payments,
            'collected_cash_expenses_total' => $collectedCashExpensesTotal
        ];
    }

    public function finalizeDelivery($deliveryId, $adminUserId, $selectedPaymentIds = [], $selectedInvoiceIds = [], $debitAccounts = [], $creditAccounts = [], $returnedItems = [], $vehicleNumber = null, $driverName = null, $partnerName = null) {
        $this->db->beginTransaction();
        try {
            // Lock the deliveries row immediately to prevent concurrent return stock save/other finalize (INV-2 Race Condition protection)
            $this->db->query("SELECT id, status, return_stock_json FROM deliveries WHERE id = :id FOR UPDATE");
            $this->db->bind(':id', $deliveryId);
            $lockedDelivery = $this->db->single();
            if (!$lockedDelivery) {
                throw new Exception("Delivery not found");
            }
            if ($lockedDelivery->status === 'Finalized') {
                throw new Exception("Delivery is already finalized");
            }
            if ($lockedDelivery->return_stock_json === null || $lockedDelivery->return_stock_json === '') {
                throw new Exception("Cannot finalize delivery: Return stock verification has not been saved yet.");
            }

            $delivery = $this->getDeliveryById($deliveryId);
            if (!$delivery) {
                throw new Exception("Delivery not found");
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
            $this->ensureRequiredAccountsExist();

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
                            $isFreeIssue = (floatval($item->unit_price ?? 0) <= 0 
                                            || floatval($item->total ?? 0) <= 0 
                                            || (isset($item->discount_type) && in_array($item->discount_type, ['Free Issue', 'Free']))
                                            || strpos($item->description ?? '', '(Free') !== false);

                            $movType = $isFreeIssue ? 'Promotional Free Issue' : 'Sales Invoice';
                            $remarks = $isFreeIssue ? 'Delivery Finalized - Free Issue Stock Deducted' : 'Delivery Finalized - Stock Deducted';

                            $ledger->logMovement($itemId, $varId ?: null, 0, $qty, $movType, $invNum, $whId, $adminUserId, $remarks, $itemCost);
                        }
                        $this->db->query("UPDATE invoices SET stock_status = 'deducted' WHERE id = :iid");
                        $this->db->bind(':iid', $invoice->id);
                        $this->db->execute();

                        // BUG-1 FIX: Promote Draft JE to Posted upon physical delivery finalization
                        $jid = $invoice->journal_entry_id;
                        if ($jid) {
                            $this->db->query("SELECT status FROM journal_entries WHERE id = :jid");
                            $this->db->bind(':jid', $jid);
                            $jeRow = $this->db->single();
                            if ($jeRow && $jeRow->status === 'Draft') {
                                // 1. Promote JE status to Posted
                                $this->db->query("UPDATE journal_entries SET status = 'Posted' WHERE id = :jid");
                                $this->db->bind(':jid', $jid);
                                $this->db->execute();

                                // 2. Calculate true current grand total
                                $subTotal = floatval($invoice->total_amount ?? 0);
                                $globalDiscVal = floatval($invoice->global_discount_val ?? 0);
                                $globalDiscType = $invoice->global_discount_type ?? 'Rs';
                                $globalDisc = ($globalDiscType === '%') ? ($subTotal * $globalDiscVal / 100) : $globalDiscVal;
                                $grandTotal = max(0, $subTotal - $globalDisc) + floatval($invoice->tax_amount ?? 0);

                                // 3. Resolve AR and Revenue transaction account IDs from the JE
                                $this->db->query("SELECT account_id FROM transactions WHERE journal_entry_id = :jid AND debit > 0 LIMIT 1");
                                $this->db->bind(':jid', $jid);
                                $arTx = $this->db->single();

                                $this->db->query("SELECT account_id FROM transactions WHERE journal_entry_id = :jid AND credit > 0 LIMIT 1");
                                $this->db->bind(':jid', $jid);
                                $revTx = $this->db->single();

                                if ($arTx) {
                                    $this->db->query("UPDATE transactions SET debit = :amt WHERE journal_entry_id = :jid AND account_id = :aid AND debit > 0");
                                    $this->db->bind(':amt', $grandTotal);
                                    $this->db->bind(':jid', $jid);
                                    $this->db->bind(':aid', $arTx->account_id);
                                    $this->db->execute();

                                    $this->db->updateAccountBalance($arTx->account_id, $grandTotal, 0);
                                }

                                if ($revTx) {
                                    $this->db->query("UPDATE transactions SET credit = :amt WHERE journal_entry_id = :jid AND account_id = :aid AND credit > 0");
                                    $this->db->bind(':amt', $grandTotal);
                                    $this->db->bind(':jid', $jid);
                                    $this->db->bind(':aid', $revTx->account_id);
                                    $this->db->execute();

                                    $this->db->updateAccountBalance($revTx->account_id, 0, $grandTotal);
                                }
                            }
                        }
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

                        // BUG-1 FIX: Void Draft JE if delivery is Cancelled / Postponed
                        $jid = $invoice->journal_entry_id;
                        if ($jid) {
                            $this->db->query("SELECT status FROM journal_entries WHERE id = :jid");
                            $this->db->bind(':jid', $jid);
                            $jeRow = $this->db->single();
                            if ($jeRow && $jeRow->status === 'Draft') {
                                $this->db->query("UPDATE journal_entries SET status = 'Voided' WHERE id = :jid");
                                $this->db->bind(':jid', $jid);
                                $this->db->execute();
                            }
                        }
                    }
                }
            }

            // 2b. Counted returns stock adjustment (CRIT-3)
            if (!empty($delivery->return_stock_json)) {
                $returnStockData = json_decode($delivery->return_stock_json, true);
                if (is_array($returnStockData)) {
                    require_once __DIR__ . '/Item.php';
                    $itemModel = new Item();
                    require_once __DIR__ . '/StockLedger.php';
                    $ledger = new StockLedger();

                    foreach ($returnStockData as $ret) {
                        $itemId = intval($ret['item_id'] ?? 0);
                        $varId = (!empty($ret['variation_option_id']) && is_numeric($ret['variation_option_id']) && intval($ret['variation_option_id']) > 0) ? intval($ret['variation_option_id']) : null;
                        $loadedQty = floatval($ret['loaded_qty'] ?? 0);
                        $deliveredQty = floatval($ret['delivered_qty'] ?? 0);
                        $actualReturnedQty = floatval($ret['actual_returned_qty'] ?? 0);

                        if (!$itemId && !empty($ret['item_name'])) {
                            $this->db->query("SELECT id FROM items WHERE name = :name LIMIT 1");
                            $this->db->bind(':name', $ret['item_name']);
                            $rowItem = $this->db->single();
                            if ($rowItem) {
                                $itemId = $rowItem->id;
                            }
                        }

                        $expectedReturned = $loadedQty - $deliveredQty;
                        $adjustment = $actualReturnedQty - $expectedReturned;
                        if ($adjustment != 0) {
                            if ($itemId) {
                                $itemModel->updateStockDelta($itemId, $adjustment);
                            }
                            if ($varId !== null) {
                                $this->db->query("UPDATE item_variation_options SET quantity_on_hand = GREATEST(0, CAST(quantity_on_hand AS SIGNED) + :adj) WHERE id = :id");
                                $this->db->bind(':adj', $adjustment);
                                $this->db->bind(':id', $varId);
                                $this->db->execute();
                            }

                            // Log stock movement in ledger (counted returns adjustment)
                            $this->db->query("SELECT warehouse_id, cost_price FROM items WHERE id = :id");
                            $this->db->bind(':id', $itemId);
                            $itemRow = $this->db->single();
                            $whId = $itemRow ? $itemRow->warehouse_id : null;
                            $itemCost = $itemRow ? floatval($itemRow->cost_price > 0 ? $itemRow->cost_price : 0.00) : 0.00;

                            $qtyIn = $adjustment > 0 ? $adjustment : 0;
                            $qtyOut = $adjustment < 0 ? abs($adjustment) : 0;
                            $ledger->logMovement($itemId, $varId, $qtyIn, $qtyOut, 'Stock Adjustment', 'DEL-' . $deliveryId, $whId, $adminUserId, 'Delivery Return Stock Finalized - Counted Returns Adjustment', $itemCost);
                        }
                    }
                }
            }


            // 3. Finalize Reserved Sales Order Journal Entries (BUG-1 Fix)
            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1200' LIMIT 1");
            $arAcc = $this->db->single();
            $defaultArId = $arAcc ? intval($arAcc->id) : 0;

            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '4000' LIMIT 1");
            $revAcc = $this->db->single();
            $defaultRevId = $revAcc ? intval($revAcc->id) : 0;

            foreach ($invoices as $invoice) {
                $jeId = $invoice->journal_entry_id ? intval($invoice->journal_entry_id) : 0;
                $jeRow = null;

                if ($jeId > 0) {
                    $this->db->query("SELECT id, status FROM journal_entries WHERE id = :jid");
                    $this->db->bind(':jid', $jeId);
                    $jeRow = $this->db->single();
                }

                if (!$jeRow && !empty($invoice->invoice_number)) {
                    $this->db->query("SELECT id, status FROM journal_entries WHERE reference = :ref LIMIT 1");
                    $this->db->bind(':ref', $invoice->invoice_number);
                    $jeRow = $this->db->single();
                    if ($jeRow) {
                        $jeId = intval($jeRow->id);
                        $this->db->query("UPDATE invoices SET journal_entry_id = :jid WHERE id = :iid");
                        $this->db->bind(':jid', $jeId);
                        $this->db->bind(':iid', $invoice->id);
                        $this->db->execute();
                    }
                }

                if ($jeRow && $jeRow->status === 'Draft') {
                    // Promote Journal Entry status from Draft to Posted
                    $this->db->query("UPDATE journal_entries SET status = 'Posted' WHERE id = :jid");
                    $this->db->bind(':jid', $jeId);
                    $this->db->execute();

                    // Update Chart of Accounts balances for linked transactions
                    $this->db->query("SELECT account_id, debit, credit FROM transactions WHERE journal_entry_id = :jid");
                    $this->db->bind(':jid', $jeId);
                    $txs = $this->db->resultSet() ?: [];
                    foreach ($txs as $tx) {
                        $this->db->updateAccountBalance(intval($tx->account_id), floatval($tx->debit), floatval($tx->credit));
                    }

                    // Update invoice stock status to deducted
                    $this->db->query("UPDATE invoices SET stock_status = 'deducted' WHERE id = :iid");
                    $this->db->bind(':iid', $invoice->id);
                    $this->db->execute();
                } elseif (!$jeRow && $defaultArId > 0 && $defaultRevId > 0) {
                    // Create and post Journal Entry if missing
                    $grandTotal = $this->getTrueGrandTotal($invoice);
                    if ($grandTotal > 0) {
                        $this->db->query("INSERT INTO journal_entries (entry_date, reference, description, created_by, status) 
                                          VALUES (NOW(), :reference, :description, :created_by, 'Posted')");
                        $this->db->bind(':reference', $invoice->invoice_number);
                        $this->db->bind(':description', 'Invoice Finalized - ' . $invoice->invoice_number);
                        $this->db->bind(':created_by', $adminUserId);
                        $this->db->execute();
                        $newJeId = intval($this->db->lastInsertId());

                        $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:jid, :acc, :debit, 0)");
                        $this->db->bind(':jid', $newJeId);
                        $this->db->bind(':acc', $defaultArId);
                        $this->db->bind(':debit', $grandTotal);
                        $this->db->execute();
                        $this->db->updateAccountBalance($defaultArId, $grandTotal, 0);

                        $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:jid, :acc, 0, :credit)");
                        $this->db->bind(':jid', $newJeId);
                        $this->db->bind(':acc', $defaultRevId);
                        $this->db->bind(':credit', $grandTotal);
                        $this->db->execute();
                        $this->db->updateAccountBalance($defaultRevId, 0, $grandTotal);

                        $this->db->query("UPDATE invoices SET journal_entry_id = :jid, stock_status = 'deducted' WHERE id = :iid");
                        $this->db->bind(':jid', $newJeId);
                        $this->db->bind(':iid', $invoice->id);
                        $this->db->execute();
                    }
                }
            }

            // 4. Financial Clearance collections balancing
            // ACCT-1 FIX: Delegate to the canonical RepTracking::finalizePayments() method
            // to eliminate duplicate GL posting paths and prevent double-posting.
            $this->db->query("
                SELECT id 
                FROM pending_collections 
                WHERE route_id IN ($ridsStr) AND status = 'Pending'
            ");
            $routePayments = $this->db->resultSet() ?: [];

            if (!empty($routePayments)) {
                // Build payment IDs list and account override maps
                $payIdsToFinalize = [];
                $customDebitMap = [];
                $customCreditMap = [];

                foreach ($routePayments as $pay) {
                    $payId = intval($pay->id);
                    if (!empty($selectedPaymentIds) && !in_array($payId, $selectedPaymentIds)) {
                        continue;
                    }
                    $payIdsToFinalize[] = $payId;

                    // Map custom debit/credit accounts from delivery accounting entries
                    if (isset($debitAccounts["pay_" . $payId])) {
                        $customDebitMap[$payId] = intval($debitAccounts["pay_" . $payId]);
                    } elseif (isset($debitAccounts[$payId])) {
                        $customDebitMap[$payId] = intval($debitAccounts[$payId]);
                    }
                    if (isset($creditAccounts["pay_" . $payId])) {
                        $customCreditMap[$payId] = intval($creditAccounts["pay_" . $payId]);
                    } elseif (isset($creditAccounts[$payId])) {
                        $customCreditMap[$payId] = intval($creditAccounts[$payId]);
                    }

                    // Clean up any existing draft journal entries for this payment
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
                }

                if (!empty($payIdsToFinalize)) {
                    require_once __DIR__ . '/RepTracking.php';
                    $trackingModel = new RepTracking();
                    $trackingModel->finalizePayments($payIdsToFinalize, $adminUserId, [], $customDebitMap, $customCreditMap);
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

            // Post unposted route expenses to GL during route finalization
            $this->db->query("SELECT * FROM route_expenses WHERE rep_route_id IN ($ridsStr) AND journal_entry_id IS NULL");
            $unpostedExpenses = $this->db->resultSet() ?: [];

            if (!empty($unpostedExpenses)) {
                require_once __DIR__ . '/../Services/RouteExpenseService.php';
                require_once __DIR__ . '/JournalEntry.php';
                $expenseService = new RouteExpenseService();
                $journalModel = new JournalEntry();

                foreach ($unpostedExpenses as $exp) {
                    $amount = floatval($exp->amount);
                    if ($amount <= 0) continue;

                    $expType = $exp->expense_type ?: 'Other';
                    $expSource = $exp->payment_source ?: 'Collected Cash';
                    $vehicleNum = $exp->vehicle_number ?: null;
                    $expenseAccountId = $expenseService->getOrCreateExpenseAccount($expType, $vehicleNum);
                    if (!$expenseAccountId) continue;

                    $creditAccountId = 0;
                    if ($expSource === 'Petty Cash') {
                        require_once __DIR__ . '/PettyCashTransaction.php';
                        $pcModel = new PettyCashTransaction();
                        $creditAccountId = $pcModel->getPettyCashAccountId();
                    } else {
                        // Collected Cash or Cash: Use 1090 Driver Transit Collections (Temp)
                        $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1090' LIMIT 1");
                        $clearingRow = $this->db->single();
                        if ($clearingRow) {
                            $creditAccountId = intval($clearingRow->id);
                        } else {
                            $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, balance, parent_id) 
                                              VALUES ('1090', 'Driver Transit Collections (Temp)', 'Asset', 0.00, NULL)");
                            $this->db->execute();
                            $creditAccountId = intval($this->db->lastInsertId());
                        }
                    }

                    $ref = 'RT-EXP-' . str_pad((string)$exp->rep_route_id, 5, '0', STR_PAD_LEFT) . '-' . $exp->id;
                    $journalDesc = "Route Expense [{$expType}] for Route #RT-" . str_pad((string)$exp->rep_route_id, 5, '0', STR_PAD_LEFT) . " - " . $exp->description;

                    $lines = [
                        [
                            'account_id' => $expenseAccountId,
                            'debit' => $amount,
                            'credit' => 0.0,
                            'description' => $journalDesc
                        ],
                        [
                            'account_id' => $creditAccountId,
                            'debit' => 0.0,
                            'credit' => $amount,
                            'description' => $journalDesc
                        ]
                    ];

                    $postRes = $journalModel->postEntry(date('Y-m-d', strtotime($exp->expense_date ?: 'now')), $ref, $journalDesc, $lines, $adminUserId);
                    if ($postRes === true) {
                        $this->db->query("SELECT id FROM journal_entries WHERE reference = :ref LIMIT 1");
                        $this->db->bind(':ref', $ref);
                        $jeRow = $this->db->single();
                        if ($jeRow) {
                            $newJid = intval($jeRow->id);
                            $this->db->query("UPDATE route_expenses SET journal_entry_id = :jid WHERE id = :eid");
                            $this->db->bind(':jid', $newJid);
                            $this->db->bind(':eid', $exp->id);
                            $this->db->execute();
                        }
                    }
                }
            }
            // Calculate and post Cash Variance if any
            $actualCash = 0.0;
            if (!empty($delivery->reconciliation_json)) {
                $recon = json_decode($delivery->reconciliation_json, true);
                if (is_array($recon)) {
                    $actualCash = floatval($recon['actual_cash'] ?? 0.0);
                }
            }

            $balancing = $this->getDeliveryBalancingData($deliveryId, $delivery->rep_route_id);
            $rawExpectedCash = floatval($balancing['raw_cash_collections'] ?? 0.0);
            $routeExpenses = floatval($balancing['collected_cash_expenses_total'] ?? 0.0);
            $netExpectedCash = max(0.0, $rawExpectedCash - $routeExpenses);
            $variance = $actualCash - $netExpectedCash;

            if (abs($variance) > 0.005) {
                // Resolve 4900 and 1000 account IDs
                $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '4900' LIMIT 1");
                $varAccRow = $this->db->single();
                $varAccId = $varAccRow ? intval($varAccRow->id) : null;

                $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1000' LIMIT 1");
                $cashAccRow = $this->db->single();
                $cashAccId = $cashAccRow ? intval($cashAccRow->id) : null;

                if ($varAccId && $cashAccId) {
                    $ref = 'RT-VAR-' . $deliveryId;
                    $journalDesc = "Cash Variance Adjustment for Route Finalization #DEL-" . str_pad((string)$deliveryId, 5, '0', STR_PAD_LEFT);
                    $lines = [];

                    if ($variance < 0) {
                        // Shortage: Debit Variance (Expense), Credit Cash
                        $amt = abs($variance);
                        $lines[] = [
                            'account_id' => $varAccId,
                            'debit' => $amt,
                            'credit' => 0.0,
                            'description' => $journalDesc . " (Shortage)"
                        ];
                        $lines[] = [
                            'account_id' => $cashAccId,
                            'debit' => 0.0,
                            'credit' => $amt,
                            'description' => $journalDesc . " (Shortage)"
                        ];
                    } else {
                        // Overage: Debit Cash, Credit Variance (Expense)
                        $amt = $variance;
                        $lines[] = [
                            'account_id' => $cashAccId,
                            'debit' => $amt,
                            'credit' => 0.0,
                            'description' => $journalDesc . " (Overage)"
                        ];
                        $lines[] = [
                            'account_id' => $varAccId,
                            'debit' => 0.0,
                            'credit' => $amt,
                            'description' => $journalDesc . " (Overage)"
                        ];
                    }

                    // Clean up any existing variance entries
                    $this->db->query("SELECT id FROM journal_entries WHERE reference = :ref");
                    $this->db->bind(':ref', $ref);
                    $oldVarJEs = $this->db->resultSet() ?: [];
                    foreach ($oldVarJEs as $oldVarJE) {
                        $this->db->query("DELETE FROM transactions WHERE journal_entry_id = :jid");
                        $this->db->bind(':jid', $oldVarJE->id);
                        $this->db->execute();

                        $this->db->query("DELETE FROM journal_entries WHERE id = :id");
                        $this->db->bind(':id', $oldVarJE->id);
                        $this->db->execute();
                    }

                    require_once __DIR__ . '/JournalEntry.php';
                    $journalModel = new JournalEntry();
                    $journalModel->postEntry(date('Y-m-d'), $ref, $journalDesc, $lines, $adminUserId);
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
