<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';
$current = require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('users.php');
}

verify_csrf();

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    flash('error', 'Ungültiger Benutzer.');
    redirect('users.php');
}

if ($id === (int) $current['id']) {
    flash('error', 'Du kannst deinen eigenen Benutzer nicht löschen.');
    redirect('users.php');
}

$stmt = db()->prepare('SELECT role, is_active FROM admin_users WHERE id = :id');
$stmt->execute([':id' => $id]);
$user = $stmt->fetch();

if ($user && (string) $user['role'] === 'admin' && (int) $user['is_active'] === 1 && active_admin_count() <= 1) {
    flash('error', 'Der letzte aktive Administrator darf nicht gelöscht werden.');
    redirect('users.php');
}

$stmt = db()->prepare('DELETE FROM admin_users WHERE id = :id');
$stmt->execute([':id' => $id]);

flash('success', 'Benutzer wurde gelöscht.');
redirect('users.php');
