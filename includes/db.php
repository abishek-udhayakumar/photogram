<?php
// includes/db.php

$host = 'localhost';
$db_name = 'photogram_db';
$username = 'abishek07';
$password = 'Hacker$007'; // Default for local dev, change as needed

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
    $pdo->exec("USE `$db_name`");

} catch (PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Start Session globally
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>