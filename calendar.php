<?php
require_once 'includes/auth_check.php';

$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');

$firstDay = mktime(0,0,0,$month,1,$year);
$daysInMonth = (int)date('t', $firstDay);
$startWeekday = (int)date('w', $firstDay); // 0=Sun

$prevMonth = $month - 1; $prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
$nextMonth = $month + 1; $nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

$monthStart = sprintf('%04d-%02d-01', $year, $month);
$monthEnd = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

$stmt = $pdo->prepare("SELECT * FROM calendar_events WHERE user_id=? AND event_date BETWEEN ? AND ? ORDER BY event_date, event_time");
$stmt->execute([$user_id, $monthStart, $monthEnd]);
$events = $stmt->fetchAll();
$eventsByDay = [];
foreach ($events as $e) {
    $d = (int)date('j', strtotime($e['event_date']));
    $eventsByDay[$d][] = $e;
}

$upcoming = $pdo->prepare("SELECT * FROM calendar_events WHERE user_id=? AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 5");
$upcoming->execute([$user_id]);
$upcoming = $upcoming->fetchAll();

$page_name = 'Calendar';
$current_page = 'calendar';
$page_title = 'Calendar';
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

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card-modern p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <a class="btn btn-sm btn-light" href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>"><i class="fa-solid fa-chevron-left"></i></a>
                        <h6 class="fw-bold mb-0"><?= date('F Y', $firstDay) ?></h6>
                        <a class="btn btn-sm btn-light" href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>"><i class="fa-solid fa-chevron-right"></i></a>
                    </div>
                    <div class="calendar-grid mb-1">
                        <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d): ?>
                            <div class="text-center small fw-semibold text-muted-soft"><?= $d ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="calendar-grid">
                        <?php for ($i = 0; $i < $startWeekday; $i++): ?>
                            <div></div>
                        <?php endfor; ?>
                        <?php for ($d = 1; $d <= $daysInMonth; $d++):
                            $isToday = $d == (int)date('j') && $month == (int)date('n') && $year == (int)date('Y');
                            $hasEvent = isset($eventsByDay[$d]);
                        ?>
                            <div class="calendar-day <?= $isToday ? 'today' : '' ?> <?= $hasEvent ? 'has-event' : '' ?>"
                                 data-bs-toggle="tooltip" onclick='openEventModal(null, "<?= sprintf('%04d-%02d-%02d',$year,$month,$d) ?>")'>
                                <div class="fw-semibold"><?= $d ?></div>
                                <?php if ($hasEvent): foreach ($eventsByDay[$d] as $e): ?>
                                    <div class="small text-truncate"><span class="dot"></span><?= htmlspecialchars($e['title']) ?></div>
                                <?php endforeach; endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card-modern p-4 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Upcoming Events</h6>
                        <button class="btn btn-sm btn-gradient" onclick="openEventModal()"><i class="fa-solid fa-plus"></i></button>
                    </div>
                    <?php if (!$upcoming): ?>
                        <p class="text-muted-soft small">No upcoming events.</p>
                    <?php else: foreach ($upcoming as $e): ?>
                        <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                            <div>
                                <span class="fw-semibold d-block"><?= htmlspecialchars($e['title']) ?></span>
                                <span class="text-muted-soft small"><?= htmlspecialchars($e['event_type']) ?> · <?= date('d M Y', strtotime($e['event_date'])) ?></span>
                            </div>
                            <button class="btn btn-sm btn-light text-danger" onclick="confirmDelete('calendar_action.php?action=delete&id=<?= $e['id'] ?>', () => location.reload())"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Event Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="eventForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" id="event_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="event_type" id="event_type" class="form-select">
                            <option>Exam</option><option>Assignment</option><option>Deadline</option><option>Study</option><option>Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="event_date" id="event_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="event_notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-gradient w-100">Save Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
function openEventModal(id = null, date = null) {
    document.getElementById('eventForm').reset();
    if (date) document.getElementById('event_date').value = date;
    new bootstrap.Modal(document.getElementById('eventModal')).show();
}
document.getElementById('eventForm').addEventListener('submit', function (e) {
    e.preventDefault();
    ajaxSubmit(this, 'calendar_action.php?action=save', () => location.reload());
});
</script>
</body>
</html>
