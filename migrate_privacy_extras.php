<?php
// migrate_privacy_extras.php
include 'includes/db.php';

try {
    // Posts enhancements
    $pdo->exec("ALTER TABLE posts ADD COLUMN is_pinned TINYINT(1) DEFAULT 0");
    $pdo->exec("ALTER TABLE posts ADD COLUMN is_archived TINYINT(1) DEFAULT 0");
    $pdo->exec("ALTER TABLE posts ADD COLUMN comments_disabled TINYINT(1) DEFAULT 0");

    // Blocked Users Table
    $sql_blocks = "CREATE TABLE IF NOT EXISTS `blocked_users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `blocker_id` int(11) NOT NULL,
        `blocked_id` int(11) NOT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_block` (`blocker_id`, `blocked_id`),
        FOREIGN KEY (`blocker_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`blocked_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql_blocks);

    // Highlights Table (Simplified for UI demo)
    $sql_highlights = "CREATE TABLE IF NOT EXISTS `highlights` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `title` varchar(50) NOT NULL,
        `cover_image` varchar(255) NOT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql_highlights);

    // Privacy Settings in User
    $pdo->exec("ALTER TABLE users ADD COLUMN hide_counts TINYINT(1) DEFAULT 0");

    echo "Migration successful: Added Pinned/Archived posts, Blocked Users table, Highlights table.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Columns already exist. Skipping.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>