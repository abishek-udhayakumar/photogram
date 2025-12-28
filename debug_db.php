<?php
require_once __DIR__ . '/includes/config.php';

echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_USER: " . DB_USER . "\n";
echo "DB_PASS: " . DB_PASS . "\n";
echo "DB_NAME: " . DB_NAME . "\n";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    echo "Connection OK!\n";
} catch (PDOException $e) {
    echo "Connection Failed: " . $e->getMessage() . "\n";
}
?>