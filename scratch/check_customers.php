<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();
$db->query("SELECT COUNT(*) as cnt FROM customers");
$total = $db->single()->cnt;
echo "Total customers: $total\n";

$db->query("SELECT COUNT(*) as cnt FROM customers WHERE status = 'active'");
$active = $db->single()->cnt;
echo "Active customers: $active\n";

$db->query("SELECT COUNT(*) as cnt FROM mca_areas");
$areas = $db->single()->cnt;
echo "Total MCA areas: $areas\n";

$db->query("SELECT COUNT(DISTINCT mca_id) as cnt FROM customers WHERE mca_id IS NOT NULL");
$assigned = $db->single()->cnt;
echo "Customers with mca_id: $assigned\n";
