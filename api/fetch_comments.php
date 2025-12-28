<?php
// api/fetch_comments.php
include '../includes/db.php';
include '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_GET['post_id'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$post_id = $_GET['post_id'];
$after_id = isset($_GET['after_id']) ? (int) $_GET['after_id'] : 0;
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'new';

try {
    if ($mode === 'all') {
        $sql = "
            SELECT c.*, u.username, u.profile_pic 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.post_id = ? 
            ORDER BY c.created_at ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$post_id]);
    } else {
        $sql = "
            SELECT c.*, u.username, u.profile_pic 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.post_id = ? AND c.id > ?
            ORDER BY c.created_at ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$post_id, $after_id]);
    }

    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process comments for frontend
    foreach ($comments as &$item) {
        $item['time_ago'] = time_elapsed_string($item['created_at']);
        $item['profile_pic'] = $item['profile_pic'] ? '/uploads/' . $item['profile_pic'] : '/assets/img/default_avatar.png';
        $item['comment'] = htmlspecialchars($item['comment']);
    }

    echo json_encode(['status' => 'success', 'data' => $comments]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>