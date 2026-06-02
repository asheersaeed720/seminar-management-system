<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Summary fetch (GET, teacher only)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'summary') {
    if (!isTeacher()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    $seminarId = (int)($_GET['seminar_id'] ?? 0);
    $summary   = getAttendanceSummary($seminarId);
    echo json_encode(['success' => true, ...$summary]);
    exit;
}

// Mark attendance (POST, teacher only)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isTeacher()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// CSRF
$token = $_POST['csrf_token'] ?? '';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$registrationId = (int)($_POST['registration_id'] ?? 0);
$seminarId      = (int)($_POST['seminar_id']      ?? 0);
$status         = $_POST['status'] ?? '';

if (!in_array($status, ['present', 'absent'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

$db = getDB();

// Verify teacher is assigned to this seminar
$stmt = $db->prepare('SELECT 1 FROM seminar_teachers WHERE seminar_id = ? AND teacher_id = ?');
$stmt->execute([$seminarId, currentUserId()]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Not assigned to this seminar']);
    exit;
}

// Verify registration belongs to this seminar
$stmt = $db->prepare('SELECT id FROM registrations WHERE id = ? AND seminar_id = ?');
$stmt->execute([$registrationId, $seminarId]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Registration not found']);
    exit;
}

// Upsert attendance
$db->prepare(
    'INSERT INTO attendance (registration_id, seminar_id, status, marked_by)
     VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE status = VALUES(status), marked_by = VALUES(marked_by), marked_at = NOW()'
)->execute([$registrationId, $seminarId, $status, currentUserId()]);

echo json_encode(['success' => true, 'status' => $status]);
