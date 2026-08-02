<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$message = '';
$messageType = 'info';

// STEP 1: Request reset link
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reset'])) {
    $email = trim($_POST['email']);
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $upd = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
        $upd->execute([$token, $expiry, $user['id']]);

        // In production this link would be emailed via PHPMailer/SMTP.
        // For the local XAMPP demo we display it directly.
        $resetLink = "reset_password.php?token=" . $token;
        $message = "Reset link generated (demo mode, no SMTP configured): <a href='$resetLink'>$resetLink</a>";
        $messageType = 'success';
    } else {
        $message = "No account found with that email.";
        $messageType = 'danger';
    }
}
$page_name = 'Forgot Password';
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
            <h3 class="gradient-text fw-bold"><i class="fa-solid fa-key"></i> StudySync</h3>
            <h5 class="fw-bold mt-3">Forgot Password</h5>
            <p class="text-muted-soft small">Enter your email to receive a reset link</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>"><?= $message /* contains safe generated link */ ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your registered email" required>
            </div>
            <button type="submit" name="request_reset" class="btn btn-gradient w-100">Send Reset Link</button>
        </form>
        <p class="text-center mt-4 mb-0 small"><a href="login.php">&larr; Back to Login</a></p>
    </div>
</div>
<?php include '../includes/scripts.php'; ?>
</body>
</html>
