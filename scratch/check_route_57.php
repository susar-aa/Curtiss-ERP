<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();

$routeId = 57;
echo "=== ROUTE $routeId DATA ===\n";
$db->query("SELECT id, route_name, status, is_merged_route, route_binding_id, bound_to_route_id FROM rep_daily_routes WHERE id = :id");
$db->bind(':id', $routeId);
print_r($db->single());

echo "\n=== ALL BOUND ROUTES ===\n";
$routeIds = [intval($routeId)];
$db->query("SELECT route_binding_id, bound_to_route_id FROM rep_daily_routes WHERE id = :rid LIMIT 1");
$db->bind(':rid', $routeId);
$routeRow = $db->single();
if ($routeRow) {
    if ($routeRow->route_binding_id) {
        $db->query("SELECT id, route_name, status FROM rep_daily_routes WHERE route_binding_id = :bid");
        $db->bind(':bid', $routeRow->route_binding_id);
        $boundRoutes = $db->resultSet() ?: [];
        print_r($boundRoutes);
        foreach ($boundRoutes as $br) {
            $routeIds[] = intval($br->id);
        }
    }
}
$routeIds = array_unique(array_filter(array_map('intval', $routeIds)));
echo "Resolved Route IDs: " . implode(',', $routeIds) . "\n";

$placeholders = [];
foreach ($routeIds as $index => $id) {
    $placeholders[] = ":rid_" . $index;
}
$placeholdersStr = implode(',', $placeholders);

echo "\n=== DELIVERIES FOR THESE ROUTES ===\n";
$db->query("SELECT id, rep_route_id, secondary_rep_route_id, vehicle_number, driver_name, status FROM deliveries WHERE rep_route_id IN ($placeholdersStr) OR secondary_rep_route_id IN ($placeholdersStr)");
foreach ($routeIds as $index => $id) {
    $db->bind(":rid_" . $index, intval($id));
}
$deliveries = $db->resultSet();
print_r($deliveries);

if (!empty($deliveries)) {
    $delId = $deliveries[0]->id;
    echo "\n=== PICKING ITEMS FOR DELIVERY $delId ===\n";
    $db->query("SELECT id, item_name, required_qty, loaded_qty, final_loaded_qty, variance, is_verified FROM delivery_picking_items WHERE delivery_id = :did AND (required_qty = 0 OR final_loaded_qty = 0 OR loaded_qty = 0)");
    $db->bind(':did', $delId);
    $zeroItems = $db->resultSet();
    echo "Found " . count($zeroItems) . " zero items in delivery_picking_items:\n";
    print_r(array_slice($zeroItems, 0, 20));
} else {
    echo "No deliveries found.\n";
}
