<?php
require 'core/Database.php';
require 'config/config.php';
$db = new Database();
$db->query('SELECT * FROM rep_targets');
print_r($db->resultSet());
