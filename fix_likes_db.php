<?php
include 'includes/db.php';

echo "Starting DB Fix Process...\n";

try {
    // 1. Check existing indexes
    echo "Attempting to drop potentially incorrect index 'unique_like'...\n";
    $pdo->query("ALTER TABLE likes DROP INDEX unique_like");
    echo "Dropped index 'unique_like' (if it existed).\n";
} catch (Exception $e) {
    echo "Drop failed (likely didn't exist or different name): " . $e->getMessage() . "\n";
}

try {
    // 2. Add correct composite index
    echo "Adding correct UNIQUE index on (user_id, post_id)...\n";
    $pdo->query("ALTER TABLE likes ADD UNIQUE KEY unique_like (user_id, post_id)");
    echo "SUCCESS: Added correct composite unique index.\n";
} catch (Exception $e) {
    echo "Add Index Failed: " . $e->getMessage() . "\n";
}

echo "DB Fix Complete.\n";
?>