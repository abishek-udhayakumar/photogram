<?php
// user/settings.php
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch current data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $bio = trim($_POST['bio']);
    $website = trim($_POST['website']);
    $location = trim($_POST['location']);
    $is_private = isset($_POST['is_private']) ? 1 : 0;

    // Validate Username uniqueness
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->rowCount() > 0) {
        $error = "Username already taken.";
    } else {
        // Handle Profile Pic
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $new_name = uniqid('p_') . '.' . $ext;
                move_uploaded_file($_FILES['profile_pic']['tmp_name'], '../uploads/' . $new_name);

                // Update DB for pic
                $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?")->execute([$new_name, $user_id]);
                $_SESSION['profile_pic'] = '/uploads/' . $new_name; // Update session
            }
        }

        // Handle Cover Photo
        if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['cover_photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $new_name = uniqid('c_') . '.' . $ext;
                move_uploaded_file($_FILES['cover_photo']['tmp_name'], '../uploads/' . $new_name);

                // Update DB for cover
                $pdo->prepare("UPDATE users SET cover_photo = ? WHERE id = ?")->execute([$new_name, $user_id]);
            }
        }

        // Update Info
        $updateInfo = $pdo->prepare("UPDATE users SET username = ?, full_name = ?, bio = ?, website = ?, location = ?, is_private = ? WHERE id = ?");
        if ($updateInfo->execute([$username, $full_name, $bio, $website, $location, $is_private, $user_id])) {
            $success = "Profile updated successfully!";
            // Update session info
            $_SESSION['username'] = $username;
            $_SESSION['full_name'] = $full_name;

            // Refetch to show updated data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } else {
            $error = "Failed to update profile.";
        }
    }
}

include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white fw-bold">Edit Profile</div>
            <div class="card-body">

                <!-- Toast Container (Positioned) -->
                <div class="toast-container position-fixed bottom-0 end-0 p-3">
                    <div id="statusToast"
                        class="toast align-items-center text-white bg-<?php echo $error ? 'danger' : 'success'; ?> border-0"
                        role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <?php echo $error ?: $success; ?>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                                aria-label="Close"></button>
                        </div>
                    </div>
                </div>

                <form method="POST" enctype="multipart/form-data">

                    <div class="mb-4 text-center">
                        <div class="position-relative d-inline-block">
                            <img src="<?php echo $user['profile_pic'] ? '/uploads/' . $user['profile_pic'] : '/assets/img/default_avatar.png'; ?>"
                                class="rounded-circle border" style="width: 100px; height: 100px; object-fit: cover;"
                                id="p-preview">
                            <label class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-1"
                                style="cursor: pointer;">
                                <i class="bi bi-camera p-1"></i>
                                <input type="file" name="profile_pic" class="d-none"
                                    onchange="previewFile(this, 'p-preview')">
                            </label>
                        </div>
                        <div class="mt-2 text-muted small">Change Profile Photo</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Cover Photo</label>
                        <div class="position-relative"
                            style="height: 150px; background-color: #eee; border-radius: 8px; overflow: hidden;">
                            <img src="<?php echo $user['cover_photo'] ? '/uploads/' . $user['cover_photo'] : ''; ?>"
                                class="w-100 h-100 object-fit-cover <?php echo $user['cover_photo'] ? '' : 'd-none'; ?>"
                                id="c-preview" style="object-fit: cover;">

                            <label
                                class="position-absolute top-50 start-50 translate-middle btn btn-light btn-sm shadow"
                                style="cursor: pointer;">
                                <i class="bi bi-image me-1"></i> Change Cover
                                <input type="file" name="cover_photo" class="d-none"
                                    onchange="previewFile(this, 'c-preview')">
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control"
                            value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control"
                            value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control"
                            rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-geo-alt"></i></span>
                            <input type="text" name="location" class="form-control border-start-0"
                                placeholder="City, Country"
                                value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Website</label>
                        <input type="url" name="website" class="form-control" placeholder="https://..."
                            value="<?php echo htmlspecialchars($user['website'] ?? ''); ?>">
                    </div>

                    <div class="mb-4 form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="privateSwitch" name="is_private" <?php echo $user['is_private'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="privateSwitch">Private Account</label>
                        <div class="form-text">If private, only followers can see your posts.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                        <a href="profile.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Preview Images
    function previewFile(input, imgId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var img = document.getElementById(imgId);
                img.src = e.target.result;
                img.classList.remove('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Show Toast if PHP set success/error
    <?php if ($success || $error): ?>     var toastEl = document.getElementById('statusToast'); var toast = new bootstrap.Toast(toastEl); toast.show();
    <?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>