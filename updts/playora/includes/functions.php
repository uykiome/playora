<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        $_SESSION['flash'] = 'Бұл бөлімді көру үшін алдымен жүйеге кіріңіз.';
        header('Location: login.php');
        exit;
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): bool
{
    $submitted = $_POST['csrf_token'] ?? '';
    return is_string($submitted)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $submitted);
}

function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

function flash_message(): ?string
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_string($message) ? $message : null;
}
