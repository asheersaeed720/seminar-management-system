<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireTeacher();

$seminarId  = (int)($_GET['seminar_id'] ?? 0);
$seminars   = getSeminarsByTeacher(currentUserId());

// Default to first seminar if none selected
if (!$seminarId && !empty($seminars)) {
    $seminarId = (int)$seminars[0]['id'];
}

$seminar      = null;
$participants = [];
$summary      = null;

if ($seminarId) {
    // Verify assignment
    $db   = getDB();
    $stmt = $db->prepare('SELECT 1 FROM seminar_teachers WHERE seminar_id=? AND teacher_id=?');
    $stmt->execute([$seminarId, currentUserId()]);
    if ($stmt->fetch()) {
        $seminar      = getSeminarById($seminarId);
        $participants = getRegistrationsBySeminar($seminarId);
        $summary      = getAttendanceSummary($seminarId);
    }
}

$pageTitle = 'Mark Attendance';
require_once __DIR__ . '/../includes/teacher_header.php';
?>
<script>
var BASE_URL   = '<?= BASE_URL ?>';
var CSRF_TOKEN = '<?= csrfToken() ?>';
</script>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-bold mb-0">Mark Attendance</h4>
</div>

<!-- Seminar Selector -->
<form method="GET" class="row g-2 mb-4">
  <div class="col-md-6">
    <select name="seminar_id" class="form-select" onchange="this.form.submit()">
      <option value="">— Select Seminar —</option>
      <?php foreach ($seminars as $s): ?>
      <option value="<?= $s['id'] ?>" <?= $seminarId === (int)$s['id'] ? 'selected' : '' ?>>
        <?= e($s['title']) ?> (<?= formatDate($s['seminar_date']) ?>)
      </option>
      <?php endforeach; ?>
    </select>
  </div>
</form>

<?php if (!$seminar): ?>
<div class="text-center py-5 text-muted">
  <i class="fa fa-clipboard-check fa-3x mb-3"></i>
  <p>Select a seminar to mark attendance.</p>
</div>
<?php else: ?>

<!-- Seminar info -->
<div class="alert alert-light border mb-4 d-flex gap-3 align-items-center">
  <i class="fa fa-calendar-alt fa-lg text-primary"></i>
  <div>
    <strong><?= e($seminar['title']) ?></strong> —
    <?= formatDate($seminar['seminar_date']) ?> at <?= formatTime($seminar['seminar_time']) ?>
    <?php if ($seminar['venue']): ?> · <?= e($seminar['venue']) ?><?php endif; ?>
  </div>
</div>

<!-- Live summary -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm p-3 text-center">
      <div class="h3 fw-bold text-primary mb-0"><?= $summary['total'] ?></div>
      <small class="text-muted">Total</small>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm p-3 text-center">
      <div class="h3 fw-bold text-success mb-0" id="att-present"><?= $summary['present'] ?? 0 ?></div>
      <small class="text-muted">Present</small>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm p-3 text-center">
      <div class="h3 fw-bold text-danger mb-0" id="att-absent"><?= $summary['absent'] ?? 0 ?></div>
      <small class="text-muted">Absent</small>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm p-3 text-center">
      <div class="h3 fw-bold text-secondary mb-0" id="att-unmarked"><?= $summary['unmarked'] ?? 0 ?></div>
      <small class="text-muted">Unmarked</small>
    </div>
  </div>
</div>

<?php if ($summary['total'] > 0): ?>
<?php $pct = $summary['total'] > 0 ? round((($summary['present'] ?? 0) / $summary['total']) * 100) : 0; ?>
<div class="card border-0 shadow-sm rounded-3 mb-4 p-3">
  <div class="d-flex justify-content-between small mb-1">
    <span class="text-muted">Attendance Rate</span>
    <strong id="att-pct-text"><?= $pct ?>%</strong>
  </div>
  <div class="progress" style="height:8px;border-radius:6px">
    <div id="att-progress" class="progress-bar bg-success" style="width:<?= $pct ?>%" aria-valuenow="<?= $pct ?>"><?= $pct ?>%</div>
  </div>
</div>
<?php endif; ?>

<div class="alert alert-info small">
  <i class="fa fa-info-circle me-2"></i>
  Click a badge to toggle attendance. Changes save instantly via AJAX — no page reload needed.
</div>

<div class="table-card card">
  <div class="card-header"><small class="text-muted"><?= count($participants) ?> registered participant(s)</small></div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead class="table-light">
        <tr><th>#</th><th>Name</th><th>Email</th><th>Roll</th><th>Department</th><th>Attendance</th></tr>
      </thead>
      <tbody>
        <?php foreach ($participants as $i => $p): ?>
        <?php $status = $p['attendance_status'] ?? 'absent'; ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td class="fw-medium"><?= e($p['student_name']) ?></td>
          <td><?= e($p['student_email']) ?></td>
          <td><?= e($p['student_roll'] ?? '—') ?></td>
          <td><?= e($p['department'] ?? '—') ?></td>
          <td>
            <span class="attendance-toggle"
                  data-reg-id="<?= $p['id'] ?>"
                  data-seminar-id="<?= $seminarId ?>"
                  data-status="<?= e($status) ?>"
                  title="Click to toggle">
              <?php if ($status === 'present'): ?>
                <span class="badge bg-success">Present</span>
              <?php else: ?>
                <span class="badge bg-danger">Absent</span>
              <?php endif; ?>
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($participants)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No participants registered for this seminar.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/teacher_footer.php'; ?>
