<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';
$user = require_login();

$id = (int) ($_GET['id'] ?? 0);

$stmt = db()->prepare('SELECT * FROM contact_messages WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$msg = $stmt->fetch();

if (!$msg) {
    flash('error', 'Nachricht nicht gefunden.');
    redirect('messages.php');
}

if ((string) $msg['status'] === 'new') {
    $upd = db()->prepare("UPDATE contact_messages SET status = 'read' WHERE id = :id");
    $upd->execute([':id' => $id]);
    $msg['status'] = 'read';
}

render_header('Nachricht #' . $id, 'messages');
?>
<div class="card">
  <div class="actions" style="justify-content:space-between;margin-bottom:18px">
    <a class="btn secondary" href="messages.php">Zurück</a>
    <div class="actions">
      <form class="inline-form" method="post" action="message_action.php">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= e((string) $id) ?>">
        <input type="hidden" name="action" value="new">
        <button class="btn secondary small" type="submit">Als neu</button>
      </form>
      <form class="inline-form" method="post" action="message_action.php">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= e((string) $id) ?>">
        <input type="hidden" name="action" value="done">
        <button class="btn small" type="submit">Erledigt</button>
      </form>
      <form class="inline-form" method="post" action="message_action.php" onsubmit="return confirm('Nachricht wirklich löschen?');">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= e((string) $id) ?>">
        <input type="hidden" name="action" value="delete">
        <button class="btn danger small" type="submit">Löschen</button>
      </form>
    </div>
  </div>

  <p>
    <strong>Datum:</strong> <?= e((string) $msg['created_at']) ?><br>
    <strong>Name:</strong> <?= e((string) $msg['name']) ?><br>
    <strong>E-Mail:</strong> <a href="mailto:<?= e((string) $msg['email']) ?>"><?= e((string) $msg['email']) ?></a><br>
    <strong>Thema:</strong> <?= e((string) $msg['topic']) ?><br>
    <strong>Status:</strong> <span class="badge <?= e((string) $msg['status']) ?>"><?= e((string) $msg['status']) ?></span>
  </p>

  <h2>Nachricht</h2>
  <div class="message-box"><?= e((string) $msg['message']) ?></div>

  <p class="muted" style="margin-top:18px">
    IP wird aus Datenschutzgründen nur gehasht gespeichert: <?= e((string) $msg['ip_hash']) ?><br>
    User-Agent: <?= e((string) $msg['user_agent']) ?>
  </p>
</div>
<?php render_footer(); ?>
