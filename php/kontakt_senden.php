<?php
declare(strict_types=1);

/**
 * Sicheres Kontaktformular mit SQLite-Speicherung.
 *
 * Voraussetzungen:
 * - PHP 8.x
 * - PDO SQLite aktiviert
 * - Schreibrechte auf /storage
 *
 * Sicherheitsmaßnahmen:
 * - Session-Cookie-Härtung
 * - CSRF-Token
 * - serverseitig gespeichertes Rechen-Captcha
 * - Honeypot-Feld gegen einfache Bots
 * - Rate-Limiting pro IP-Hash und Session
 * - strikte Methodenkontrolle
 * - Eingabevalidierung + Längenlimits
 * - Prepared Statements gegen SQL-Injection
 * - HTML-Escaping bei Statusausgabe
 * - keine ungeprüfte Mail-Header-Verarbeitung
 */

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

const MAX_NAME_LENGTH = 120;
const MAX_EMAIL_LENGTH = 190;
const MAX_TOPIC_LENGTH = 120;
const MAX_MESSAGE_LENGTH = 5000;
const RATE_LIMIT_SECONDS = 60;

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function fail(string $message, int $status = 400): never
{
    http_response_code($status);
    echo '<!doctype html><html lang="de"><meta charset="utf-8"><title>Kontaktformular</title>';
    echo '<body style="font-family:system-ui;margin:2rem;line-height:1.5">';
    echo '<h1>Kontaktformular</h1>';
    echo '<p>' . e($message) . '</p>';
    echo '<p><a href="../kontakt.php">Zurück zum Formular</a></p>';
    echo '</body></html>';
    exit;
}

function success(string $message): never
{
    echo '<!doctype html><html lang="de"><meta charset="utf-8"><title>Kontaktformular</title>';
    echo '<body style="font-family:system-ui;margin:2rem;line-height:1.5">';
    echo '<h1>Kontaktformular</h1>';
    echo '<p>' . e($message) . '</p>';
    echo '<p><a href="../kontakt.php">Zurück zur Kontaktseite</a></p>';
    echo '</body></html>';
    exit;
}

function normalize_line(string $value): string
{
    $value = trim($value);
    $value = preg_replace('/[\r\n\t]+/u', ' ', $value) ?? '';
    $value = preg_replace('/\s{2,}/u', ' ', $value) ?? '';
    return $value;
}

function limited(string $value, int $maxLength): string
{
    if (mb_strlen($value, 'UTF-8') > $maxLength) {
        fail('Eine Eingabe ist zu lang.');
    }
    return $value;
}

function client_ip_hash(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return hash('sha256', $ip);
}

function database(): PDO
{
    $storageDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage';
    if (!is_dir($storageDir) && !mkdir($storageDir, 0750, true)) {
        fail('Der Speicherordner konnte nicht erstellt werden.', 500);
    }

    $dbFile = $storageDir . DIRECTORY_SEPARATOR . 'kontakt.sqlite';
    $pdo = new PDO('sqlite:' . $dbFile, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');

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
        'CREATE INDEX IF NOT EXISTS idx_contact_messages_created_at
         ON contact_messages(created_at)'
    );

    $pdo->exec(
        'CREATE INDEX IF NOT EXISTS idx_contact_messages_ip_hash
         ON contact_messages(ip_hash)'
    );

    return $pdo;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Ungültige Anfrage.', 405);
}

if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], (string) $_POST['csrf_token'])) {
    fail('Sicherheitsprüfung fehlgeschlagen. Bitte öffne die Kontaktseite als kontakt.php über einen PHP-Server und lade sie neu.');
}

if (!empty($_POST['website'] ?? '')) {
    fail('Die Anfrage wurde abgelehnt.');
}

$captchaInput = trim((string) ($_POST['captcha'] ?? ''));
$captchaExpected = $_SESSION['captcha_result'] ?? null;

if ($captchaExpected === null || !ctype_digit($captchaInput) || (int) $captchaInput !== (int) $captchaExpected) {
    fail('Das Rechen-Captcha wurde falsch gelöst.');
}

$lastSubmit = $_SESSION['last_contact_submit'] ?? 0;
if (is_int($lastSubmit) && time() - $lastSubmit < RATE_LIMIT_SECONDS) {
    fail('Bitte warte kurz, bevor du erneut eine Nachricht sendest.', 429);
}

$name = limited(normalize_line((string) ($_POST['name'] ?? '')), MAX_NAME_LENGTH);
$emailRaw = limited(normalize_line((string) ($_POST['email'] ?? '')), MAX_EMAIL_LENGTH);
$topic = limited(normalize_line((string) ($_POST['topic'] ?? 'Allgemeine Anfrage')), MAX_TOPIC_LENGTH);
$message = trim((string) ($_POST['message'] ?? ''));

if (mb_strlen($message, 'UTF-8') > MAX_MESSAGE_LENGTH) {
    fail('Die Nachricht ist zu lang.');
}

$email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL);

$allowedTopics = [
    'Allgemeine Anfrage',
    'Mitgliedschaft',
    'Jugendfeuerwehr',
    'Brandschutzerziehung',
    'Förderverein',
    'Presse',
];

if ($topic === '') {
    $topic = 'Allgemeine Anfrage';
}

if (!in_array($topic, $allowedTopics, true)) {
    fail('Ungültiges Thema.');
}

if ($name === '' || !$email || $message === '') {
    fail('Bitte Name, gültige E-Mail und Nachricht angeben.');
}

$userAgent = limited(normalize_line((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown')), 500);
$ipHash = client_ip_hash();

try {
    $pdo = database();

    $stmt = $pdo->prepare(
        'INSERT INTO contact_messages
            (created_at, name, email, topic, message, ip_hash, user_agent, status)
         VALUES
            (:created_at, :name, :email, :topic, :message, :ip_hash, :user_agent, :status)'
    );

    $stmt->execute([
        ':created_at' => gmdate('c'),
        ':name' => $name,
        ':email' => $email,
        ':topic' => $topic,
        ':message' => $message,
        ':ip_hash' => $ipHash,
        ':user_agent' => $userAgent,
        ':status' => 'new',
    ]);

    $_SESSION['last_contact_submit'] = time();

    unset($_SESSION['captcha_result']);

    success('Danke. Deine Nachricht wurde sicher gespeichert und kann intern bearbeitet werden.');
} catch (Throwable $exception) {
    error_log('Kontaktformular-Fehler: ' . $exception->getMessage());
    fail('Die Nachricht konnte aktuell nicht gespeichert werden.', 500);
}
