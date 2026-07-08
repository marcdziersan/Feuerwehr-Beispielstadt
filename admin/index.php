<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';

if (users_count() === 0) {
    redirect('setup.php');
}

if (current_user()) {
    redirect('dashboard.php');
}

redirect('login.php');
