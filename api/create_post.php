<?php
// api/create_post.php
include '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid method']);
    exit;
}

$caption = trim($_POST['caption'] ?? '');

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
    echo json_encode(['status' => 'error', 'message' => 'No image uploaded']);
    exit;
}

$upload_dir = '../uploads/';
$file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (!in_array($file_ext, $allowed)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed.']);
    exit;
}

$new_name = uniqid('post_') . '.' . $file_ext;

if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_name)) {
    try {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, image, caption) VALUES (?, ?, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $new_name, $caption])) {
            $new_id = $pdo->lastInsertId();

            // Fetch the new post data to return
            $stmt = $pdo->prepare("
                SELECT p.*, u.username, u.profile_pic 
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                WHERE p.id = ?
            ");
            $stmt->execute([$new_id]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            // Format data for frontend
            $post['profile_pic'] = $post['profile_pic'] ? '/uploads/' . $post['profile_pic'] : '/assets/img/default_avatar.png';
            $post['image_url'] = '/uploads/' . $post['image'];
            $post['caption'] = htmlspecialchars($post['caption']);
            include_once '../includes/functions.php';
            $post['time_ago'] = 'Just now';

            echo json_encode(['status' => 'success', 'data' => $post]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file']);
}
