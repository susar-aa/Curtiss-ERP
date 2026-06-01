<?php

// Enable error reporting to prevent blank 500 errors in the future
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class RepTracking {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllRoutes() {
        $this->db->query("
            SELECT r.*, COALESCE(e.first_name, u.username) as first_name, COALESCE(e.last_name, '') as last_name,
                (SELECT COUNT(*) FROM invoices WHERE rep_route_id = r.id AND status != 'Voided') as bill_count,
                (SELECT COALESCE(SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)), 0) 
                 FROM invoices WHERE rep_route_id = r.id AND status != 'Voided') as total_sales,
                (SELECT COUNT(*) FROM pending_collections WHERE route_id = r.id AND status = 'Pending') as unfinalized_count
            FROM rep_daily_routes r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN employees e ON u.email = e.email
            WHERE r.id NOT IN (SELECT rep_route_id FROM deliveries)
            ORDER BY r.start_time DESC
        ");
        return $this->db->resultSet();
    }

    public function getRouteBills($routeId) {
        $this->db->query("
            SELECT i.*, c.name as customer_name,
            (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as true_grand_total
            FROM invoices i
            JOIN customers c ON i.customer_id = c.id
            WHERE i.rep_route_id = :rid
            ORDER BY i.created_at ASC
        ");
        $this->db->bind(':rid', $routeId);
        return $this->db->resultSet();
    }

    // UPDATED: Now fetches live high-level statistics for the printable report header block
    public function getRouteById($routeId) {
        $this->db->query("
            SELECT r.*, COALESCE(e.first_name, u.username) as first_name, COALESCE(e.last_name, '') as last_name,
                (SELECT COUNT(*) FROM invoices WHERE rep_route_id = r.id AND status != 'Voided') as bill_count,
                (SELECT COALESCE(SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)), 0) 
                 FROM invoices WHERE rep_route_id = r.id AND status != 'Voided') as total_sales
            FROM rep_daily_routes r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN employees e ON u.email = e.email
            WHERE r.id = :rid
        ");
        $this->db->bind(':rid', $routeId);
        return $this->db->single();
    }

    public function getRouteLoadingItems($routeId) {
        $this->db->query("
            SELECT ii.description as item_name,
                   SUM(ii.quantity) as total_qty,
                   COALESCE(ic.name, 'Uncategorized') as category_name
            FROM invoice_items ii
            JOIN invoices i ON ii.invoice_id = i.id
            LEFT JOIN items it ON ii.item_id = it.id
            LEFT JOIN item_categories ic ON it.category_id = ic.id
            WHERE i.rep_route_id = :rid AND i.status != 'Voided'
            GROUP BY ii.description, COALESCE(ic.id, 0), COALESCE(ic.name, 'Uncategorized')
            ORDER BY category_name ASC, ii.description ASC
        ");
        $this->db->bind(':rid', $routeId);
        return $this->db->resultSet();
    }

    /**
     * Chronological GPS path: day start → each invoice → day end.
     */
    public function getRoutePath($routeId) {
        $route = $this->getRouteById($routeId);
        if (!$route) {
            return null;
        }

        $waypoints = [];

        if (!empty($route->start_lat) && !empty($route->start_lng)) {
            $waypoints[] = [
                'type' => 'start',
                'lat' => (float) $route->start_lat,
                'lng' => (float) $route->start_lng,
                'time' => $route->start_time,
                'label' => 'Day Start',
                'detail' => $route->route_name,
            ];
        }

        $this->db->query("
            SELECT i.id, i.invoice_number, i.created_at, i.latitude, i.longitude, c.name as customer_name,
            (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as true_grand_total
            FROM invoices i
            JOIN customers c ON i.customer_id = c.id
            WHERE i.rep_route_id = :rid AND i.status != 'Voided'
              AND i.latitude IS NOT NULL AND i.longitude IS NOT NULL
            ORDER BY i.created_at ASC
        ");
        $this->db->bind(':rid', $routeId);
        $bills = $this->db->resultSet();

        $seq = 1;
        foreach ($bills as $bill) {
            $waypoints[] = [
                'type' => 'invoice',
                'id' => (int) $bill->id,
                'lat' => (float) $bill->latitude,
                'lng' => (float) $bill->longitude,
                'time' => $bill->created_at,
                'label' => $bill->invoice_number,
                'detail' => $bill->customer_name,
                'amount' => (float) $bill->true_grand_total,
                'sequence' => $seq++,
            ];
        }

        if (!empty($route->end_lat) && !empty($route->end_lng)) {
            $waypoints[] = [
                'type' => 'end',
                'lat' => (float) $route->end_lat,
                'lng' => (float) $route->end_lng,
                'time' => $route->end_time,
                'label' => 'Day End',
                'detail' => $route->status === 'Completed' ? 'Route completed' : 'Last recorded position',
            ];
        }

        return [
            'route_id' => (int) $routeId,
            'route_name' => $route->route_name,
            'rep_name' => trim($route->first_name . ' ' . $route->last_name),
            'status' => $route->status,
            'start_time' => $route->start_time,
            'end_time' => $route->end_time,
            'waypoints' => $waypoints,
            'point_count' => count($waypoints),
        ];
    }

    public function getRouteCollections($routeId) {
        $this->db->query("
            SELECT pc.id, pc.customer_id, pc.route_id as rep_route_id, pc.payment_method, pc.amount, 
                   pc.bank_name, pc.cheque_number, pc.cheque_date, pc.created_at, pc.status,
                   c.name as customer_name, pc.finalized_by, pc.notes,
                   CASE WHEN pc.status = 'Finalized' THEN 999999 ELSE NULL END as journal_entry_id,
                   DATE(pc.created_at) as payment_date,
                   COALESCE(pc.cheque_number, pc.bank_name, '') as reference
            FROM pending_collections pc
            JOIN customers c ON pc.customer_id = c.id
            WHERE pc.route_id = :rid
            ORDER BY pc.created_at ASC
        ");
        $this->db->bind(':rid', $routeId);
        return $this->db->resultSet() ?: [];
    }

    public function finalizePayments($paymentIds, $userId, $bankAllocations = []) {
        if (empty($paymentIds)) return true;

        // Resolve necessary account IDs based on codes
        $this->db->query("SELECT id, account_code FROM chart_of_accounts WHERE account_code IN ('1000', '1010', '1600', '1605', '1200')");
        $accounts = $this->db->resultSet();
        $accMap = [];
        foreach ($accounts as $a) {
            $accMap[$a->account_code] = $a->id;
        }

        $cashAcc = $accMap['1000'] ?? null;
        $chequeAcc = $accMap['1010'] ?? null;
        $tempBankAcc = $accMap['1605'] ?? $accMap['1600'] ?? null;
        $arAcc = $accMap['1200'] ?? null;

        if (!$arAcc) {
            throw new Exception("Accounts Receivable account (1200) not found in Chart of Accounts.");
        }

        foreach ($paymentIds as $pid) {
            // Fetch payment details from pending_collections
            $this->db->query("SELECT * FROM pending_collections WHERE id = :id AND status = 'Pending'");
            $this->db->bind(':id', $pid);
            $payment = $this->db->single();
            if (!$payment) continue;

            $amount = floatval($payment->amount);
            $method = $payment->payment_method;

            // Map payment method to asset account
            $assetAccId = null;
            if ($method === 'Cash') {
                $assetAccId = $cashAcc;
            } elseif ($method === 'Bank Transfer') {
                // Read granular target bank allocation for this specific payment
                $selectedBankAccId = isset($bankAllocations[$pid]) ? $bankAllocations[$pid] : null;
                $assetAccId = !empty($selectedBankAccId) ? $selectedBankAccId : $tempBankAcc;
            } elseif ($method === 'Cheque') {
                $assetAccId = $chequeAcc;
            }

            if (!$assetAccId) {
                continue; // Skip if no valid account mapped
            }

            // Create Journal Entry
            $this->db->query("INSERT INTO journal_entries (entry_date, reference, description, created_by, status) 
                              VALUES (CURDATE(), :ref, :desc, :uid, 'Posted')");
            $refStr = "FINAL-PMT-" . $payment->id;
            $descStr = "Finalized Route Collection (" . $method . ") for Customer ID: " . $payment->customer_id;
            $this->db->bind(':ref', $refStr);
            $this->db->bind(':desc', $descStr);
            $this->db->bind(':uid', $userId);
            $this->db->execute();
            $jid = $this->db->lastInsertId();

            // Transaction 1: Debit Asset Account (Cash / Bank / Cheque)
            $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:jid, :aid, :amount, 0)");
            $this->db->bind(':jid', $jid);
            $this->db->bind(':aid', $assetAccId);
            $this->db->bind(':amount', $amount);
            $this->db->execute();

            $this->db->query("UPDATE chart_of_accounts SET balance = balance + :amt WHERE id = :aid");
            $this->db->bind(':amt', $amount);
            $this->db->bind(':aid', $assetAccId);
            $this->db->execute();

            // Transaction 2: Credit Accounts Receivable (1200)
            $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:jid, :aid, 0, :amount)");
            $this->db->bind(':jid', $jid);
            $this->db->bind(':aid', $arAcc);
            $this->db->bind(':amount', $amount);
            $this->db->execute();

            $this->db->query("UPDATE chart_of_accounts SET balance = balance - :amt WHERE id = :aid");
            $this->db->bind(':amt', $amount);
            $this->db->bind(':aid', $arAcc);
            $this->db->execute();

            // Insert into customer_payments officially to credit customer subledger
            $this->db->query("INSERT INTO customer_payments (customer_id, amount, payment_method, payment_date, bank_name, cheque_number, cheque_date, reference, journal_entry_id, created_by, rep_route_id) 
                              VALUES (:cid, :amt, :method, CURDATE(), :bank, :chqnum, :chqdate, :ref, :jid, :uid, :rid)");
            $this->db->bind(':cid', $payment->customer_id);
            $this->db->bind(':amt', $amount);
            $this->db->bind(':method', $method);
            $this->db->bind(':bank', $payment->bank_name);
            $this->db->bind(':chqnum', $payment->cheque_number);
            $this->db->bind(':chqdate', $payment->cheque_date);
            $this->db->bind(':ref', $payment->cheque_number ?: $payment->bank_name ?: '');
            $this->db->bind(':jid', $jid);
            $this->db->bind(':uid', $userId);
            $this->db->bind(':rid', $payment->route_id);
            $this->db->execute();

            // Link pending collection to generated journal entry and mark as Finalized
            $this->db->query("UPDATE pending_collections SET status = 'Finalized', finalized_by = :uid, finalized_at = NOW() WHERE id = :pid");
            $this->db->bind(':uid', $userId);
            $this->db->bind(':pid', $pid);
            $this->db->execute();
        }

        return true;
    }
}