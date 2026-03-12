<?php
declare(strict_types=1);

// Simple session-based admin auth (pages + API)

function auth_boot(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'secure' => $secure,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function is_admin_authed(): bool {
    auth_boot();
    return isset($_SESSION['admin_id']) && is_int($_SESSION['admin_id']);
}

function require_admin_page(): void {
    if (is_admin_authed()) return;
    header('Location: admin_login.php');
    exit;
}

function require_admin_api(): void {
    if (is_admin_authed()) return;
    http_response_code(401);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

