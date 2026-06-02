<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (isTeacher()) redirect(BASE_URL . '/teacher/index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';
    if (attemptLogin($email, $password, 'teacher')) {
        redirect(BASE_URL . '/teacher/index.php');
    }
    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Teacher Login — <?= SITE_NAME ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<style>body{background:linear-gradient(135deg,#1a3a1a 0%,#2e7d32 100%);min-height:100vh;}</style>
</head>
<body>
<div class="login-wrapper">
  <div class="login-card card">
    <div class="card-body p-5">
      <div class="login-icon bg-success text-white mb-3">
        <i class="fa fa-chalkboard-teacher"></i>
      </div>
      <h2 class="text-center fw-bold mb-1">Teacher Login</h2>
      <p class="text-center text-muted small mb-4"><?= SITE_TAGLINE ?></p>

      <?php if ($error): ?>
      <div class="alert alert-danger"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" id="loginForm" novalidate>
        <div class="mb-3">
          <label class="form-label fw-medium">Email Address</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa fa-envelope"></i></span>
            <input type="email" name="email" class="form-control" placeholder="teacher@university.edu"
                   value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label fw-medium">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa fa-lock"></i></span>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
          </div>
        </div>
        <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">
          <i class="fa fa-sign-in-alt me-2"></i>Sign In
        </button>
      </form>

      <hr class="mt-4">
      <div class="text-center">
        <a href="<?= BASE_URL ?>/admin/login.php" class="text-muted small">
          <i class="fa fa-user-shield me-1"></i>Admin Login
        </a>
        &nbsp;|&nbsp;
        <a href="<?= BASE_URL ?>/" class="text-muted small">
          <i class="fa fa-globe me-1"></i>Public Site
        </a>
      </div>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
