<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireTeacher();

$pageTitle = 'Dashboard';
$tid       = currentUserId();
$mySeminars = getSeminarsByTeacher($tid);
$upcoming   = array_filter($mySeminars, fn($s) => $s['status'] === 'upcoming');
$completed  = array_filter($mySeminars, fn($s) => $s['status'] === 'completed');

$totalRegs = 0;
foreach ($mySeminars as $s) $totalRegs += $s['reg_count'];

require_once __DIR__ . '/../includes/teacher_header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold mb-0">Dashboard</h4>
    <p class="text-muted small mb-0">Welcome, <?= e(currentUserName()) ?></p>
  </div>
  <small class="text-muted"><?= date('l, d M Y') ?></small>
</div>

<!-- Stats -->
<div class="row g-4 mb-4">
  <div class="col-6 col-md-4">
    <div class="stat-card card p-4">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fa fa-calendar-alt"></i></div>
        <div><div class="h4 fw-bold mb-0"><?= count($mySeminars) ?></div><div class="text-muted small">Total Assigned</div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card card p-4">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fa fa-clock"></i></div>
        <div><div class="h4 fw-bold mb-0"><?= count($upcoming) ?></div><div class="text-muted small">Upcoming</div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card card p-4">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="fa fa-users"></i></div>
        <div><div class="h4 fw-bold mb-0"><?= $totalRegs ?></div><div class="text-muted small">Total Registrations</div></div>
      </div>
    </div>
  </div>
</div>

<!-- Assigned Seminars -->
<div class="table-card card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h6 class="fw-bold mb-0">My Assigned Seminars</h6>
    <a href="<?= BASE_URL ?>/teacher/seminars.php" class="btn btn-sm btn-outline-primary">View All</a>
  </div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead class="table-light"><tr><th>Title</th><th>Date</th><th>Venue</th><th>Registered</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach (array_slice($mySeminars, 0, 5) as $s): ?>
        <tr>
          <td class="fw-medium"><?= e($s['title']) ?></td>
          <td><?= formatDate($s['seminar_date']) ?></td>
          <td><?= e($s['venue'] ?? '—') ?></td>
          <td><span class="badge bg-info"><?= $s['reg_count'] ?></span></td>
          <td><?= statusBadge($s['status']) ?></td>
          <td>
            <a href="<?= BASE_URL ?>/teacher/attendance.php?seminar_id=<?= $s['id'] ?>" class="btn btn-sm btn-success">
              <i class="fa fa-clipboard-check me-1"></i>Attendance
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($mySeminars)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No seminars assigned yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/teacher_footer.php'; ?>
