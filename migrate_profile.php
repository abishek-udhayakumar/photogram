<?php
// migrate_profile.php
include 'includes/db.php';

try {
    $sql = "
    ALTER TABLE users 
    ADD COLUMN cover_photo VARCHAR(255) DEFAULT NULL AFTER profile_pic,
    ADD COLUMN website VARCHAR(255) DEFAULT NULL AFTER bio,
    ADD COLUMN is_private TINYINT(1) DEFAULT 0 AFTER website;
    ";

    $pdo->exec($sql);
    echo "Migration successful: Added cover_photo, website, is_private to users table.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Columns already exist. Skipping.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>