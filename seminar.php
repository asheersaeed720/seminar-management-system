<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$id      = (int)($_GET['id'] ?? 0);
$seminar = $id ? getSeminarById($id) : null;
if (!$seminar) {
    http_response_code(404);
    $pageTitle = 'Seminar Not Found';
    require_once __DIR__ . '/includes/public_header.php';
    echo '<div class="container py-5 text-center"><h2>Seminar not found.</h2><a href="seminars.php" class="btn btn-primary mt-3">Back to Seminars</a></div>';
    require_once __DIR__ . '/includes/public_footer.php';
    exit;
}

$pageTitle  = $seminar['title'];
$teachers   = getSeminarTeachers($id);
$cap        = getSeminarCapacityInfo($id);

require_once __DIR__ . '/includes/public_header.php';
?>

<div class="container py-5">
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/seminars.php">Seminars</a></li>
      <li class="breadcrumb-item active"><?= e($seminar['title']) ?></li>
    </ol>
  </nav>

  <div class="row g-4">
    <!-- Main -->
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header p-4" style="background:linear-gradient(120deg,var(--primary),#2d5fa6)">
          <div class="mb-2"><?= statusBadge($seminar['status']) ?></div>
          <h1 class="h2 text-white fw-bold mb-0"><?= e($seminar['title']) ?></h1>
          <?php if ($seminar['speaker']): ?>
          <p class="text-white opacity-75 mt-2 mb-0"><i class="fa fa-microphone me-2"></i>Speaker: <?= e($seminar['speaker']) ?></p>
          <?php endif; ?>
        </div>
        <div class="card-body p-4">
          <?php if ($seminar['description']): ?>
          <h5 class="fw-semibold mb-3">About This Seminar</h5>
          <p class="text-muted" style="line-height:1.8"><?= nl2br(e($seminar['description'])) ?></p>
          <?php endif; ?>

          <?php if (!empty($teachers)): ?>
          <hr>
          <h5 class="fw-semibold mb-3">Assigned Teachers</h5>
          <div class="row g-3">
            <?php foreach ($teachers as $t): ?>
            <div class="col-sm-6">
              <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px">
                  <i class="fa fa-user text-white"></i>
                </div>
                <div>
                  <div class="fw-semibold small"><?= e($t['name']) ?></div>
                  <?php if ($t['department']): ?><small class="text-muted"><?= e($t['department']) ?></small><?php endif; ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top:70px">
        <div class="card-body p-4">
          <h5 class="fw-bold mb-4">Seminar Details</h5>
          <ul class="list-unstyled d-flex flex-column gap-3">
            <li class="d-flex gap-3">
              <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-2" style="width:38px;height:38px;min-width:38px;font-size:1rem">
                <i class="fa fa-calendar"></i>
              </div>
              <div><div class="small text-muted">Date</div><div class="fw-semibold"><?= formatDate($seminar['seminar_date']) ?></div></div>
            </li>
            <li class="d-flex gap-3">
              <div class="stat-icon bg-success bg-opacity-10 text-success rounded-2" style="width:38px;height:38px;min-width:38px;font-size:1rem">
                <i class="fa fa-clock"></i>
              </div>
              <div><div class="small text-muted">Time</div><div class="fw-semibold"><?= formatTime($seminar['seminar_time']) ?></div></div>
            </li>
            <?php if ($seminar['venue']): ?>
            <li class="d-flex gap-3">
              <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-2" style="width:38px;height:38px;min-width:38px;font-size:1rem">
                <i class="fa fa-map-marker-alt"></i>
              </div>
              <div><div class="small text-muted">Venue</div><div class="fw-semibold"><?= e($seminar['venue']) ?></div></div>
            </li>
            <?php endif; ?>
            <li class="d-flex gap-3">
              <div class="stat-icon bg-info bg-opacity-10 text-info rounded-2" style="width:38px;height:38px;min-width:38px;font-size:1rem">
                <i class="fa fa-users"></i>
              </div>
              <div>
                <div class="small text-muted">Seats</div>
                <div class="fw-semibold"><?= $cap['registered'] ?> / <?= $seminar['capacity'] ?> registered</div>
                <?php $pct = $seminar['capacity'] > 0 ? min(100, round(($cap['registered'] / $seminar['capacity']) * 100)) : 0; ?>
                <div class="progress mt-1 capacity-bar" style="height:5px">
                  <div class="progress-bar <?= $pct >= 90 ? 'bg-danger' : 'bg-success' ?>" style="width:<?= $pct ?>%"></div>
                </div>
              </div>
            </li>
          </ul>

          <hr>
          <?php if ($cap['available'] > 0 && $seminar['status'] === 'upcoming'): ?>
            <a href="<?= BASE_URL ?>/register.php?seminar_id=<?= $seminar['id'] ?>"
               class="btn btn-accent w-100 py-2 fw-semibold">
              <i class="fa fa-user-plus me-2"></i>Register Now
            </a>
            <p class="text-center small text-muted mt-2 mb-0"><?= $cap['available'] ?> seats remaining</p>
          <?php elseif ($seminar['status'] !== 'upcoming'): ?>
            <button class="btn btn-secondary w-100 py-2" disabled>
              <?= ucfirst($seminar['status']) ?>
            </button>
          <?php else: ?>
            <button class="btn btn-danger w-100 py-2" disabled>Fully Booked</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/public_footer.php'; ?>
