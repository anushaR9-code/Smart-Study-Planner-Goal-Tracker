<?php
require_once 'includes/auth_check.php';
$streak = update_streak($pdo, $user_id, $current_user);

// ---- Stat counts ----
$totalTasks = $pdo->prepare("SELECT COUNT(*) FROM study_tasks WHERE user_id=?");
$totalTasks->execute([$user_id]); $totalTasks = $totalTasks->fetchColumn();

$completedTasks = $pdo->prepare("SELECT COUNT(*) FROM study_tasks WHERE user_id=? AND status='Completed'");
$completedTasks->execute([$user_id]); $completedTasks = $completedTasks->fetchColumn();

$pendingTasks = $totalTasks - $completedTasks;

$studyHours = $pdo->prepare("SELECT COALESCE(SUM(hours),0) FROM study_hours WHERE user_id=? AND log_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
$studyHours->execute([$user_id]); $studyHours = round($studyHours->fetchColumn(), 1);

// ---- Today's schedule ----
$todaySchedule = $pdo->prepare("SELECT * FROM study_tasks WHERE user_id=? AND task_date = CURDATE() ORDER BY start_time ASC");
$todaySchedule->execute([$user_id]);
$todaySchedule = $todaySchedule->fetchAll();

// ---- Goals progress (top 3) ----
$goals = $pdo->prepare("SELECT * FROM goals WHERE user_id=? ORDER BY progress DESC LIMIT 3");
$goals->execute([$user_id]);
$goals = $goals->fetchAll();

// ---- Weekly progress chart data (hours per day, last 7 days) ----
$weekData = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(hours),0) FROM study_hours WHERE user_id=? AND log_date=?");
    $stmt->execute([$user_id, $d]);
    $weekData[date('D', strtotime($d))] = (float)$stmt->fetchColumn();
}

$weeklyPct = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

$page_name = 'Dashboard';
$current_page = 'dashboard';
$page_title = 'Dashboard';
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

        <p class="text-muted-soft mb-4">Welcome back, <strong><?= htmlspecialchars($current_user['full_name']) ?></strong> 👋 Let's make today productive!</p>

        <!-- ===== STAT CARDS ===== -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card-modern stat-card">
                    <div class="stat-icon" style="background:#3B82F6;"><i class="fa-solid fa-list-check"></i></div>
                    <div><div class="stat-value"><?= $totalTasks ?></div><div class="stat-label">Total Tasks</div></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card-modern stat-card">
                    <div class="stat-icon" style="background:#22C55E;"><i class="fa-solid fa-check"></i></div>
                    <div><div class="stat-value"><?= $completedTasks ?></div><div class="stat-label">Completed</div></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card-modern stat-card">
                    <div class="stat-icon" style="background:#F59E0B;"><i class="fa-solid fa-hourglass-half"></i></div>
                    <div><div class="stat-value"><?= $pendingTasks ?></div><div class="stat-label">Pending</div></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card-modern stat-card">
                    <div class="stat-icon" style="background:#6C63FF;"><i class="fa-solid fa-clock"></i></div>
                    <div><div class="stat-value"><?= $studyHours ?></div><div class="stat-label">Study Hours (7d)</div></div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <!-- Today's schedule -->
            <div class="col-lg-7">
                <div class="card-modern p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Today's Schedule</h6>
                        <a href="planner.php" class="small">View Full Schedule &rarr;</a>
                    </div>
                    <?php if (!$todaySchedule): ?>
                        <p class="text-muted-soft small mb-0">No study sessions scheduled for today. <a href="planner.php">Add one</a>.</p>
                    <?php else: foreach ($todaySchedule as $t): ?>
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <span class="text-muted-soft small" style="width:90px;"><?= date('h:i A', strtotime($t['start_time'])) ?></span>
                                <span class="fw-semibold"><?= htmlspecialchars($t['topic']) ?></span>
                            </div>
                            <span class="badge badge-priority-<?= $t['priority'] ?> rounded-pill px-3 py-2"><?= $t['priority'] ?></span>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- Goals progress -->
            <div class="col-lg-5">
                <div class="card-modern p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Goals Progress</h6>
                        <a href="goals.php" class="small">View All Goals &rarr;</a>
                    </div>
                    <?php if (!$goals): ?>
                        <p class="text-muted-soft small mb-0">No goals yet. <a href="goals.php">Create one</a>.</p>
                    <?php else: foreach ($goals as $g): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-semibold"><?= htmlspecialchars($g['goal_title']) ?></span>
                                <span><?= $g['progress'] ?>%</span>
                            </div>
                            <div class="progress-modern"><div class="bar" style="width:<?= $g['progress'] ?>%;"></div></div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="card-modern p-4 text-center h-100">
                    <h6 class="fw-bold">Study Streak</h6>
                    <div class="display-6 fw-bold text-warning"><i class="fa-solid fa-fire"></i> <?= $streak ?></div>
                    <p class="text-muted-soft small mb-0">Days</p>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card-modern p-4 h-100">
                    <h6 class="fw-bold mb-3">Weekly Progress (<?= $weeklyPct ?>% tasks complete)</h6>
                    <canvas id="weeklyChart" height="90"></canvas>
                </div>
            </div>
        </div>

    </main>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
const ctx = document.getElementById('weeklyChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($weekData)) ?>,
        datasets: [{
            label: 'Study Hours',
            data: <?= json_encode(array_values($weekData)) ?>,
            backgroundColor: 'rgba(108,99,255,0.75)',
            borderRadius: 6,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
</body>
</html>
