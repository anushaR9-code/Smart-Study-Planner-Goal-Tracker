<?php
// Expects $current_page to be set by the including page (e.g. 'dashboard')
$current_page = $current_page ?? '';
$nav = [
    'dashboard'  => ['dashboard.php', 'fa-grid-2', 'Dashboard'],
    'subjects'   => ['subjects.php', 'fa-book', 'Subjects'],
    'planner'    => ['planner.php', 'fa-calendar-check', 'Study Planner'],
    'todo'       => ['todo.php', 'fa-list-check', 'To-Do List'],
    'goals'      => ['goals.php', 'fa-bullseye', 'Goals'],
    'notes'      => ['notes.php', 'fa-note-sticky', 'Notes'],
    'calendar'   => ['calendar.php', 'fa-calendar-days', 'Calendar'],
    'pomodoro'   => ['pomodoro.php', 'fa-stopwatch', 'Pomodoro'],
    'analytics'  => ['analytics.php', 'fa-chart-line', 'Analytics'],
    'profile'    => ['profile.php', 'fa-user', 'Profile'],
];
?>
<aside class="sidebar">
    <div class="logo d-flex align-items-center gap-2">
        <i class="fa-solid fa-book-open-reader"></i> StudySync
    </div>
    <nav class="nav flex-column">
        <?php foreach ($nav as $key => [$href, $icon, $label]): ?>
            <a href="<?= $href ?>" class="nav-link <?= $current_page === $key ? 'active' : '' ?>">
                <i class="fa-solid <?= $icon ?>"></i> <?= $label ?>
            </a>
        <?php endforeach; ?>
        <hr style="border-color: rgba(255,255,255,0.1);">
        <a href="/studysync/auth/logout.php" class="nav-link">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </nav>
</aside>
