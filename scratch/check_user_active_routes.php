<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();
$db->query("SELECT * FROM rep_daily_routes WHERE user_id = 2 AND status = 'Active'");
$routes = $db->resultSet() ?: [];
echo "Active routes for user 2: " . count($routes) . "\n";
foreach ($routes as $r) {
    echo "ID: {$r->id}, Name: {$r->route_name}\n";
}
