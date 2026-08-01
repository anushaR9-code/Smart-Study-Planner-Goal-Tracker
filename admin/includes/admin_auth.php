<?php
/**
 * Session bootstrap + auth guard for ADMIN pages.
 * Include this at the very top of every protected admin page.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: /studysync/admin/login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT * FROM admin WHERE id = ?");
$stmt->execute([$admin_id]);
$current_admin = $stmt->fetch();

if (!$current_admin) {
    session_destroy();
    header("Location: /studysync/admin/login.php");
    exit;
}
