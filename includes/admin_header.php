<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'Admin') ?> — <?= SITE_NAME ?> Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="admin-body">

<!-- Sidebar -->
<div class="d-flex" id="wrapper">
  <nav id="sidebar" class="sidebar-admin d-flex flex-column">
    <div class="sidebar-brand p-3 d-flex align-items-center gap-2">
      <i class="fa-solid fa-graduation-cap fs-4"></i>
      <div>
        <div class="fw-bold"><?= SITE_NAME ?></div>
        <small class="opacity-75">Admin Panel</small>
      </div>
    </div>
    <hr class="border-secondary mx-3 my-0">
    <ul class="nav flex-column px-2 py-3 flex-grow-1">
      <li class="nav-item">
        <a href="<?= BASE_URL ?>/admin/index.php" class="nav-link sidebar-link <?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'active' : '' ?>">
          <i class="fa fa-tachometer-alt fa-fw"></i> Dashboard
        </a>
      </li>
      <li class="nav-item mt-2">
        <small class="sidebar-label px-3">MANAGEMENT</small>
      </li>
      <li class="nav-item">
        <a href="<?= BASE_URL ?>/admin/teachers.php" class="nav-link sidebar-link <?= (basename($_SERVER['PHP_SELF']) === 'teachers.php' || basename($_SERVER['PHP_SELF']) === 'teacher_form.php') ? 'active' : '' ?>">
          <i class="fa fa-chalkboard-teacher fa-fw"></i> Teachers
        </a>
      </li>
      <li class="nav-item">
        <a href="<?= BASE_URL ?>/admin/seminars.php" class="nav-link sidebar-link <?= (in_array(basename($_SERVER['PHP_SELF']), ['seminars.php','seminar_form.php','seminar_assign.php'])) ? 'active' : '' ?>">
          <i class="fa fa-calendar-alt fa-fw"></i> Seminars
        </a>
      </li>
      <li class="nav-item mt-2">
        <small class="sidebar-label px-3">REPORTS</small>
      </li>
      <li class="nav-item">
        <a href="<?= BASE_URL ?>/admin/registrations.php" class="nav-link sidebar-link <?= (basename($_SERVER['PHP_SELF']) === 'registrations.php') ? 'active' : '' ?>">
          <i class="fa fa-users fa-fw"></i> Registrations
        </a>
      </li>
      <li class="nav-item mt-auto pt-3">
        <a href="<?= BASE_URL ?>/" class="nav-link sidebar-link" target="_blank">
          <i class="fa fa-globe fa-fw"></i> Public Site
        </a>
      </li>
    </ul>
    <hr class="border-secondary mx-3 my-0">
    <div class="p-3">
      <div class="d-flex align-items-center gap-2 mb-2">
        <div class="avatar-sm bg-warning rounded-circle d-flex align-items-center justify-content-center">
          <i class="fa fa-user text-dark"></i>
        </div>
        <div>
          <div class="text-white small fw-medium"><?= e(currentUserName()) ?></div>
          <small class="text-muted">Administrator</small>
        </div>
      </div>
      <a href="<?= BASE_URL ?>/admin/logout.php" class="btn btn-sm btn-outline-danger w-100">
        <i class="fa fa-sign-out-alt me-1"></i> Logout
      </a>
    </div>
  </nav>

  <!-- Page content -->
  <div id="page-content-wrapper" class="flex-grow-1 overflow-auto">
    <div class="admin-topbar d-flex align-items-center px-4 py-2 gap-3">
      <button class="btn btn-sm btn-outline-secondary" id="sidebarToggle">
        <i class="fa fa-bars"></i>
      </button>
      <nav aria-label="breadcrumb" class="mb-0">
        <ol class="breadcrumb mb-0 small">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/index.php">Admin</a></li>
          <?php if (!empty($breadcrumb)): foreach ($breadcrumb as $bc): ?>
          <li class="breadcrumb-item <?= $bc['active'] ?? false ? 'active' : '' ?>">
            <?= isset($bc['url']) ? '<a href="' . e($bc['url']) . '">' . e($bc['label']) . '</a>' : e($bc['label']) ?>
          </li>
          <?php endforeach; endif; ?>
        </ol>
      </nav>
    </div>
    <div class="p-4">
