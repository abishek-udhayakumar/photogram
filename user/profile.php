<?php
// user/profile.php
include '../includes/db.php';
include '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit;
}

// 1. Fetch User Data
// --------------------------------------------------------------------------------
if (isset($_GET['username'])) {
    $username = $_GET['username'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $profile_user = $stmt->fetch();

    if (!$profile_user)
        die("User not found");
} else {
    // Current User
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $profile_user = $stmt->fetch();
}

$user_id = $profile_user['id'];
$is_me = ($user_id == $_SESSION['user_id']);

// 2. Stats
// --------------------------------------------------------------------------------
$post_count = $pdo->query("SELECT COUNT(*) FROM posts WHERE user_id = $user_id")->fetchColumn();
$follower_count = $pdo->query("SELECT COUNT(*) FROM follows WHERE following_id = $user_id")->fetchColumn();
$following_count = $pdo->query("SELECT COUNT(*) FROM follows WHERE follower_id = $user_id")->fetchColumn();

// 3. Follow Status & Mutuals
// --------------------------------------------------------------------------------
$is_following = false;
$mutual_count = 0;
$mutual_preview = [];

if (!$is_me) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ? AND following_id = ?");
    $stmt->execute([$_SESSION['user_id'], $user_id]);
    $is_following = $stmt->fetchColumn() > 0;

    // Mutuals
    $sql = "
         SELECT u.username FROM users u
         JOIN follows f1 ON u.id = f1.follower_id -- u follows target
         JOIN follows f2 ON u.id = f2.following_id -- I follow u
         WHERE f1.following_id = ? AND f2.follower_id = ?
         LIMIT 3
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $_SESSION['user_id']]);
    $mutual_preview = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Count total mutuals
    $sql = "
         SELECT COUNT(*) FROM users u
         JOIN follows f1 ON u.id = f1.follower_id
         JOIN follows f2 ON u.id = f2.following_id
         WHERE f1.following_id = ? AND f2.follower_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $_SESSION['user_id']]);
    $mutual_count = $stmt->fetchColumn();
}

// 3b. Check Block Status
$is_blocked = false;
$am_blocked = false;
if (!$is_me) {
    // Check if I blocked them
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM blocked_users WHERE blocker_id = ? AND blocked_id = ?");
    $stmt->execute([$_SESSION['user_id'], $user_id]);
    $is_blocked = $stmt->fetchColumn() > 0;

    // Check if they blocked me
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM blocked_users WHERE blocker_id = ? AND blocked_id = ?");
    $stmt->execute([$user_id, $_SESSION['user_id']]);
    $am_blocked = $stmt->fetchColumn() > 0;

    if ($am_blocked) {
        // Show "User not found" or restricted view. simplified to die for now or show restricted
        // Better UX: Show profile header but 0 posts and "User not found" placeholder
    }
}

