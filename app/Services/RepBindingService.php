<?php
class RepBindingService {
    public static function createBinding($bindingName, $routeIds, $userId, $ipAddress) {
        $db = new Database();
        $db->beginTransaction();
        try {
            // Fetch original routes details
            $routeIdsList = implode(',', array_map('intval', $routeIds));

            // Validate Route Name uniqueness (excluding the routes being bound and any completed/finalized/bound ones)
            $db->query("SELECT id FROM rep_daily_routes WHERE route_name = :name AND status NOT IN ('Completed', 'Finalized', 'Bound') AND id NOT IN ($routeIdsList) LIMIT 1");
            $db->bind(':name', $bindingName);
            if ($db->single()) {
                throw new Exception("The route name '{$bindingName}' is already taken by another active route. Please enter a unique route name.");
            }

            $db->query("SELECT id, user_id, route_name, start_meter, start_time, status, route_binding_id, bound_to_route_id FROM rep_daily_routes WHERE id IN ($routeIdsList)");
            $originalRoutes = $db->resultSet() ?: [];
            if (count($originalRoutes) < 2) {
                throw new Exception("Selected routes not found in database.");
            }

            // Fetch associated records to create snapshot
            // Invoices
            $db->query("SELECT id, rep_route_id FROM invoices WHERE rep_route_id IN ($routeIdsList)");
            $originalInvoices = $db->resultSet() ?: [];

            // Deliveries
            $db->query("SELECT id, rep_route_id, secondary_rep_route_id FROM deliveries WHERE rep_route_id IN ($routeIdsList) OR secondary_rep_route_id IN ($routeIdsList)");
            $originalDeliveries = $db->resultSet() ?: [];

            // Cheques
            $db->query("SELECT id, rep_route_id FROM cheques WHERE rep_route_id IN ($routeIdsList)");
            $originalCheques = $db->resultSet() ?: [];

            // Customer Payments
            $db->query("SELECT id, rep_route_id FROM customer_payments WHERE rep_route_id IN ($routeIdsList)");
            $originalPayments = $db->resultSet() ?: [];

            // Pending Collections
            $db->query("SELECT id, route_id FROM pending_collections WHERE route_id IN ($routeIdsList)");
            $originalCollections = $db->resultSet() ?: [];

            // Create JSON snapshot
            $snapshot = [
                'original_routes' => array_map(function($r) { return (array)$r; }, $originalRoutes),
                'invoices' => array_map(function($i) { return (array)$i; }, $originalInvoices),
                'deliveries' => array_map(function($d) { return (array)$d; }, $originalDeliveries),
                'cheques' => array_map(function($c) { return (array)$c; }, $originalCheques),
                'customer_payments' => array_map(function($p) { return (array)$p; }, $originalPayments),
                'pending_collections' => array_map(function($col) { return (array)$col; }, $originalCollections)
            ];
            $snapshotJson = json_encode($snapshot);

            // Insert into route_bindings
            $db->query("INSERT INTO route_bindings (name, created_by, snapshot) VALUES (:name, :created_by, :snapshot)");
            $db->bind(':name', $bindingName);
            $db->bind(':created_by', $userId);
            $db->bind(':snapshot', $snapshotJson);
            $db->execute();
            $bindingId = $db->lastInsertId();

            // Create new combined route in rep_daily_routes
            // Copy rep, start odo, and start time from the first selected route
            $firstRoute = $originalRoutes[0];
            $db->query("INSERT INTO rep_daily_routes (user_id, route_name, start_meter, start_time, status, route_binding_id, is_merged_route) 
                        VALUES (:user_id, :route_name, :start_meter, :start_time, 'Adjustments', :route_binding_id, 1)");
            $db->bind(':user_id', $firstRoute->user_id);
            $db->bind(':route_name', $bindingName);
            $db->bind(':start_meter', $firstRoute->start_meter);
            $db->bind(':start_time', $firstRoute->start_time);
            $db->bind(':route_binding_id', $bindingId);
            $db->execute();
            $newRouteId = $db->lastInsertId();

            // Mark source routes as Bound and link to the new route
            $db->query("UPDATE rep_daily_routes SET status = 'Bound', bound_to_route_id = :new_route_id, route_binding_id = :binding_id WHERE id IN ($routeIdsList)");
            $db->bind(':new_route_id', $newRouteId);
            $db->bind(':binding_id', $bindingId);
            $db->execute();

            // Move invoices
            if (!empty($originalInvoices)) {
                $db->query("UPDATE invoices SET rep_route_id = :new_route_id WHERE rep_route_id IN ($routeIdsList)");
                $db->bind(':new_route_id', $newRouteId);
                $db->execute();
            }

            // Move deliveries
            if (!empty($originalDeliveries)) {
                $db->query("UPDATE deliveries SET rep_route_id = :new_route_id WHERE rep_route_id IN ($routeIdsList)");
                $db->bind(':new_route_id', $newRouteId);
                $db->execute();

                $db->query("UPDATE deliveries SET secondary_rep_route_id = NULL WHERE secondary_rep_route_id IN ($routeIdsList)");
                $db->execute();
            }

            // Move cheques
            if (!empty($originalCheques)) {
                $db->query("UPDATE cheques SET rep_route_id = :new_route_id WHERE rep_route_id IN ($routeIdsList)");
                $db->bind(':new_route_id', $newRouteId);
                $db->execute();
            }

            // Move customer payments
            if (!empty($originalPayments)) {
                $db->query("UPDATE customer_payments SET rep_route_id = :new_route_id WHERE rep_route_id IN ($routeIdsList)");
                $db->bind(':new_route_id', $newRouteId);
                $db->execute();
            }

            // Move pending collections
            if (!empty($originalCollections)) {
                $db->query("UPDATE pending_collections SET route_id = :new_route_id WHERE route_id IN ($routeIdsList)");
                $db->bind(':new_route_id', $newRouteId);
                $db->execute();
            }

            // Write Audit Log
            $routesDescription = implode(', ', array_map(function($r) { return "{$r->route_name} (#{$r->id})"; }, $originalRoutes));
            $auditDesc = "Bound routes [{$routesDescription}] into combined route '{$bindingName}' (#{$newRouteId})";
            $db->query("INSERT INTO audit_logs (user_id, action, module, description, reference_id, ip_address) 
                        VALUES (:uid, 'ROUTE_BIND', 'Logistics', :desc, :ref, :ip)");
            $db->bind(':uid', $userId);
            $db->bind(':desc', $auditDesc);
            $db->bind(':ref', $newRouteId);
            $db->bind(':ip', $ipAddress);
            $db->execute();

            $db->commit();
            return ['status' => 'success', 'message' => 'Routes successfully bound under "' . $bindingName . '"!'];
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function unbindRoute($bindingId, $routeId, $userId, $ipAddress) {
        $db = new Database();
        $db->beginTransaction();
        try {
            if ($routeId > 0 && $bindingId === 0) {
                $db->query("SELECT route_binding_id FROM rep_daily_routes WHERE id = :rid LIMIT 1");
                $db->bind(':rid', $routeId);
                $row = $db->single();
                if ($row) {
                    $bindingId = intval($row->route_binding_id);
                }
            }

            if (empty($bindingId)) {
                throw new Exception("Invalid Binding ID or Route ID provided.");
            }

            // Fetch the route binding record
            $db->query("SELECT * FROM route_bindings WHERE id = :bid LIMIT 1");
            $db->bind(':bid', $bindingId);
            $binding = $db->single();
            if (!$binding) {
                throw new Exception("Route binding record not found.");
            }

            $snapshot = json_decode($binding->snapshot, true);
            if (empty($snapshot)) {
                throw new Exception("Snapshot data is missing or corrupted.");
            }

            // Restore original routes
            foreach ($snapshot['original_routes'] as $orig) {
                $db->query("UPDATE rep_daily_routes SET 
                            route_name = :route_name,
                            status = :status,
                            route_binding_id = :route_binding_id,
                            bound_to_route_id = :bound_to_route_id
                            WHERE id = :id");
                $db->bind(':route_name', $orig['route_name']);
                $db->bind(':status', $orig['status']);
                $db->bind(':route_binding_id', $orig['route_binding_id']);
                $db->bind(':bound_to_route_id', $orig['bound_to_route_id']);
                $db->bind(':id', $orig['id']);
                $db->execute();
            }

            // Restore Invoices
            foreach ($snapshot['invoices'] as $inv) {
                $db->query("UPDATE invoices SET rep_route_id = :orig_rid WHERE id = :id");
                $db->bind(':orig_rid', $inv['rep_route_id']);
                $db->bind(':id', $inv['id']);
                $db->execute();
            }

            // Restore Deliveries
            foreach ($snapshot['deliveries'] as $del) {
                $db->query("UPDATE deliveries SET rep_route_id = :orig_rid, secondary_rep_route_id = :orig_sec_rid WHERE id = :id");
                $db->bind(':orig_rid', $del['rep_route_id']);
                $db->bind(':orig_sec_rid', $del['secondary_rep_route_id']);
                $db->bind(':id', $del['id']);
                $db->execute();
            }

            // Restore Cheques
            foreach ($snapshot['cheques'] as $chq) {
                $db->query("UPDATE cheques SET rep_route_id = :orig_rid WHERE id = :id");
                $db->bind(':orig_rid', $chq['rep_route_id']);
                $db->bind(':id', $chq['id']);
                $db->execute();
            }

            // Restore Customer Payments
            foreach ($snapshot['customer_payments'] as $pmt) {
                $db->query("UPDATE customer_payments SET rep_route_id = :orig_rid WHERE id = :id");
                $db->bind(':orig_rid', $pmt['rep_route_id']);
                $db->bind(':id', $pmt['id']);
                $db->execute();
            }

            // Restore Pending Collections
            foreach ($snapshot['pending_collections'] as $col) {
                $db->query("UPDATE pending_collections SET route_id = :orig_rid WHERE id = :id");
                $db->bind(':orig_rid', $col['route_id']);
                $db->bind(':id', $col['id']);
                $db->execute();
            }

            // Delete the combined route
            $db->query("DELETE FROM rep_daily_routes WHERE route_binding_id = :bid AND is_merged_route = 1");
            $db->bind(':bid', $bindingId);
            $db->execute();

            // Mark route_binding as undone (audit trail preservation)
            $db->query("UPDATE route_bindings SET undo_by = :undo_by, undo_at = NOW() WHERE id = :bid");
            $db->bind(':undo_by', $userId);
            $db->bind(':bid', $bindingId);
            $db->execute();

            // Write Audit Log
            $routesDescription = implode(', ', array_map(function($r) { return "{$r['route_name']} (#{$r['id']})"; }, $snapshot['original_routes']));
            $auditDesc = "Undid route binding '{$binding->name}' (#{$bindingId}), restoring constituent routes [{$routesDescription}]";
            $db->query("INSERT INTO audit_logs (user_id, action, module, description, reference_id, ip_address) 
                        VALUES (:uid, 'ROUTE_UNBIND', 'Logistics', :desc, :ref, :ip)");
            $db->bind(':uid', $userId);
            $db->bind(':desc', $auditDesc);
            $db->bind(':ref', $bindingId);
            $db->bind(':ip', $ipAddress);
            $db->execute();

            $db->commit();
            return ['status' => 'success', 'message' => 'Route binding successfully undone! Routes are now separated.'];
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
