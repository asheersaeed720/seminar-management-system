<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$seminarId = (int)($_GET['id'] ?? $_POST['seminar_id'] ?? 0);
$seminar   = $seminarId ? getSeminarById($seminarId) : null;
if (!$seminar) {
    setFlash('error', 'Seminar not found.');
    redirect(BASE_URL . '/admin/seminars.php');
}

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $selected = array_map('intval', $_POST['teacher_ids'] ?? []);

    // Replace all assignments
    $db->prepare('DELETE FROM seminar_teachers WHERE seminar_id = ?')->execute([$seminarId]);
    if (!empty($selected)) {
        $ins = $db->prepare('INSERT IGNORE INTO seminar_teachers (seminar_id, teacher_id) VALUES (?, ?)');
        foreach ($selected as $tid) {
            $ins->execute([$seminarId, $tid]);
        }
    }
    setFlash('success', 'Teacher assignments saved.');
    redirect(BASE_URL . '/admin/seminars.php');
}

$allTeachers     = getAllTeachers();
$assignedIds     = array_column(getSeminarTeachers($seminarId), 'id');

$pageTitle  = 'Assign Teachers';
$breadcrumb = [
    ['label' => 'Seminars', 'url' => BASE_URL . '/admin/seminars.php'],
    ['label' => 'Assign Teachers', 'active' => true]
];
require_once __DIR__ . '/../includes/admin_header.php';
renderFlash();
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold mb-0">Assign Teachers</h4>
    <p class="text-muted small mb-0"><?= e($seminar['title']) ?></p>
  </div>
  <a href="<?= BASE_URL ?>/admin/seminars.php" class="btn btn-outline-secondary btn-sm">
    <i class="fa fa-arrow-left me-1"></i>Back
  </a>
</div>

<div class="card border-0 shadow-sm rounded-3" style="max-width:560px">
  <div class="card-body p-4">
    <div class="alert alert-light border mb-4 small">
      <i class="fa fa-info-circle text-primary me-2"></i>
      Select one or more teachers to assign to this seminar. Previously saved assignments will be replaced.
    </div>

    <?php if (empty($allTeachers)): ?>
    <div class="alert alert-warning">
      No teachers in the system. <a href="<?= BASE_URL ?>/admin/teacher_form.php">Add a teacher first.</a>
    </div>
    <?php else: ?>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="seminar_id" value="<?= $seminarId ?>">

      <div class="d-flex flex-column gap-2 mb-4">
        <?php foreach ($allTeachers as $t): ?>
        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 cursor-pointer
                      <?= in_array($t['id'], $assignedIds) ? 'border-success bg-success bg-opacity-5' : '' ?>">
          <input type="checkbox" name="teacher_ids[]" value="<?= $t['id'] ?>"
                 class="form-check-input mt-0" style="width:18px;height:18px"
                 <?= in_array($t['id'], $assignedIds) ? 'checked' : '' ?>>
          <div class="avatar-sm bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:38px;height:38px">
            <i class="fa fa-user text-success"></i>
          </div>
          <div>
            <div class="fw-medium"><?= e($t['name']) ?></div>
            <small class="text-muted"><?= e($t['email']) ?><?= $t['department'] ? ' · ' . e($t['department']) : '' ?></small>
          </div>
        </label>
        <?php endforeach; ?>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success px-4">
          <i class="fa fa-save me-2"></i>Save Assignments
        </button>
        <a href="<?= BASE_URL ?>/admin/seminars.php" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
