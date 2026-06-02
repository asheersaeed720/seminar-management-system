<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$editId  = (int)($_GET['id'] ?? 0);
$seminar = $editId ? getSeminarById($editId) : null;
$isEdit  = $seminar !== null;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $title   = sanitize($_POST['title']         ?? '');
    $desc    = sanitize($_POST['description']   ?? '');
    $speaker = sanitize($_POST['speaker']       ?? '');
    $date    = sanitize($_POST['seminar_date']  ?? '');
    $time    = sanitize($_POST['seminar_time']  ?? '');
    $venue   = sanitize($_POST['venue']         ?? '');
    $cap     = (int)($_POST['capacity']         ?? 50);
    $status  = sanitize($_POST['status']        ?? 'upcoming');
    $allowed = ['upcoming','ongoing','completed','cancelled'];

    if (!$title)                    $errors[] = 'Title is required.';
    if (!$date)                     $errors[] = 'Date is required.';
    if (!$time)                     $errors[] = 'Time is required.';
    if ($cap < 1)                   $errors[] = 'Capacity must be at least 1.';
    if (!in_array($status, $allowed)) $status = 'upcoming';

    if (empty($errors)) {
        $db = getDB();
        if ($isEdit) {
            $db->prepare(
                'UPDATE seminars SET title=?,description=?,speaker=?,seminar_date=?,seminar_time=?,venue=?,capacity=?,status=? WHERE id=?'
            )->execute([$title,$desc,$speaker,$date,$time,$venue,$cap,$status,$editId]);
            setFlash('success', 'Seminar updated.');
        } else {
            $db->prepare(
                'INSERT INTO seminars (title,description,speaker,seminar_date,seminar_time,venue,capacity,status,created_by) VALUES (?,?,?,?,?,?,?,?,?)'
            )->execute([$title,$desc,$speaker,$date,$time,$venue,$cap,$status,currentUserId()]);
            setFlash('success', 'Seminar created.');
        }
        redirect(BASE_URL . '/admin/seminars.php');
    }
}

$pageTitle  = $isEdit ? 'Edit Seminar' : 'New Seminar';
$breadcrumb = [
    ['label' => 'Seminars', 'url' => BASE_URL . '/admin/seminars.php'],
    ['label' => $pageTitle, 'active' => true]
];
require_once __DIR__ . '/../includes/admin_header.php';

$v = function(string $field) use ($seminar): string {
    return e($_POST[$field] ?? $seminar[$field] ?? '');
};
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-bold mb-0"><?= $isEdit ? 'Edit Seminar' : 'Create New Seminar' ?></h4>
  <a href="<?= BASE_URL ?>/admin/seminars.php" class="btn btn-outline-secondary btn-sm">
    <i class="fa fa-arrow-left me-1"></i>Back
  </a>
</div>

<div class="card border-0 shadow-sm rounded-3" style="max-width:720px">
  <div class="card-body p-4">
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): echo '<li>' . e($e) . '</li>'; endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="POST" id="seminarForm" novalidate>
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <div class="mb-3">
        <label class="form-label fw-medium">Seminar Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" required value="<?= $v('title') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label fw-medium">Description</label>
        <textarea name="description" class="form-control" rows="4"><?= $v('description') ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label fw-medium">Speaker / Presenter</label>
        <input type="text" name="speaker" class="form-control" value="<?= $v('speaker') ?>">
      </div>
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label fw-medium">Date <span class="text-danger">*</span></label>
          <input type="date" name="seminar_date" class="form-control" required value="<?= $v('seminar_date') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-medium">Time <span class="text-danger">*</span></label>
          <input type="time" name="seminar_time" class="form-control" required value="<?= $v('seminar_time') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-medium">Capacity <span class="text-danger">*</span></label>
          <input type="number" name="capacity" class="form-control" required min="1"
                 value="<?= e($_POST['capacity'] ?? $seminar['capacity'] ?? 50) ?>">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label fw-medium">Venue</label>
        <input type="text" name="venue" class="form-control" value="<?= $v('venue') ?>">
      </div>
      <div class="mb-4">
        <label class="form-label fw-medium">Status</label>
        <select name="status" class="form-select">
          <?php foreach (['upcoming','ongoing','completed','cancelled'] as $st): ?>
          <option value="<?= $st ?>" <?= ($v('status') ?: ($seminar['status'] ?? 'upcoming')) === $st ? 'selected' : '' ?>>
            <?= ucfirst($st) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4">
          <i class="fa fa-save me-2"></i><?= $isEdit ? 'Update Seminar' : 'Create Seminar' ?>
        </button>
        <a href="<?= BASE_URL ?>/admin/seminars.php" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
