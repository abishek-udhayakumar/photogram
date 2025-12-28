<?php
require_once __DIR__ . '/includes/db.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS `highlights` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `title` varchar(100) DEFAULT 'Highlight',
      `cover_image` varchar(255) DEFAULT NULL,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($sql);
    echo "Table 'highlights' created successfully.\n";

} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage() . "\n");
}
?>