<?php
class RepRouteService {
    public static function attachInvoices($routeId, $invoiceIds, $userId) {
        $db = new Database();
        
        foreach ($invoiceIds as $compositeId) {
            if (strpos($compositeId, 'standard:') === 0) {
                $soId = intval(substr($compositeId, 9));
                
                // Fetch standard sales order details
                $db->query("SELECT * FROM sales_orders WHERE id = :id");
                $db->bind(':id', $soId);
                $so = $db->single();
                if (!$so) {
                    throw new Exception("Standard Sales Order not found for ID: " . $soId);
                }
                
                // Fetch items
                $db->query("SELECT * FROM sales_order_items WHERE sales_order_id = :id");
                $db->bind(':id', $soId);
                $soItems = $db->resultSet() ?: [];
                
                // Prepare items payload
                $itemsPayload = [];
                foreach ($soItems as $item) {
                    $compositeItemSelection = $item->item_id . '|' . ($item->variation_option_id ?: '0');
                    $itemsPayload[] = [
                        'item_selection' => $compositeItemSelection,
                        'description' => $item->name,
                        'quantity' => $item->qty,
                        'unit_price' => $item->billing_price,
                        'discount_value' => $item->discount_value,
                        'discount_type' => $item->discount_type,
                        'total' => $item->total
                    ];
                }
                
                // AR and Revenue Accounts
                $arAccountId = null;
                $db->query("SELECT id FROM chart_of_accounts WHERE account_type = 'Asset' AND (account_name LIKE '%Receivable%' OR account_code LIKE '1100%') LIMIT 1");
                $arRow = $db->single();
                $arAccountId = $arRow ? $arRow->id : null;

                $revenueAccountId = null;
                $db->query("SELECT id FROM chart_of_accounts WHERE account_type = 'Revenue' AND (account_name LIKE '%Sales%' OR account_name LIKE '%Revenue%' OR account_code LIKE '4000%') LIMIT 1");
                $revRow = $db->single();
                $revenueAccountId = $revRow ? $revRow->id : null;

                if (!$arAccountId || !$revenueAccountId) {
                    throw new Exception("Accounting Accounts not configured.");
                }
                
                // Create invoice number
                $db->query("SELECT id FROM invoices ORDER BY id DESC LIMIT 1");
                $lastRow = $db->single();
                $nextId = $lastRow ? ($lastRow->id + 1) : 1;
                $invoiceNumber = str_pad((string)$nextId, 5, '0', STR_PAD_LEFT);
                
                $invoiceData = [
                    'customer_id' => $so->customer_id,
                    'invoice_number' => $invoiceNumber,
                    'invoice_date' => date('Y-m-d'),
                    'due_date' => date('Y-m-d'),
                    'payment_term_id' => $so->payment_term_id,
                    'subtotal' => $so->subtotal,
                    'global_discount_val' => $so->discount,
                    'global_discount_type' => 'Rs',
                    'notes' => trim($so->notes ?? ''),
                    'rep_route_id' => $routeId,
                    'grand_total' => $so->grand_total,
                    'stock_status' => 'reserved'
                ];
                
                require_once dirname(__DIR__) . '/Models/Invoice.php';
                $invoiceModel = new Invoice();
                $invoiceId = $invoiceModel->createInvoiceWithAccounting(
                    $invoiceData,
                    $itemsPayload,
                    $arAccountId,
                    $revenueAccountId,
                    $userId
                );
                
                if ($invoiceId) {
                    // Update standard sales order status to Transferred
                    $db->query("UPDATE sales_orders SET status = 'Transferred' WHERE id = :id");
                    $db->bind(':id', $soId);
                    $db->execute();
                } else {
                    throw new Exception("Failed to convert Sales Order to Invoice.");
                }
                
            } else {
                // It is a route booking (invoice)
                $invId = $compositeId;
                if (strpos($invId, 'route:') === 0) {
                    $invId = substr($invId, 6);
                }
                $invId = intval($invId);
                
                $db->beginTransaction();
                try {
                    $db->query("UPDATE invoices SET rep_route_id = :rid WHERE id = :id");
                    $db->bind(':rid', $routeId);
                    $db->bind(':id', $invId);
                    $db->execute();
                    $db->commit();
                } catch (Exception $e) {
                    $db->rollBack();
                    throw $e;
                }
            }
        }
    }

