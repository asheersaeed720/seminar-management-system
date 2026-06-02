<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$seminarFilter = (int)($_GET['seminar_id'] ?? 0);
$seminars      = getAllSeminars();
$seminar       = $seminarFilter ? getSeminarById($seminarFilter) : null;

$attendanceData = [];
$summary        = null;
if ($seminar) {
    $attendanceData = getRegistrationsBySeminar($seminarFilter);
    $summary        = getAttendanceSummary($seminarFilter);
}

$pageTitle  = 'Attendance Report';
$breadcrumb = [['label' => 'Attendance Report', 'active' => true]];
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="mb-4">
  <h4 class="fw-bold mb-0">Attendance Report</h4>
  <p class="text-muted small mb-0">View attendance records per seminar.</p>
</div>

<form method="GET" class="row g-2 mb-4">
  <div class="col-md-5">
    <select name="seminar_id" class="form-select">
      <option value="">— Select a Seminar —</option>
      <?php foreach ($seminars as $s): ?>
      <option value="<?= $s['id'] ?>" <?= $seminarFilter === (int)$s['id'] ? 'selected' : '' ?>>
        <?= e($s['title']) ?> (<?= formatDate($s['seminar_date']) ?>)
      </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-auto">
    <button type="submit" class="btn btn-primary"><i class="fa fa-chart-bar me-1"></i>View Report</button>
  </div>
</form>

<?php if (!$seminar): ?>
<div class="text-center py-5 text-muted">
  <i class="fa fa-clipboard-list fa-3x mb-3"></i>
  <p>Select a seminar above to view its attendance report.</p>
</div>
<?php else: ?>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card card p-3 text-center">
      <div class="h3 fw-bold text-primary mb-0"><?= $summary['total'] ?></div>
      <small class="text-muted">Total Registered</small>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card p-3 text-center">
      <div class="h3 fw-bold text-success mb-0"><?= $summary['present'] ?? 0 ?></div>
      <small class="text-muted">Present</small>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card p-3 text-center">
      <div class="h3 fw-bold text-danger mb-0"><?= $summary['absent'] ?? 0 ?></div>
      <small class="text-muted">Absent</small>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card p-3 text-center">
      <div class="h3 fw-bold text-warning mb-0"><?= $summary['unmarked'] ?? 0 ?></div>
      <small class="text-muted">Unmarked</small>
    </div>
  </div>
</div>

<?php if ($summary['total'] > 0): ?>
<?php $pct = round(($summary['present'] / $summary['total']) * 100); ?>
<div class="card border-0 shadow-sm rounded-3 mb-4 p-3">
  <div class="d-flex justify-content-between align-items-center mb-1 small text-muted">
    <span>Attendance Rate</span><strong class="text-dark"><?= $pct ?>%</strong>
  </div>
  <div class="progress" style="height:10px;border-radius:6px">
    <div class="progress-bar bg-success" style="width:<?= $pct ?>%" aria-valuenow="<?= $pct ?>"></div>
  </div>
</div>
<?php endif; ?>

<div class="table-card card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <span class="fw-semibold"><?= e($seminar['title']) ?></span>
    <small class="text-muted"><?= formatDate($seminar['seminar_date']) ?></small>
  </div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead class="table-light">
        <tr><th>#</th><th>Student</th><th>Email</th><th>Roll</th><th>Department</th><th>Attendance</th></tr>
      </thead>
      <tbody>
        <?php foreach ($attendanceData as $i => $r): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td class="fw-medium"><?= e($r['student_name']) ?></td>
          <td><?= e($r['student_email']) ?></td>
          <td><?= e($r['student_roll'] ?? '—') ?></td>
          <td><?= e($r['department'] ?? '—') ?></td>
          <td>
            <?php $status = $r['attendance_status'] ?? null; ?>
            <?php if ($status === 'present'): ?>
              <span class="badge bg-success">Present</span>
            <?php elseif ($status === 'absent'): ?>
              <span class="badge bg-danger">Absent</span>
            <?php else: ?>
              <span class="badge bg-secondary">Not Marked</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($attendanceData)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No registrations for this seminar.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