// 3c. Highlights
$highlights = [];
if (!$am_blocked) {
    $stmt = $pdo->prepare("SELECT * FROM highlights WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $highlights = $stmt->fetchAll();
}

// 4. Determine Active Tab (posts, saved, liked, archived)
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'posts';
if (!$is_me && ($tab == 'saved' || $tab == 'liked' || $tab == 'archived')) {
    $tab = 'posts';
}

// 5. Fetch Content Based on Tab
$content_items = [];

if (!$am_blocked) {
    switch ($tab) {
        case 'saved':
            $stmt = $pdo->prepare("
                SELECT p.* FROM posts p 
                JOIN saved_posts s ON p.id = s.post_id 
                WHERE s.user_id = ? 
                ORDER BY s.created_at DESC
            ");
            $stmt->execute([$user_id]);
            $content_items = $stmt->fetchAll();
            break;

        case 'liked':
            $stmt = $pdo->prepare("
                SELECT p.* FROM posts p 
                JOIN likes l ON p.id = l.post_id 
                WHERE l.user_id = ? 
                ORDER BY l.created_at DESC
            ");
            $stmt->execute([$user_id]);
            $content_items = $stmt->fetchAll();
            break;

        case 'archived':
            // Only for me
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? AND is_archived = 1 ORDER BY created_at DESC");
            $stmt->execute([$user_id]);
            $content_items = $stmt->fetchAll();
            break;

        case 'posts':
        default:
            $stmt = $pdo->prepare("
                SELECT * FROM posts 
                WHERE user_id = ? AND is_archived = 0 
                ORDER BY is_pinned DESC, created_at DESC
            ");
            $stmt->execute([$user_id]);
            $content_items = $stmt->fetchAll();
            break;
    }
}

include '../includes/header.php';
?>

<div class="row justify-content-center fade-in">
    <div class="col-lg-9">

        <!-- Profile Header Card -->
        <div class="profile-card mb-4 shadow-md">
            <!-- Cover Photo -->
            <div style="height: 250px; background-color: var(--border-color); position: relative;">
                <?php if (!empty($profile_user['cover_photo'])): ?>
                    <img src="/uploads/<?php echo htmlspecialchars($profile_user['cover_photo']); ?>"
                        class="w-100 h-100 object-fit-cover" alt="Cover">
                <?php else: ?>
                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                        <i class="bi bi-image fs-1 opacity-50"></i>
                    </div>
                <?php endif; ?>
            </div>

            <div class="px-4 pb-4 position-relative">
                <div class="d-flex justify-content-between align-items-end" style="margin-top: -65px;">
                    <!-- Avatar -->
                    <img src="<?php echo $profile_user['profile_pic'] ? '/uploads/' . $profile_user['profile_pic'] : '/assets/img/default_avatar.png'; ?>"
                        class="rounded-circle border border-4 shadow-sm"
                        style="width: 130px; height: 130px; object-fit: cover; background: var(--card-bg); border-color: var(--card-bg) !important;"
                        alt="Profile">

                    <!-- Action Buttons -->
                    <div class="mb-3">
                        <?php if ($is_me): ?>
                            <a href="/user/settings.php" class="btn btn-secondary btn-sm fw-bold">Edit Profile</a>
                        <?php elseif ($is_blocked): ?>
                            <button class="btn btn-danger btn-sm fw-bold px-4"
                                onclick="toggleBlock(<?php echo $user_id; ?>, this)">Unblock</button>
                        <?php else: ?>
                            <button
                                class="btn <?php echo $is_following ? 'btn-secondary' : 'btn-primary'; ?> fw-bold btn-sm px-4"
                                onclick="toggleFollow(<?php echo $user_id; ?>, this)">
                                <?php echo $is_following ? 'Unfollow' : 'Follow'; ?>
                            </button>
                            <button class="btn btn-secondary fw-bold btn-sm ms-2">Message</button>

                            <!-- More Options Dropdown for Block -->
                            <div class="btn-group ms-2">
                                <button type="button" class="btn btn-sm btn-light border-0" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item text-danger" href="#"
                                            onclick="toggleBlock(<?php echo $user_id; ?>, this)">Block User</a></li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-3">
                    <h2 class="h4 mb-0 fw-bold d-flex align-items-center gap-2">
                        <?php echo htmlspecialchars($profile_user['username']); ?>
                        <?php if (!empty($profile_user['is_verified'])): ?>
                            <i class="bi bi-patch-check-fill text-primary" title="Verified" style="font-size: 1rem;"></i>
                        <?php endif; ?>
                        <?php if ($profile_user['is_private']): ?>
                            <i class="bi bi-lock-fill fs-6 text-muted" title="Private Account"></i>
                        <?php endif; ?>
                    </h2>
                    <div class="text-muted small mb-3"><?php echo htmlspecialchars($profile_user['full_name']); ?></div>

                    <!-- Meta Info: Location, Joined, Website -->
                    <div class="d-flex flex-wrap gap-3 mb-3 text-muted small">
                        <?php if (!empty($profile_user['location'])): ?>
                            <div><i class="bi bi-geo-alt-fill me-1"></i>
                                <?php echo htmlspecialchars($profile_user['location']); ?></div>
                        <?php endif; ?>

                        <div><i class="bi bi-calendar3 me-1"></i> Joined
                            <?php echo date('F Y', strtotime($profile_user['created_at'])); ?>
                        </div>

                        <?php if (!empty($profile_user['website'])): ?>
                            <div>
                                <i class="bi bi-link-45deg me-1"></i>
                                <a href="<?php echo htmlspecialchars($profile_user['website']); ?>" target="_blank"
                                    class="text-primary text-decoration-none fw-bold">
                                    <?php echo htmlspecialchars(parse_url($profile_user['website'], PHP_URL_HOST) ?? $profile_user['website']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Mutuals Indicator -->
                    <?php if (!$is_me && $mutual_count > 0): ?>
                        <div class="text-muted small mb-3">
                            <i class="bi bi-person-check-fill me-1"></i>
                            Followed by <?php echo implode(', ', $mutual_preview); ?>
                            <?php if ($mutual_count > count($mutual_preview)): ?>
                                + <?php echo $mutual_count - count($mutual_preview); ?> more
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Stats -->
                    <div class="d-flex gap-4 mb-4 border-top border-bottom py-3"
                        style="border-color: var(--border-color) !important;">
                        <div class="text-center" style="cursor: pointer;"
                            onclick="document.getElementById('profileTabs').scrollIntoView({behavior: 'smooth'})">
                            <span class="stat-value"><?php echo $post_count; ?></span>
                            <span class="stat-label">Posts</span>
                        </div>
                        <div class="text-center" style="cursor: pointer;"
                            onclick="openUserList('followers', <?php echo $user_id; ?>)">
                            <span class="stat-value" id="follower-count"><?php echo $follower_count; ?></span>
                            <span class="stat-label">Followers</span>
                        </div>
                        <div class="text-center" style="cursor: pointer;"
                            onclick="openUserList('following', <?php echo $user_id; ?>)">
                            <span class="stat-value"><?php echo $following_count; ?></span>
                            <span class="stat-label">Following</span>
                        </div>
                    </div>

                    <div class="bio text-break mb-2" style="max-width: 600px;">
                        <?php echo nl2br(htmlspecialchars($profile_user['bio'] ?? '')); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Highlights (Stories) -->
        <?php if (!empty($highlights) || $is_me): ?>
            <div class="mb-4 d-flex gap-4 overflow-auto pb-2" style="scrollbar-width: none;">
                <?php if ($is_me): ?>
                    <div class="text-center" style="cursor: pointer; min-width: 70px;">
                        <div class="rounded-circle border d-flex align-items-center justify-content-center bg-white"
                            style="width: 70px; height: 70px;">
                            <i class="bi bi-plus-lg fs-3 text-muted"></i>
                        </div>
                        <div class="small mt-1 text-muted">New</div>
                    </div>
                <?php endif; ?>

                <?php foreach ($highlights as $h): ?>
                    <div class="text-center" style="cursor: pointer; min-width: 70px;">
                        <div class="rounded-circle p-1 border border-2 border-secondary" style="width: 70px; height: 70px;">
                            <img src="/assets/img/default_story.png"
                                class="w-100 h-100 rounded-circle object-fit-cover bg-light">
                        </div>
                        <div class="small mt-1 text-truncate" style="max-width: 70px;">
                            <?php echo htmlspecialchars($h['title']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Dummy highlights for demo -->
                <div class="text-center" style="cursor: pointer; min-width: 70px;">
                    <div class="rounded-circle p-1 border border-2 border-secondary" style="width: 70px; height: 70px;">
                        <img src="https://via.placeholder.com/150"
                            class="w-100 h-100 rounded-circle object-fit-cover bg-light">
                    </div>
                    <div class="small mt-1">Life Update</div>
                </div>
                <div class="text-center" style="cursor: pointer; min-width: 70px;">
                    <div class="rounded-circle p-1 border border-2 border-secondary" style="width: 70px; height: 70px;">
                        <img src="https://via.placeholder.com/150"
                            class="w-100 h-100 rounded-circle object-fit-cover bg-light">
                    </div>
                    <div class="small mt-1">Travel</div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Content Area -->
        <div class="profile-card pb-3 shadow-md">
            <!-- Sticky Header -->
            <div class="sticky-tabs" id="profileTabs">
                <ul class="nav nav-tabs nav-fill justify-content-center p-0 m-0 border-0">
                    <li class="nav-item">
                        <a class="nav-link py-3 <?php echo $tab == 'posts' ? 'active' : ''; ?>"
                            href="?username=<?php echo $profile_user['username']; ?>&tab=posts">
                            <i class="bi bi-grid-3x3 me-2"></i> POSTS
                        </a>
                    </li>
                    <?php if ($is_me): ?>
                        <li class="nav-item">
                            <a class="nav-link py-3 <?php echo $tab == 'saved' ? 'active' : ''; ?>"
                                href="?username=<?php echo $profile_user['username']; ?>&tab=saved">
                                <i class="bi bi-bookmark me-2"></i> SAVED
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-3 <?php echo $tab == 'liked' ? 'active' : ''; ?>"
                                href="?username=<?php echo $profile_user['username']; ?>&tab=liked">
                                <i class="bi bi-heart me-2"></i> LIKED
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-3 <?php echo $tab == 'archived' ? 'active' : ''; ?>"
                                href="?username=<?php echo $profile_user['username']; ?>&tab=archived">
                                <i class="bi bi-archive me-2"></i> ARCHIVE
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link py-3" href="#">
                            <i class="bi bi-camera-video me-2"></i> REELS
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Grid -->
            <div class="p-1">
                <div class="row g-1">
                    <?php if (count($content_items) > 0): ?>
                        <?php foreach ($content_items as $p): ?>
                            <div class="col-4">
                                <div class="ratio ratio-1x1 position-relative group-hover">
                                    <img src="/uploads/<?php echo htmlspecialchars($p['image']); ?>"
                                        class="w-100 h-100 object-fit-cover" alt="Post" loading="lazy">

                                    <?php if (isset($p['is_pinned']) && $p['is_pinned']): ?>
                                        <div class="position-absolute top-0 end-0 p-2 text-white">
                                            <i class="bi bi-pin-angle-fill drop-shadow"></i>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Hover Overlay -->
                                    <a href="/index.php#post-<?php echo $p['id']; ?>"
                                        class="position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-25 opacity-0 d-flex align-items-center justify-content-center text-white text-decoration-none"
                                        style="transition: opacity 0.2s;" onmouseenter="this.style.opacity='1'"
                                        onmouseleave="this.style.opacity='0'">
                                        <i class="bi bi-heart-fill me-1"></i> <span class="small fw-bold">View</span>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <div class="text-muted mb-3">
                                <i class="bi bi-camera fs-1 opacity-25"></i>
                            </div>
                            <h5 class="fw-normal text-muted">No Posts Yet</h5>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reusable User List Modal -->
<div class="modal fade" id="userListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-center w-100" id="userListModalLabel">Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="userListBody">
                <div class="p-3">
                    <!-- Skeleton Loader for List -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="skeleton rounded-circle me-3" style="width:40px; height:40px;"></div>
                        <div class="flex-grow-1">
                            <div class="skeleton mb-1" style="width:60%; height:12px;"></div>
                            <div class="skeleton" style="width:40%; height:10px;"></div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="skeleton rounded-circle me-3" style="width:40px; height:40px;"></div>
                        <div class="flex-grow-1">
                            <div class="skeleton mb-1" style="width:60%; height:12px;"></div>
                            <div class="skeleton" style="width:40%; height:10px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/main.js"></script>
<?php include '../includes/footer.php'; ?>