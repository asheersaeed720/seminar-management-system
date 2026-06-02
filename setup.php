<?php
/**
 * University Seminar Management System — One-time Setup Script
 * Run this ONCE via browser: http://localhost/seminar-management-system/setup.php
 * DELETE this file after setup is complete.
 */

// Prevent running setup twice with a simple lock
$lockFile = __DIR__ . '/.setup_done';
if (file_exists($lockFile)) {
    die('<h2 style="font-family:sans-serif;color:red">Setup already completed. This file cannot be run again.<br>If you need to re-run setup, delete the .setup_done file.</h2>');
}

require_once __DIR__ . '/includes/config.php';

$errors   = [];
$messages = [];

try {
    // Connect without selecting a database to create it
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");
    $messages[] = '✔ Database <strong>' . DB_NAME . '</strong> created (or already exists).';

    // Create tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name          VARCHAR(100)  NOT NULL,
        email         VARCHAR(100)  NOT NULL UNIQUE,
        password      VARCHAR(255)  NOT NULL,
        role          ENUM('admin','teacher') NOT NULL DEFAULT 'teacher',
        phone         VARCHAR(25),
        department    VARCHAR(120),
        created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    $messages[] = '✔ Table <strong>users</strong> ready.';

    $pdo->exec("CREATE TABLE IF NOT EXISTS seminars (
        id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title          VARCHAR(200)  NOT NULL,
        description    TEXT,
        speaker        VARCHAR(150),
        seminar_date   DATE          NOT NULL,
        seminar_time   TIME          NOT NULL,
        venue          VARCHAR(200),
        capacity       INT UNSIGNED  DEFAULT 50,
        status         ENUM('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
        created_by     INT UNSIGNED,
        created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    )");
    $messages[] = '✔ Table <strong>seminars</strong> ready.';

    $pdo->exec("CREATE TABLE IF NOT EXISTS seminar_teachers (
        id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        seminar_id  INT UNSIGNED NOT NULL,
        teacher_id  INT UNSIGNED NOT NULL,
        assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_seminar_teacher (seminar_id, teacher_id),
        FOREIGN KEY (seminar_id)  REFERENCES seminars(id) ON DELETE CASCADE,
        FOREIGN KEY (teacher_id)  REFERENCES users(id)    ON DELETE CASCADE
    )");
    $messages[] = '✔ Table <strong>seminar_teachers</strong> ready.';

    $pdo->exec("CREATE TABLE IF NOT EXISTS registrations (
        id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        seminar_id        INT UNSIGNED  NOT NULL,
        student_name      VARCHAR(100)  NOT NULL,
        student_email     VARCHAR(100)  NOT NULL,
        student_phone     VARCHAR(25),
        student_roll      VARCHAR(50),
        department        VARCHAR(120),
        registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (seminar_id) REFERENCES seminars(id) ON DELETE CASCADE
    )");
    $messages[] = '✔ Table <strong>registrations</strong> ready.';

    $pdo->exec("CREATE TABLE IF NOT EXISTS attendance (
        id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        registration_id INT UNSIGNED NOT NULL,
        seminar_id      INT UNSIGNED NOT NULL,
        status          ENUM('present','absent') NOT NULL DEFAULT 'absent',
        marked_by       INT UNSIGNED,
        marked_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_attendance (registration_id, seminar_id),
        FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
        FOREIGN KEY (seminar_id)      REFERENCES seminars(id)      ON DELETE CASCADE,
        FOREIGN KEY (marked_by)       REFERENCES users(id)         ON DELETE SET NULL
    )");
    $messages[] = '✔ Table <strong>attendance</strong> ready.';

    // Seed admin user
    $adminEmail    = 'admin@university.edu';
    $adminPassword = 'Admin@123';
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND role = ?');
    $stmt->execute([$adminEmail, 'admin']);
    if (!$stmt->fetch()) {
        $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')")
            ->execute(['System Admin', $adminEmail, password_hash($adminPassword, PASSWORD_DEFAULT)]);
        $messages[] = '✔ Admin user created: <strong>' . $adminEmail . '</strong> / password: <strong>' . $adminPassword . '</strong>';
    } else {
        $messages[] = 'ℹ Admin user already exists.';
    }

    // Seed a demo teacher
    $teacherEmail = 'teacher@university.edu';
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$teacherEmail]);
    if (!$stmt->fetch()) {
        $pdo->prepare("INSERT INTO users (name, email, password, role, department) VALUES (?, ?, ?, 'teacher', ?)")
            ->execute(['Dr. Jane Smith', $teacherEmail, password_hash('Teacher@123', PASSWORD_DEFAULT), 'Computer Science']);
        $messages[] = '✔ Demo teacher created: <strong>' . $teacherEmail . '</strong> / password: <strong>Teacher@123</strong>';
    }

    // Seed a demo seminar
    $stmt = $pdo->query('SELECT id FROM seminars LIMIT 1');
    if (!$stmt->fetch()) {
        $stmt2 = $pdo->query("SELECT id FROM users WHERE role='admin' LIMIT 1");
        $adminId = $stmt2->fetchColumn();
        $pdo->prepare(
            "INSERT INTO seminars (title, description, speaker, seminar_date, seminar_time, venue, capacity, status, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'upcoming', ?)"
        )->execute([
            'Introduction to Artificial Intelligence',
            'An in-depth overview of AI fundamentals, machine learning, and real-world applications in academia and industry.',
            'Dr. Jane Smith',
            date('Y-m-d', strtotime('+7 days')),
            '10:00:00',
            'Auditorium A, Main Campus',
            80,
            $adminId,
        ]);
        $messages[] = '✔ Demo seminar created.';
    }

    // Create lock file
    file_put_contents($lockFile, date('Y-m-d H:i:s'));

} catch (PDOException $e) {
    $errors[] = 'Database error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Setup — University Seminar System</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>body{font-family:'Poppins',sans-serif;background:#f0f2f5;}</style>
</head>
<body>
<div class="container py-5" style="max-width:640px">
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-5">
      <h1 class="h3 fw-bold mb-4 text-center">
        <i class="fa fa-graduation-cap"></i> System Setup
      </h1>

      <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <h6 class="fw-bold">Setup failed:</h6>
        <ul class="mb-0"><?php foreach ($errors as $e): echo '<li>' . htmlspecialchars($e) . '</li>'; endforeach; ?></ul>
      </div>
      <?php else: ?>
      <div class="alert alert-success">
        <h6 class="fw-bold mb-2">✔ Setup completed successfully!</h6>
        <ul class="mb-0 small"><?php foreach ($messages as $m): echo '<li>' . $m . '</li>'; endforeach; ?></ul>
      </div>

      <div class="card bg-warning bg-opacity-10 border-warning mt-3 p-3 small">
        <strong>⚠ Security Notice:</strong> Delete <code>setup.php</code> from your server now. It contains default passwords and must not be accessible in production.
      </div>

      <div class="mt-4 d-flex flex-column gap-2">
        <a href="/seminar-management-system/" class="btn btn-outline-primary">
          🌐 Go to Public Site
        </a>
        <a href="/seminar-management-system/admin/login.php" class="btn btn-primary">
          🔐 Admin Login (admin@university.edu / Admin@123)
        </a>
        <a href="/seminar-management-system/teacher/login.php" class="btn btn-success">
          👨‍🏫 Teacher Login (teacher@university.edu / Teacher@123)
        </a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</body>
</html>
