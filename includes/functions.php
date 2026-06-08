<?php
require_once __DIR__ . '/db.php';

// ── Sanitisation ─────────────────────────────────────────────
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function sanitize(string $s): string {
    return trim(strip_tags($s));
}

// ── Flash messages ────────────────────────────────────────────
function setFlash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function renderFlash(): void {
    $flash = getFlash();
    if (!$flash) return;
    $map = ['success' => 'success', 'error' => 'danger', 'info' => 'info', 'warning' => 'warning'];
    $cls = $map[$flash['type']] ?? 'info';
    echo '<div class="alert alert-' . $cls . ' alert-dismissible fade show" role="alert">'
        . e($flash['message'])
        . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}

// ── Seminars ──────────────────────────────────────────────────
function getAllSeminars(string $status = ''): array {
    $db = getDB();
    $sql = 'SELECT s.*, u.name AS creator_name,
                   (SELECT COUNT(*) FROM registrations r WHERE r.seminar_id = s.id) AS reg_count
            FROM seminars s LEFT JOIN users u ON u.id = s.created_by';
    $params = [];
    if ($status) {
        $sql .= ' WHERE s.status = ?';
        $params[] = $status;
    }
    $sql .= ' ORDER BY s.seminar_date DESC, s.seminar_time DESC';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getSeminarById(int $id): ?array {
    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT s.*, u.name AS creator_name,
                (SELECT COUNT(*) FROM registrations r WHERE r.seminar_id = s.id) AS reg_count
         FROM seminars s LEFT JOIN users u ON u.id = s.created_by
         WHERE s.id = ? LIMIT 1'
    );
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function getSeminarTeachers(int $seminarId): array {
    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT u.id, u.name, u.email, u.department
         FROM seminar_teachers st JOIN users u ON u.id = st.teacher_id
         WHERE st.seminar_id = ?'
    );
    $stmt->execute([$seminarId]);
    return $stmt->fetchAll();
}

// ── Teachers ─────────────────────────────────────────────────
function getAllTeachers(): array {
    $db   = getDB();
    $stmt = $db->query("SELECT * FROM users WHERE role = 'teacher' ORDER BY name");
    return $stmt->fetchAll();
}

function getTeacherById(int $id): ?array {
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'teacher' LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

// ── Registrations ─────────────────────────────────────────────
function getRegistrationsBySeminar(int $seminarId): array {
    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT * FROM registrations WHERE seminar_id = ? ORDER BY registration_date ASC'
    );
    $stmt->execute([$seminarId]);
    return $stmt->fetchAll();
}

function isAlreadyRegistered(int $seminarId, string $email): bool {
    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT id FROM registrations WHERE seminar_id = ? AND student_email = ? LIMIT 1'
    );
    $stmt->execute([$seminarId, $email]);
    return (bool)$stmt->fetch();
}

function getSeminarCapacityInfo(int $seminarId): array {
    $db      = getDB();
    $stmt    = $db->prepare('SELECT capacity FROM seminars WHERE id = ? LIMIT 1');
    $stmt->execute([$seminarId]);
    $row     = $stmt->fetch();
    $capacity = (int)($row['capacity'] ?? 0);

    $stmt2 = $db->prepare('SELECT COUNT(*) FROM registrations WHERE seminar_id = ?');
    $stmt2->execute([$seminarId]);
    $registered = (int)$stmt2->fetchColumn();

    return [
        'capacity'   => $capacity,
        'registered' => $registered,
        'available'  => max(0, $capacity - $registered),
    ];
}

// ── Teacher's seminars ────────────────────────────────────────
function getSeminarsByTeacher(int $teacherId): array {
    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT s.*,
                (SELECT COUNT(*) FROM registrations r WHERE r.seminar_id = s.id) AS reg_count
         FROM seminars s
         JOIN seminar_teachers st ON st.seminar_id = s.id
         WHERE st.teacher_id = ?
         ORDER BY s.seminar_date DESC'
    );
    $stmt->execute([$teacherId]);
    return $stmt->fetchAll();
}

// ── Dashboard stats ───────────────────────────────────────────
function getDashboardStats(): array {
    $db = getDB();
    return [
        'seminars'      => (int)$db->query("SELECT COUNT(*) FROM seminars")->fetchColumn(),
        'upcoming'      => (int)$db->query("SELECT COUNT(*) FROM seminars WHERE status='upcoming'")->fetchColumn(),
        'teachers'      => (int)$db->query("SELECT COUNT(*) FROM users WHERE role='teacher'")->fetchColumn(),
        'registrations' => (int)$db->query("SELECT COUNT(*) FROM registrations")->fetchColumn(),
    ];
}

// ── Helpers ───────────────────────────────────────────────────
function formatDate(string $date): string {
    return date('d M Y', strtotime($date));
}

function formatTime(string $time): string {
    return date('h:i A', strtotime($time));
}

function statusBadge(string $status): string {
    $map = [
        'upcoming'  => 'primary',
        'ongoing'   => 'success',
        'completed' => 'secondary',
        'cancelled' => 'danger',
    ];
    $cls = $map[$status] ?? 'light';
    return '<span class="badge bg-' . $cls . '">' . ucfirst($status) . '</span>';
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function csrfToken(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid CSRF token.');
    }
}
