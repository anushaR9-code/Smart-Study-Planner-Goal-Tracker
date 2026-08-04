<?php
require_once 'includes/auth_check.php';

// Weekly study hours (last 7 days)
$weekLabels = []; $weekHours = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(hours),0) FROM study_hours WHERE user_id=? AND log_date=?");
    $stmt->execute([$user_id, $d]);
    $weekLabels[] = date('D', strtotime($d));
    $weekHours[] = (float)$stmt->fetchColumn();
}

// Monthly progress (last 6 months, tasks completed)
$monthLabels = []; $monthCompleted = [];
for ($i = 5; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-$i month"));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM study_tasks WHERE user_id=? AND status='Completed' AND DATE_FORMAT(task_date,'%Y-%m')=?");
    $stmt->execute([$user_id, $m]);
    $monthLabels[] = date('M', strtotime($m . '-01'));
    $monthCompleted[] = (int)$stmt->fetchColumn();
}

// Completed vs Pending
$stmt = $pdo->prepare("SELECT status, COUNT(*) c FROM study_tasks WHERE user_id=? GROUP BY status");
$stmt->execute([$user_id]);
$statusCounts = ['Completed' => 0, 'Pending' => 0];
foreach ($stmt->fetchAll() as $r) { $statusCounts[$r['status']] = (int)$r['c']; }

// Goal progress overview
$stmt = $pdo->prepare("SELECT goal_title, progress FROM goals WHERE user_id=? ORDER BY created_at DESC LIMIT 6");
$stmt->execute([$user_id]);
$goalRows = $stmt->fetchAll();

// Summary stats
$totalHours = $pdo->prepare("SELECT COALESCE(SUM(hours),0) FROM study_hours WHERE user_id=?");
$totalHours->execute([$user_id]); $totalHours = round($totalHours->fetchColumn(),1);
$avgDaily = round($totalHours / 7, 2);
$tasksCompleted = $statusCounts['Completed'];
$goalsCompleted = $pdo->prepare("SELECT COUNT(*) FROM goals WHERE user_id=? AND status='Completed'");
$goalsCompleted->execute([$user_id]); $goalsCompleted = $goalsCompleted->fetchColumn();

$page_name = 'Analytics';
$current_page = 'analytics';
$page_title = 'Analytics';
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

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card-modern stat-card"><div class="stat-icon" style="background:#6C63FF;"><i class="fa-solid fa-clock"></i></div>
                    <div><div class="stat-value"><?= $totalHours ?> hrs</div><div class="stat-label">Total Study Hours</div></div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card-modern stat-card"><div class="stat-icon" style="background:#3B82F6;"><i class="fa-solid fa-chart-line"></i></div>
                    <div><div class="stat-value"><?= $avgDaily ?> hrs</div><div class="stat-label">Average Daily</div></div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card-modern stat-card"><div class="stat-icon" style="background:#22C55E;"><i class="fa-solid fa-check"></i></div>
                    <div><div class="stat-value"><?= $tasksCompleted ?></div><div class="stat-label">Tasks Completed</div></div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card-modern stat-card"><div class="stat-icon" style="background:#F59E0B;"><i class="fa-solid fa-bullseye"></i></div>
                    <div><div class="stat-value"><?= $goalsCompleted ?></div><div class="stat-label">Goals Completed</div></div></div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="card-modern p-4 h-100">
                    <h6 class="fw-bold mb-3">Study Hours (This Week)</h6>
                    <canvas id="weeklyChart" height="110"></canvas>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card-modern p-4 h-100">
                    <h6 class="fw-bold mb-3">Tasks Overview</h6>
                    <canvas id="taskDonut" height="110"></canvas>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card-modern p-4 h-100">
                    <h6 class="fw-bold mb-3">Monthly Progress (Tasks Completed)</h6>
                    <canvas id="monthlyChart" height="110"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card-modern p-4 h-100">
                    <h6 class="fw-bold mb-3">Goal Progress</h6>
                    <canvas id="goalChart" height="110"></canvas>
                </div>
            </div>
        </div>

    </main>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
new Chart(document.getElementById('weeklyChart'), {
    type: 'bar',
    data: { labels: <?= json_encode($weekLabels) ?>, datasets: [{ label: 'Hours', data: <?= json_encode($weekHours) ?>, backgroundColor: 'rgba(108,99,255,0.75)', borderRadius: 6 }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
new Chart(document.getElementById('taskDonut'), {
    type: 'doughnut',
    data: { labels: ['Completed', 'Pending'], datasets: [{ data: [<?= $statusCounts['Completed'] ?>, <?= $statusCounts['Pending'] ?>], backgroundColor: ['#22C55E', '#EF4444'] }] },
    options: { plugins: { legend: { position: 'bottom' } } }
});
new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: { labels: <?= json_encode($monthLabels) ?>, datasets: [{ label: 'Completed Tasks', data: <?= json_encode($monthCompleted) ?>, borderColor: '#FF6B9D', backgroundColor: 'rgba(255,107,157,0.15)', fill: true, tension: 0.4 }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
new Chart(document.getElementById('goalChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($goalRows, 'goal_title')) ?>,
        datasets: [{ label: 'Progress %', data: <?= json_encode(array_map('intval', array_column($goalRows, 'progress'))) ?>, backgroundColor: 'rgba(59,130,246,0.75)', borderRadius: 6 }]
    },
    options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, max: 100 } } }
});
</script>
</body>
</html>
