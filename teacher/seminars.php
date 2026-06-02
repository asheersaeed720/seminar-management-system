<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireTeacher();

$pageTitle  = 'My Seminars';
$seminars   = getSeminarsByTeacher(currentUserId());

require_once __DIR__ . '/../includes/teacher_header.php';
?>

<div class="mb-4">
  <h4 class="fw-bold mb-0">My Assigned Seminars</h4>
  <p class="text-muted small"><?= count($seminars) ?> seminar(s) assigned to you</p>
</div>

<?php if (empty($seminars)): ?>
<div class="text-center py-5 text-muted">
  <i class="fa fa-calendar-times fa-3x mb-3"></i>
  <p>No seminars have been assigned to you yet.<br>Please contact the admin.</p>
</div>
<?php else: ?>
<div class="row g-4">
  <?php foreach ($seminars as $s): ?>
  <div class="col-md-6 col-lg-4">
    <div class="seminar-card card h-100">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-start">
          <?= statusBadge($s['status']) ?>
          <span class="badge bg-info"><?= $s['reg_count'] ?> registered</span>
        </div>
        <h5 class="mt-2 mb-0 fw-semibold"><?= e($s['title']) ?></h5>
      </div>
      <div class="card-body">
        <div class="seminar-meta d-flex flex-column gap-1">
          <span><i class="fa fa-calendar me-2"></i><?= formatDate($s['seminar_date']) ?></span>
          <span><i class="fa fa-clock me-2"></i><?= formatTime($s['seminar_time']) ?></span>
          <?php if ($s['venue']): ?><span><i class="fa fa-map-marker-alt me-2"></i><?= e($s['venue']) ?></span><?php endif; ?>
          <span><i class="fa fa-users me-2"></i>Capacity: <?= $s['capacity'] ?></span>
        </div>
      </div>
      <div class="card-footer bg-transparent border-0 pb-3 px-3 d-flex gap-2">
        <a href="<?= BASE_URL ?>/teacher/participants.php?seminar_id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
          <i class="fa fa-users me-1"></i>Participants
        </a>
        <a href="<?= BASE_URL ?>/teacher/attendance.php?seminar_id=<?= $s['id'] ?>" class="btn btn-sm btn-success flex-grow-1">
          <i class="fa fa-clipboard-check me-1"></i>Attendance
        </a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/teacher_footer.php'; ?>
