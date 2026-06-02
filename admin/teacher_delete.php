<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(BASE_URL . '/admin/teachers.php');
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $db = getDB();
    $db->prepare("DELETE FROM users WHERE id = ? AND role = 'teacher'")->execute([$id]);
    setFlash('success', 'Teacher deleted.');
}
redirect(BASE_URL . '/admin/teachers.php');
