<?php
// api/comment.php
include '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['post_id']) || !isset($_POST['comment'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];
$comment = trim($_POST['comment']);

if ($comment === '') {
    echo json_encode(['status' => 'error', 'message' => 'Empty comment']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO comments (user_id, post_id, comment) VALUES (?, ?, ?)");
if ($stmt->execute([$user_id, $post_id, $comment])) {
    $new_id = $pdo->lastInsertId();

    // Fetch details to return
    $stmt = $pdo->prepare("
        SELECT c.*, u.username, u.profile_pic 
        FROM comments c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.id = ?
    ");
    $stmt->execute([$new_id]);
    $new_comment = $stmt->fetch(PDO::FETCH_ASSOC);

    // Add formatted time
    include_once '../includes/functions.php';
    $new_comment['time_ago'] = 'Just now'; // Initial state

    echo json_encode(['status' => 'success', 'data' => $new_comment]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'DB Error']);
}
