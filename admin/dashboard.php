<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';
$user = require_login();

$pdo = db();

$totalMessages = (int) $pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();
$newMessages = (int) $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn();
$doneMessages = (int) $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'done'")->fetchColumn();
$totalUsers = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();

$stmt = $pdo->query('SELECT id, created_at, name, email, topic, status FROM contact_messages ORDER BY id DESC LIMIT 8');
$latest = $stmt->fetchAll();

render_header('Dashboard', 'dashboard');
?>
<div class="grid grid-4">
  <article class="card metric"><strong><?= e((string) $totalMessages) ?></strong><span>Nachrichten gesamt</span></article>
  <article class="card metric"><strong><?= e((string) $newMessages) ?></strong><span>Neue Nachrichten</span></article>
  <article class="card metric"><strong><?= e((string) $doneMessages) ?></strong><span>Erledigt</span></article>
  <article class="card metric"><strong><?= e((string) $totalUsers) ?></strong><span>Benutzer</span></article>
</div>

<section class="card" style="margin-top:18px">
  <h2>Neueste Nachrichten</h2>
  <div class="table-wrap">
    <table>
      <thead><tr><th>ID</th><th>Datum</th><th>Name</th><th>Thema</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($latest as $msg): ?>
        <tr>
          <td>#<?= e((string) $msg['id']) ?></td>
          <td><?= e((string) $msg['created_at']) ?></td>
          <td><?= e((string) $msg['name']) ?><br><span class="muted"><?= e((string) $msg['email']) ?></span></td>
          <td><?= e((string) $msg['topic']) ?></td>
          <td><span class="badge <?= e((string) $msg['status']) ?>"><?= e((string) $msg['status']) ?></span></td>
          <td><a class="btn small blue" href="message_view.php?id=<?= e((string) $msg['id']) ?>">Öffnen</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$latest): ?>
        <tr><td colspan="6">Noch keine Nachrichten vorhanden.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php render_footer(); ?>
