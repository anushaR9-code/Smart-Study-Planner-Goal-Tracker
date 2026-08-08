<?php
/**
 * AJAX handler for Study Planner task CRUD + status toggle
 */
header('Content-Type: application/json');
require_once 'includes/auth_check.php';

$action = $_GET['action'] ?? '';

try {
    if ($action === 'save') {
        $id         = $_POST['id'] ?? '';
        $topic      = trim($_POST['topic'] ?? '');
        $subjectId  = $_POST['subject_id'] ?: null;
        $date       = $_POST['task_date'] ?? '';
        $start      = $_POST['start_time'] ?? '';
        $end        = $_POST['end_time'] ?? '';
        $priority   = $_POST['priority'] ?? 'Medium';

        if ($topic === '' || $date === '' || $start === '' || $end === '') {
            echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled.']);
            exit;
        }

        if ($id !== '') {
            $stmt = $pdo->prepare("UPDATE study_tasks SET subject_id=?, topic=?, task_date=?, start_time=?, end_time=?, priority=? WHERE id=? AND user_id=?");
            $stmt->execute([$subjectId, $topic, $date, $start, $end, $priority, $id, $user_id]);
            echo json_encode(['status' => 'success', 'message' => 'Task updated successfully.']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO study_tasks (user_id, subject_id, topic, task_date, start_time, end_time, priority) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$user_id, $subjectId, $topic, $date, $start, $end, $priority]);
            echo json_encode(['status' => 'success', 'message' => 'Task added successfully.']);
        }
    } elseif ($action === 'delete') {
        $id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM study_tasks WHERE id=? AND user_id=?");
        $stmt->execute([$id, $user_id]);
        echo json_encode(['status' => 'success', 'message' => 'Task deleted.']);
    } elseif ($action === 'status') {
        $id = $_GET['id'] ?? 0;
        $status = $_GET['status'] === 'Completed' ? 'Completed' : 'Pending';
        $stmt = $pdo->prepare("UPDATE study_tasks SET status=? WHERE id=? AND user_id=?");
        $stmt->execute([$status, $id, $user_id]);
        echo json_encode(['status' => 'success', 'message' => 'Status updated.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
