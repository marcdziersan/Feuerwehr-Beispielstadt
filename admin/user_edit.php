<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';
$current = require_admin();

$id = (int) ($_GET['id'] ?? 0);
$isEdit = $id > 0;
$userRow = [
    'id' => 0,
    'username' => '',
    'display_name' => '',
    'email' => '',
    'role' => 'editor',
    'is_active' => 1,
];

if ($isEdit) {
    $stmt = db()->prepare('SELECT id, username, display_name, email, role, is_active FROM admin_users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $found = $stmt->fetch();

    if (!$found) {
        flash('error', 'Benutzer nicht gefunden.');
        redirect('users.php');
    }

    $userRow = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = normalize_line((string) ($_POST['username'] ?? ''), 80);
    $displayName = normalize_line((string) ($_POST['display_name'] ?? ''), 120);
    $emailRaw = normalize_line((string) ($_POST['email'] ?? ''), 190);
    $role = normalize_line((string) ($_POST['role'] ?? 'editor'), 20);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $password = (string) ($_POST['password'] ?? '');

    if (!preg_match('/^[a-zA-Z0-9._-]{3,80}$/', $username)) {
        flash('error', 'Benutzername: 3–80 Zeichen, nur Buchstaben, Zahlen, Punkt, Unterstrich oder Bindestrich.');
    } elseif ($displayName === '') {
        flash('error', 'Bitte Anzeigenamen angeben.');
    } elseif ($emailRaw !== '' && !filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Bitte gültige E-Mail-Adresse angeben.');
    } elseif (!in_array($role, ['admin', 'editor'], true)) {
        flash('error', 'Ungültige Rolle.');
    } elseif (!$isEdit && mb_strlen($password, 'UTF-8') < 12) {
        flash('error', 'Neue Benutzer brauchen ein Passwort mit mindestens 12 Zeichen.');
    } elseif ($password !== '' && mb_strlen($password, 'UTF-8') < 12) {
        flash('error', 'Das neue Passwort muss mindestens 12 Zeichen lang sein.');
    } else {
        if ($isEdit && (int) $userRow['id'] === (int) $current['id'] && $role !== 'admin') {
            flash('error', 'Du kannst dir nicht selbst die Adminrolle entziehen.');
            redirect('user_edit.php?id=' . $id);
        }

        if ($isEdit && (int) $userRow['id'] === (int) $current['id'] && $isActive === 0) {
            flash('error', 'Du kannst deinen eigenen Benutzer nicht sperren.');
            redirect('user_edit.php?id=' . $id);
        }

        if ($isEdit) {
            if ($password !== '') {
                $stmt = db()->prepare(
                    'UPDATE admin_users
                     SET updated_at = :updated_at, username = :username, display_name = :display_name, email = :email,
                         role = :role, is_active = :is_active, password_hash = :password_hash
                     WHERE id = :id'
                );
                $params = [
                    ':updated_at' => gmdate('c'),
                    ':username' => $username,
                    ':display_name' => $displayName,
                    ':email' => $emailRaw !== '' ? $emailRaw : null,
                    ':role' => $role,
                    ':is_active' => $isActive,
                    ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    ':id' => $id,
                ];
            } else {
                $stmt = db()->prepare(
                    'UPDATE admin_users
                     SET updated_at = :updated_at, username = :username, display_name = :display_name, email = :email,
                         role = :role, is_active = :is_active
                     WHERE id = :id'
                );
                $params = [
                    ':updated_at' => gmdate('c'),
                    ':username' => $username,
                    ':display_name' => $displayName,
                    ':email' => $emailRaw !== '' ? $emailRaw : null,
                    ':role' => $role,
                    ':is_active' => $isActive,
                    ':id' => $id,
                ];
            }

            try {
                $stmt->execute($params);
                flash('success', 'Benutzer wurde gespeichert.');
                redirect('users.php');
            } catch (PDOException $e) {
                flash('error', 'Benutzername ist bereits vergeben.');
            }
        } else {
            $stmt = db()->prepare(
                'INSERT INTO admin_users
                    (created_at, updated_at, username, display_name, email, password_hash, role, is_active)
                 VALUES
                    (:created_at, :updated_at, :username, :display_name, :email, :password_hash, :role, :is_active)'
            );

            try {
                $stmt->execute([
                    ':created_at' => gmdate('c'),
                    ':updated_at' => gmdate('c'),
                    ':username' => $username,
                    ':display_name' => $displayName,
                    ':email' => $emailRaw !== '' ? $emailRaw : null,
                    ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    ':role' => $role,
                    ':is_active' => $isActive,
                ]);

                flash('success', 'Benutzer wurde angelegt.');
                redirect('users.php');
            } catch (PDOException $e) {
                flash('error', 'Benutzername ist bereits vergeben.');
            }
        }
    }

    $userRow = [
        'id' => $id,
        'username' => $username,
        'display_name' => $displayName,
        'email' => $emailRaw,
        'role' => $role,
        'is_active' => $isActive,
    ];
}

render_header($isEdit ? 'Benutzer bearbeiten' : 'Benutzer anlegen', 'users');
?>
<div class="card">
  <form class="form" method="post">
    <?= csrf_field() ?>
    <div class="field"><label for="username">Benutzername</label><input id="username" name="username" value="<?= e((string) $userRow['username']) ?>" required></div>
    <div class="field"><label for="display_name">Anzeigename</label><input id="display_name" name="display_name" value="<?= e((string) $userRow['display_name']) ?>" required></div>
    <div class="field"><label for="email">E-Mail</label><input id="email" name="email" type="email" value="<?= e((string) ($userRow['email'] ?? '')) ?>"></div>
    <div class="field">
      <label for="role">Rolle</label>
      <select id="role" name="role">
        <option value="editor" <?= $userRow['role'] === 'editor' ? 'selected' : '' ?>>Editor</option>
        <option value="admin" <?= $userRow['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
      </select>
    </div>
    <div class="field">
      <label for="password"><?= $isEdit ? 'Neues Passwort optional' : 'Passwort' ?></label>
      <input id="password" name="password" type="password" <?= $isEdit ? '' : 'required' ?> autocomplete="new-password">
    </div>
    <label><input type="checkbox" name="is_active" value="1" <?= (int) $userRow['is_active'] === 1 ? 'checked' : '' ?>> Benutzer aktiv</label>
    <div class="actions">
      <button class="btn" type="submit">Speichern</button>
      <a class="btn secondary" href="users.php">Abbrechen</a>
      <?php if ($isEdit && (int) $userRow['id'] !== (int) $current['id']): ?>
        <button class="btn danger" type="submit" formaction="user_delete.php" formmethod="post" name="id" value="<?= e((string) $userRow['id']) ?>" onclick="return confirm('Benutzer wirklich löschen?');">Löschen</button>
      <?php endif; ?>
    </div>
  </form>
</div>
<?php render_footer(); ?>
