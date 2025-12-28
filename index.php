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

// Fetch posts ONLY from followed users
$sql = "
    SELECT p.*, u.username, u.profile_pic, 
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) as is_liked,
    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.user_id IN (SELECT following_id FROM follows WHERE follower_id = ?)
    ORDER BY p.created_at DESC
    LIMIT 50
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, $user_id]);
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

                    <!-- Premium Header -->
                    <div class="post-header">
                        <div class="d-flex align-items-center">
                            <a href="/user/profile.php?username=<?php echo htmlspecialchars($post['username']); ?>">
                                <img src="<?php echo htmlspecialchars($post['profile_pic'] ? '/uploads/' . $post['profile_pic'] : '/assets/img/default_avatar.png'); ?>"
                                    class="user-avatar-md shadow-sm" alt="Avatar">
                            </a>
                            <div class="ms-2">
                                <a href="/user/profile.php?username=<?php echo htmlspecialchars($post['username']); ?>"
                                    class="username-link text-decoration-none">
                                    <?php echo htmlspecialchars($post['username']); ?>
                                </a>
                                <!-- Optional location/subtitle could go here -->
                            </div>
                        </div>

                        <!-- Options (Glassy Dropdown) -->
                        <div class="dropdown">
                            <button class="btn btn-link text-muted p-0 border-0" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 overflow-hidden mt-2 p-1">
                                <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                                    <li><a class="dropdown-item text-danger fw-bold rounded-2 px-3 py-2"
                                            href="/api/delete_post.php?id=<?php echo $post['id']; ?>"><i
                                                class="bi bi-trash me-2"></i>Delete</a></li>
                                    <li><a class="dropdown-item rounded-2 px-3 py-2" href="#"><i
                                                class="bi bi-archive me-2"></i>Archive</a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item text-danger fw-bold rounded-2 px-3 py-2" href="#"><i
                                                class="bi bi-flag me-2"></i>Report</a></li>
                                    <li><a class="dropdown-item rounded-2 px-3 py-2" href="#"><i
                                                class="bi bi-person-x me-2"></i>Unfollow</a></li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider my-1">
                                </li>
                                <li><a class="dropdown-item rounded-2 px-3 py-2" href="#"><i
                                            class="bi bi-link-45deg me-2"></i>Copy Link</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Image with Double Tap Heart Burst -->
                    <div class="post-image-container" ondblclick="handleDoubleTap(<?php echo $post['id']; ?>, this)">
                        <img src="/uploads/<?php echo htmlspecialchars($post['image']); ?>" class="post-image-content"
                            alt="Post Content" loading="lazy">
                        <i class="bi bi-heart-fill heart-burst"></i>
                    </div>

                    <!-- Action Bar -->
                    <div class="post-actions-bar">
                        <div class="d-flex gap-4">
                            <!-- Like -->
                            <button class="btn-action like-btn <?php echo $post['is_liked'] ? 'liked' : ''; ?>"
                                onclick="toggleLike(<?php echo $post['id']; ?>)" title="Like">
                                <i class="bi <?php echo $post['is_liked'] ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                            </button>

                            <!-- Comment -->
                            <button class="btn-action" data-action="focus-comment" data-post-id="<?php echo $post['id']; ?>"
                                title="Comment">
                                <i class="bi bi-chat"></i>
                            </button>

                            <!-- Share -->
                            <button class="btn-action" onclick="alert('Share feature coming soon!')" title="Share">
                                <i class="bi bi-send"></i>
                            </button>
                        </div>

                        <!-- Save -->
                        <div class="action-group-right">
                            <button class="btn-action save-btn" onclick="toggleSave(<?php echo $post['id']; ?>)" title="Save">
                                <i class="bi bi-bookmark"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="post-details">
                        <div class="likes-count" onclick="openUserList('likes', <?php echo $post['id']; ?>)">
                            <span
                                id="like-count-<?php echo $post['id']; ?>"><?php echo number_format($post['like_count']); ?></span>
                            likes
                        </div>

                        <?php if ($post['caption']): ?>
                            <div class="caption-text mb-2">
                                <span class="caption-username"><?php echo htmlspecialchars($post['username']); ?></span>
                                <?php
                                $caption = htmlspecialchars($post['caption']);
                                if (strlen($caption) > 120) {
                                    $short = substr($caption, 0, 120);
                                    echo "<span class='caption-short'>{$short}... <button class='more-btn fw-bold' onclick='toggleCaption(this)'>more</button></span>";
                                    echo "<span class='caption-full'>" . nl2br($caption) . "</span>";
                                } else {
                                    echo nl2br($caption);
                                }
                                ?>
                            </div>
                        <?php endif; ?>

                        <!-- Comments Preview Area -->
                        <div id="post-comments-section-<?php echo $post['id']; ?>">
                            <?php if ($post['comment_count'] > 2): ?>
                                <a href="#" class="text-secondary small fw-medium text-decoration-none mb-2 d-block view-all-link"
                                    data-action="view-all-comments" data-post-id="<?php echo $post['id']; ?>">
                                    View all <?php echo $post['comment_count']; ?> comments
                                </a>
                            <?php endif; ?>

                            <div class="comments-preview px-0 mb-2" id="comments-preview-<?php echo $post['id']; ?>">
                                <?php if ($post['comment_count'] > 0): ?>
                                    <?php
                                    // Fetch last 2 comments
                                    $stmt_c = $pdo->prepare("
                                        SELECT c.*, u.username 
                                        FROM comments c 
                                        JOIN users u ON c.user_id = u.id 
                                        WHERE c.post_id = ? 
                                        ORDER BY c.created_at DESC LIMIT 2
                                    ");
                                    $stmt_c->execute([$post['id']]);
                                    $latest_comments = array_reverse($stmt_c->fetchAll());

                                    foreach ($latest_comments as $c):
                                        ?>
                                        <div class="d-flex align-items-baseline mb-1">
                                            <span class="fw-bold me-2 small"><?php echo htmlspecialchars($c['username']); ?></span>
                                            <span class="text-secondary small text-truncate"
                                                style="max-width: 250px;"><?php echo htmlspecialchars($c['comment']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <small class="time-ago"><?php echo time_elapsed_string($post['created_at']); ?></small>
                    </div>

                    <!-- Seamless Comment Input -->
                    <div class="comment-input-area">
                        <form class="d-flex w-100 align-items-center" data-action="submit-comment"
                            data-post-id="<?php echo $post['id']; ?>">
                            <input type="text" class="comment-input" placeholder="Add a comment..." autocomplete="off">
                            <button type="submit" class="post-btn" disabled>Post</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5 fade-in">
                <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                    <i class="bi bi-camera fs-1 text-muted"></i>
                </div>
                <h3 class="h5 fw-bold text-dark">No Posts Yet</h3>
                <p class="text-muted small mb-4">Follow people to see their photos and videos here.</p>
                <a href="/user/search.php" class="btn btn-primary px-4 py-2 rounded-pill">Find People</a>
            </div>
        <?php endif; ?>

    </div>

    <!-- Sidebar -->
    <div class="col-md-4 col-lg-4 d-none d-md-block ps-4">
        <!-- User Mini Profile -->
        <div class="d-flex align-items-center mb-4 fade-in">
            <a href="/user/profile.php" class="text-decoration-none">
                <img src="<?php echo $_SESSION['profile_pic']; ?>" class="rounded-circle border"
                    style="width: 56px; height: 56px; object-fit: cover;">
            </a>
            <div class="ms-3">
                <a href="/user/profile.php" class="text-decoration-none text-dark fw-bold d-block lh-1 text-truncate"
                    style="max-width: 150px;">
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </a>
                <span class="text-secondary small"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            </div>
            <a href="/auth/logout.php" class="ms-auto text-primary small fw-bold text-decoration-none">Switch</a>
        </div>

        <div class="d-flex justify-content-between mb-3 align-items-center">
            <span class="text-secondary fw-bold small">Suggestions For You</span>
            <a href="/user/search.php" class="text-dark small fw-bold text-decoration-none">See All</a>
        </div>

        <!-- Suggestions Placeholder -->
        <div class="suggestions-list mb-4">
            <!-- This would ideally be dynamic -->
            <p class="text-muted small fst-italic">Find more friends in Search!</p>
        </div>

        <div class="small text-muted text-uppercase" style="font-size: 0.7rem; line-height: 1.6;">
            &copy; <?php echo date('Y'); ?> Photogram • Premium Social
        </div>
    </div>
</div>

<!-- Unified Comments Modal (Premium) -->
<div class="modal fade" id="commentsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" style="max-height: 95vh;">
        <div class="modal-content overflow-hidden d-flex flex-column border-0 shadow-lg rounded-4"
            style="height: 85vh;">
            <div class="modal-body p-0 d-flex flex-column flex-grow-1">
                <div class="row g-0 h-100 w-100">
                    <!-- Left Side: Image (Desktop) uses black bg for focus -->
                    <div
                        class="col-md-7 d-none d-md-flex align-items-center bg-black justify-content-center h-100 position-relative">
                        <img id="modalPostImage" src="" class="img-fluid"
                            style="max-height: 100%; max-width: 100%; object-fit: contain;">
                    </div>

                    <!-- Right Side: Comments -->
                    <div class="col-md-5 d-flex flex-column h-100 bg-white">
                        <!-- Header -->
                        <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <img id="modalOwnerAvatar" src="" class="rounded-circle me-2 border" width="32"
                                    height="32">
                                <span id="modalOwnerName" class="fw-bold text-dark small"></span>
                            </div>
                            <!-- More options in modal -->
                            <button type="button" class="btn btn-link text-dark p-0" data-bs-dismiss="modal">
                                <i class="bi bi-x-lg fs-5"></i>
                            </button>
                        </div>

                        <!-- Comments List (Scrollable) -->
                        <div class="flex-grow-1 p-3 overflow-auto custom-scrollbar" id="modalCommentsList"
                            style="min-height: 0;">
                            <div class="text-center mt-5">
                                <div class="spinner-border text-secondary" role="status"></div>
                            </div>
                        </div>

                        <!-- Footer: Actions & Input -->
                        <div class="p-3 border-top bg-light-subtle">
                            <!-- Action Icons in Modal (Like/Save) -->
                            <div class="d-flex gap-3 mb-3 fs-4">
                                <i class="bi bi-heart cursor-pointer" id="modalLikeBtn"></i>
                                <!-- Hook up logic later if needed -->
                                <i class="bi bi-chat"></i>
                                <i class="bi bi-send"></i>
                                <i class="bi bi-bookmark ms-auto"></i>
                            </div>

                            <div class="mb-2 fw-bold small text-dark"><span id="modalLikeCount">0</span> likes</div>
                            <div class="text-muted small text-uppercase mb-3" id="modalDate"
                                style="font-size: 0.65rem;">JUNE 24</div>

                            <form id="modalCommentForm" class="d-flex align-items-center position-relative"
                                autocomplete="off">
                                <input type="text" id="modalCommentInput"
                                    class="form-control rounded-pill bg-light border-0 shadow-none pe-5"
                                    placeholder="Add a comment..." style="font-size: 0.9rem; height: 44px;">
                                <button type="submit"
                                    class="btn text-primary fw-bold small position-absolute end-0 pe-3"
                                    style="font-size: 0.85rem;" disabled>Post</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Users List Modal (Generic for Likes/Followers) -->
<div class="modal fade" id="usersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold fs-6 text-center w-100" id="usersModalTitle">Likes</h5>
                <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="usersList" class="list-group list-group-flush custom-scrollbar"
                    style="max-height: 400px; overflow-y: auto;">
                    <!-- Users loaded via JS -->
                    <div class="text-center p-4">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Post Modal -->
<div class="modal fade" id="createPostModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content overflow-hidden border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 text-center justify-content-center position-relative">
                <h5 class="modal-title fw-bold">Create new post</h5>
                <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <form id="createPostForm">
                    <div class="row g-0" style="min-height: 400px;">
                        <!-- Left: Image Upload/Preview -->
                        <div class="col-md-7 bg-light d-flex align-items-center justify-content-center border-end position-relative"
                            style="min-height: 400px; background-color: #fafafa;">

                            <!-- Upload State -->
                            <div class="text-center p-4 fade-in" id="uploadState">
                                <i class="bi bi-images display-1 text-muted mb-3"></i>
                                <h5 class="fw-light mb-4">Drag photos and videos here</h5>
                                <label class="btn btn-primary btn-sm px-3 rounded-pill fw-bold">
                                    Select from computer
                                    <input type="file" name="image" id="modalImageInput" class="d-none" accept="image/*"
                                        required>
                                </label>
                            </div>

                            <!-- Preview State -->
                            <div id="previewState" class="d-none w-100 h-100 d-flex align-items-center bg-black">
                                <img id="modalImagePreview" src="#" class="img-fluid w-100 h-100"
                                    style="object-fit: contain;">
                                <button type="button"
                                    class="btn btn-dark btn-sm rounded-circle position-absolute top-0 end-0 m-3"
                                    id="clearImageBtn">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Right: Caption & Share -->
                        <div class="col-md-5 d-flex flex-column bg-white">
                            <div class="p-3 border-bottom d-flex align-items-center">
                                <img src="<?php echo $_SESSION['profile_pic'] ?? '/assets/img/default_avatar.png'; ?>"
                                    class="rounded-circle me-2" width="28" height="28" style="object-fit:cover;">
                                <span
                                    class="fw-bold small"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            </div>

                            <div class="flex-grow-1 p-3">
                                <textarea name="caption" class="form-control border-0 shadow-none h-100 p-0"
                                    placeholder="Write a caption..."
                                    style="resize: none; font-size: 0.95rem;"></textarea>
                            </div>

                            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                                <small class="text-muted"><span id="captionCount">0</span>/2,200</small>
                                <button type="submit" class="btn btn-link text-primary fw-bold text-decoration-none p-0"
                                    id="sharePostBtn" disabled>Share</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>