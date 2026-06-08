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

$pageTitle = 'Participants — ' . ($seminar['title'] ?? '');
require_once __DIR__ . '/../includes/teacher_header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold mb-0">Registered Participants</h4>
    <p class="text-muted small mb-0"><?= e($seminar['title']) ?></p>
  </div>
  <a href="<?= BASE_URL ?>/teacher/seminars.php" class="btn btn-outline-secondary btn-sm">
    <i class="fa fa-arrow-left me-1"></i>Back
  </a>
</div>

<div class="table-card card">
  <div class="card-header">
    <small class="text-muted"><?= count($participants) ?> participant(s)</small>
  </div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead class="table-light">
        <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Roll No.</th><th>Department</th><th>Registered</th></tr>
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
          <td><?= date('d M Y', strtotime($p['registration_date'])) ?></td>
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
