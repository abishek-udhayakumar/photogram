<?php
include 'includes/db.php';

try {
    // 1. Check existing indexes
    $pdo->query("ALTER TABLE likes DROP INDEX unique_like");
    echo "Dropped old index.\n";
} catch (Exception $e) {
    // Ignore if not exists
}

try {
    // 2. Add correct composite index
    $pdo->query("ALTER TABLE likes ADD UNIQUE KEY unique_like (user_id, post_id)");
    echo "Added correct composite unique index (user_id, post_id).\n";
} catch (Exception $e) {
    echo "Error adding index: " . $e->getMessage() . "\n";
}

// 3. Verify
echo "Done.\n";
?>