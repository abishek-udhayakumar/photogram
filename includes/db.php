<?php
// includes/db.php
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
    $pdo->exec("USE `" . DB_NAME . "`");

} catch (PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Start Session globally
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>