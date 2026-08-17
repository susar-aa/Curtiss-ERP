<?php
require_once 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/config/database.php';
require_once 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/core/Database.php';
require_once 'c:/xampp/htdocs/CURTISS/Curtiss-ERP/app/Models/Loan.php';
try {
    $m = new Loan();
    $stats = $m->getDashboardStats();
    print_r($stats);
} catch (Exception $e) { echo "ERROR: " . $e->getMessage(); }
