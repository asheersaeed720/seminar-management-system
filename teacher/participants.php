<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireTeacher();

$seminarId = (int)($_GET['seminar_id'] ?? 0);

// Verify teacher is assigned to this seminar
$db   = getDB();
$stmt = $db->prepare(
    'SELECT 1 FROM seminar_teachers WHERE seminar_id = ? AND teacher_id = ?'
);
$stmt->execute([$seminarId, currentUserId()]);
if (!$stmt->fetch()) {
    setFlash('error', 'Access denied.');
    redirect(BASE_URL . '/teacher/seminars.php');
}

$seminar      = getSeminarById($seminarId);
$participants = getRegistrationsBySeminar($seminarId);
$summary      = getAttendanceSummary($seminarId);

$pageTitle = 'Participants — ' . ($seminar['title'] ?? '');
require_once __DIR__ . '/../includes/teacher_header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold mb-0">Registered Participants</h4>
    <p class="text-muted small mb-0"><?= e($seminar['title']) ?></p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= BASE_URL ?>/teacher/attendance.php?seminar_id=<?= $seminarId ?>" class="btn btn-success btn-sm">
      <i class="fa fa-clipboard-check me-1"></i>Mark Attendance
    </a>
    <a href="<?= BASE_URL ?>/teacher/seminars.php" class="btn btn-outline-secondary btn-sm">
      <i class="fa fa-arrow-left me-1"></i>Back
    </a>
  </div>
</div>

<!-- Summary -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm p-3 text-center">
      <div class="h3 fw-bold text-primary mb-0"><?= $summary['total'] ?></div>
      <small class="text-muted">Registered</small>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm p-3 text-center">
      <div class="h3 fw-bold text-success mb-0"><?= $summary['present'] ?? 0 ?></div>
      <small class="text-muted">Present</small>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm p-3 text-center">
      <div class="h3 fw-bold text-danger mb-0"><?= $summary['absent'] ?? 0 ?></div>
      <small class="text-muted">Absent</small>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm p-3 text-center">
      <div class="h3 fw-bold text-secondary mb-0"><?= $summary['unmarked'] ?? 0 ?></div>
      <small class="text-muted">Unmarked</small>
    </div>
  </div>
</div>

<div class="table-card card">
  <div class="card-header">
    <small class="text-muted"><?= count($participants) ?> participant(s)</small>
  </div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead class="table-light">
        <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Roll No.</th><th>Department</th><th>Attendance</th></tr>
      </thead>
      <tbody>
        <?php foreach ($participants as $i => $p): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td class="fw-medium"><?= e($p['student_name']) ?></td>
          <td><?= e($p['student_email']) ?></td>
          <td><?= e($p['student_phone'] ?? '—') ?></td>
          <td><?= e($p['student_roll'] ?? '—') ?></td>
          <td><?= e($p['department'] ?? '—') ?></td>
          <td>
            <?php $status = $p['attendance_status'] ?? null; ?>
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
        <?php if (empty($participants)): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">No participants registered.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/teacher_footer.php'; ?>
