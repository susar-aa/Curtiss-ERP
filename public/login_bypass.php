<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['role'] = 'Admin';
header('Location: /Curtiss-ERP/public/supplier');
exit;
