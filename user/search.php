<?php
// user/search.php
include '../includes/db.php';
include '../includes/header.php';

$results = [];
if (isset($_GET['q'])) {
    $q = trim($_GET['q']);
    if ($q) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username LIKE ? OR full_name LIKE ? LIMIT 20");
        $stmt->execute(["%$q%", "%$q%"]);
        $results = $stmt->fetchAll();
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card p-3">
            <form action="" method="GET">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control border-start-0" placeholder="Search users"
                        value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                </div>
            </form>

            <div class="mt-4">
                <?php if (isset($_GET['q'])): ?>
                    <h6 class="text-muted">Results for "<?php echo htmlspecialchars($_GET['q']); ?>"</h6>
                    <?php if (count($results) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($results as $u): ?>
                                <a href="profile.php?username=<?php echo $u['username']; ?>"
                                    class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                                    <img src="<?php echo $u['profile_pic'] ? '/uploads/' . $u['profile_pic'] : '/assets/img/default_avatar.png'; ?>"
                                        class="user-avatar">
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($u['username']); ?></div>
                                        <div class="text-muted small"><?php echo htmlspecialchars($u['full_name']); ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No users found.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-search display-1"></i>
                        <p class="mt-3">Search for friends to follow.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>