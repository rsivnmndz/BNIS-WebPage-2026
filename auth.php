<?php
declare(strict_types=1);

function auth_boot() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function is_admin_authed(): bool {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_username']);
}
