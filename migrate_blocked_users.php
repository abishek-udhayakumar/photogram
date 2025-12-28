<?php
// migrate_blocked_users.php
include 'includes/db.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS blocked_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        blocker_id INT NOT NULL,
        blocked_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (blocker_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (blocked_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_block (blocker_id, blocked_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($sql);
    echo "Table 'blocked_users' created successfully or already exists.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
