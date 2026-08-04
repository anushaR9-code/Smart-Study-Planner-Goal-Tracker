<?php
/**
 * AJAX handler for Calendar events CRUD
 */
header('Content-Type: application/json');
require_once 'includes/auth_check.php';

$action = $_GET['action'] ?? '';

try {
    if ($action === 'save') {
        $title = trim($_POST['title'] ?? '');
        $type  = $_POST['event_type'] ?? 'Other';
        $date  = $_POST['event_date'] ?? '';
        $notes = trim($_POST['notes'] ?? '');

        if ($title === '' || $date === '') {
            echo json_encode(['status' => 'error', 'message' => 'Title and date are required.']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO calendar_events (user_id, title, event_type, event_date, notes) VALUES (?,?,?,?,?)");
        $stmt->execute([$user_id, $title, $type, $date, $notes]);
        echo json_encode(['status' => 'success', 'message' => 'Event added.']);
    } elseif ($action === 'delete') {
        $id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM calendar_events WHERE id=? AND user_id=?");
        $stmt->execute([$id, $user_id]);
        echo json_encode(['status' => 'success', 'message' => 'Event deleted.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
