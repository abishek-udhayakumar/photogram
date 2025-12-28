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
    $current_user_id = $_SESSION['user_id'] ?? 0;

    // Check post owner
    $stmt_owner = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmt_owner->execute([$post_id]);
    $post_owner_id = $stmt_owner->fetchColumn();

    $comments_by_id = [];
    $roots = [];

    // First pass: Process and index
    foreach ($comments as $item) {
        $item['time_ago'] = time_elapsed_string($item['created_at']);
        $item['profile_pic'] = $item['profile_pic'] ? '/uploads/' . $item['profile_pic'] : '/assets/img/default_avatar.png';
        $item['comment'] = htmlspecialchars($item['comment']);
        // Permission: Comment Owner OR Post Owner
        $item['can_delete'] = ($item['user_id'] == $current_user_id) || ($post_owner_id == $current_user_id);

        $item['replies'] = []; // Init replies array
        $comments_by_id[$item['id']] = $item;
    }

    // Second pass: Build Tree
    foreach ($comments_by_id as $id => &$item) {
        if ($item['parent_id']) {
            // It's a reply
            if (isset($comments_by_id[$item['parent_id']])) {
                $comments_by_id[$item['parent_id']]['replies'][] = &$item;
            } else {
                // Formatting orphan replies as roots just in case
                $roots[] = &$item;
            }
        } else {
            // It's a root comment
            $roots[] = &$item;
        }
    }

    echo json_encode(['status' => 'success', 'data' => $roots]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>