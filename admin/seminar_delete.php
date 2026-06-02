<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(BASE_URL . '/admin/seminars.php');
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    getDB()->prepare('DELETE FROM seminars WHERE id = ?')->execute([$id]);
    setFlash('success', 'Seminar deleted.');
}
redirect(BASE_URL . '/admin/seminars.php');
