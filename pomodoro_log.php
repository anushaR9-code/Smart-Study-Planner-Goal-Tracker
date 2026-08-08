<?php
/**
 * Logs a completed Pomodoro study session into study_hours,
 * accumulating hours for the current date (used by dashboard/analytics charts).
 */
header('Content-Type: application/json');
require_once 'includes/auth_check.php';

$hours = isset($_POST['hours']) ? (float)$_POST['hours'] : 0;
if ($hours <= 0) { echo json_encode(['status' => 'error', 'message' => 'Invalid duration.']); exit; }

$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT id, hours FROM study_hours WHERE user_id=? AND log_date=?");
$stmt->execute([$user_id, $today]);
$row = $stmt->fetch();

if ($row) {
    $upd = $pdo->prepare("UPDATE study_hours SET hours = hours + ? WHERE id = ?");
    $upd->execute([$hours, $row['id']]);
} else {
    $ins = $pdo->prepare("INSERT INTO study_hours (user_id, log_date, hours) VALUES (?,?,?)");
    $ins->execute([$user_id, $today, $hours]);
}

update_streak($pdo, $user_id, $current_user);

echo json_encode(['status' => 'success', 'message' => 'Session logged.']);
