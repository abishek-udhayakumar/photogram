<?php
// api/search_users.php
include '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_GET['query'])) {
    echo json_encode(['status' => 'error', 'message' => 'No query provided']);
    exit;
}

$q = trim($_GET['query']);

if (strlen($q) < 1) {
    echo json_encode(['status' => 'success', 'data' => []]);
    exit;
}

try {
    // Search by username or full_name
    $stmt = $pdo->prepare("SELECT id, username, full_name, profile_pic FROM users WHERE username LIKE ? OR full_name LIKE ? LIMIT 10");
    $stmt->execute(["%$q%", "%$q%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format profile pic
    foreach ($results as &$user) {
        $user['profile_pic'] = $user['profile_pic'] ? '/uploads/' . $user['profile_pic'] : '/assets/img/default_avatar.png';
    }

    echo json_encode(['status' => 'success', 'data' => $results]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
