<?php
// auth/login.php
include '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = trim($_POST['login_input']); // can be email or username
    $password = $_POST['password'];

    if (empty($login_input) || empty($password)) {
        $error = "Please enter credentials.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$login_input, $login_input]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['profile_pic'] = $user['profile_pic'] ? '/uploads/' . $user['profile_pic'] : '/assets/img/default_avatar.png'; // Basic handling

            header("Location: /");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}
$hide_nav = true;
include '../includes/header.php';
?>

<div class="d-flex align-items-center justify-content-center" style="min-height: 80vh;">
    <div class="glass-panel p-5 rounded-4 shadow-lg text-center fade-in"
        style="width: 100%; max-width: 400px; border: 1px solid rgba(255,255,255,0.2);">

        <h1 class="mb-2"
            style="font-weight: 800; background: linear-gradient(135deg, var(--primary), var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 2.5rem;">
            Photogram</h1>
        <p class="text-muted mb-4 small fw-semibold text-uppercase letter-spacing-1">Premium Social Experience</p>

        <?php if ($error): ?>
            <div
                class="alert alert-danger py-2 px-3 small rounded-3 mb-4 shadow-sm border-0 bg-danger-subtle text-danger fw-bold">
                <i class="bi bi-exclamation-circle-fill me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-floating mb-3">
                <input type="text" name="login_input" class="form-control bg-light border-0 shadow-none fw-medium"
                    id="floatingInput" placeholder="name@example.com" required style="height: 50px;">
                <label for="floatingInput" class="text-muted">Username or Email</label>
            </div>
            <div class="form-floating mb-4">
                <input type="password" name="password" class="form-control bg-light border-0 shadow-none fw-medium"
                    id="floatingPassword" placeholder="Password" required style="height: 50px;">
                <label for="floatingPassword" class="text-muted">Password</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 shadow-md mb-3 fs-6">
                Log In <i class="bi bi-arrow-right-short ms-1 fs-5 align-middle"></i>
            </button>
        </form>

        <div class="mt-4 pt-3 border-top border-light-subtle">
            <p class="text-muted small mb-0">Don't have an account?</p>
            <a href="register.php" class="text-primary fw-bold text-decoration-none">Create an account</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>