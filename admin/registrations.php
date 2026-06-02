<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$seminarFilter = (int)($_GET['seminar_id'] ?? 0);
$db            = getDB();

$seminars = getAllSeminars();
$seminar  = $seminarFilter ? getSeminarById($seminarFilter) : null;

$sql    = 'SELECT r.*, s.title AS seminar_title, s.seminar_date
           FROM registrations r JOIN seminars s ON s.id = r.seminar_id';
$params = [];
if ($seminarFilter) {
    $sql .= ' WHERE r.seminar_id = ?';
    $params[] = $seminarFilter;
}
$sql .= ' ORDER BY r.registration_date DESC';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$registrations = $stmt->fetchAll();

$pageTitle  = 'Registrations';
$breadcrumb = [['label' => 'Registrations', 'active' => true]];
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-bold mb-0">Seminar Registrations</h4>
  <?php if ($seminar): ?>
  <a href="<?= BASE_URL ?>/admin/registrations.php" class="btn btn-outline-secondary btn-sm">
    <i class="fa fa-times me-1"></i>Clear Filter
  </a>
  <?php endif; ?>
</div>

<!-- Filter -->
<form method="GET" class="row g-2 mb-4">
  <div class="col-md-5">
    <select name="seminar_id" class="form-select">
      <option value="">— All Seminars —</option>
      <?php foreach ($seminars as $s): ?>
      <option value="<?= $s['id'] ?>" <?= $seminarFilter === (int)$s['id'] ? 'selected' : '' ?>>
        <?= e($s['title']) ?> (<?= formatDate($s['seminar_date']) ?>)
      </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-auto">
    <button type="submit" class="btn btn-primary"><i class="fa fa-filter me-1"></i>Filter</button>
  </div>
</form>

<?php if ($seminar): ?>
<div class="alert alert-light border mb-3 d-flex gap-3 align-items-center">
  <i class="fa fa-calendar-alt fa-lg text-primary"></i>
  <div>
    <strong><?= e($seminar['title']) ?></strong> —
    <?= formatDate($seminar['seminar_date']) ?> at <?= formatTime($seminar['seminar_time']) ?>
    <?php if ($seminar['venue']): ?> · <?= e($seminar['venue']) ?><?php endif; ?>
  </div>
</div>
<?php endif; ?>

<div class="table-card card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <span class="text-muted small"><?= count($registrations) ?> registration(s)</span>
  </div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead class="table-light">
        <tr><th>#</th><th>Student</th><th>Email</th><th>Phone</th><th>Roll</th><th>Department</th><th>Seminar</th><th>Registered</th></tr>
      </thead>
      <tbody>
        <?php foreach ($registrations as $i => $r): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td class="fw-medium"><?= e($r['student_name']) ?></td>
          <td><a href="mailto:<?= e($r['student_email']) ?>" class="text-decoration-none"><?= e($r['student_email']) ?></a></td>
          <td><?= e($r['student_phone'] ?? '—') ?></td>
          <td><?= e($r['student_roll'] ?? '—') ?></td>
          <td><?= e($r['department'] ?? '—') ?></td>
          <td><span class="badge bg-light text-dark border"><?= e($r['seminar_title']) ?></span></td>
          <td class="text-muted small"><?= date('d M Y, h:i A', strtotime($r['registration_date'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($registrations)): ?>
        <tr><td colspan="8" class="text-center text-muted py-5">No registrations found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
