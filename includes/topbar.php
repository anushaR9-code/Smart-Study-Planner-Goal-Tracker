<?php
// Expects $current_user array and optional $page_title
$page_title = $page_title ?? 'Dashboard';
?>
<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="icon-btn sidebar-toggle-btn" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>
        <h4 class="mb-0 fw-bold"><?= htmlspecialchars($page_title) ?></h4>
    </div>
    <div class="d-flex align-items-center gap-3">
        <button class="icon-btn" onclick="toggleTheme()" title="Toggle dark mode">
            <i id="themeIcon" class="fa-solid fa-moon"></i>
        </button>
        <a href="profile.php" class="d-flex align-items-center gap-2 text-decoration-none text-reset">
            <img src="<?= htmlspecialchars($current_user['profile_pic'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($current_user['full_name'])) ?>"
                 class="avatar-sm" alt="avatar">
            <span class="d-none d-md-inline fw-semibold"><?= htmlspecialchars($current_user['full_name']) ?></span>
        </a>
    </div>
</div>
