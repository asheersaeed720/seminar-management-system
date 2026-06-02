<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$pageTitle  = 'Teachers';
$breadcrumb = [['label' => 'Teachers', 'active' => true]];
$teachers   = getAllTeachers();

require_once __DIR__ . '/../includes/admin_header.php';
renderFlash();
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-bold mb-0">Manage Teachers</h4>
  <a href="<?= BASE_URL ?>/admin/teacher_form.php" class="btn btn-primary">
    <i class="fa fa-plus me-2"></i>Add Teacher
  </a>
</div>

<div class="table-card card">
  <div class="card-header">
    <span class="text-muted small"><?= count($teachers) ?> teacher(s) total</span>
  </div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead class="table-light">
        <tr>
          <th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Department</th><th>Seminars</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($teachers as $i => $t): ?>
        <?php
          $db   = getDB();
          $stmt = $db->prepare('SELECT COUNT(*) FROM seminar_teachers WHERE teacher_id = ?');
          $stmt->execute([$t['id']]);
          $seminarsCount = $stmt->fetchColumn();
        ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="avatar-sm bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px">
                <i class="fa fa-user text-success small"></i>
              </div>
              <span class="fw-medium"><?= e($t['name']) ?></span>
            </div>
          </td>
          <td><?= e($t['email']) ?></td>
          <td><?= e($t['phone'] ?? '—') ?></td>
          <td><?= e($t['department'] ?? '—') ?></td>
          <td><span class="badge bg-primary"><?= $seminarsCount ?></span></td>
          <td>
            <div class="d-flex gap-2">
              <a href="<?= BASE_URL ?>/admin/teacher_form.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary">
                <i class="fa fa-edit"></i>
              </a>
              <form method="POST" action="<?= BASE_URL ?>/admin/teacher_delete.php" class="form-delete">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger">
                  <i class="fa fa-trash"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($teachers)): ?>
        <tr><td colspan="7" class="text-center text-muted py-5">No teachers found. <a href="<?= BASE_URL ?>/admin/teacher_form.php">Add one.</a></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
