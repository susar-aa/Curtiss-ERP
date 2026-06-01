<?php

// Enable error reporting to prevent blank 500 errors in the future
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Delivery {
    private $db;

    public function __construct() {
        $this->db = new Database();
        
        // Auto-heal schema to add selected_credit_invoices column if not present
        try {
            $this->db->query("SHOW COLUMNS FROM deliveries LIKE 'selected_credit_invoices'");
            if (!$this->db->single()) {
                $this->db->query("ALTER TABLE deliveries ADD COLUMN selected_credit_invoices TEXT NULL");
                $this->db->execute();
            }
        } catch (Exception $e) {
            // Ignore if already exists
        }
    }

    public function createDelivery($data) {
        $this->db->beginTransaction();

        try {
            // 1. Insert into deliveries table
            $this->db->query("INSERT INTO deliveries (rep_route_id, delivery_date, vehicle_number, driver_name, partner_name, selected_credit_invoices) 
                              VALUES (:rep_route_id, :delivery_date, :vehicle_number, :driver_name, :partner_name, :selected_credit_invoices)");
            $this->db->bind(':rep_route_id', $data['rep_route_id']);
            $this->db->bind(':delivery_date', $data['delivery_date']);
            $this->db->bind(':vehicle_number', $data['vehicle_number']);
            $this->db->bind(':driver_name', $data['driver_name']);
            $this->db->bind(':partner_name', $data['partner_name']);
            $this->db->bind(':selected_credit_invoices', $data['selected_credit_invoices'] ?? null);
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
            LEFT JOIN employees e ON u.employee_id = e.id
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
            LEFT JOIN employees e ON u.employee_id = e.id
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

    public function getDeliveryCreditInvoices($routeId) {
        // Fetch the delivery first to check if there are selected credit invoices
        $this->db->query("SELECT selected_credit_invoices FROM deliveries WHERE rep_route_id = :rid LIMIT 1");
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
                WHERE i.id IN ($idList)
                ORDER BY i.invoice_date ASC, i.id ASC
            ");
            return $this->db->resultSet();
        }

        // Fallback to old behavior if no selected credit invoices exist
        $this->db->query("
            SELECT i.*, c.name as customer_name,
            (total_amount - COALESCE(CASE WHEN global_discount_type = '%' THEN (total_amount * global_discount_val / 100) ELSE global_discount_val END, 0) + COALESCE(tax_amount, 0)) as true_grand_total
            FROM invoices i
            JOIN customers c ON i.customer_id = c.id
            WHERE i.customer_id IN (SELECT DISTINCT customer_id FROM invoices WHERE rep_route_id = :rid AND status != 'Voided')
              AND i.rep_route_id != :rid
              AND i.status != 'Voided'
            ORDER BY i.invoice_date ASC, i.id ASC
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
            SELECT id, customer_id, delivery_status, status, total_amount, global_discount_val, global_discount_type, tax_amount 
            FROM invoices 
            WHERE rep_route_id = :rid AND status != 'Voided'
        ");
        $this->db->bind(':rid', $delivery->rep_route_id);
        $routeInvoices = $this->db->resultSet();

        $cash_sales = 0.0;
        $credit_sales = 0.0;

        // Group dispatches by customer
        $custDispatches = [];
        foreach ($routeInvoices as $inv) {
            $amt = $this->getTrueGrandTotal($inv);
            if ($inv->delivery_status === 'Delivered') {
                $custDispatches[$inv->customer_id] = ($custDispatches[$inv->customer_id] ?? 0.0) + $amt;
            }
        }

        // For each customer who received a delivery, calculate their cash & credit sales
        foreach ($custDispatches as $cid => $dispatchAmt) {
            if ($delivery->status === 'Finalized') {
                // If already finalized, we can just read from the finalized invoice status!
                $custInvs = array_filter($routeInvoices, function($i) use ($cid) { return $i->customer_id == $cid; });
                $paidAmt = 0.0;
                foreach ($custInvs as $i) {
                    if ($i->status === 'Paid') {
                        $paidAmt += $this->getTrueGrandTotal($i);
                    }
                }
                $cash_sales += $paidAmt;
                $credit_sales += max(0.0, $dispatchAmt - $paidAmt);
            } else {
                // Fetch customer payments on this route. Route collections should first apply
                // to the current route delivery amounts and not reduce cash sales because of
                // unrelated older unpaid invoices.
                $this->db->query("SELECT COALESCE(SUM(amount), 0) as total_paid FROM customer_payments WHERE rep_route_id = :rid AND customer_id = :cid");
                $this->db->bind(':rid', $delivery->rep_route_id);
                $this->db->bind(':cid', $cid);
                $todayRoutePayments = floatval($this->db->single()->total_paid);

                $custCashSales = min($dispatchAmt, max(0.0, $todayRoutePayments));
                $cash_sales += $custCashSales;
                $credit_sales += max(0.0, $dispatchAmt - $custCashSales);
            }
        }

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
            'cash_sales' => floatval($cash_sales),
            'credit_sales' => floatval($credit_sales),
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

            require_once '../app/Models/FIFO.php';
            $fifo = new FIFO();

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

                            // Deplete via FIFO batches
                            $fifo->depleteStock($itemId, $varId, $qty, $item->id, null);
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

            // 3. Financial Clearance balancing: Post double entry ledger entries and update invoice statuses at finalization
            $this->db->query("
                SELECT * 
                FROM customer_payments 
                WHERE rep_route_id = :rid
            ");
            $this->db->bind(':rid', $delivery->rep_route_id);
            $routePayments = $this->db->resultSet();

            if (!empty($routePayments)) {
                $this->db->query("SELECT id, account_code FROM chart_of_accounts WHERE account_code IN ('1000', '1010', '1600', '1200')");
                $accounts = $this->db->resultSet();
                $accMap = [];
                foreach ($accounts as $a) { $accMap[$a->account_code] = $a->id; }

                $cashAcc = $accMap['1000'] ?? null;
                $chequeAcc = $accMap['1010'] ?? null;
                $bankAcc = $accMap['1600'] ?? null;
                $arAcc = $accMap['1200'] ?? null;

                if (!$arAcc) throw new Exception("Missing AR Account (1200) in Chart of Accounts.");

                foreach ($routePayments as $pay) {
                    $amount = floatval($pay->amount);
                    if ($amount <= 0) continue;

                    $method = $pay->payment_method;
                    $assetAccId = null;
                    if ($method === 'Cash') {
                        $assetAccId = $cashAcc;
                    } elseif ($method === 'Cheque') {
                        $assetAccId = $chequeAcc;
                    } elseif ($method === 'Bank Transfer') {
                        $assetAccId = $bankAcc;
                    }

                    if (!$assetAccId) continue;

                    $refCode = "PMT-BAL-" . time() . rand(10,99);

                    // Insert Journal Entry
                    $this->db->query("INSERT INTO journal_entries (entry_date, reference, description, created_by, status) 
                                      VALUES (CURDATE(), :ref, :desc, :uid, 'Posted')");
                    $this->db->bind(':ref', $refCode);
                    $this->db->bind(':desc', "Finalized Delivery Collection ($method)");
                    $this->db->bind(':uid', $adminUserId);
                    $this->db->execute();
                    $payJid = $this->db->lastInsertId();

                    // Update payment record to associate with this journal entry
                    $this->db->query("UPDATE customer_payments SET journal_entry_id = :jid WHERE id = :pid");
                    $this->db->bind(':jid', $payJid);
                    $this->db->bind(':pid', $pay->id);
                    $this->db->execute();

                    // Debit Asset Account
                    $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:jid, :aid, :deb, 0)");
                    $this->db->bind(':jid', $payJid);
                    $this->db->bind(':aid', $assetAccId);
                    $this->db->bind(':deb', $amount);
                    $this->db->execute();

                    $this->db->query("UPDATE chart_of_accounts SET balance = balance + :amt WHERE id = :aid");
                    $this->db->bind(':amt', $amount);
                    $this->db->bind(':aid', $assetAccId);
                    $this->db->execute();

                    // Credit AR Account
                    $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit) VALUES (:jid, :aid, 0, :cred)");
                    $this->db->bind(':jid', $payJid);
                    $this->db->bind(':aid', $arAcc);
                    $this->db->bind(':cred', $amount);
                    $this->db->execute();

                    $this->db->query("UPDATE chart_of_accounts SET balance = balance - :amt WHERE id = :aid");
                    $this->db->bind(':amt', $amount);
                    $this->db->bind(':aid', $arAcc);
                    $this->db->execute();

                    // FIFO mark invoices Paid
                    $customerId = $pay->customer_id;
                    $this->db->query("
                        SELECT id, total_amount, global_discount_val, global_discount_type, tax_amount 
                        FROM invoices 
                        WHERE customer_id = :cid AND status = 'Unpaid' 
                        ORDER BY invoice_date ASC, id ASC
                    ");
                    $this->db->bind(':cid', $customerId);
                    $unpaidList = $this->db->resultSet();

                    $pool = $amount;
                    foreach ($unpaidList as $unp) {
                        $gTotal = $this->getTrueGrandTotal($unp);
                        if ($pool >= $gTotal) {
                            $this->db->query("UPDATE invoices SET status = 'Paid' WHERE id = :id");
                            $this->db->bind(':id', $unp->id);
                            $this->db->execute();
                            $pool -= $gTotal;
                        } else {
                            break;
                        }
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
}
