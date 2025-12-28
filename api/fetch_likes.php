<?php
// api/fetch_likes.php
include '../includes/db.php';
include '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_GET['post_id'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$post_id = $_GET['post_id'];
$current_user_id = $_SESSION['user_id'] ?? 0;

try {
    $sql = "
        SELECT u.id, u.username, u.full_name, u.profile_pic,
        (SELECT COUNT(*) FROM follows WHERE follower_id = ? AND following_id = u.id) as is_following,
        (SELECT COUNT(*) FROM follows WHERE follower_id = u.id AND following_id = ?) as is_followed_by
        FROM likes l
        JOIN users u ON l.user_id = u.id
        WHERE l.post_id = ?
        ORDER BY l.created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$current_user_id, $current_user_id, $post_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as &$user) {
        $user['profile_pic'] = $user['profile_pic'] ? '/uploads/' . $user['profile_pic'] : '/assets/img/default_avatar.png';
        // Prevent following yourself
        if ($user['id'] == $current_user_id) {
            $user['is_self'] = true;
        } else {
            $user['is_self'] = false;
        }
        // Mutual Logic
        $user['is_mutual'] = ($user['is_following'] > 0 && $user['is_followed_by'] > 0);
    }

    echo json_encode(['status' => 'success', 'data' => $users]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
