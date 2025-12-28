<?php
// includes/config.php

require_once __DIR__ . '/Env.php';

// Load Environment Variables
Env::load(__DIR__ . '/../.env');

// Define Constants
if (!defined('BASE_URL')) {
    define('BASE_URL', Env::get('BASE_URL', 'http://photo.me'));
}

if (!defined('APP_NAME')) {
    define('APP_NAME', Env::get('APP_NAME', 'Photogram'));
}

// Database Config
define('DB_HOST', Env::get('DB_HOST', '127.0.0.1'));
define('DB_NAME', Env::get('DB_NAME', 'photogram_db'));
define('DB_USER', Env::get('DB_USER', 'abishek07'));
define('DB_PASS', Env::get('DB_PASS', 'Hacker$007'));

error_reporting(E_ALL);
if (Env::get('APP_ENV') === 'production') {
    ini_set('display_errors', 0);
} else {
    ini_set('display_errors', 1);
}
?>