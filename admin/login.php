<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';

if (users_count() === 0) {
    redirect('setup.php');
}

if (current_user()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = normalize_line((string) ($_POST['username'] ?? ''), 80);
    $password = (string) ($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT * FROM admin_users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user && (int) $user['is_active'] === 1 && password_verify($password, (string) $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION[SESSION_USER_KEY] = (int) $user['id'];

        $upd = db()->prepare('UPDATE admin_users SET last_login_at = :last_login_at WHERE id = :id');
        $upd->execute([':last_login_at' => gmdate('c'), ':id' => (int) $user['id']]);

        redirect('dashboard.php');
    }

    flash('error', 'Benutzername oder Passwort ist falsch.');
}

?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | FF Admin</title>
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="login-page">
  <main class="login-card">
    <h1>🔒 Admin-Login</h1>
    <p>Geschützter Bereich für Kontaktanfragen und Benutzerverwaltung.</p>
    <?php foreach (flashes() as $flash): ?>
      <div class="alert <?= e($flash['type'] === 'error' ? 'error' : 'success') ?>"><?= e((string) $flash['message']) ?></div>
    <?php endforeach; ?>
    <form class="form" method="post" action="login.php">
      <?= csrf_field() ?>
      <div class="field">
        <label for="username">Benutzername</label>
        <input id="username" name="username" required autocomplete="username">
      </div>
      <div class="field">
        <label for="password">Passwort</label>
        <input id="password" name="password" type="password" required autocomplete="current-password">
      </div>
      <button class="btn" type="submit">Einloggen</button>
      <a class="lock-note" href="../kontakt.php">Zurück zur Kontaktseite</a>
    </form>
  </main>
</body>
</html>
