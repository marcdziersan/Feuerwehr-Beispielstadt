<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';
$user = require_login();

$status = normalize_line((string) ($_GET['status'] ?? 'all'), 20);
$allowed = ['all', 'new', 'read', 'done'];
if (!in_array($status, $allowed, true)) {
    $status = 'all';
}

if ($status === 'all') {
    $stmt = db()->query('SELECT id, created_at, name, email, topic, status FROM contact_messages ORDER BY id DESC LIMIT 300');
    $messages = $stmt->fetchAll();
} else {
    $stmt = db()->prepare('SELECT id, created_at, name, email, topic, status FROM contact_messages WHERE status = :status ORDER BY id DESC LIMIT 300');
    $stmt->execute([':status' => $status]);
    $messages = $stmt->fetchAll();
}

render_header('Nachrichten', 'messages');
?>
<div class="actions" style="margin-bottom:18px">
  <a class="btn secondary small" href="messages.php">Alle</a>
  <a class="btn secondary small" href="messages.php?status=new">Neu</a>
  <a class="btn secondary small" href="messages.php?status=read">Gelesen</a>
  <a class="btn secondary small" href="messages.php?status=done">Erledigt</a>
</div>

<div class="table-wrap">
  <table>
    <thead><tr><th>ID</th><th>Datum</th><th>Absender</th><th>Thema</th><th>Status</th><th>Aktion</th></tr></thead>
    <tbody>
      <?php foreach ($messages as $msg): ?>
      <tr>
        <td>#<?= e((string) $msg['id']) ?></td>
        <td><?= e((string) $msg['created_at']) ?></td>
        <td><?= e((string) $msg['name']) ?><br><span class="muted"><?= e((string) $msg['email']) ?></span></td>
        <td><?= e((string) $msg['topic']) ?></td>
        <td><span class="badge <?= e((string) $msg['status']) ?>"><?= e((string) $msg['status']) ?></span></td>
        <td><a class="btn small blue" href="message_view.php?id=<?= e((string) $msg['id']) ?>">Öffnen</a></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$messages): ?>
        <tr><td colspan="6">Keine Nachrichten gefunden.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<?php render_footer(); ?>
