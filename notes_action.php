<?php
/**
 * AJAX handler for Notes CRUD + pin toggle
 */
header('Content-Type: application/json');
require_once 'includes/auth_check.php';

$action = $_GET['action'] ?? '';

try {
    if ($action === 'save') {
        $id      = $_POST['id'] ?? '';
        $title   = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if ($title === '') {
            echo json_encode(['status' => 'error', 'message' => 'Note title is required.']);
            exit;
        }

        if ($id !== '') {
            $stmt = $pdo->prepare("UPDATE notes SET title=?, content=? WHERE id=? AND user_id=?");
            $stmt->execute([$title, $content, $id, $user_id]);
            echo json_encode(['status' => 'success', 'message' => 'Note updated.']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO notes (user_id, title, content) VALUES (?,?,?)");
            $stmt->execute([$user_id, $title, $content]);
            echo json_encode(['status' => 'success', 'message' => 'Note added.']);
        }
    } elseif ($action === 'pin') {
        $id = $_GET['id'] ?? 0;
        $pinned = $_GET['pinned'] == 1 ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE notes SET is_pinned=? WHERE id=? AND user_id=?");
        $stmt->execute([$pinned, $id, $user_id]);
        echo json_encode(['status' => 'success', 'message' => 'Note updated.']);
    } elseif ($action === 'delete') {
        $id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM notes WHERE id=? AND user_id=?");
        $stmt->execute([$id, $user_id]);
        echo json_encode(['status' => 'success', 'message' => 'Note deleted.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
