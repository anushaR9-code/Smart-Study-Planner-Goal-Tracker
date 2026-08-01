<?php
require_once 'includes/admin_auth.php';

$statusBreakdown = $pdo->query("SELECT status, COUNT(*) c FROM study_tasks GROUP BY status")->fetchAll();
$priorityBreakdown = $pdo->query("SELECT priority, COUNT(*) c FROM study_tasks GROUP BY priority")->fetchAll();
$goalStatusBreakdown = $pdo->query("SELECT status, COUNT(*) c FROM goals GROUP BY status")->fetchAll();

$topUsers = $pdo->query("SELECT u.full_name, COALESCE(SUM(sh.hours),0) total_hours
    FROM users u LEFT JOIN study_hours sh ON sh.user_id = u.id
    GROUP BY u.id ORDER BY total_hours DESC LIMIT 5")->fetchAll();

$page_name = 'Reports & Statistics';
$current_page = 'reports';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include '../includes/head.php'; ?>
</head>
<body data-theme="light">
<div class="app-wrapper">
    <?php include 'includes/admin_sidebar.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="icon-btn sidebar-toggle-btn" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
                <h4 class="mb-0 fw-bold">Reports & Statistics</h4>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-4">
                <div class="card-modern p-4 h-100">
                    <h6 class="fw-bold mb-3">Task Status</h6>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card-modern p-4 h-100">
                    <h6 class="fw-bold mb-3">Task Priority</h6>
                    <canvas id="priorityChart"></canvas>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card-modern p-4 h-100">
                    <h6 class="fw-bold mb-3">Goal Status</h6>
                    <canvas id="goalChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card-modern p-4">
            <h6 class="fw-bold mb-3">Top 5 Most Active Users (by Study Hours)</h6>
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead><tr><th>#</th><th>User</th><th>Total Hours</th></tr></thead>
                    <tbody>
                        <?php foreach ($topUsers as $i => $u): ?>
                        <tr><td><?= $i + 1 ?></td><td><?= htmlspecialchars($u['full_name']) ?></td><td><?= round($u['total_hours'],1) ?> hrs</td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php include '../includes/scripts.php'; ?>
<script>
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: { labels: <?= json_encode(array_column($statusBreakdown,'status')) ?>, datasets: [{ data: <?= json_encode(array_map('intval', array_column($statusBreakdown,'c'))) ?>, backgroundColor: ['#22C55E','#EF4444'] }] },
    options: { plugins: { legend: { position: 'bottom' } } }
});
new Chart(document.getElementById('priorityChart'), {
    type: 'pie',
    data: { labels: <?= json_encode(array_column($priorityBreakdown,'priority')) ?>, datasets: [{ data: <?= json_encode(array_map('intval', array_column($priorityBreakdown,'c'))) ?>, backgroundColor: ['#EF4444','#F59E0B','#22C55E'] }] },
    options: { plugins: { legend: { position: 'bottom' } } }
});
new Chart(document.getElementById('goalChart'), {
    type: 'doughnut',
    data: { labels: <?= json_encode(array_column($goalStatusBreakdown,'status')) ?>, datasets: [{ data: <?= json_encode(array_map('intval', array_column($goalStatusBreakdown,'c'))) ?>, backgroundColor: ['#6C63FF','#F59E0B','#22C55E'] }] },
    options: { plugins: { legend: { position: 'bottom' } } }
});
</script>
</body>
</html>
