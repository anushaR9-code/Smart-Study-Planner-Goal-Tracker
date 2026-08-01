<?php $current_page = $current_page ?? ''; ?>
<aside class="sidebar">
    <div class="logo d-flex align-items-center gap-2">
        <i class="fa-solid fa-user-shield"></i> Admin Panel
    </div>
    <nav class="nav flex-column">
        <a href="dashboard.php" class="nav-link <?= $current_page === 'dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-grid-2"></i> Dashboard</a>
        <a href="users.php" class="nav-link <?= $current_page === 'users' ? 'active' : '' ?>"><i class="fa-solid fa-users"></i> Manage Users</a>
        <a href="reports.php" class="nav-link <?= $current_page === 'reports' ? 'active' : '' ?>"><i class="fa-solid fa-chart-pie"></i> Reports & Statistics</a>
        <hr style="border-color: rgba(255,255,255,0.1);">
        <a href="../index.php" class="nav-link"><i class="fa-solid fa-house"></i> Visit Site</a>
        <a href="logout.php" class="nav-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
</aside>
