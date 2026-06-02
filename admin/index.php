<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$pageTitle = 'Dashboard';
$stats     = getDashboardStats();

$db = getDB();
$recentSeminars = $db->query(
    'SELECT s.*, (SELECT COUNT(*) FROM registrations r WHERE r.seminar_id = s.id) AS reg_count
     FROM seminars s ORDER BY s.created_at DESC LIMIT 5'
)->fetchAll();
$recentRegs = $db->query(
    'SELECT r.*, s.title AS seminar_title
     FROM registrations r JOIN seminars s ON s.id = r.seminar_id
     ORDER BY r.registration_date DESC LIMIT 6'
)->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold mb-0">Dashboard</h4>
    <p class="text-muted small mb-0">Welcome back, <?= e(currentUserName()) ?></p>
  </div>
  <div class="text-muted small"><?= date('l, d M Y') ?></div>
</div>

<!-- Stat cards -->
<div class="row g-4 mb-4">
  <div class="col-6 col-lg-3">
    <div class="stat-card card p-4">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fa fa-calendar-alt"></i></div>
        <div><div class="h4 fw-bold mb-0"><?= $stats['seminars'] ?></div><div class="text-muted small">Total Seminars</div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card card p-4">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fa fa-clock"></i></div>
        <div><div class="h4 fw-bold mb-0"><?= $stats['upcoming'] ?></div><div class="text-muted small">Upcoming</div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card card p-4">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fa fa-chalkboard-teacher"></i></div>
        <div><div class="h4 fw-bold mb-0"><?= $stats['teachers'] ?></div><div class="text-muted small">Teachers</div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card card p-4">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="fa fa-users"></i></div>
        <div><div class="h4 fw-bold mb-0"><?= $stats['registrations'] ?></div><div class="text-muted small">Registrations</div></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Recent Seminars -->
  <div class="col-lg-7">
    <div class="table-card card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0">Recent Seminars</h6>
        <a href="<?= BASE_URL ?>/admin/seminars.php" class="btn btn-sm btn-outline-primary">View All</a>
      </div>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead class="table-light"><tr><th>Title</th><th>Date</th><th>Regs</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($recentSeminars as $s): ?>
            <tr>
              <td><a href="<?= BASE_URL ?>/admin/seminar_form.php?id=<?= $s['id'] ?>" class="text-decoration-none fw-medium"><?= e($s['title']) ?></a></td>
              <td><?= formatDate($s['seminar_date']) ?></td>
              <td><span class="badge bg-info"><?= $s['reg_count'] ?></span></td>
              <td><?= statusBadge($s['status']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($recentSeminars)): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">No seminars yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Recent Registrations -->
  <div class="col-lg-5">
    <div class="table-card card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0">Recent Registrations</h6>
        <a href="<?= BASE_URL ?>/admin/registrations.php" class="btn btn-sm btn-outline-primary">View All</a>
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach ($recentRegs as $r): ?>
        <li class="list-group-item py-3">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px">
              <i class="fa fa-user text-primary small"></i>
            </div>
            <div>
              <div class="fw-medium small"><?= e($r['student_name']) ?></div>
              <div class="text-muted" style="font-size:.75rem"><?= e($r['seminar_title']) ?></div>
            </div>
            <div class="ms-auto text-muted" style="font-size:.72rem"><?= date('d M', strtotime($r['registration_date'])) ?></div>
          </div>
        </li>
        <?php endforeach; ?>
        <?php if (empty($recentRegs)): ?>
        <li class="list-group-item text-center text-muted py-4">No registrations yet.</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
