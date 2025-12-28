<?php
require_once __DIR__ . '/includes/db.php';

try {
    // Add is_private column
    try {
        $sql = "ALTER TABLE `users` ADD COLUMN `is_private` tinyint(1) DEFAULT 0 AFTER `profile_pic`;";
        $pdo->exec($sql);
        echo "Column 'is_private' added successfully.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "Duplicate column name") !== false) {
            echo "Column 'is_private' already exists.\n";
        } else {
            echo "Error adding is_private: " . $e->getMessage() . "\n";
        }
    }

    // Add is_verified column (also seen in profile code)
    try {
        $sql = "ALTER TABLE `users` ADD COLUMN `is_verified` tinyint(1) DEFAULT 0 AFTER `is_private`;";
        $pdo->exec($sql);
        echo "Column 'is_verified' added successfully.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "Duplicate column name") !== false) {
            echo "Column 'is_verified' already exists.\n";
        } else {
            echo "Error adding is_verified: " . $e->getMessage() . "\n";
        }
    }

    // Add location column (also seen in profile code)
    try {
        $sql = "ALTER TABLE `users` ADD COLUMN `location` varchar(100) DEFAULT NULL AFTER `bio`;";
        $pdo->exec($sql);
        echo "Column 'location' added successfully.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "Duplicate column name") !== false) {
            echo "Column 'location' already exists.\n";
        }
    }

    // Add website column (also seen in profile code)
    try {
        $sql = "ALTER TABLE `users` ADD COLUMN `website` varchar(255) DEFAULT NULL AFTER `location`;";
        $pdo->exec($sql);
        echo "Column 'website' added successfully.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "Duplicate column name") !== false) {
            echo "Column 'website' already exists.\n";
        }
    }

    // Add cover_photo column (also seen in profile code)
    try {
        $sql = "ALTER TABLE `users` ADD COLUMN `cover_photo` varchar(255) DEFAULT NULL AFTER `profile_pic`;";
        $pdo->exec($sql);
        echo "Column 'cover_photo' added successfully.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "Duplicate column name") !== false) {
            echo "Column 'cover_photo' already exists.\n";
        }
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
?>