    public static function deleteRoute($routeId, $mode, $reason, $userId, $username) {
        $db = new Database();
        
        // Fetch Route Details for auditing/logging
        $db->query("SELECT * FROM rep_daily_routes WHERE id = :id");
        $db->bind(':id', $routeId);
        $route = $db->single();
        if (!$route) {
            throw new Exception("Route not found.");
        }

        require_once dirname(__DIR__) . '/Models/Invoice.php';
        $invoiceModel = new Invoice();

        // Handle Invoices first (outside main transaction to avoid nested PDO transaction exception)
        if ($mode === 'delete_with_so' || $mode === 'force_delete_all') {
            $db->query("SELECT i.id, i.invoice_number, c.name as customer_name, i.total_amount 
                        FROM invoices i 
                        JOIN customers c ON i.customer_id = c.id 
                        WHERE i.rep_route_id = :rid");
            $db->bind(':rid', $routeId);
            $invoices = $db->resultSet() ?: [];

            foreach ($invoices as $inv) {
                $invoiceId = $inv->id;
                $invoiceNumber = $inv->invoice_number;
                $customerName = $inv->customer_name;
                $grandTotal = floatval($inv->total_amount);

                // Revert stock/ledger entries, delete items & invoice (runs its own transaction)
                $success = $invoiceModel->deleteInvoiceWithAccounting($invoiceId, $userId);
                if ($success) {
                    // Audit trail write for invoice deletion
                    $db->query("INSERT INTO deleted_invoices (invoice_number, customer_name, total_amount, deleted_user_name, delete_reason, record_type) 
                                      VALUES (:inv_num, :cust_name, :total, :deleted_user, :reason, 'Invoice')");
                    $db->bind(':inv_num', $invoiceNumber);
                    $db->bind(':cust_name', $customerName);
                    $db->bind(':total', $grandTotal);
                    $db->bind(':deleted_user', $username);
                    $db->bind(':reason', $reason . " (via Route Deletion)");
                    $db->execute();
                } else {
                    throw new Exception("Failed to delete associated invoice: " . $invoiceNumber);
                }
            }
        }

        // Start main transaction for route deletion and other entity cleaning
        $db->beginTransaction();
        try {
            if ($mode === 'detach') {
                // Mode 1: Detach Invoices/Payments/Deliveries
                $db->query("UPDATE invoices SET rep_route_id = NULL WHERE rep_route_id = :rid");
                $db->bind(':rid', $routeId);
                $db->execute();

                $db->query("UPDATE deliveries SET rep_route_id = NULL WHERE rep_route_id = :rid");
                $db->bind(':rid', $routeId);
                $db->execute();

                $db->query("UPDATE deliveries SET secondary_rep_route_id = NULL WHERE secondary_rep_route_id = :rid");
                $db->bind(':rid', $routeId);
                $db->execute();

                $db->query("UPDATE customer_payments SET rep_route_id = NULL WHERE rep_route_id = :rid");
                $db->bind(':rid', $routeId);
                $db->execute();

                $db->query("DELETE FROM pending_collections WHERE route_id = :rid");
                $db->bind(':rid', $routeId);
                $db->execute();
            } 
            elseif ($mode === 'delete_with_so') {
                // Mode 2: Detach other details
                $db->query("UPDATE deliveries SET rep_route_id = NULL WHERE rep_route_id = :rid");
                $db->bind(':rid', $routeId);
                $db->execute();

                $db->query("UPDATE deliveries SET secondary_rep_route_id = NULL WHERE secondary_rep_route_id = :rid");
                $db->bind(':rid', $routeId);
                $db->execute();

                $db->query("UPDATE customer_payments SET rep_route_id = NULL WHERE rep_route_id = :rid");
                $db->bind(':rid', $routeId);
                $db->execute();

                $db->query("DELETE FROM pending_collections WHERE route_id = :rid");
                $db->bind(':rid', $routeId);
                $db->execute();
            } 
            elseif ($mode === 'force_delete_all') {
                // Mode 3: Delete payments, cheques, deliveries, collections
                $db->query("SELECT id, payment_method, reference, customer_id FROM customer_payments WHERE rep_route_id = :rid");
                $db->bind(':rid', $routeId);
                $payments = $db->resultSet() ?: [];

                foreach ($payments as $pmt) {
                    $paymentId = $pmt->id;

                    // Get journal entry and transactions
                    $db->query("SELECT journal_entry_id FROM customer_payments WHERE id = :id");
                    $db->bind(':id', $paymentId);
                    $pmtRow = $db->single();
                    if ($pmtRow && $pmtRow->journal_entry_id) {
                        $jid = $pmtRow->journal_entry_id;
                        
                        $db->query("DELETE FROM transactions WHERE journal_entry_id = :jid");
                        $db->bind(':jid', $jid);
                        $db->execute();

                        $db->query("DELETE FROM journal_entries WHERE id = :jid");
                        $db->bind(':jid', $jid);
                        $db->execute();
                    }

                    // Delete payment row
                    $db->query("DELETE FROM customer_payments WHERE id = :id");
                    $db->bind(':id', $paymentId);
                    $db->execute();
                }

                // Delete associated cheques directly via rep_route_id
                $db->query("DELETE FROM cheques WHERE rep_route_id = :rid");
                $db->bind(':rid', $routeId);
                $db->execute();

                // Delete deliveries
                $db->query("DELETE FROM deliveries WHERE rep_route_id = :rid OR secondary_rep_route_id = :rid");
                $db->bind(':rid', $routeId);
                $db->execute();

                // Delete pending collections
                $db->query("DELETE FROM pending_collections WHERE route_id = :rid");
                $db->bind(':rid', $routeId);
                $db->execute();
            }

            // Finally, delete the route itself
            $db->query("DELETE FROM rep_daily_routes WHERE id = :rid");
            $db->bind(':rid', $routeId);
            $db->execute();

            // Log activity
            require_once dirname(__DIR__) . '/Models/AuditLog.php';
            $audit = new AuditLog();
            $audit->logAction($userId, 'Delete Route', 'RepTracking', "Deleted Daily Route #RT-{$routeId} - {$route->route_name}. Mode: {$mode}. Reason: {$reason}", $routeId);

            $db->commit();
            return "Daily Route #RT-{$routeId} and associated records handled with mode '{$mode}' successfully deleted!";
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function ensureDeliveryAndPickingPopulated($db, $routeId) {
        $routeIds = [intval($routeId)];
        $db->query("SELECT route_binding_id, bound_to_route_id FROM rep_daily_routes WHERE id = :rid LIMIT 1");
        $db->bind(':rid', $routeId);
        $routeRow = $db->single();
        if ($routeRow) {
            if ($routeRow->route_binding_id) {
                $db->query("SELECT id FROM rep_daily_routes WHERE route_binding_id = :bid");
                $db->bind(':bid', $routeRow->route_binding_id);
                $boundRoutes = $db->resultSet() ?: [];
                foreach ($boundRoutes as $br) {
                    $routeIds[] = intval($br->id);
                }
            }
            if ($routeRow->bound_to_route_id) {
                $routeIds[] = intval($routeRow->bound_to_route_id);
            }
        }
        $routeIds = array_unique(array_filter(array_map('intval', $routeIds)));
        $placeholders = [];
        foreach ($routeIds as $index => $id) {
            $placeholders[] = ":rid_" . $index;
        }
        $placeholdersStr = implode(',', $placeholders);

        $db->query("SELECT id FROM deliveries WHERE rep_route_id IN ($placeholdersStr) OR secondary_rep_route_id IN ($placeholdersStr) ORDER BY id DESC LIMIT 1");
        foreach ($routeIds as $index => $id) {
            $db->bind(":rid_" . $index, intval($id));
        }
        $del = $db->single();
        
        $deliveryId = null;
        if (!$del) {
            $db->query("SELECT r.route_name, COALESCE(e.first_name, u.username) as first_name, COALESCE(e.last_name, '') as last_name 
                        FROM rep_daily_routes r 
                        LEFT JOIN users u ON r.user_id = u.id 
                        LEFT JOIN employees e ON u.employee_id = e.id 
                        WHERE r.id = :rid");
            $db->bind(':rid', $routeId);
            $routeInfo = $db->single();
            $repName = $routeInfo ? trim($routeInfo->first_name . ' ' . $routeInfo->last_name) : 'Pending Rep';

            $db->query("INSERT INTO deliveries (rep_route_id, delivery_date, vehicle_number, driver_name, partner_name, status) 
                        VALUES (:rid, CURDATE(), 'Pending Vehicle', :driver, '', 'Arranged')");
            $db->bind(':rid', $routeId);
            $db->bind(':driver', $repName);
            $db->execute();
            $deliveryId = $db->lastInsertId();
        } else {
            $deliveryId = $del->id;
        }

        if ($deliveryId) {
            self::populatePickingItems($db, $deliveryId);
        }
        return $deliveryId;
    }

    public static function ensurePickingItemsPopulated($db, $deliveryId) {
        self::populatePickingItems($db, $deliveryId);
    }

    public static function populatePickingItems($db, $deliveryId) {
        $db->query("SELECT rep_route_id, secondary_rep_route_id FROM deliveries WHERE id = :id LIMIT 1");
        $db->bind(':id', $deliveryId);
        $delivery = $db->single();
        if (!$delivery) {
            return;
        }

        $rids = [];
        if ($delivery->rep_route_id) { $rids[] = intval($delivery->rep_route_id); }
        if ($delivery->secondary_rep_route_id) { $rids[] = intval($delivery->secondary_rep_route_id); }
        if (empty($rids)) {
            return;
        }

        $ridsStr1 = implode(',', array_map('intval', $rids));
        $db->query("SELECT DISTINCT route_binding_id, bound_to_route_id FROM rep_daily_routes WHERE id IN ($ridsStr1)");
        $bindings = $db->resultSet() ?: [];
        
        $bindingIds = [];
        foreach ($bindings as $b) {
            if (!empty($b->route_binding_id)) $bindingIds[] = intval($b->route_binding_id);
            if (!empty($b->bound_to_route_id)) $rids[] = intval($b->bound_to_route_id);
        }
        if (!empty($bindingIds)) {
            $bindingIdsStr = implode(',', array_unique($bindingIds));
            $db->query("SELECT id FROM rep_daily_routes WHERE route_binding_id IN ($bindingIdsStr)");
            $allRoutes = $db->resultSet() ?: [];
            foreach ($allRoutes as $r) {
                $rids[] = intval($r->id);
            }
        }
        $rids = array_unique(array_filter(array_map('intval', $rids)));

        $placeholders3 = [];
        foreach ($rids as $index => $id) {
            $placeholders3[] = ":rid3_" . $index;
        }
        $placeholdersStr3 = implode(',', $placeholders3);

        // Fetch aggregated invoice items on these routes
        $db->query("
            SELECT ii.item_id, ii.variation_option_id, ii.description as item_name, SUM(ii.quantity) as required_qty
            FROM invoice_items ii
            JOIN invoices i ON ii.invoice_id = i.id
            WHERE i.rep_route_id IN ($placeholdersStr3) AND i.status != 'Voided'
            GROUP BY ii.item_id, COALESCE(ii.variation_option_id, 0), ii.description
            HAVING required_qty > 0
        ");
        foreach ($rids as $index => $id) {
            $db->bind(":rid3_" . $index, intval($id));
        }
        $invoiceItems = $db->resultSet() ?: [];

        // Existing delivery_picking_items map
        $db->query("SELECT id, item_id, variation_option_id, item_name, is_verified FROM delivery_picking_items WHERE delivery_id = :did");
        $db->bind(':did', $deliveryId);
        $existingRows = $db->resultSet() ?: [];
        $existingItemsMap = [];
        foreach ($existingRows as $row) {
            $key = intval($row->item_id) . '_' . intval($row->variation_option_id ?? 0);
            $existingItemsMap[$key] = $row;
        }

        $validKeys = [];
        foreach ($invoiceItems as $item) {
            $key = intval($item->item_id) . '_' . intval($item->variation_option_id ?? 0);
            $validKeys[$key] = true;

            if (isset($existingItemsMap[$key])) {
                $ex = $existingItemsMap[$key];
                if (intval($ex->is_verified) === 0) {
                    $db->query("
                        UPDATE delivery_picking_items 
                        SET required_qty = :req_qty, 
                            loaded_qty = CASE WHEN is_verified = 0 THEN :req_qty ELSE loaded_qty END,
                            item_name = :item_name
                        WHERE id = :id
                    ");
                    $db->bind(':req_qty', floatval($item->required_qty));
                    $db->bind(':item_name', $item->item_name);
                    $db->bind(':id', $ex->id);
                    $db->execute();
                }
            } else {
                $db->query("
                    INSERT INTO delivery_picking_items (delivery_id, item_name, item_id, variation_option_id, required_qty, loaded_qty, is_picked)
                    VALUES (:delivery_id, :item_name, :item_id, :variation_option_id, :required_qty, :loaded_qty, 0)
                ");
                $db->bind(':delivery_id', $deliveryId);
                $db->bind(':item_name', $item->item_name);
                $db->bind(':item_id', $item->item_id);
                $db->bind(':variation_option_id', $item->variation_option_id);
                $db->bind(':required_qty', floatval($item->required_qty));
                $db->bind(':loaded_qty', floatval($item->required_qty));
                $db->execute();
            }
        }

        // Remove unverified items no longer present in invoices
        foreach ($existingItemsMap as $key => $ex) {
            if (!isset($validKeys[$key]) && intval($ex->is_verified) === 0) {
                $db->query("DELETE FROM delivery_picking_items WHERE id = :id");
                $db->bind(':id', $ex->id);
                $db->execute();
            }
        }
    }
}
