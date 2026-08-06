<?php
/**
 * AJAX handler for Goal Tracker CRUD
 */
header('Content-Type: application/json');
require_once 'includes/auth_check.php';

$action = $_GET['action'] ?? '';

try {
    if ($action === 'save') {
        $id          = $_POST['id'] ?? '';
        $title       = trim($_POST['goal_title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $targetDate  = $_POST['target_date'] ?: null;
        $progress    = max(0, min(100, (int)($_POST['progress'] ?? 0)));
        $status      = $_POST['status'] ?? 'Not Started';

        if ($title === '') {
            echo json_encode(['status' => 'error', 'message' => 'Goal title is required.']);
            exit;
        }
        // Auto-complete status if progress hits 100
        if ($progress === 100) $status = 'Completed';

        if ($id !== '') {
            $stmt = $pdo->prepare("UPDATE goals SET goal_title=?, description=?, target_date=?, progress=?, status=? WHERE id=? AND user_id=?");
            $stmt->execute([$title, $description, $targetDate, $progress, $status, $id, $user_id]);
            echo json_encode(['status' => 'success', 'message' => 'Goal updated.']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO goals (user_id, goal_title, description, target_date, progress, status) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$user_id, $title, $description, $targetDate, $progress, $status]);
            echo json_encode(['status' => 'success', 'message' => 'Goal created.']);
        }
    } elseif ($action === 'delete') {
        $id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM goals WHERE id=? AND user_id=?");
        $stmt->execute([$id, $user_id]);
        echo json_encode(['status' => 'success', 'message' => 'Goal deleted.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
