<?php
// user/create_post.php
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caption = $_POST['caption'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../uploads/';
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_ext, $allowed)) {
            $new_name = uniqid('post_') . '.' . $file_ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_name)) {
                // Resize logic could go here later.

                $stmt = $pdo->prepare("INSERT INTO posts (user_id, image, caption) VALUES (?, ?, ?)");
                if ($stmt->execute([$_SESSION['user_id'], $new_name, $caption])) {
                    header("Location: /");
                    exit;
                } else {
                    $error = "Database Error.";
                }
            } else {
                $error = "Failed to upload file. Check permissions.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, GIF allowed.";
        }
    } else {
        $error = "Please select an image.";
    }
}

include '../includes/header.php';
?>

<div class="container" style="max-width: 600px;">
    <div class="card">
        <div class="card-header bg-white text-center fw-bold py-3">
            Create New Post
        </div>
        <div class="card-body p-4">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-4 text-center">
                    <i class="bi bi-images display-1 text-muted" id="preview-icon"></i>
                    <img id="image-preview" src="#" alt="Preview" class="img-fluid rounded d-none mb-3"
                        style="max-height: 400px; width: 100%; object-fit: contain;">

                    <div class="mt-3">
                        <label class="btn btn-primary btn-sm">
                            Select from computer
                            <input type="file" name="image" id="image-input" class="d-none" accept="image/*" required
                                onchange="previewImage(this)">
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <textarea name="caption" class="form-control" rows="3" placeholder="Write a caption..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100">Share</button>
            </form>
        </div>
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('image-preview').src = e.target.result;
                document.getElementById('image-preview').classList.remove('d-none');
                document.getElementById('preview-icon').classList.add('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<?php include '../includes/footer.php'; ?>