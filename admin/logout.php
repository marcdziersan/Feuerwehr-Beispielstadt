<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';

unset($_SESSION[SESSION_USER_KEY]);
session_regenerate_id(true);
flash('success', 'Du wurdest abgemeldet.');
redirect('login.php');
