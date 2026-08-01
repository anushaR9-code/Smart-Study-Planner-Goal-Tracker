<?php
require_once 'includes/admin_auth.php';

// Handle block/unblock/delete actions
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'delete') {
        $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
    } elseif ($_GET['action'] === 'block') {
        $pdo->prepare("UPDATE users SET status='blocked' WHERE id=?")->execute([$id]);
    } elseif ($_GET['action'] === 'unblock') {
        $pdo->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$id]);
    }
    header("Location: users.php");
    exit;
}

$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT u.*, (SELECT COUNT(*) FROM study_tasks t WHERE t.user_id=u.id) task_count
        FROM users u WHERE u.full_name LIKE ? OR u.email LIKE ? ORDER BY u.created_at DESC");
    $like = "%$search%";
    $stmt->execute([$like, $like]);
} else {
    $stmt = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM study_tasks t WHERE t.user_id=u.id) task_count
        FROM users u ORDER BY u.created_at DESC");
}
$users = $stmt->fetchAll();

$page_name = 'Manage Users';
$current_page = 'users';
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
                <h4 class="mb-0 fw-bold">Manage Users</h4>
            </div>
        </div>

        <div class="card-modern p-4">
            <form method="GET" class="mb-3" style="max-width:320px;">
                <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>" onchange="this.form.submit()">
            </form>
            <div class="table-responsive">
                <table class="table table-modern align-middle">
                    <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Tasks</th><th>Streak</th><th>Status</th><th>Registered</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php if (!$users): ?>
                            <tr><td colspan="8" class="text-center text-muted-soft py-4">No users found.</td></tr>
                        <?php else: foreach ($users as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['full_name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['phone'] ?: '-') ?></td>
                            <td><?= (int)$u['task_count'] ?></td>
                            <td><i class="fa-solid fa-fire text-warning"></i> <?= (int)$u['study_streak'] ?></td>
                            <td><span class="badge <?= $u['status']==='active' ? 'bg-success' : 'bg-danger' ?>"><?= ucfirst($u['status']) ?></span></td>
                            <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <?php if ($u['status'] === 'active'): ?>
                                    <a href="?action=block&id=<?= $u['id'] ?>" class="btn btn-sm btn-light text-warning" onclick="return confirm('Block this user?')"><i class="fa-solid fa-lock"></i></a>
                                <?php else: ?>
                                    <a href="?action=unblock&id=<?= $u['id'] ?>" class="btn btn-sm btn-light text-success" onclick="return confirm('Unblock this user?')"><i class="fa-solid fa-lock-open"></i></a>
                                <?php endif; ?>
                                <a href="?action=delete&id=<?= $u['id'] ?>" class="btn btn-sm btn-light text-danger" onclick="return confirm('Permanently delete this user and all their data?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php include '../includes/scripts.php'; ?>
</body>
</html>
