<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();
$tables = ['invoices', 'customers', 'customer_payments', 'items', 'item_categories', 'employees', 'payment_terms', 'credit_notes', 'rep_daily_routes', 'discount_rules', 'discount_rule_tiers'];
foreach ($tables as $t) {
    echo "====================================\n";
    echo "Table: $t\n";
    try {
        $db->query("DESCRIBE `$t`");
        $cols = $db->resultSet();
        foreach ($cols as $c) {
            echo "  {$c->Field} - {$c->Type}\n";
        }
    } catch (Throwable $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
}
