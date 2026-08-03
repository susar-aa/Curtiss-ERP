<?php
require 'app/config/config.php';
require 'app/libraries/Database.php';
$db = new Database();
$db->query('SELECT * FROM rep_kpi_configs');
print_r($db->resultSet());
