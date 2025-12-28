<?php
// auth/register.php
include '../includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Basic Validation
    if (empty($full_name) || empty($email) || empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Check if email or username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->rowCount() > 0) {
            $error = "Email or Username already taken.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, username, password) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$full_name, $email, $username, $hashed_password])) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Something went wrong.";
            }
        }
    }
}
$hide_nav = true; // Use simple layout
include '../includes/header.php';
?>

<div class="auth-container">
    <h2 class="mb-4 brand-text" style="font-family: 'Inter', sans-serif; font-weight: 800;">Photogram</h2>
    <p class="text-muted mb-4">Sign up to see photos and videos from your friends.</p>

    <?php if ($error): ?>
        <div class="alert alert-danger p-2 fs-6"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success p-2 fs-6"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <input type="text" name="email" class="form-control" placeholder="Mobile Number or Email" required>
        </div>
        <div class="mb-3">
            <input type="text" name="full_name" class="form-control" placeholder="Full Name" required>
        </div>
        <div class="mb-3">
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>
        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-primary">Sign up</button>
    </form>

    <div class="mt-4 border-top pt-3">
        <p class="text-muted text-sm">Have an account? <a href="login.php" class="text-link">Log in</a></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>