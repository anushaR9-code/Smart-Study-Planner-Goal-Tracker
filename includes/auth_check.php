<?php
/**
 * Session bootstrap + auth guard for logged-in USER pages.
 * Include this at the very top of every protected user page.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /studysync/auth/login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';

$user_id = $_SESSION['user_id'];

// Fetch current user (used by navbar/sidebar for name & avatar)
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_user = $stmt->fetch();

if (!$current_user || $current_user['status'] === 'blocked') {
    session_destroy();
    header("Location: /studysync/auth/login.php?blocked=1");
    exit;
}

/**
 * Update the user's study streak.
 * Call this once per session on dashboard load.
 */
function update_streak($pdo, $user_id, $current_user) {
    $today = date('Y-m-d');
    $last = $current_user['last_active_date'];

    if ($last === $today) {
        return $current_user['study_streak']; // already counted today
    }

    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $streak = ($last === $yesterday) ? $current_user['study_streak'] + 1 : 1;

    $upd = $pdo->prepare("UPDATE users SET study_streak = ?, last_active_date = ? WHERE id = ?");
    $upd->execute([$streak, $today, $user_id]);

    return $streak;
}
