<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle  = 'Home';
$stats      = getDashboardStats();
$upcoming   = getAllSeminars('upcoming');
$recentAll  = array_slice($upcoming, 0, 6);

require_once __DIR__ . '/includes/public_header.php';
?>

<!-- Hero -->
<section class="hero-section">
  <div class="container text-center">
    <div class="hero-badge"><i class="fa fa-star me-1"></i> Knowledge Meets Community</div>
    <h1 class="mb-3">Discover & Attend<br>University Seminars</h1>
    <p class="lead opacity-75 mb-4">Explore expert-led seminars, register in seconds, and expand your horizons.</p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
      <a href="<?= BASE_URL ?>/seminars.php" class="btn btn-light btn-lg fw-semibold px-4">
        <i class="fa fa-search me-2"></i>Browse Seminars
      </a>
    </div>
  </div>
</section>

<!-- Stats -->
<section class="py-5">
  <div class="container">
    <div class="row g-4 text-center">
      <div class="col-6 col-md-3">
        <div class="stat-card card p-4">
          <div class="stat-icon bg-primary bg-opacity-10 text-primary mx-auto mb-3">
            <i class="fa fa-calendar-alt"></i>
          </div>
          <h3 class="fw-bold mb-0"><?= $stats['seminars'] ?></h3>
          <p class="text-muted small mb-0">Total Seminars</p>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card card p-4">
          <div class="stat-icon bg-success bg-opacity-10 text-success mx-auto mb-3">
            <i class="fa fa-clock"></i>
          </div>
          <h3 class="fw-bold mb-0"><?= $stats['upcoming'] ?></h3>
          <p class="text-muted small mb-0">Upcoming</p>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card card p-4">
          <div class="stat-icon bg-warning bg-opacity-10 text-warning mx-auto mb-3">
            <i class="fa fa-chalkboard-teacher"></i>
          </div>
          <h3 class="fw-bold mb-0"><?= $stats['teachers'] ?></h3>
          <p class="text-muted small mb-0">Expert Teachers</p>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card card p-4">
          <div class="stat-icon bg-info bg-opacity-10 text-info mx-auto mb-3">
            <i class="fa fa-users"></i>
          </div>
          <h3 class="fw-bold mb-0"><?= $stats['registrations'] ?></h3>
          <p class="text-muted small mb-0">Registrations</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Upcoming Seminars -->
<section class="py-3 pb-5">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h2 class="section-title mb-0">Upcoming Seminars</h2>
      <a href="<?= BASE_URL ?>/seminars.php" class="btn btn-outline-primary btn-sm">View All</a>
    </div>

    <?php if (empty($recentAll)): ?>
      <div class="text-center py-5 text-muted">
        <i class="fa fa-calendar-times fa-3x mb-3"></i>
        <p>No upcoming seminars at the moment. Check back soon!</p>
      </div>
    <?php else: ?>
    <div class="row g-4">
      <?php foreach ($recentAll as $s): ?>
      <div class="col-md-6 col-lg-4">
        <div class="seminar-card card h-100">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-start">
              <?= statusBadge($s['status']) ?>
              <?php $cap = getSeminarCapacityInfo((int)$s['id']); ?>
              <small class="opacity-75"><?= $cap['available'] ?> seats left</small>
            </div>
            <h5 class="mt-2 mb-0 fw-semibold"><?= e($s['title']) ?></h5>
          </div>
          <div class="card-body">
            <div class="seminar-meta d-flex flex-column gap-1">
              <span><i class="fa fa-calendar me-2"></i><?= formatDate($s['seminar_date']) ?></span>
              <span><i class="fa fa-clock me-2"></i><?= formatTime($s['seminar_time']) ?></span>
              <?php if ($s['venue']): ?><span><i class="fa fa-map-marker-alt me-2"></i><?= e($s['venue']) ?></span><?php endif; ?>
              <?php if ($s['speaker']): ?><span><i class="fa fa-microphone me-2"></i><?= e($s['speaker']) ?></span><?php endif; ?>
            </div>
            <?php if ($s['description']): ?>
            <p class="text-muted small mt-2 mb-0"><?= e(mb_substr($s['description'], 0, 100)) ?>…</p>
            <?php endif; ?>
          </div>
          <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3 d-flex gap-2">
            <a href="<?= BASE_URL ?>/seminar.php?id=<?= $s['id'] ?>" class="btn btn-outline-primary btn-sm flex-grow-1">Details</a>
            <?php if ($cap['available'] > 0 && $s['status'] === 'upcoming'): ?>
            <a href="<?= BASE_URL ?>/register.php?seminar_id=<?= $s['id'] ?>" class="btn btn-accent btn-sm flex-grow-1">Register</a>
            <?php else: ?>
            <button class="btn btn-secondary btn-sm flex-grow-1" disabled>Full</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/public_footer.php'; ?>
