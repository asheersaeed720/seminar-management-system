<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$regId = (int)($_GET['id'] ?? 0);
if (!$regId) redirect(BASE_URL . '/seminars.php');

$db   = getDB();
$stmt = $db->prepare(
    'SELECT r.*, s.title, s.seminar_date, s.seminar_time, s.venue
     FROM registrations r JOIN seminars s ON s.id = r.seminar_id
     WHERE r.id = ? LIMIT 1'
);
$stmt->execute([$regId]);
$reg = $stmt->fetch();
if (!$reg) redirect(BASE_URL . '/seminars.php');

$pageTitle = 'Registration Successful';
require_once __DIR__ . '/includes/public_header.php';
?>

<div class="container py-5" style="max-width:600px">
  <div class="card border-0 shadow-sm rounded-3 text-center overflow-hidden">
    <div class="p-5" style="background:linear-gradient(135deg,#1a7a3c,#2ecc71)">
      <div class="mb-3">
        <span class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center"
              style="width:80px;height:80px">
          <i class="fa fa-check-circle fa-3x text-success"></i>
        </span>
      </div>
      <h1 class="h3 text-white fw-bold mb-1">You're Registered!</h1>
      <p class="text-white opacity-75 mb-0">Your spot is confirmed.</p>
    </div>
    <div class="card-body p-4">
      <table class="table table-borderless small text-start">
        <tr><th class="text-muted fw-medium" style="width:40%">Seminar</th><td class="fw-semibold"><?= e($reg['title']) ?></td></tr>
        <tr><th class="text-muted fw-medium">Name</th><td><?= e($reg['student_name']) ?></td></tr>
        <tr><th class="text-muted fw-medium">Email</th><td><?= e($reg['student_email']) ?></td></tr>
        <?php if ($reg['student_roll']): ?><tr><th class="text-muted fw-medium">Roll No.</th><td><?= e($reg['student_roll']) ?></td></tr><?php endif; ?>
        <tr><th class="text-muted fw-medium">Date</th><td><?= formatDate($reg['seminar_date']) ?></td></tr>
        <tr><th class="text-muted fw-medium">Time</th><td><?= formatTime($reg['seminar_time']) ?></td></tr>
        <?php if ($reg['venue']): ?><tr><th class="text-muted fw-medium">Venue</th><td><?= e($reg['venue']) ?></td></tr><?php endif; ?>
        <tr><th class="text-muted fw-medium">Ref. #</th><td class="text-primary fw-semibold">#<?= str_pad($reg['id'], 6, '0', STR_PAD_LEFT) ?></td></tr>
      </table>

      <div class="alert alert-info small text-start mt-3">
        <i class="fa fa-info-circle me-2"></i>
        Please keep your <strong>Reference #<?= str_pad($reg['id'], 6, '0', STR_PAD_LEFT) ?></strong> and arrive on time.
        Attendance will be marked by the assigned teacher.
      </div>

      <div class="d-flex gap-2 mt-3">
        <a href="<?= BASE_URL ?>/seminars.php" class="btn btn-outline-primary flex-grow-1">
          <i class="fa fa-arrow-left me-2"></i>Browse More
        </a>
        <a href="<?= BASE_URL ?>/seminar.php?id=<?= $reg['seminar_id'] ?>" class="btn btn-primary flex-grow-1">
          Seminar Details
        </a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/public_footer.php'; ?>
