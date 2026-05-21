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
            $this->db->query("INSERT INTO deliveries (rep_route_id, delivery_date, vehicle_number, driver_name, partner_name) 
                              VALUES (:rep_route_id, :delivery_date, :vehicle_number, :driver_name, :partner_name)");
            $this->db->bind(':rep_route_id', $data['rep_route_id']);
            $this->db->bind(':delivery_date', $data['delivery_date']);
            $this->db->bind(':vehicle_number', $data['vehicle_number']);
            $this->db->bind(':driver_name', $data['driver_name']);
            $this->db->bind(':partner_name', $data['partner_name']);
            $this->db->execute();
            $deliveryId = $this->db->lastInsertId();

            // 2. Update status of rep_daily_routes to 'Delivery Arranged'
            $this->db->query("UPDATE rep_daily_routes SET status = 'Delivery Arranged' WHERE id = :route_id");
            $this->db->bind(':route_id', $data['rep_route_id']);
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
                (SELECT COUNT(*) FROM invoices WHERE rep_route_id = r.id AND status != 'Voided') as bill_count,
                (SELECT COALESCE(SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)), 0) 
                 FROM invoices WHERE rep_route_id = r.id AND status != 'Voided') as total_sales
            FROM deliveries d
            JOIN rep_daily_routes r ON d.rep_route_id = r.id
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN employees e ON u.email = e.email
            ORDER BY d.delivery_date DESC, d.created_at DESC
        ");
        return $this->db->resultSet();
    }

    public function getDeliveryById($id) {
        $this->db->query("
            SELECT d.*, r.route_name, r.start_time, r.user_id as user_id, COALESCE(e.first_name, u.username) as first_name, COALESCE(e.last_name, '') as last_name,
                (SELECT COUNT(*) FROM invoices WHERE rep_route_id = r.id AND status != 'Voided') as bill_count,
                (SELECT COALESCE(SUM(total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)), 0) 
                 FROM invoices WHERE rep_route_id = r.id AND status != 'Voided') as total_sales
            FROM deliveries d
            JOIN rep_daily_routes r ON d.rep_route_id = r.id
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN employees e ON u.email = e.email
            WHERE d.id = :id
        ");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getDeliveryInvoices($routeId) {
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

    public function getDeliverySpreadsheetData($routeId) {
        $this->db->query("
            SELECT ii.description as item_name, SUM(ii.quantity) as total_qty 
            FROM invoice_items ii 
            JOIN invoices i ON ii.invoice_id = i.id 
            WHERE i.rep_route_id = :rid AND i.status != 'Voided' 
            GROUP BY ii.description 
            ORDER BY ii.description ASC
        ");
        $this->db->bind(':rid', $routeId);
        return $this->db->resultSet();
    }

    public function getDeliveryBalancingData($deliveryId) {
        $delivery = $this->getDeliveryById($deliveryId);
        if (!$delivery) return null;

        // 1. Fetch Invoices stats (today's Cash Sales vs today's Credit Sales)
        $this->db->query("
            SELECT 
                COALESCE(SUM(CASE WHEN status = 'Paid' THEN (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) ELSE 0 END), 0) as cash_sales,
                COALESCE(SUM(CASE WHEN status = 'Unpaid' THEN (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) ELSE 0 END), 0) as credit_sales
            FROM invoices 
            WHERE rep_route_id = :rid AND status != 'Voided'
        ");
        $this->db->bind(':rid', $delivery->rep_route_id);
        $salesStats = $this->db->single();

        // 2. Fetch Driver Collections logged today
        $this->db->query("
            SELECT 
                COALESCE(SUM(CASE WHEN payment_method = 'Cash' THEN amount ELSE 0 END), 0) as cash_collections,
                COALESCE(SUM(CASE WHEN payment_method = 'Cheque' THEN amount ELSE 0 END), 0) as cheque_collections,
                COALESCE(SUM(CASE WHEN payment_method = 'Bank Transfer' THEN amount ELSE 0 END), 0) as bank_collections
            FROM customer_payments 
            WHERE rep_route_id = :rid
        ");
        $this->db->bind(':rid', $delivery->rep_route_id);
        $collectionsStats = $this->db->single();

        // 3. Fetch Stock summary
        $this->db->query("
            SELECT 
                MAX(ii.item_id) as item_id, 
                MAX(ii.variation_option_id) as variation_option_id,
                TRIM(ii.description) as item_name,
                SUM(ii.loaded_quantity) as loaded_qty,
                SUM(CASE WHEN i.delivery_status = 'Delivered' THEN ii.quantity ELSE 0 END) as delivered_qty,
                (SUM(ii.loaded_quantity) - SUM(CASE WHEN i.delivery_status = 'Delivered' THEN ii.quantity ELSE 0 END)) as remaining_qty
            FROM invoice_items ii
            JOIN invoices i ON ii.invoice_id = i.id
            WHERE i.rep_route_id = :rid AND i.status != 'Voided'
            GROUP BY TRIM(ii.description)
            ORDER BY TRIM(ii.description) ASC
        ");
        $this->db->bind(':rid', $delivery->rep_route_id);
        $stockItems = $this->db->resultSet();

        // 4. Fetch cheques collected on this delivery
        $this->db->query("
            SELECT c.*, cust.name as customer_name
            FROM cheques c
            JOIN customers cust ON c.customer_id = cust.id
            WHERE c.rep_route_id = :rid
        ");
        $this->db->bind(':rid', $delivery->rep_route_id);
        $chequesCollected = $this->db->resultSet();

        return [
            'delivery' => $delivery,
            'cash_sales' => floatval($salesStats->cash_sales),
            'credit_sales' => floatval($salesStats->credit_sales),
            'cash_collections' => floatval($collectionsStats->cash_collections),
            'cheque_collections' => floatval($collectionsStats->cheque_collections),
            'bank_collections' => floatval($collectionsStats->bank_collections),
            'stock_items' => $stockItems,
            'cheques' => $chequesCollected
        ];
    }

    public function finalizeDelivery($deliveryId, $adminUserId) {
        $this->db->beginTransaction();
        try {
            $delivery = $this->getDeliveryById($deliveryId);
            if (!$delivery) {
                throw new Exception("Delivery not found");
            }
            if ($delivery->status === 'Finalized') {
                throw new Exception("Delivery is already finalized");
            }

            // 1. Update statuses to Finalized
            $this->db->query("UPDATE deliveries SET status = 'Finalized' WHERE id = :id");
            $this->db->bind(':id', $deliveryId);
            $this->db->execute();

            $this->db->query("UPDATE rep_daily_routes SET status = 'Finalized' WHERE id = :route_id");
            $this->db->bind(':route_id', $delivery->rep_route_id);
            $this->db->execute();

            // 2. Stock deductions & reservation releases
            $this->db->query("SELECT id, delivery_status, stock_status FROM invoices WHERE rep_route_id = :route_id AND status != 'Voided'");
            $this->db->bind(':route_id', $delivery->rep_route_id);
            $invoices = $this->db->resultSet();

            foreach ($invoices as $invoice) {
                $this->db->query("SELECT * FROM invoice_items WHERE invoice_id = :iid");
                $this->db->bind(':iid', $invoice->id);
                $items = $this->db->resultSet();

                if ($invoice->delivery_status === 'Delivered') {
                    if ($invoice->stock_status === 'reserved') {
                        foreach ($items as $item) {
                            $qty = floatval($item->quantity);
                            $itemId = $item->item_id;
                            $varId = $item->variation_option_id;

                            // Fallback to description name match if itemId is null
                            if (!$itemId && !empty($item->description)) {
                                $this->db->query("SELECT id FROM items WHERE name = :name LIMIT 1");
                                $this->db->bind(':name', $item->description);
                                $rowItem = $this->db->single();
                                if ($rowItem) $itemId = $rowItem->id;
                            }

                            if ($itemId) {
                                // Deduct physical stock
                                $this->db->query("UPDATE items SET quantity_on_hand = GREATEST(0, CAST(quantity_on_hand AS SIGNED) - :qty) WHERE id = :id");
                                $this->db->bind(':qty', $qty);
                                $this->db->bind(':id', $itemId);
                                $this->db->execute();

                                // Release reserved stock
                                $this->db->query("UPDATE items SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :qty) WHERE id = :id");
                                $this->db->bind(':qty', $qty);
                                $this->db->bind(':id', $itemId);
                                $this->db->execute();
                            }
                            if ($varId) {
                                // Deduct variation physical stock
                                $this->db->query("UPDATE item_variation_options SET quantity_on_hand = GREATEST(0, CAST(quantity_on_hand AS SIGNED) - :qty) WHERE id = :id");
                                $this->db->bind(':qty', $qty);
                                $this->db->bind(':id', $varId);
                                $this->db->execute();

                                // Release variation reserved stock
                                $this->db->query("UPDATE item_variation_options SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :qty) WHERE id = :id");
                                $this->db->bind(':qty', $qty);
                                $this->db->bind(':id', $varId);
                                $this->db->execute();
                            }
                        }
                        $this->db->query("UPDATE invoices SET stock_status = 'deducted' WHERE id = :iid");
                        $this->db->bind(':iid', $invoice->id);
                        $this->db->execute();
                    }
                } else {
                    // Release reserved stock for undelivered invoices
                    if ($invoice->stock_status === 'reserved') {
                        foreach ($items as $item) {
                            $qty = floatval($item->quantity);
                            $itemId = $item->item_id;
                            $varId = $item->variation_option_id;

                            if (!$itemId && !empty($item->description)) {
                                $this->db->query("SELECT id FROM items WHERE name = :name LIMIT 1");
                                $this->db->bind(':name', $item->description);
                                $rowItem = $this->db->single();
                                if ($rowItem) $itemId = $rowItem->id;
                            }

                            if ($itemId) {
                                $this->db->query("UPDATE items SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :qty) WHERE id = :id");
                                $this->db->bind(':qty', $qty);
                                $this->db->bind(':id', $itemId);
                                $this->db->execute();
                            }
                            if ($varId) {
                                $this->db->query("UPDATE item_variation_options SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) - :qty) WHERE id = :id");
                                $this->db->bind(':qty', $qty);
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

            // 3. Financial Clearance balancing: Transfer from 1090 transit account to final cash, cheque, bank
            $this->db->query("
                SELECT payment_method, SUM(amount) as total_amount 
                FROM customer_payments 
                WHERE rep_route_id = :rid
                GROUP BY payment_method
            ");
            $this->db->bind(':rid', $delivery->rep_route_id);
            $payments = $this->db->resultSet();

            $totalCollectedAmt = 0.0;
            $cashTransit = 0.0;
            $chequeTransit = 0.0;
            $bankTransit = 0.0;

            foreach ($payments as $pay) {
                $amt = floatval($pay->total_amount);
                if ($amt <= 0) continue;
                $totalCollectedAmt += $amt;
                if ($pay->payment_method === 'Cash') {
                    $cashTransit = $amt;
                } elseif ($pay->payment_method === 'Cheque') {
                    $chequeTransit = $amt;
                } elseif ($pay->payment_method === 'Bank Transfer') {
                    $bankTransit = $amt;
                }
            }

            if ($totalCollectedAmt > 0) {
                $this->db->query("SELECT id, account_code FROM chart_of_accounts WHERE account_code IN ('1000', '1010', '1600', '1090')");
                $accounts = $this->db->resultSet();
                $accMap = [];
                foreach ($accounts as $a) { $accMap[$a->account_code] = $a->id; }

                $cashAcc = $accMap['1000'] ?? null;
                $chequeAcc = $accMap['1010'] ?? null;
                $bankAcc = $accMap['1600'] ?? null;
                $transitAcc = $accMap['1090'] ?? null;

                if ($transitAcc) {
                    $refCode = "BAL-FIN-" . time() . rand(10,99);
                    $this->db->query("INSERT INTO journal_entries (entry_date, reference, description, created_by, status) 
                                      VALUES (CURDATE(), :ref, 'Admin Delivery Trip Settle Balancing Finalization', :uid, 'Posted')");
                    $this->db->bind(':ref', $refCode);
                    $this->db->bind(':uid', $adminUserId);
                    $this->db->execute();
                    $balanceJid = $this->db->lastInsertId();

                    // Credit Transit Account (1090)
                    $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:jid, :aid, 0, :cred)");
                    $this->db->bind(':jid', $balanceJid);
                    $this->db->bind(':aid', $transitAcc);
                    $this->db->bind(':cred', $totalCollectedAmt);
                    $this->db->execute();

                    $this->db->query("UPDATE chart_of_accounts SET balance = balance - :amt WHERE id = :aid");
                    $this->db->bind(':amt', $totalCollectedAmt);
                    $this->db->bind(':aid', $transitAcc);
                    $this->db->execute();

                    // Debit final assets
                    if ($cashTransit > 0 && $cashAcc) {
                        $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:jid, :aid, :deb, 0)");
                        $this->db->bind(':jid', $balanceJid);
                        $this->db->bind(':aid', $cashAcc);
                        $this->db->bind(':deb', $cashTransit);
                        $this->db->execute();

                        $this->db->query("UPDATE chart_of_accounts SET balance = balance + :amt WHERE id = :aid");
                        $this->db->bind(':amt', $cashTransit);
                        $this->db->bind(':aid', $cashAcc);
                        $this->db->execute();
                    }
                    if ($chequeTransit > 0 && $chequeAcc) {
                        $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:jid, :aid, :deb, 0)");
                        $this->db->bind(':jid', $balanceJid);
                        $this->db->bind(':aid', $chequeAcc);
                        $this->db->bind(':deb', $chequeTransit);
                        $this->db->execute();

                        $this->db->query("UPDATE chart_of_accounts SET balance = balance + :amt WHERE id = :aid");
                        $this->db->bind(':amt', $chequeTransit);
                        $this->db->bind(':aid', $chequeAcc);
                        $this->db->execute();
                    }
                    if ($bankTransit > 0 && $bankAcc) {
                        $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:jid, :aid, :deb, 0)");
                        $this->db->bind(':jid', $balanceJid);
                        $this->db->bind(':aid', $bankAcc);
                        $this->db->bind(':deb', $bankTransit);
                        $this->db->execute();

                        $this->db->query("UPDATE chart_of_accounts SET balance = balance + :amt WHERE id = :aid");
                        $this->db->bind(':amt', $bankTransit);
                        $this->db->bind(':aid', $bankAcc);
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
}
