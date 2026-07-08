<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';

if (users_count() > 0) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = normalize_line((string) ($_POST['username'] ?? ''), 80);
    $displayName = normalize_line((string) ($_POST['display_name'] ?? ''), 120);
    $emailRaw = normalize_line((string) ($_POST['email'] ?? ''), 190);
    $password = (string) ($_POST['password'] ?? '');
    $passwordRepeat = (string) ($_POST['password_repeat'] ?? '');

    if (!preg_match('/^[a-zA-Z0-9._-]{3,80}$/', $username)) {
        flash('error', 'Benutzername: 3–80 Zeichen, nur Buchstaben, Zahlen, Punkt, Unterstrich oder Bindestrich.');
    } elseif ($displayName === '') {
        flash('error', 'Bitte Anzeigenamen angeben.');
    } elseif ($emailRaw !== '' && !filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Bitte eine gültige E-Mail-Adresse angeben.');
    } elseif (mb_strlen($password, 'UTF-8') < 12) {
        flash('error', 'Das Passwort muss mindestens 12 Zeichen lang sein.');
    } elseif ($password !== $passwordRepeat) {
        flash('error', 'Die Passwörter stimmen nicht überein.');
    } else {
        $stmt = db()->prepare(
            'INSERT INTO admin_users
                (created_at, updated_at, username, display_name, email, password_hash, role, is_active)
             VALUES
                (:created_at, :updated_at, :username, :display_name, :email, :password_hash, :role, :is_active)'
        );

        $stmt->execute([
            ':created_at' => gmdate('c'),
            ':updated_at' => gmdate('c'),
            ':username' => $username,
            ':display_name' => $displayName,
            ':email' => $emailRaw !== '' ? $emailRaw : null,
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ':role' => 'admin',
            ':is_active' => 1,
        ]);

        flash('success', 'Administrator wurde angelegt. Du kannst dich jetzt anmelden.');
        redirect('login.php');
    }
}

?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ersteinrichtung | FF Admin</title>
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="login-page">
  <main class="login-card">
    <h1>Ersteinrichtung</h1>
    <p>Lege den ersten Administrator an. Diese Seite ist danach automatisch gesperrt.</p>
    <?php foreach (flashes() as $flash): ?>
      <div class="alert <?= e($flash['type'] === 'error' ? 'error' : 'success') ?>"><?= e((string) $flash['message']) ?></div>
    <?php endforeach; ?>
    <form class="form" method="post" action="setup.php">
      <?= csrf_field() ?>
      <div class="field"><label for="username">Benutzername</label><input id="username" name="username" required autocomplete="username"></div>
      <div class="field"><label for="display_name">Anzeigename</label><input id="display_name" name="display_name" required></div>
      <div class="field"><label for="email">E-Mail optional</label><input id="email" name="email" type="email" autocomplete="email"></div>
      <div class="field"><label for="password">Passwort mindestens 12 Zeichen</label><input id="password" name="password" type="password" required autocomplete="new-password"></div>
      <div class="field"><label for="password_repeat">Passwort wiederholen</label><input id="password_repeat" name="password_repeat" type="password" required autocomplete="new-password"></div>
      <button class="btn" type="submit">Administrator anlegen</button>
    </form>
  </main>
</body>
</html>
