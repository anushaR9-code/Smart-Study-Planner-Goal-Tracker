<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (isset($_SESSION['user_id'])) { header("Location: ../dashboard.php"); exit; }

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    // ---- Server-side validation ----
    if ($full_name === '') $errors[] = "Full name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Enter a valid email address.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = "An account with this email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT); // password hashing
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$full_name, $email, $phone, $hashed]);
            $success = true;
        }
    }
}
$page_name = 'Register';
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
            <h5 class="fw-bold mt-3">Create Your Account</h5>
            <p class="text-muted-soft small">Start planning smarter today</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">Registration successful! <a href="login.php">Login now</a></div>
        <?php else: ?>
            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="POST" novalidate>
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone (optional)</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
                </div>
                <button type="submit" class="btn btn-gradient w-100 mt-2">Register</button>
            </form>
        <?php endif; ?>

        <p class="text-center mt-4 mb-0 small">Already have an account? <a href="login.php" class="fw-bold">Login</a></p>
    </div>
</div>
<?php include '../includes/scripts.php'; ?>
</body>
</html>
