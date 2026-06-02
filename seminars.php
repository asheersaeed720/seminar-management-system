<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Browse Seminars';

$statusFilter = sanitize($_GET['status'] ?? '');
$search       = sanitize($_GET['q'] ?? '');
$allowed      = ['', 'upcoming', 'ongoing', 'completed', 'cancelled'];
if (!in_array($statusFilter, $allowed)) $statusFilter = '';

$db  = getDB();
$sql = 'SELECT s.*,
               (SELECT COUNT(*) FROM registrations r WHERE r.seminar_id = s.id) AS reg_count
        FROM seminars s WHERE 1=1';
$params = [];
if ($statusFilter) {
    $sql .= ' AND s.status = ?';
    $params[] = $statusFilter;
}
if ($search) {
    $sql .= ' AND (s.title LIKE ? OR s.speaker LIKE ? OR s.venue LIKE ?)';
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}
$sql .= ' ORDER BY s.seminar_date DESC';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$seminars = $stmt->fetchAll();

require_once __DIR__ . '/includes/public_header.php';
?>

<div class="container py-5">
  <h1 class="section-title mb-1">Seminars</h1>
  <p class="text-muted mb-4">Find and register for upcoming knowledge sessions.</p>

  <!-- Filters -->
  <form method="GET" class="row g-2 mb-4">
    <div class="col-md-6">
      <div class="input-group">
        <span class="input-group-text"><i class="fa fa-search"></i></span>
        <input type="text" name="q" class="form-control" placeholder="Search by title, speaker, venue…" value="<?= e($search) ?>">
      </div>
    </div>
    <div class="col-md-3">
      <select name="status" class="form-select">
        <option value="">All Statuses</option>
        <?php foreach (['upcoming','ongoing','completed','cancelled'] as $st): ?>
        <option value="<?= $st ?>" <?= $statusFilter === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
      <button type="submit" class="btn btn-primary flex-grow-1"><i class="fa fa-filter me-1"></i>Filter</button>
      <a href="seminars.php" class="btn btn-outline-secondary"><i class="fa fa-times"></i></a>
    </div>
  </form>

  <?php if (empty($seminars)): ?>
  <div class="text-center py-5 text-muted">
    <i class="fa fa-search fa-3x mb-3"></i>
    <p>No seminars found. Try different filters.</p>
  </div>
  <?php else: ?>
  <p class="text-muted small mb-3"><?= count($seminars) ?> seminar(s) found</p>
  <div class="row g-4">
    <?php foreach ($seminars as $s): ?>
    <?php $cap = getSeminarCapacityInfo((int)$s['id']); ?>
    <div class="col-md-6 col-lg-4">
      <div class="seminar-card card h-100">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-start">
            <?= statusBadge($s['status']) ?>
            <small class="opacity-75"><?= $cap['available'] ?>/<?= $s['capacity'] ?> seats</small>
          </div>
          <h5 class="mt-2 mb-0 fw-semibold"><?= e($s['title']) ?></h5>
        </div>
        <div class="card-body">
          <div class="seminar-meta d-flex flex-column gap-1 mb-2">
            <span><i class="fa fa-calendar me-2"></i><?= formatDate($s['seminar_date']) ?></span>
            <span><i class="fa fa-clock me-2"></i><?= formatTime($s['seminar_time']) ?></span>
            <?php if ($s['venue']): ?><span><i class="fa fa-map-marker-alt me-2"></i><?= e($s['venue']) ?></span><?php endif; ?>
            <?php if ($s['speaker']): ?><span><i class="fa fa-microphone me-2"></i><?= e($s['speaker']) ?></span><?php endif; ?>
          </div>
          <!-- Capacity bar -->
          <div class="mt-2">
            <?php $pct = $s['capacity'] > 0 ? min(100, round(($s['reg_count'] / $s['capacity']) * 100)) : 0; ?>
            <div class="d-flex justify-content-between small text-muted mb-1">
              <span><?= $s['reg_count'] ?> registered</span><span><?= $pct ?>% full</span>
            </div>
            <div class="progress capacity-bar">
              <div class="progress-bar <?= $pct >= 90 ? 'bg-danger' : ($pct >= 60 ? 'bg-warning' : 'bg-success') ?>"
                   style="width:<?= $pct ?>%"></div>
            </div>
          </div>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3 d-flex gap-2">
          <a href="<?= BASE_URL ?>/seminar.php?id=<?= $s['id'] ?>" class="btn btn-outline-primary btn-sm flex-grow-1">Details</a>
          <?php if ($cap['available'] > 0 && $s['status'] === 'upcoming'): ?>
          <a href="<?= BASE_URL ?>/register.php?seminar_id=<?= $s['id'] ?>" class="btn btn-accent btn-sm flex-grow-1">Register</a>
          <?php else: ?>
          <button class="btn btn-secondary btn-sm flex-grow-1" disabled><?= $s['status'] !== 'upcoming' ? ucfirst($s['status']) : 'Full' ?></button>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/public_footer.php'; ?>
