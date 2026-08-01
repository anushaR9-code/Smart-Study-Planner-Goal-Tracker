<?php
require_once 'includes/admin_auth.php';

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetchColumn();
$blockedUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE status='blocked'")->fetchColumn();
$totalTasks = $pdo->query("SELECT COUNT(*) FROM study_tasks")->fetchColumn();
$totalGoals = $pdo->query("SELECT COUNT(*) FROM goals")->fetchColumn();
$totalNotes = $pdo->query("SELECT COUNT(*) FROM notes")->fetchColumn();
$totalHours = round($pdo->query("SELECT COALESCE(SUM(hours),0) FROM study_hours")->fetchColumn(), 1);

$recentUsers = $pdo->query("SELECT id, full_name, email, created_at, status FROM users ORDER BY created_at DESC LIMIT 6")->fetchAll();

// New signups per day, last 7 days (for chart)
$labels = []; $signups = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE DATE(created_at)=?");
    $stmt->execute([$d]);
    $labels[] = date('D', strtotime($d));
    $signups[] = (int)$stmt->fetchColumn();
}

$page_name = 'Admin Dashboard';
$current_page = 'dashboard';
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
                <h4 class="mb-0 fw-bold">Admin Dashboard</h4>
            </div>
            <span class="fw-semibold">👋 <?= htmlspecialchars($current_admin['username']) ?></span>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card-modern stat-card"><div class="stat-icon" style="background:#6C63FF;"><i class="fa-solid fa-users"></i></div>
                    <div><div class="stat-value"><?= $totalUsers ?></div><div class="stat-label">Total Users</div></div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card-modern stat-card"><div class="stat-icon" style="background:#22C55E;"><i class="fa-solid fa-user-check"></i></div>
                    <div><div class="stat-value"><?= $activeUsers ?></div><div class="stat-label">Active Users</div></div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card-modern stat-card"><div class="stat-icon" style="background:#EF4444;"><i class="fa-solid fa-user-slash"></i></div>
                    <div><div class="stat-value"><?= $blockedUsers ?></div><div class="stat-label">Blocked Users</div></div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card-modern stat-card"><div class="stat-icon" style="background:#F59E0B;"><i class="fa-solid fa-clock"></i></div>
                    <div><div class="stat-value"><?= $totalHours ?></div><div class="stat-label">Total Study Hours (all users)</div></div></div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="card-modern p-4 h-100">
                    <h6 class="fw-bold mb-3">New Signups (Last 7 Days)</h6>
                    <canvas id="signupChart" height="100"></canvas>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card-modern p-4 h-100">
                    <h6 class="fw-bold mb-3">Platform Content</h6>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted-soft">Study Tasks</span><span class="fw-bold"><?= $totalTasks ?></span></div>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted-soft">Goals</span><span class="fw-bold"><?= $totalGoals ?></span></div>
                    <div class="d-flex justify-content-between py-2"><span class="text-muted-soft">Notes</span><span class="fw-bold"><?= $totalNotes ?></span></div>
                </div>
            </div>
        </div>

        <div class="card-modern p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Recently Registered Users</h6>
                <a href="users.php" class="small">View All &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead><tr><th>Name</th><th>Email</th><th>Registered</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($recentUsers as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['full_name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                            <td><span class="badge <?= $u['status']==='active' ? 'bg-success' : 'bg-danger' ?>"><?= ucfirst($u['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php include '../includes/scripts.php'; ?>
<script>
new Chart(document.getElementById('signupChart'), {
    type: 'line',
    data: { labels: <?= json_encode($labels) ?>, datasets: [{ label: 'Signups', data: <?= json_encode($signups) ?>, borderColor: '#6C63FF', backgroundColor: 'rgba(108,99,255,0.15)', fill: true, tension: 0.4 }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
</script>
</body>
</html>
