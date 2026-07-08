<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';
$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('messages.php');
}

verify_csrf();

$id = (int) ($_POST['id'] ?? 0);
$action = normalize_line((string) ($_POST['action'] ?? ''), 20);

if ($id <= 0) {
    flash('error', 'Ungültige Nachricht.');
    redirect('messages.php');
}

if ($action === 'delete') {
    $stmt = db()->prepare('DELETE FROM contact_messages WHERE id = :id');
    $stmt->execute([':id' => $id]);
    flash('success', 'Nachricht wurde gelöscht.');
    redirect('messages.php');
}

if (in_array($action, ['new', 'read', 'done'], true)) {
    $stmt = db()->prepare('UPDATE contact_messages SET status = :status WHERE id = :id');
    $stmt->execute([':status' => $action, ':id' => $id]);
    flash('success', 'Status wurde aktualisiert.');
    redirect('message_view.php?id=' . $id);
}

flash('error', 'Unbekannte Aktion.');
redirect('messages.php');
