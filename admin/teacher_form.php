<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$editId  = (int)($_GET['id'] ?? 0);
$teacher = $editId ? getTeacherById($editId) : null;
$isEdit  = $teacher !== null;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $name     = sanitize($_POST['name']       ?? '');
    $email    = sanitize($_POST['email']      ?? '');
    $phone    = sanitize($_POST['phone']      ?? '');
    $dept     = sanitize($_POST['department'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name)                             $errors[] = 'Name is required.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (!$isEdit && strlen($password) < 6)  $errors[] = 'Password must be at least 6 characters.';
    if ($isEdit && $password && strlen($password) < 6) $errors[] = 'New password must be at least 6 characters.';

    // Email uniqueness check
    $db   = getDB();
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
    $stmt->execute([$email, $editId]);
    if ($stmt->fetch()) $errors[] = 'Email is already in use.';

    if (empty($errors)) {
        if ($isEdit) {
            $sql    = 'UPDATE users SET name=?, email=?, phone=?, department=? WHERE id=?';
            $params = [$name, $email, $phone, $dept, $editId];
            if ($password) {
                $sql    = 'UPDATE users SET name=?, email=?, phone=?, department=?, password=? WHERE id=?';
                $params = [$name, $email, $phone, $dept, password_hash($password, PASSWORD_DEFAULT), $editId];
            }
            $db->prepare($sql)->execute($params);
            setFlash('success', 'Teacher updated successfully.');
        } else {
            $db->prepare(
                "INSERT INTO users (name, email, password, phone, department, role) VALUES (?,?,?,?,?,'teacher')"
            )->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $phone, $dept]);
            setFlash('success', 'Teacher added successfully.');
        }
        redirect(BASE_URL . '/admin/teachers.php');
    }
}

$pageTitle  = $isEdit ? 'Edit Teacher' : 'Add Teacher';
$breadcrumb = [
    ['label' => 'Teachers', 'url' => BASE_URL . '/admin/teachers.php'],
    ['label' => $pageTitle, 'active' => true]
];
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-bold mb-0"><?= $isEdit ? 'Edit Teacher' : 'Add New Teacher' ?></h4>
  <a href="<?= BASE_URL ?>/admin/teachers.php" class="btn btn-outline-secondary btn-sm">
    <i class="fa fa-arrow-left me-1"></i>Back
  </a>
</div>

<div class="card border-0 shadow-sm rounded-3" style="max-width:640px">
  <div class="card-body p-4">
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): echo '<li>' . e($e) . '</li>'; endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="POST" id="teacherForm" data-mode="<?= $isEdit ? 'edit' : 'add' ?>" novalidate>
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <div class="row g-3">
        <div class="col-12 mb-3">
          <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" required
                 value="<?= e($teacher['name'] ?? $_POST['name'] ?? '') ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
          <input type="email" name="email" class="form-control" required
                 value="<?= e($teacher['email'] ?? $_POST['email'] ?? '') ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label fw-medium">Phone</label>
          <input type="tel" name="phone" class="form-control"
                 value="<?= e($teacher['phone'] ?? $_POST['phone'] ?? '') ?>">
        </div>
        <div class="col-12 mb-3">
          <label class="form-label fw-medium">Department</label>
          <input type="text" name="department" class="form-control"
                 value="<?= e($teacher['department'] ?? $_POST['department'] ?? '') ?>">
        </div>
        <div class="col-12 mb-3">
          <label class="form-label fw-medium">
            Password <?= $isEdit ? '<span class="text-muted small">(leave blank to keep current)</span>' : '<span class="text-danger">*</span>' ?>
          </label>
          <input type="password" name="password" class="form-control"
                 placeholder="<?= $isEdit ? 'New password (optional)' : 'Min. 6 characters' ?>"
                 <?= !$isEdit ? 'required' : '' ?>>
        </div>
      </div>

      <div class="d-flex gap-2 mt-2">
        <button type="submit" class="btn btn-primary px-4">
          <i class="fa fa-save me-2"></i><?= $isEdit ? 'Update Teacher' : 'Add Teacher' ?>
        </button>
        <a href="<?= BASE_URL ?>/admin/teachers.php" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
