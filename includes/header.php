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
    <title><?php echo APP_NAME; ?></title>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS (Premium) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css?v=2.0">
</head>

<body>

    <?php if (!isset($hide_nav) || !$hide_nav): ?>
        <!-- Desktop Navigation (Hidden on Mobile) -->
        <nav class="navbar navbar-expand-lg fixed-top d-none d-lg-block">
            <div class="container">
                <a class="navbar-brand" href="/">Photogram</a>

                <div class="collapse navbar-collapse show" id="mainNav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- User Logged In -->
                        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center gap-3">
                            <li class="nav-item">
                                <a class="nav-link fs-4" href="/"><i class="bi bi-house-door-fill"></i></a>
                            </li>
                            <li class="nav-item position-relative me-3">
                                <div class="input-group">
                                    <span
                                        class="input-group-text bg-light border-0 text-secondary ps-3 pe-2 rounded-start-pill"><i
                                            class="bi bi-search small"></i></span>
                                    <input type="text" id="navSearchInput"
                                        class="form-control bg-light border-0 shadow-none ps-0 rounded-end-pill"
                                        placeholder="Search" style="width: 220px; font-size: 0.9rem;">
                                </div>
                                <!-- Dropdown Results -->
                                <div id="navSearchResults"
                                    class="search-dropdown shadow-lg rounded-4 overflow-hidden position-absolute w-100 bg-white mt-1"
                                    style="z-index: 1000; display: none;"></div>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fs-4 bg-transparent border-0" data-bs-toggle="modal"
                                    data-bs-target="#createPostModal" title="Create Post"><i
                                        class="bi bi-plus-square"></i></button>
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
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/user/profile.php">Profile</a>
                                    </li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/user/settings.php">Settings</a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="#" id="theme-toggle">Toggle Dark Mode</a></li>
                                    <li><a class="dropdown-item text-danger"
                                            href="<?php echo BASE_URL; ?>/auth/logout.php">Logout</a></li>
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

        <!-- Mobile Top Bar (Visible only on Mobile) -->
        <nav class="navbar fixed-top d-lg-none bg-body border-bottom" style="height: 60px;">
            <div class="container d-flex justify-content-between align-items-center">
                <a class="navbar-brand fs-3" href="/">Photogram</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="d-flex align-items-center gap-3">
                        <a href="#" id="theme-toggle-mobile" class="text-dark"><i class="bi bi-moon fs-5"></i></a>
                        <a href="/user/notifications.php" class="text-dark position-relative">
                            <i class="bi bi-heart fs-4"></i>
                            <!-- Notification dot could go here -->
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </nav>

        <!-- Mobile Bottom Navigation (Visible only on Mobile) -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <nav class="mobile-bottom-nav d-lg-none">
                <a href="/" class="nav-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                    <i class="bi <?php echo $current_page == 'index.php' ? 'bi-house-door-fill' : 'bi-house-door'; ?>"></i>
                </a>
                <a href="/user/search.php" class="nav-item <?php echo $current_page == 'search.php' ? 'active' : ''; ?>">
                    <i class="bi bi-search"></i>
                </a>
                <button class="nav-item center-btn" data-bs-toggle="modal" data-bs-target="#createPostModal">
                    <i class="bi bi-plus-lg text-white"></i>
                </button>
                <a href="/user/profile.php" class="nav-item <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
                    <img src="<?php echo $_SESSION['profile_pic'] ?? '/assets/img/default_avatar.png'; ?>" class="nav-avatar">
                </a>
            </nav>
        <?php endif; ?>

    <?php endif; ?>

    <div class="container main-content">