<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$pageTitle  = 'Seminars';
$breadcrumb = [['label' => 'Seminars', 'active' => true]];
$seminars   = getAllSeminars();

require_once __DIR__ . '/../includes/admin_header.php';
renderFlash();
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-bold mb-0">Manage Seminars</h4>
  <a href="<?= BASE_URL ?>/admin/seminar_form.php" class="btn btn-primary">
    <i class="fa fa-plus me-2"></i>New Seminar
  </a>
</div>

<div class="table-card card">
  <div class="card-header"><span class="text-muted small"><?= count($seminars) ?> seminar(s)</span></div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead class="table-light">
        <tr><th>#</th><th>Title</th><th>Date</th><th>Venue</th><th>Capacity</th><th>Regs</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($seminars as $i => $s): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td>
            <div class="fw-medium"><?= e($s['title']) ?></div>
            <?php if ($s['speaker']): ?><small class="text-muted"><i class="fa fa-microphone me-1"></i><?= e($s['speaker']) ?></small><?php endif; ?>
          </td>
          <td><small><?= formatDate($s['seminar_date']) ?><br><?= formatTime($s['seminar_time']) ?></small></td>
          <td><?= e($s['venue'] ?? '—') ?></td>
          <td><?= $s['capacity'] ?></td>
          <td><span class="badge bg-info"><?= $s['reg_count'] ?></span></td>
          <td><?= statusBadge($s['status']) ?></td>
          <td>
            <div class="d-flex gap-1 flex-wrap">
              <a href="<?= BASE_URL ?>/seminar.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="Preview">
                <i class="fa fa-eye"></i>
              </a>
              <a href="<?= BASE_URL ?>/admin/seminar_form.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="fa fa-edit"></i>
              </a>
              <a href="<?= BASE_URL ?>/admin/seminar_assign.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-success" title="Assign Teachers">
                <i class="fa fa-user-plus"></i>
              </a>
              <a href="<?= BASE_URL ?>/admin/registrations.php?seminar_id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-info" title="Registrations">
                <i class="fa fa-users"></i>
              </a>
              <form method="POST" action="<?= BASE_URL ?>/admin/seminar_delete.php" class="form-delete">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fa fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($seminars)): ?>
        <tr><td colspan="8" class="text-center text-muted py-5">No seminars yet. <a href="<?= BASE_URL ?>/admin/seminar_form.php">Create one.</a></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
