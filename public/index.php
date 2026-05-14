<?php
// Start secure session for the ERP
session_start();

// Load Configuration and Core Files
require_once '../config/database.php';
require_once '../core/Database.php';
require_once '../core/Controller.php';
require_once '../core/App.php';

// Initialize the MVC Router
$app = new App();