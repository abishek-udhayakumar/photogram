<?php
// migrate_profile_extras.php
include 'includes/db.php';

try {
    $sql = "
    ALTER TABLE users 
    ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER is_private,
    ADD COLUMN location VARCHAR(100) DEFAULT NULL AFTER website;
    ";

    $pdo->exec($sql);
    echo "Migration successful: Added is_verified, location to users table.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Columns already exist. Skipping.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>