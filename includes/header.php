<?php
// includes/header.php
if (!isset($pdo)) {
    // If included manually without db.php, try to include it
    if (file_exists(__DIR__ . '/db.php')) {
        include_once __DIR__ . '/db.php';
    } else {
        // Fallback relative path assumption
        include_once '../includes/db.php';
    }
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photogram</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>

    <?php if (!isset($hide_nav) || !$hide_nav): ?>
        <nav class="navbar navbar-expand-lg fixed-top">
            <div class="container">
                <a class="navbar-brand" href="/">Photogram</a>

                <!-- Mobile Toggle -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="mainNav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- User Logged In -->
                        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center gap-3">
                            <li class="nav-item">
                                <a class="nav-link fs-4" href="/"><i class="bi bi-house-door-fill"></i></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fs-4" href="/user/search.php"><i class="bi bi-search"></i></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fs-4" href="/user/create_post.php"><i class="bi bi-plus-square"></i></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fs-4" href="/user/notifications.php"><i class="bi bi-heart"></i></a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link" href="#" data-bs-toggle="dropdown">
                                    <img src="<?php echo $_SESSION['profile_pic'] ?? '/assets/img/default_avatar.png'; ?>"
                                        class="user-avatar" alt="Profile">
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="/user/profile.php">Profile</a></li>
                                    <li><a class="dropdown-item" href="/user/settings.php">Settings</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="#" id="theme-toggle">Toggle Dark Mode</a></li>
                                    <li><a class="dropdown-item text-danger" href="/auth/logout.php">Logout</a></li>
                                </ul>
                            </li>
                        </ul>
                    <?php else: ?>
                        <!-- Guest -->
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item">
                                <a class="btn btn-primary" href="/auth/login.php">Log In</a>
                            </li>
                            <li class="nav-item ms-2">
                                <a class="nav-link text-link" href="/auth/register.php">Sign Up</a>
                            </li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <div class="container main-content">