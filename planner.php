<?php
require_once 'includes/auth_check.php';

$subjects = $pdo->prepare("SELECT id, subject_name FROM subjects WHERE user_id=? ORDER BY subject_name");
$subjects->execute([$user_id]);
$subjects = $subjects->fetchAll();

$filterDate = $_GET['date'] ?? date('Y-m-d');
$stmt = $pdo->prepare("SELECT t.*, s.subject_name FROM study_tasks t
    LEFT JOIN subjects s ON s.id = t.subject_id
    WHERE t.user_id = ? AND t.task_date = ?
    ORDER BY t.start_time ASC");
$stmt->execute([$user_id, $filterDate]);
$tasks = $stmt->fetchAll();

$page_name = 'Study Planner';
$current_page = 'planner';
$page_title = 'Study Planner';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include 'includes/head.php'; ?>
</head>
<body data-theme="<?= $current_user['theme'] ?>">
<div class="app-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <?php include 'includes/topbar.php'; ?>

        <div class="card-modern p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <form method="GET" class="d-flex align-items-center gap-2">
                    <a class="btn btn-sm btn-light" href="?date=<?= date('Y-m-d', strtotime($filterDate . ' -1 day')) ?>"><i class="fa-solid fa-chevron-left"></i></a>
                    <input type="date" name="date" value="<?= htmlspecialchars($filterDate) ?>" class="form-control form-control-sm" onchange="this.form.submit()">
                    <a class="btn btn-sm btn-light" href="?date=<?= date('Y-m-d', strtotime($filterDate . ' +1 day')) ?>"><i class="fa-solid fa-chevron-right"></i></a>
                </form>
                <div class="d-flex gap-2">
                    <input type="text" id="searchTasks" class="form-control form-control-sm" placeholder="Search tasks...">
                    <button class="btn btn-gradient btn-sm" onclick="openTaskModal()"><i class="fa-solid fa-plus"></i> Add Task</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-modern align-middle">
                    <thead>
                        <tr><th>Time</th><th>Subject / Topic</th><th>Priority</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody id="tasksBody">
                        <?php if (!$tasks): ?>
                            <tr><td colspan="5" class="text-center text-muted-soft py-4">No study sessions for this date. Add one!</td></tr>
                        <?php else: foreach ($tasks as $t): ?>
                            <tr class="task-row" id="task-<?= $t['id'] ?>">
                                <td><?= date('h:i A', strtotime($t['start_time'])) ?> - <?= date('h:i A', strtotime($t['end_time'])) ?></td>
                                <td class="task-topic">
                                    <strong><?= htmlspecialchars($t['topic']) ?></strong><br>
                                    <span class="text-muted-soft small"><?= htmlspecialchars($t['subject_name'] ?? '-') ?></span>
                                </td>
                                <td><span class="badge badge-priority-<?= $t['priority'] ?> rounded-pill px-3 py-2"><?= $t['priority'] ?></span></td>
                                <td>
                                    <span class="badge badge-status-<?= $t['status'] ?> rounded-pill px-3 py-2 cursor-pointer"
                                          onclick="toggleTaskStatus(<?= $t['id'] ?>, '<?= $t['status'] ?>')"><?= $t['status'] ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-light" onclick='openTaskModal(<?= json_encode($t) ?>)'><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn btn-sm btn-light text-danger" onclick="confirmDelete('planner_action.php?action=delete&id=<?= $t['id'] ?>', () => document.getElementById('task-<?= $t['id'] ?>').remove())"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Add/Edit Task Modal -->
<div class="modal fade" id="taskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="taskForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskModalTitle">Add Study Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="task_id">
                    <div class="mb-3">
                        <label class="form-label">Topic</label>
                        <input type="text" name="topic" id="task_topic" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <select name="subject_id" id="task_subject" class="form-select">
                            <option value="">None</option>
                            <?php foreach ($subjects as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['subject_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="task_date" id="task_date" class="form-control" value="<?= htmlspecialchars($filterDate) ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="start_time" id="task_start" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_time" id="task_end" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <select name="priority" id="task_priority" class="form-select">
                            <option value="High">High</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-gradient w-100">Save Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
function openTaskModal(data = null) {
    document.getElementById('taskForm').reset();
    if (data) {
        document.getElementById('taskModalTitle').textContent = 'Edit Study Task';
        document.getElementById('task_id').value = data.id;
        document.getElementById('task_topic').value = data.topic;
        document.getElementById('task_subject').value = data.subject_id || '';
        document.getElementById('task_date').value = data.task_date;
        document.getElementById('task_start').value = data.start_time;
        document.getElementById('task_end').value = data.end_time;
        document.getElementById('task_priority').value = data.priority;
    } else {
        document.getElementById('taskModalTitle').textContent = 'Add Study Task';
        document.getElementById('task_id').value = '';
        document.getElementById('task_date').value = '<?= $filterDate ?>';
    }
    new bootstrap.Modal(document.getElementById('taskModal')).show();
}
document.getElementById('taskForm').addEventListener('submit', function (e) {
    e.preventDefault();
    ajaxSubmit(this, 'planner_action.php?action=save', () => location.reload());
});
function toggleTaskStatus(id, current) {
    const next = current === 'Completed' ? 'Pending' : 'Completed';
    fetch('planner_action.php?action=status&id=' + id + '&status=' + next)
        .then(r => r.json())
        .then(() => location.reload());
}
liveFilter('searchTasks', '.task-row', '.task-topic');
</script>
</body>
</html>
