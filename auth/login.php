<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (isset($_SESSION['user_id'])) { header("Location: ../dashboard.php"); exit; }

$error = '';
if (isset($_GET['blocked'])) $error = "Your account has been blocked by the admin.";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] === 'blocked') {
            $error = "Your account has been blocked by the admin.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            header("Location: ../dashboard.php");
            exit;
        }
    } else {
        $error = "Invalid email or password.";
    }
}
$page_name = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include '../includes/head.php'; ?>
</head>
<body>
<div class="auth-wrapper">
    <div class="glass-card auth-box">
        <div class="text-center mb-4">
            <h3 class="gradient-text fw-bold"><i class="fa-solid fa-book-open-reader"></i> StudySync</h3>
            <h5 class="fw-bold mt-3">Welcome Back!</h5>
            <p class="text-muted-soft small">Login to continue your learning journey</p>
        </div>

        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST" novalidate>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <div class="text-end mb-3">
                <a href="forgot_password.php" class="small">Forgot Password?</a>
            </div>
            <button type="submit" class="btn btn-gradient w-100">Login</button>
        </form>

        <p class="text-center mt-4 mb-0 small">Don't have an account? <a href="register.php" class="fw-bold">Register</a></p>
    </div>
</div>
<?php include '../includes/scripts.php'; ?>
</body>
</html>
