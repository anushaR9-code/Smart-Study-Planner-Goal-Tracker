<?php
require_once 'includes/auth_check.php';

$filter = $_GET['filter'] ?? 'All';
$sql = "SELECT * FROM goals WHERE user_id=?";
$params = [$user_id];
if ($filter !== 'All') { $sql .= " AND status=?"; $params[] = $filter; }
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$goals = $stmt->fetchAll();

$page_name = 'Goals';
$current_page = 'goals';
$page_title = 'Goal Tracker';
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

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <ul class="nav nav-pills">
                <?php foreach (['All','Not Started','In Progress','Completed'] as $f): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $filter === $f ? 'active' : '' ?>" style="<?= $filter===$f ? 'background:var(--primary);color:#fff;' : 'color:var(--text-main);' ?>" href="?filter=<?= urlencode($f) ?>"><?= $f ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <button class="btn btn-gradient" onclick="openGoalModal()"><i class="fa-solid fa-plus"></i> New Goal</button>
        </div>

        <div class="row g-3">
            <?php if (!$goals): ?>
                <p class="text-muted-soft">No goals found. Create your first goal!</p>
            <?php else: foreach ($goals as $g): ?>
                <div class="col-md-6">
                    <div class="card-modern p-4" id="goal-<?= $g['id'] ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($g['goal_title']) ?></h6>
                                <p class="text-muted-soft small mb-2"><?= htmlspecialchars($g['description'] ?: '') ?></p>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" onclick='openGoalModal(<?= json_encode($g) ?>)'>Edit</a></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="confirmDelete('goals_action.php?action=delete&id=<?= $g['id'] ?>', () => document.getElementById('goal-<?= $g['id'] ?>').remove())">Delete</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between small mb-1 mt-2">
                            <span><?= htmlspecialchars($g['status']) ?><?= $g['target_date'] ? ' · Target: ' . date('d M Y', strtotime($g['target_date'])) : '' ?></span>
                            <span class="fw-semibold"><?= $g['progress'] ?>%</span>
                        </div>
                        <div class="progress-modern"><div class="bar" style="width:<?= $g['progress'] ?>%;"></div></div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </main>
</div>

<!-- Goal Modal -->
<div class="modal fade" id="goalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="goalForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="goalModalTitle">New Goal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="goal_id">
                    <div class="mb-3">
                        <label class="form-label">Goal Title</label>
                        <input type="text" name="goal_title" id="goal_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="goal_description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Target Date</label>
                        <input type="date" name="target_date" id="goal_target_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Progress (<span id="progressVal">0</span>%)</label>
                        <input type="range" name="progress" id="goal_progress" class="form-range" min="0" max="100" value="0" oninput="progressVal.textContent=this.value">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="goal_status" class="form-select">
                            <option>Not Started</option>
                            <option>In Progress</option>
                            <option>Completed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-gradient w-100">Save Goal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
function openGoalModal(data = null) {
    document.getElementById('goalForm').reset();
    if (data) {
        document.getElementById('goalModalTitle').textContent = 'Edit Goal';
        document.getElementById('goal_id').value = data.id;
        document.getElementById('goal_title').value = data.goal_title;
        document.getElementById('goal_description').value = data.description || '';
        document.getElementById('goal_target_date').value = data.target_date || '';
        document.getElementById('goal_progress').value = data.progress;
        document.getElementById('progressVal').textContent = data.progress;
        document.getElementById('goal_status').value = data.status;
    } else {
        document.getElementById('goalModalTitle').textContent = 'New Goal';
        document.getElementById('goal_id').value = '';
        document.getElementById('progressVal').textContent = 0;
    }
    new bootstrap.Modal(document.getElementById('goalModal')).show();
}
document.getElementById('goalForm').addEventListener('submit', function (e) {
    e.preventDefault();
    ajaxSubmit(this, 'goals_action.php?action=save', () => location.reload());
});
</script>
</body>
</html>
