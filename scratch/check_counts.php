<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();

$tables = ['items', 'item_categories', 'customers', 'mca_areas', 'users', 'employees', 'payment_terms', 'invoices', 'rep_daily_routes', 'discount_rules'];
foreach ($tables as $t) {
    try {
        $db->query("SELECT COUNT(*) as cnt FROM `$t`");
        $res = $db->single();
        echo "Table '$t' count: " . ($res->cnt ?? 0) . "\n";
    } catch (Throwable $e) {
        echo "Table '$t' error: " . $e->getMessage() . "\n";
    }
}
