<?php
// api/delete_comment.php
include '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['comment_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$comment_id = $_POST['comment_id'];

try {
    // Check permissions: User must own the comment OR own the post
    $stmt = $pdo->prepare("
        SELECT c.user_id as comment_owner, p.user_id as post_owner 
        FROM comments c 
        JOIN posts p ON c.post_id = p.id 
        WHERE c.id = ?
    ");
    $stmt->execute([$comment_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['status' => 'error', 'message' => 'Comment not found']);
        exit;
    }

    if ($user_id != $row['comment_owner'] && $user_id != $row['post_owner']) {
        echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
        exit;
    }

    // Get post_id before deleting to update count later
    $stmt_post = $pdo->prepare("SELECT post_id FROM comments WHERE id = ?");
    $stmt_post->execute([$comment_id]);
    $post_id = $stmt_post->fetchColumn();

    // Delete
    $del = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $del->execute([$comment_id]);

    // Get updated count
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
    $stmtCount->execute([$post_id]);
    $new_count = $stmtCount->fetchColumn();

    echo json_encode(['status' => 'success', 'new_count' => $new_count]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
