<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$error = '';
$success = false;

$stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    $error = "This reset link is invalid or has expired.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $upd = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        $upd->execute([$hashed, $user['id']]);
        $success = true;
    }
}
$page_name = 'Reset Password';
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
            <h3 class="gradient-text fw-bold"><i class="fa-solid fa-lock"></i> StudySync</h3>
            <h5 class="fw-bold mt-3">Reset Password</h5>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">Password reset successfully! <a href="login.php">Login now</a></div>
        <?php elseif ($error && !$user): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php else: ?>
            <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <form method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
                </div>
                <button type="submit" class="btn btn-gradient w-100">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/scripts.php'; ?>
</body>
</html>
