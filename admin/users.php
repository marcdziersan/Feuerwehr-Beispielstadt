<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';
$user = require_admin();

$stmt = db()->query('SELECT id, created_at, username, display_name, email, role, is_active, last_login_at FROM admin_users ORDER BY id ASC');
$users = $stmt->fetchAll();

render_header('Benutzerverwaltung', 'users');
?>
<div class="actions" style="margin-bottom:18px">
  <a class="btn" href="user_edit.php">Benutzer anlegen</a>
</div>

<div class="table-wrap">
  <table>
    <thead><tr><th>ID</th><th>Benutzer</th><th>Rolle</th><th>Status</th><th>Letzter Login</th><th>Aktion</th></tr></thead>
    <tbody>
      <?php foreach ($users as $item): ?>
      <tr>
        <td>#<?= e((string) $item['id']) ?></td>
        <td><strong><?= e((string) $item['display_name']) ?></strong><br><span class="muted"><?= e((string) $item['username']) ?> · <?= e((string) ($item['email'] ?? '')) ?></span></td>
        <td><?= e((string) $item['role']) ?></td>
        <td><?= ((int) $item['is_active'] === 1) ? 'aktiv' : 'gesperrt' ?></td>
        <td><?= e((string) ($item['last_login_at'] ?? '—')) ?></td>
        <td><a class="btn small blue" href="user_edit.php?id=<?= e((string) $item['id']) ?>">Bearbeiten</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php render_footer(); ?>
