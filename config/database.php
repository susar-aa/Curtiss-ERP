<?php
// Database Constants
define('DB_HOST', 'localhost');
define('DB_USER', 'suzxlabs'); 
define('DB_PASS', 'Susara@200611003614');    
define('DB_NAME', 'curtiss_erp'); 

// App Root URL - Dynamically determined for local dev (XAMPP) & Plesk production
if (isset($_SERVER['HTTP_HOST'])) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $script = $_SERVER['SCRIPT_NAME'];
    $dir = str_replace('\\', '/', dirname($script));
    if ($dir === '/') {
        $dir = '';
    }
    define('APP_URL', $protocol . '://' . $_SERVER['HTTP_HOST'] . $dir);
} else {
    define('APP_URL', 'https://curtiss.suzxlabs.com');
}

// Site Name
define('APP_NAME', 'CURTISS ERP');

// Brevo API Configuration (Replace with your actual API Key from brevo.com)
define('BREVO_API_KEY', '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');


