<?php
require 'config/database.php';
require 'core/Database.php';

try {
    $db = new Database();
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $db->query("UPDATE users SET password_hash = :hash WHERE username = 'admin'");
    $db->bind(':hash', $password_hash);
    if ($db->execute()) {
        echo "Successfully updated admin password to admin123!\n";
    } else {
        echo "Failed to update admin password.\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
