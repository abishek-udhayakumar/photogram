<?php
// api/get_users.php
include '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$viewer_id = $_SESSION['user_id'];
$target_user_id = $_GET['user_id'] ?? $viewer_id;
$type = $_GET['type'] ?? 'followers'; // followers, following, mutual

$users = [];

if ($type === 'followers') {
    $sql = "
        SELECT u.id, u.username, u.full_name, u.profile_pic,
        (SELECT COUNT(*) FROM follows WHERE follower_id = ? AND following_id = u.id) as is_following
        FROM users u
        JOIN follows f ON u.id = f.follower_id
        WHERE f.following_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$viewer_id, $target_user_id]);
    $users = $stmt->fetchAll();

} elseif ($type === 'following') {
    $sql = "
        SELECT u.id, u.username, u.full_name, u.profile_pic,
        (SELECT COUNT(*) FROM follows WHERE follower_id = ? AND following_id = u.id) as is_following
        FROM users u
        JOIN follows f ON u.id = f.following_id
        WHERE f.follower_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$viewer_id, $target_user_id]);
    $users = $stmt->fetchAll();
}

// Logic for mutuals (optional, complex query, usually done on profile load for summary, but list view is same as followers restricted)
// For now, handling followers/following lists is primary.

echo json_encode(['status' => 'success', 'data' => $users]);
