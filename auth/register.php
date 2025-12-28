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

<div class="d-flex align-items-center justify-content-center" style="min-height: 80vh;">
    <div class="glass-panel p-5 rounded-4 shadow-lg text-center fade-in"
        style="width: 100%; max-width: 400px; border: 1px solid rgba(255,255,255,0.2);">

        <h1 class="mb-2"
            style="font-weight: 800; background: linear-gradient(135deg, var(--primary), var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 2.5rem;">
            Photogram</h1>
        <p class="text-muted mb-4 small fw-semibold text-uppercase letter-spacing-1">Join the Experience</p>

        <?php if ($error): ?>
            <div
                class="alert alert-danger py-2 px-3 small rounded-3 mb-4 shadow-sm border-0 bg-danger-subtle text-danger fw-bold">
                <i class="bi bi-exclamation-circle-fill me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div
                class="alert alert-success py-2 px-3 small rounded-3 mb-4 shadow-sm border-0 bg-success-subtle text-success fw-bold">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-floating mb-3">
                <input type="text" name="email" class="form-control bg-light border-0 shadow-none fw-medium"
                    id="floatingEmail" placeholder="name@example.com" required style="height: 50px;">
                <label for="floatingEmail" class="text-muted">Mobile Number or Email</label>
            </div>

            <div class="form-floating mb-3">
                <input type="text" name="full_name" class="form-control bg-light border-0 shadow-none fw-medium"
                    id="floatingFullName" placeholder="Full Name" required style="height: 50px;">
                <label for="floatingFullName" class="text-muted">Full Name</label>
            </div>

            <div class="form-floating mb-3">
                <input type="text" name="username" class="form-control bg-light border-0 shadow-none fw-medium"
                    id="floatingUsername" placeholder="Username" required style="height: 50px;">
                <label for="floatingUsername" class="text-muted">Username</label>
            </div>

            <div class="form-floating mb-4">
                <input type="password" name="password" class="form-control bg-light border-0 shadow-none fw-medium"
                    id="floatingPassword" placeholder="Password" required style="height: 50px;">
                <label for="floatingPassword" class="text-muted">Password</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 shadow-md mb-3 fs-6">
                Sign Up <i class="bi bi-arrow-right-short ms-1 fs-5 align-middle"></i>
            </button>

            <p class="text-muted opacity-75 small lh-sm mb-0 px-2" style="font-size: 0.75rem;">
                By signing up, you agree to our Terms, Data Policy and Cookies Policy.
            </p>
        </form>

        <div class="mt-4 pt-3 border-top border-light-subtle">
            <p class="text-muted small mb-0">Have an account?</p>
            <a href="login.php" class="text-primary fw-bold text-decoration-none">Log in</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>