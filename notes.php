<?php
require_once 'includes/auth_check.php';

$stmt = $pdo->prepare("SELECT * FROM notes WHERE user_id=? ORDER BY is_pinned DESC, updated_at DESC");
$stmt->execute([$user_id]);
$notes = $stmt->fetchAll();

$page_name = 'Notes';
$current_page = 'notes';
$page_title = 'Notes';
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

        <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
            <input type="text" id="searchNotes" class="form-control" style="max-width:320px;" placeholder="Search notes...">
            <button class="btn btn-gradient" onclick="openNoteModal()"><i class="fa-solid fa-plus"></i> Add Note</button>
        </div>

        <div class="row g-3">
            <?php if (!$notes): ?>
                <p class="text-muted-soft">No notes yet. Click "Add Note" to create one.</p>
            <?php else: foreach ($notes as $n): ?>
                <div class="col-md-4 note-item" id="note-<?= $n['id'] ?>">
                    <div class="card-modern note-card h-100">
                        <i class="fa-solid fa-thumbtack pin-btn <?= $n['is_pinned'] ? 'pinned' : '' ?>" onclick="togglePin(<?= $n['id'] ?>, <?= $n['is_pinned'] ? 0 : 1 ?>)"></i>
                        <h6 class="fw-bold mb-2 note-title" style="max-width:85%;"><?= htmlspecialchars($n['title']) ?></h6>
                        <p class="text-muted-soft small note-content" style="max-height:80px; overflow:hidden;"><?= nl2br(htmlspecialchars($n['content'])) ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="text-muted-soft small"><?= date('d M Y', strtotime($n['updated_at'])) ?></span>
                            <div>
                                <button class="btn btn-sm btn-light" onclick='openNoteModal(<?= json_encode($n) ?>)'><i class="fa-solid fa-pen"></i></button>
                                <button class="btn btn-sm btn-light text-danger" onclick="confirmDelete('notes_action.php?action=delete&id=<?= $n['id'] ?>', () => document.getElementById('note-<?= $n['id'] ?>').remove())"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </main>
</div>

<!-- Note Modal -->
<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="noteForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="noteModalTitle">Add Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="note_id">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" id="note_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea name="content" id="note_content_field" class="form-control" rows="6"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-gradient w-100">Save Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
function openNoteModal(data = null) {
    document.getElementById('noteForm').reset();
    if (data) {
        document.getElementById('noteModalTitle').textContent = 'Edit Note';
        document.getElementById('note_id').value = data.id;
        document.getElementById('note_title').value = data.title;
        document.getElementById('note_content_field').value = data.content;
    } else {
        document.getElementById('noteModalTitle').textContent = 'Add Note';
        document.getElementById('note_id').value = '';
    }
    new bootstrap.Modal(document.getElementById('noteModal')).show();
}
document.getElementById('noteForm').addEventListener('submit', function (e) {
    e.preventDefault();
    ajaxSubmit(this, 'notes_action.php?action=save', () => location.reload());
});
function togglePin(id, pinned) {
    fetch('notes_action.php?action=pin&id=' + id + '&pinned=' + pinned).then(r => r.json()).then(() => location.reload());
}
liveFilter('searchNotes', '.note-item', '.note-title');
</script>
</body>
</html>
