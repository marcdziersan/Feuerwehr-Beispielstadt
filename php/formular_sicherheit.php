<?php
declare(strict_types=1);

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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$captchaA = random_int(2, 12);
$captchaB = random_int(2, 12);
$_SESSION['captcha_result'] = $captchaA + $captchaB;

$csrfToken = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$captchaQuestion = htmlspecialchars($captchaA . ' + ' . $captchaB, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
