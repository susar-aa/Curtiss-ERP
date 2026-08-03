<?php
require __DIR__ . '/../app/config/config.php';
require __DIR__ . '/../app/libraries/Database.php';
$db = new Database();
$db->query("INSERT IGNORE INTO rep_kpi_configs (kpi_key, kpi_name, weight, target_value, min_score, max_score) VALUES ('credit_limit', 'Total Credit Limit (LKR)', 0, 500000, 0, 0)");
$db->execute();
echo "Inserted credit_limit";
