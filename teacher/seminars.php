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
        <?php if ($s['status'] === 'upcoming'): ?>
        <?php
          $protocol   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
          $regLink    = $protocol . '://' . $_SERVER['HTTP_HOST'] . BASE_URL . '/register.php?seminar_id=' . $s['id'];
        ?>
        <button type="button" class="btn btn-sm btn-outline-secondary"
                data-bs-toggle="modal" data-bs-target="#qrModal"
                data-title="<?= e($s['title']) ?>"
                data-qr="https://api.qrserver.com/v1/create-qr-code/?data=<?= urlencode($regLink) ?>&size=220x220&margin=10"
                data-link="<?= e($regLink) ?>">
          <i class="fa fa-qrcode me-1"></i>QR
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- QR Code Modal -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="qrModalLabel"><i class="fa fa-qrcode me-2"></i>Registration QR Code</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-4">
        <p class="text-muted small mb-3" id="qrSeminarTitle"></p>
        <img id="qrImage" src="" alt="QR Code" class="img-fluid rounded-3 border mb-3" style="max-width:220px">
        <p class="small text-muted mb-2">Students scan this code to open the registration form</p>
        <a id="qrDirectLink" href="#" target="_blank" class="btn btn-sm btn-outline-primary">
          <i class="fa fa-external-link-alt me-1"></i>Open Link
        </a>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('qrModal').addEventListener('show.bs.modal', function (e) {
  var btn = e.relatedTarget;
  document.getElementById('qrModalLabel').innerHTML = '<i class="fa fa-qrcode me-2"></i>' + btn.dataset.title;
  document.getElementById('qrSeminarTitle').textContent = 'Share this QR code with students to register instantly';
  document.getElementById('qrImage').src = btn.dataset.qr;
  document.getElementById('qrDirectLink').href = btn.dataset.link;
});
</script>

<?php require_once __DIR__ . '/../includes/teacher_footer.php'; ?>
