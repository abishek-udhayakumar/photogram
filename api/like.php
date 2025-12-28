<?php
// api/like.php
include '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['post_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];

// Atomic Toggle Logic
try {
    // Try to Like first
    $stmt = $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $post_id]);
    $action = 'liked';

    // Optional: Notification here
} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
        // Duplicate entry = Already liked -> Unlike
        $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$user_id, $post_id]);
        $action = 'unliked';
    } else {
        throw $e;
    }
}

// Get new count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
$stmt->execute([$post_id]);
$count = $stmt->fetchColumn();

echo json_encode(['status' => 'success', 'action' => $action, 'count' => $count]);
