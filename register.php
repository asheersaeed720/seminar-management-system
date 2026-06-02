<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$seminarId = (int)($_GET['seminar_id'] ?? $_POST['seminar_id'] ?? 0);
$seminar   = $seminarId ? getSeminarById($seminarId) : null;

if (!$seminar || $seminar['status'] !== 'upcoming') {
    setFlash('error', 'This seminar is not available for registration.');
    redirect(BASE_URL . '/seminars.php');
}

$cap    = getSeminarCapacityInfo($seminarId);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name   = sanitize($_POST['student_name']  ?? '');
    $email  = sanitize($_POST['student_email'] ?? '');
    $phone  = sanitize($_POST['student_phone'] ?? '');
    $roll   = sanitize($_POST['student_roll']  ?? '');
    $dept   = sanitize($_POST['department']    ?? '');

    if (!$name)                             $errors[] = 'Full name is required.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if ($cap['available'] <= 0)             $errors[] = 'Sorry, this seminar is fully booked.';
    if (isAlreadyRegistered($seminarId, $email)) $errors[] = 'This email is already registered for this seminar.';

    if (empty($errors)) {
        $db   = getDB();
        $stmt = $db->prepare(
            'INSERT INTO registrations (seminar_id, student_name, student_email, student_phone, student_roll, department)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$seminarId, $name, $email, $phone, $roll, $dept]);
        $regId = $db->lastInsertId();
        redirect(BASE_URL . '/success.php?id=' . $regId);
    }
}

$pageTitle = 'Register — ' . $seminar['title'];
require_once __DIR__ . '/includes/public_header.php';
?>

<div class="container py-5" style="max-width:680px">
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/seminar.php?id=<?= $seminarId ?>">Seminar</a></li>
      <li class="breadcrumb-item active">Register</li>
    </ol>
  </nav>

  <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="card-header p-4" style="background:linear-gradient(120deg,var(--primary),#2d5fa6)">
      <small class="text-white opacity-75"><i class="fa fa-calendar me-1"></i><?= formatDate($seminar['seminar_date']) ?> at <?= formatTime($seminar['seminar_time']) ?></small>
      <h2 class="h4 text-white fw-bold mt-1 mb-0"><?= e($seminar['title']) ?></h2>
      <?php if ($seminar['venue']): ?>
      <small class="text-white opacity-75"><i class="fa fa-map-marker-alt me-1"></i><?= e($seminar['venue']) ?></small>
      <?php endif; ?>
    </div>
    <div class="card-body p-4">

      <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($errors as $e): echo '<li>' . e($e) . '</li>'; endforeach; ?></ul>
      </div>
      <?php endif; ?>

      <?php if ($cap['available'] <= 0): ?>
      <div class="alert alert-danger text-center">
        <i class="fa fa-ban fa-2x mb-2"></i><br>
        This seminar is fully booked. No more registrations are accepted.
      </div>
      <?php else: ?>
      <p class="text-muted small mb-4">
        <i class="fa fa-info-circle me-1 text-primary"></i>
        <strong><?= $cap['available'] ?></strong> seats remaining out of <?= $seminar['capacity'] ?>.
      </p>

      <form method="POST" id="registrationForm" novalidate>
        <input type="hidden" name="seminar_id" value="<?= $seminarId ?>">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="row g-3">
          <div class="col-12 mb-3">
            <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="student_name" class="form-control"
                   placeholder="Enter your full name"
                   value="<?= e($_POST['student_name'] ?? '') ?>" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
            <input type="email" name="student_email" class="form-control"
                   placeholder="you@example.com"
                   value="<?= e($_POST['student_email'] ?? '') ?>" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-medium">Phone Number</label>
            <input type="tel" name="student_phone" class="form-control"
                   placeholder="+1 234 567 8900"
                   value="<?= e($_POST['student_phone'] ?? '') ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-medium">Roll / Student ID</label>
            <input type="text" name="student_roll" class="form-control"
                   placeholder="e.g. CS-2021-001"
                   value="<?= e($_POST['student_roll'] ?? '') ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-medium">Department</label>
            <select name="department" class="form-select">
              <option value="">— Select Department —</option>
              <?php
              $departments = [
                  'Computer Science',
                  'Information Technology',
                  'Software Engineering',
                  'Electrical Engineering',
                  'Mechanical Engineering',
                  'Civil Engineering',
                  'Business Administration',
                  'Economics',
                  'Mathematics',
                  'Physics',
                  'Chemistry',
                  'Biology',
                  'Psychology',
                  'Education',
                  'Other',
              ];
              $selectedDept = $_POST['department'] ?? '';
              foreach ($departments as $d): ?>
              <option value="<?= e($d) ?>" <?= $selectedDept === $d ? 'selected' : '' ?>><?= e($d) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="d-grid mt-2">
          <button type="submit" class="btn btn-accent btn-lg fw-semibold">
            <i class="fa fa-user-plus me-2"></i>Complete Registration
          </button>
        </div>
      </form>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/public_footer.php'; ?>
