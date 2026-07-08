<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $https,
    'httponly' => true,
    'samesite' => 'Strict',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');

const APP_NAME = 'FF Admin';
const SESSION_USER_KEY = 'ff_admin_user_id';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $storageDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage';
    if (!is_dir($storageDir) && !mkdir($storageDir, 0750, true)) {
        http_response_code(500);
        exit('Storage-Verzeichnis konnte nicht erstellt werden.');
    }

    $pdo = new PDO('sqlite:' . $storageDir . DIRECTORY_SEPARATOR . 'kontakt.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');

    migrate($pdo);

    return $pdo;
}

function migrate(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS contact_messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            created_at TEXT NOT NULL,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            topic TEXT NOT NULL,
            message TEXT NOT NULL,
            ip_hash TEXT NOT NULL,
            user_agent TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT "new"
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS admin_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            username TEXT NOT NULL UNIQUE,
            display_name TEXT NOT NULL,
            email TEXT,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT "editor",
            is_active INTEGER NOT NULL DEFAULT 1,
            last_login_at TEXT
        )'
    );

    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_admin_users_username ON admin_users(username)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_contact_messages_status ON contact_messages(status)');
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['admin_csrf_token'])) {
        $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['admin_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $posted = (string) ($_POST['csrf_token'] ?? '');
    $stored = (string) ($_SESSION['admin_csrf_token'] ?? '');

    if ($stored === '' || $posted === '' || !hash_equals($stored, $posted)) {
        flash('error', 'Sicherheitsprüfung fehlgeschlagen. Bitte erneut versuchen.');
        redirect('login.php');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flashes(): array
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return is_array($items) ? $items : [];
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function users_count(): int
{
    $stmt = db()->query('SELECT COUNT(*) FROM admin_users');
    return (int) $stmt->fetchColumn();
}

function active_admin_count(): int
{
    $stmt = db()->query("SELECT COUNT(*) FROM admin_users WHERE role = 'admin' AND is_active = 1");
    return (int) $stmt->fetchColumn();
}

function current_user(): ?array
{
    if (empty($_SESSION[SESSION_USER_KEY])) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, username, display_name, email, role, is_active, last_login_at FROM admin_users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int) $_SESSION[SESSION_USER_KEY]]);
    $user = $stmt->fetch();

    if (!$user || (int) $user['is_active'] !== 1) {
        unset($_SESSION[SESSION_USER_KEY]);
        return null;
    }

    return $user;
}

function require_login(): array
{
    if (users_count() === 0) {
        redirect('setup.php');
    }

    $user = current_user();
    if (!$user) {
        redirect('login.php');
    }

    return $user;
}

function require_admin(): array
{
    $user = require_login();

    if (($user['role'] ?? '') !== 'admin') {
        flash('error', 'Für diese Aktion brauchst du Administratorrechte.');
        redirect('dashboard.php');
    }

    return $user;
}

function normalize_line(string $value, int $max = 190): string
{
    $value = trim($value);
    $value = preg_replace('/[\r\n\t]+/u', ' ', $value) ?? '';
    $value = preg_replace('/\s{2,}/u', ' ', $value) ?? '';

    if (mb_strlen($value, 'UTF-8') > $max) {
        $value = mb_substr($value, 0, $max, 'UTF-8');
    }

    return $value;
}

function render_header(string $title, string $active = ''): void
{
    $user = current_user();
    $display = $user ? (string) $user['display_name'] : 'Gast';

    echo '<!doctype html><html lang="de"><head>';
    echo '<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($title) . ' | ' . APP_NAME . '</title>';
    echo '<link rel="stylesheet" href="assets/admin.css">';
    echo '</head><body class="admin-body">';
    echo '<div class="admin-shell">';
    echo '<aside class="admin-sidebar">';
    echo '<a class="admin-brand" href="dashboard.php"><span class="brand-mark">🔥</span><span><strong>FF Admin</strong><small>Kontaktverwaltung</small></span></a>';
    echo '<nav class="admin-menu" aria-label="Admin-Navigation">';
    echo '<a class="' . ($active === 'dashboard' ? 'active' : '') . '" href="dashboard.php">Dashboard</a>';
    echo '<a class="' . ($active === 'messages' ? 'active' : '') . '" href="messages.php">Nachrichten</a>';
    echo '<a class="' . ($active === 'users' ? 'active' : '') . '" href="users.php">Benutzer</a>';
    echo '<a href="../kontakt.php">Kontaktseite</a>';
    echo '<a href="logout.php">Abmelden</a>';
    echo '</nav>';
    echo '</aside>';
    echo '<main class="admin-main">';
    echo '<header class="admin-topbar"><div><p>Angemeldet als</p><strong>' . e($display) . '</strong></div><a class="top-lock" href="logout.php" aria-label="Abmelden">🔒</a></header>';
    echo '<section class="admin-content">';
    echo '<h1>' . e($title) . '</h1>';

    foreach (flashes() as $flash) {
        $type = $flash['type'] === 'error' ? 'error' : 'success';
        echo '<div class="alert ' . e($type) . '">' . e((string) $flash['message']) . '</div>';
    }
}

function render_footer(): void
{
    echo '</section></main></div></body></html>';
}
