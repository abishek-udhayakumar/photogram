<?php
// admin/index.php
session_start();
include '../includes/db.php';
include '../includes/functions.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Stats
$users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$posts_count = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$comments_count = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();

// Handle Actions
if (isset($_GET['delete_post'])) {
    $id = $_GET['delete_post'];
    $pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([$id]);
    header("Location: index.php");
    exit;
}

if (isset($_GET['delete_user'])) {
    $id = $_GET['delete_user'];
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    header("Location: index.php");
    exit;
}

// Latest Posts
$posts = $pdo->query("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 10")->fetchAll();
// Latest Users
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 10")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - Photogram Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .sidebar {
            height: 100vh;
            position: fixed;
            width: 250px;
            background: #212529;
            color: white;
            padding-top: 20px;
        }

        .sidebar a {
            color: #aaa;
            text-decoration: none;
            display: block;
            padding: 10px 20px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            color: white;
            background: #343a40;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .stat-card {
            border: none;
            border-radius: 10px;
            color: white;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h4 class="text-center mb-4">Photogram</h4>
        <a href="index.php" class="active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        <a href="#"><i class="bi bi-people me-2"></i> Users</a>
        <a href="#"><i class="bi bi-images me-2"></i> Posts</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
    </div>

    <div class="main-content">
        <h2 class="mb-4">Dashboard</h2>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card bg-primary p-3">
                    <h3><?php echo $users_count; ?></h3>
                    <span>Total Users</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-success p-3">
                    <h3><?php echo $posts_count; ?></h3>
                    <span>Total Posts</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-warning p-3">
                    <h3><?php echo $comments_count; ?></h3>
                    <span>Total Comments</span>
                </div>
            </div>
        </div>

        <!-- Content Management -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white fw-bold">Recent Posts</div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Caption</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($posts as $p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($p['username']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($p['caption'], 0, 30)); ?>...</td>
                                        <td><?php echo time_elapsed_string($p['created_at']); ?></td>
                                        <td>
                                            <a href="?delete_post=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Delete this post?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white fw-bold">Recent Users</div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td>
                                            <a href="?delete_user=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Block/Delete this user?')">Block</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

</body>

</html>