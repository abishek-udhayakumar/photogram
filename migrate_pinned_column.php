<?php
require_once __DIR__ . '/includes/db.php';

try {
    // Add is_pinned column if it doesn't exist
    $sql = "
    ALTER TABLE `posts` 
    ADD COLUMN `is_pinned` tinyint(1) DEFAULT 0 AFTER `is_archived`;
    ";

    $pdo->exec($sql);
    echo "Column 'is_pinned' added successfully to 'posts' table.\n";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Column 'is_pinned' already exists.\n";
    } else {
        // If is_archived doesn't exist yet (unlikely), fall back to after caption usually
        try {
            $sql_fallback = "ALTER TABLE `posts` ADD COLUMN `is_pinned` tinyint(1) DEFAULT 0 AFTER `caption`;";
            $pdo->exec($sql_fallback);
            echo "Column 'is_pinned' added successfully (fallback).\n";
        } catch (PDOException $ex) {
            if (strpos($ex->getMessage(), "Duplicate column name") !== false) {
                echo "Column 'is_pinned' already exists.\n";
            } else {
                die("Error adding column is_pinned: " . $ex->getMessage() . "\n");
            }
        }
    }
}
?>