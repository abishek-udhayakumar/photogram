<?php
require_once __DIR__ . '/includes/db.php';

try {
    // Add is_archived column if it doesn't exist
    $sql = "
    ALTER TABLE `posts` 
    ADD COLUMN `is_archived` tinyint(1) DEFAULT 0 AFTER `caption`;
    ";

    $pdo->exec($sql);
    echo "Column 'is_archived' added successfully to 'posts' table.\n";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Column 'is_archived' already exists.\n";
    } else {
        die("Error adding column: " . $e->getMessage() . "\n");
    }
}
?>