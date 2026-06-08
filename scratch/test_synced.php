<?php
require 'config/database.php';
require 'core/Database.php';
$db = new Database();
$synced_attributes = [];
try {
    $db->query("SELECT * FROM product_attributes ORDER BY name ASC");
    $synced_attributes = $db->resultSet() ?: [];
    foreach ($synced_attributes as $attr) {
        $db->query("SELECT * FROM product_attribute_terms WHERE attribute_id = :id ORDER BY name ASC");
        $db->bind(':id', $attr->id);
        $attr->terms = $db->resultSet() ?: [];
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
echo json_encode($synced_attributes, JSON_PRETTY_PRINT) . "\n";
