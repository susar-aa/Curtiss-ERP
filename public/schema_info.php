<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../core/Database.php';

$db = new Database();

echo "=== USERS COLUMNS ===\n";
$db->query("DESCRIBE users");
foreach ($db->resultSet() as $row) {
    echo "{$row->Field} | {$row->Type}\n";
}

echo "\n=== EMPLOYEES COLUMNS ===\n";
$db->query("DESCRIBE employees");
foreach ($db->resultSet() as $row) {
    echo "{$row->Field} | {$row->Type}\n";
}

echo "\n=== ALL REPRESENTATIVES WITH ROUTES ===\n";
$db->query("SELECT DISTINCT r.user_id, u.username, u.role, u.status, e.first_name, e.last_name 
            FROM rep_daily_routes r 
            LEFT JOIN users u ON r.user_id = u.id 
            LEFT JOIN employees e ON u.employee_id = e.id");
foreach ($db->resultSet() as $row) {
    echo "User ID: {$row->user_id} | Username: {$row->username} | Role: {$row->role} | Status: {$row->status} | Name: {$row->first_name} {$row->last_name}\n";
}

echo "\n=== ACTIVE REPS (Query from Controller) ===\n";
$db->query("SELECT u.id, u.username, e.first_name, e.last_name 
            FROM users u 
            LEFT JOIN employees e ON u.employee_id = e.id 
            WHERE u.role = 'rep' AND (u.status IS NULL OR u.status = 'Active') 
            ORDER BY e.first_name ASC, u.username ASC");
foreach ($db->resultSet() as $row) {
    echo "ID: {$row->id} | Username: {$row->username} | Name: {$row->first_name} {$row->last_name}\n";
}
