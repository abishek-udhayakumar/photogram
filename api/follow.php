<?php
// api/follow.php
include '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['user_id'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$follower_id = $_SESSION['user_id'];
$following_id = $_POST['user_id'];

if ($follower_id == $following_id) {
    echo json_encode(['status' => 'error', 'message' => 'Cannot follow self']);
    exit;
}

// Check state
$stmt = $pdo->prepare("SELECT id FROM follows WHERE follower_id = ? AND following_id = ?");
$stmt->execute([$follower_id, $following_id]);
$following = $stmt->fetch();

if ($following) {
    // Unfollow
    $stmt = $pdo->prepare("DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
    $stmt->execute([$follower_id, $following_id]);
    $action = 'unfollowed';
} else {
    // Follow
    $stmt = $pdo->prepare("INSERT INTO follows (follower_id, following_id) VALUES (?, ?)");
    $stmt->execute([$follower_id, $following_id]);
    $action = 'followed';
}

echo json_encode(['status' => 'success', 'action' => $action]);
