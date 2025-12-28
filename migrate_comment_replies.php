<?php
// migrate_comment_replies.php
include 'includes/db.php';

try {
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM comments LIKE 'parent_id'");
    if ($stmt->fetch()) {
        echo "Column 'parent_id' already exists.\n";
    } else {
        $sql = "ALTER TABLE comments ADD COLUMN parent_id INT(11) NULL DEFAULT NULL AFTER id";
        $pdo->exec($sql);
        echo "Column 'parent_id' added successfully.\n";

        // Add FK
        $sqlFK = "ALTER TABLE comments ADD CONSTRAINT fk_comment_parent FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE";
        $pdo->exec($sqlFK);
        echo "Foreign Foreign Key constraint added.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
