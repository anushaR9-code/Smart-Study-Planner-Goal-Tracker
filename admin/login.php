<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (isset($_SESSION['admin_id'])) { header("Location: dashboard.php"); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['username'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid admin credentials.";
    }
}
$page_name = 'Admin Login';
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
            <h3 class="gradient-text fw-bold"><i class="fa-solid fa-user-shield"></i> StudySync</h3>
            <h5 class="fw-bold mt-3">Admin Panel Login</h5>
            <p class="text-muted-soft small">Restricted access for administrators</p>
        </div>

        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username or Email</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-gradient w-100">Login as Admin</button>
        </form>
        <p class="text-center mt-4 mb-0 small"><a href="../index.php">&larr; Back to Website</a></p>
        <p class="text-center small text-muted-soft mt-2">Default: admin / Admin@123</p>
    </div>
</div>
<?php include '../includes/scripts.php'; ?>
</body>
</html>
