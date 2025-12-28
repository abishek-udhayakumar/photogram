<?php
// index.php
include 'includes/db.php';
include 'includes/functions.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch posts from followed users + own posts + random (for discovery if feed is empty)
// Complex query to show relevant posts
$sql = "
    SELECT p.*, u.username, u.profile_pic, 
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) as is_liked,
    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.user_id = ? 
    OR p.user_id IN (SELECT following_id FROM follows WHERE follower_id = ?)
    ORDER BY p.created_at DESC
    LIMIT 50
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, $user_id, $user_id]);
$posts = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <!-- Main Feed -->
    <div class="col-md-8 col-lg-6">

        <!-- Stories Placeholder (Optional UI) -->
        <div class="card p-3 mb-4 d-none d-md-block">
            <div class="d-flex gap-3 overflow-auto">
                <style>
                    .story-circle {
                        width: 60px;
                        height: 60px;
                        border-radius: 50%;
                        background: #eee;
                        border: 2px solid var(--accent-color);
                        flex-shrink: 0;
                    }
                </style>
                <?php for ($i = 0; $i < 6; $i++): ?>
                    <div class="story-circle"></div>
                <?php endfor; ?>
            </div>
        </div>

        <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $post): ?>
                <div class="card card-feed fade-in" id="post-<?php echo $post['id']; ?>"
                    data-post-id="<?php echo $post['id']; ?>">

                    <!-- Header -->
                    <div class="post-header">
                        <div class="d-flex align-items-center">
                            <a href="/user/profile.php?username=<?php echo htmlspecialchars($post['username']); ?>">
                                <img src="<?php echo htmlspecialchars($post['profile_pic'] ? '/uploads/' . $post['profile_pic'] : '/assets/img/default_avatar.png'); ?>"
                                    class="user-avatar-md me-2" alt="Avatar">
                            </a>
                            <div class="post-header-info">
                                <a href="/user/profile.php?username=<?php echo htmlspecialchars($post['username']); ?>"
                                    class="username-link">
                                    <?php echo htmlspecialchars($post['username']); ?>
                                </a>
                                <!-- Simple verified check (if you joined user table with 'is_verified' in SQL, currently not in SELECT but usually safe to omit or query) -->
                            </div>
                        </div>

                        <!-- Options Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg">
                                <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                                    <li><a class="dropdown-item text-danger"
                                            href="/api/delete_post.php?id=<?php echo $post['id']; ?>">Delete</a></li>
                                    <li><a class="dropdown-item" href="#">Archive</a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item text-danger" href="#">Report</a></li>
                                    <li><a class="dropdown-item" href="#">Unfollow</a></li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#">Copy Link</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Image with Double Tap -->
                    <div class="post-image-container" ondblclick="handleDoubleTap(<?php echo $post['id']; ?>, this)">
                        <img src="/uploads/<?php echo htmlspecialchars($post['image']); ?>" class="post-image-content"
                            alt="Post Content" loading="lazy">
                        <i class="bi bi-heart-fill heart-burst"></i>
                    </div>

                    <!-- Action Bar -->
                    <div class="post-actions-bar">
                        <div class="action-group-left">
                            <button class="btn-action like-btn <?php echo $post['is_liked'] ? 'liked' : ''; ?>"
                                onclick="toggleLike(<?php echo $post['id']; ?>)">
                                <i class="bi <?php echo $post['is_liked'] ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                            </button>
                            <!-- Updated: Event Delegation [focus-comment] -->
                            <button class="btn-action" data-action="focus-comment" data-post-id="<?php echo $post['id']; ?>">
                                <i class="bi bi-chat"></i>
                            </button>
                            <button class="btn-action" onclick="alert('Share feature coming soon!')">
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                        <div class="action-group-right">
                            <button class="btn-action" onclick="alert('Save feature coming soon!')">
                                <i class="bi bi-bookmark"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Details: Likes, Caption, Comments -->
                    <div class="post-details">
                        <div class="likes-count" onclick="openUserList('likes', <?php echo $post['id']; ?>)">
                            <span id="like-count-<?php echo $post['id']; ?>"><?php echo $post['like_count']; ?></span> likes
                        </div>

                        <?php if ($post['caption']): ?>
                            <div class="caption-text">
                                <span class="caption-username"><?php echo htmlspecialchars($post['username']); ?></span>
                                <?php
                                $caption = htmlspecialchars($post['caption']);
                                if (strlen($caption) > 100) {
                                    $short = substr($caption, 0, 100);
                                    echo "<span class='caption-short'>{$short}... <button class='more-btn' onclick='toggleCaption(this)'>more</button></span>";
                                    echo "<span class='caption-full'>" . nl2br($caption) . "</span>";
                                } else {
                                    echo nl2br($caption);
                                }
                                ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($post['comment_count'] > 2): ?>
                            <!-- Updated: Event Delegation [view-all-comments] -->
                            <a href="#" class="text-muted small text-decoration-none mb-2 d-block view-all-link"
                                data-action="view-all-comments" data-post-id="<?php echo $post['id']; ?>">
                                View all <?php echo $post['comment_count']; ?> comments
                            </a>
                        <?php endif; ?>

                        <!-- In-line Comments Preview -->
                        <div class="comments-preview mb-2 px-3">
                            <div class="comments-list-<?php echo $post['id']; ?> d-flex flex-column gap-2"
                                id="comments-list-<?php echo $post['id']; ?>" style="max-height: 250px; overflow-y: auto;">

                                <?php
                                // Fetch last 3 comments
                                $stmt_c = $pdo->prepare("
                                    SELECT * FROM (
                                        SELECT c.*, u.username, u.profile_pic 
                                        FROM comments c 
                                        JOIN users u ON c.user_id = u.id 
                                        WHERE c.post_id = ? 
                                        ORDER BY c.created_at DESC LIMIT 3
                                    ) sub ORDER BY created_at ASC
                                ");
                                $stmt_c->execute([$post['id']]);
                                $latest_comments = $stmt_c->fetchAll();

                                foreach ($latest_comments as $c):
                                    ?>
                                    <div class="comment-item d-flex align-items-start mb-2"
                                        data-comment-id="<?php echo $c['id']; ?>" id="comment-<?php echo $c['id']; ?>">
                                        <img src="<?php echo $c['profile_pic'] ? '/uploads/' . $c['profile_pic'] : '/assets/img/default_avatar.png'; ?>"
                                            class="rounded-circle me-2 border"
                                            style="width: 28px; height: 28px; object-fit: cover;">
                                        <div class="bg-light rounded px-3 py-2 flex-grow-1" style="font-size: 0.9rem;">
                                            <div class="d-flex justify-content-between align-items-baseline">
                                                <a href="/user/profile.php?username=<?php echo $c['username']; ?>"
                                                    class="fw-bold text-dark text-decoration-none me-2"><?php echo htmlspecialchars($c['username']); ?></a>
                                                <small class="text-muted"
                                                    style="font-size: 0.7rem;"><?php echo time_elapsed_string($c['created_at']); ?></small>
                                            </div>
                                            <div class="text-break"><?php echo htmlspecialchars($c['comment']); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <small class="time-ago px-3"><?php echo time_elapsed_string($post['created_at']); ?></small>
                    </div>

                    <!-- Add Comment -->
                    <div class="comment-input-area">
                        <!-- Updated: Event Delegation [submit-comment] -->
                        <form class="d-flex w-100 align-items-center" data-action="submit-comment"
                            data-post-id="<?php echo $post['id']; ?>">
                            <input type="text" class="comment-input" placeholder="Add a comment..." autocomplete="off">
                            <button type="submit" class="post-btn" disabled>Post</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-camera fs-1 text-muted"></i>
                <h3 class="mt-3">No Posts Yet</h3>
                <p class="text-muted">Follow people or create a post to see content here.</p>
                <a href="/user/create_post.php" class="btn btn-primary">Create Post</a>
            </div>
        <?php endif; ?>

    </div>

    <!-- Sidebar -->
    <div class="col-md-4 col-lg-3 d-none d-md-block">
        <div class="card p-3 border-0 bg-transparent shadow-none">
            <div class="d-flex align-items-center mb-3">
                <img src="<?php echo $_SESSION['profile_pic']; ?>" class="user-avatar"
                    style="width: 50px; height: 50px;">
                <div class="ms-2">
                    <div class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    <div class="text-muted small"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                </div>
                <a href="/auth/logout.php" class="ms-auto text-link small">Switch</a>
            </div>

            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted fw-bold small">Suggestions For You</span>
                <a href="/user/search.php" class="text-dark small fw-bold">See All</a>
            </div>

            <!-- Suggestions Placeholder -->
            <div class="suggestions-list">
                <p class="text-muted small">Find more friends in Search!</p>
            </div>

            <div class="mt-4 small text-muted text-uppercase" style="font-size: 0.7rem;">
                &copy; <?php echo date('Y'); ?> Photogram from Abishek
            </div>
        </div>
    </div>
</div>

<!-- Unified Comments Modal -->
<div class="modal fade" id="commentsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content overflow-hidden" style="border-radius: 0; min-height: 80vh;">
            <div class="modal-body p-0">
                <div class="row g-0 h-100">
                    <!-- Left Side: Image (Desktop) -->
                    <div class="col-md-7 d-none d-md-flex align-items-center bg-black justify-content-center"
                        style="min-height: 80vh;">
                        <img id="modalPostImage" src="" class="img-fluid"
                            style="max-height: 80vh; object-fit: contain;">
                    </div>

                    <!-- Right Side: Comments -->
                    <div class="col-md-5 d-flex flex-column h-100 bg-white">
                        <!-- Header -->
                        <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <img id="modalOwnerAvatar" src="" class="rounded-circle me-2 border" width="32"
                                    height="32">
                                <span id="modalOwnerName" class="fw-bold small"></span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <!-- Comments List (Scrollable) -->
                        <div class="flex-grow-1 p-3 overflow-auto" id="modalCommentsList" style="height: 0;">
                            <!-- Comments injected here -->
                            <div class="text-center mt-5">
                                <div class="spinner-border text-muted" role="status"></div>
                            </div>
                        </div>

                        <!-- Footer: Actions & Input -->
                        <div class="p-3 border-top">
                            <form id="modalCommentForm" class="d-flex align-items-center" autocomplete="off">
                                <input type="text" id="modalCommentInput" class="form-control border-0 shadow-none px-0"
                                    placeholder="Add a comment..." style="font-size: 0.9rem;">
                                <button type="submit" class="btn text-primary fw-bold text-uppercase small"
                                    style="font-size: 0.8rem;" disabled>Post</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/comments.js"></script>
<script src="/assets/js/main.js"></script>
<?php include 'includes/footer.php'; ?>