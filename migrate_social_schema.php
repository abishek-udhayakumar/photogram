<?php
// migrate_social_schema.php
include 'includes/db.php';

try {
    $pdo->exec("DROP TABLE IF EXISTS likes");
    $sql_likes = "
    CREATE TABLE likes (
      id int(11) NOT NULL AUTO_INCREMENT,
      user_id int(11) NOT NULL,
      post_id int(11) NOT NULL,
      created_at timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      UNIQUE KEY unique_like (user_id, post_id),
      FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
      FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($sql_likes);
    echo "Table 'likes' recreated successfully.\n";

    $pdo->exec("DROP TABLE IF EXISTS follows");
    $sql_follows = "
    CREATE TABLE follows (
      id int(11) NOT NULL AUTO_INCREMENT,
      follower_id int(11) NOT NULL,
      following_id int(11) NOT NULL,
      created_at timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      UNIQUE KEY unique_follow (follower_id, following_id),
      FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
      FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($sql_follows);
    echo "Table 'follows' recreated successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
