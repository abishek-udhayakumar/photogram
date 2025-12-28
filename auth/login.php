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

<div class="auth-container">
    <h2 class="mb-4 brand-text" style="font-family: 'Inter', sans-serif; font-weight: 800;">Photogram</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger p-2 fs-6"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <input type="text" name="login_input" class="form-control" placeholder="Phone number, username, or email"
                required>
        </div>
        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-primary">Log in</button>
    </form>

    <div class="mt-4 border-top pt-3">
        <p class="text-muted text-sm">Don't have an account? <a href="register.php" class="text-link">Sign up</a></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>