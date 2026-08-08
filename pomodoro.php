<?php
require_once 'includes/auth_check.php';
$page_name = 'Pomodoro Timer';
$current_page = 'pomodoro';
$page_title = 'Pomodoro Timer';
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

        <div class="card-modern p-4 mx-auto" style="max-width:480px;">
            <div class="d-flex justify-content-center gap-2 mb-4">
                <button class="btn btn-sm pomo-mode-btn active" id="mode-study" style="background:var(--primary);color:#fff;" onclick="Pomodoro.setMode('study'); setActive('study')">Study</button>
                <button class="btn btn-sm pomo-mode-btn" id="mode-short" style="background:var(--bg-body);" onclick="Pomodoro.setMode('short'); setActive('short')">Break</button>
                <button class="btn btn-sm pomo-mode-btn" id="mode-long" style="background:var(--bg-body);" onclick="Pomodoro.setMode('long'); setActive('long')">Long Break</button>
            </div>

            <div class="pomodoro-circle-wrap mb-4">
                <svg width="260" height="260">
                    <circle cx="130" cy="130" r="110" stroke="var(--border-color)" stroke-width="14" fill="none"/>
                    <circle id="pomodoroProgress" cx="130" cy="130" r="110" stroke="url(#grad)" stroke-width="14" fill="none"
                            stroke-linecap="round" stroke-dasharray="691.15" stroke-dashoffset="0"
                            transform="rotate(-90 130 130)"/>
                    <defs>
                        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#6C63FF"/>
                            <stop offset="100%" stop-color="#FF6B9D"/>
                        </linearGradient>
                    </defs>
                </svg>
                <div style="position:absolute; text-align:center;">
                    <div class="pomodoro-time" id="pomodoroDisplay">25:00</div>
                    <div class="text-muted-soft small">Study Time</div>
                </div>
            </div>

            <div class="d-flex justify-content-center gap-3">
                <button class="btn btn-gradient px-4" onclick="Pomodoro.start()"><i class="fa-solid fa-play"></i> Start</button>
                <button class="btn btn-light px-4" onclick="Pomodoro.pause()"><i class="fa-solid fa-pause"></i> Pause</button>
                <button class="btn btn-light px-4" onclick="Pomodoro.reset()"><i class="fa-solid fa-rotate-left"></i> Reset</button>
            </div>
        </div>

    </main>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
function setActive(mode) {
    document.querySelectorAll('.pomo-mode-btn').forEach(b => { b.style.background = 'var(--bg-body)'; b.style.color = 'var(--text-main)'; });
    document.getElementById('mode-' + mode).style.background = 'var(--primary)';
    document.getElementById('mode-' + mode).style.color = '#fff';
}
Pomodoro.updateDisplay();
</script>
</body>
</html>
