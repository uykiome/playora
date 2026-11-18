<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/lang.php';

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

handle_language_switch();

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
        $_SESSION['flash'] = t('flash.loginToView');
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

/** Builds a link to index.php that keeps the current search, genre and sort. */
function catalog_url(array $params, array $current = []): string
{
    $merged = array_merge($current, $params);
    $clean = [];
    foreach ($merged as $key => $value) {
        if ($value === '' || $value === null) {
            continue;
        }
        if ($key === 'genre' && $value === 'all') {
            continue;
        }
        if ($key === 'sort' && $value === 'rating') {
            continue;
        }
        if ($key === 'page' && (int) $value <= 1) {
            continue;
        }
        $clean[$key] = $value;
    }
    return 'index.php' . ($clean ? '?' . http_build_query($clean) : '');
}

function format_price(float $price): string
{
    return $price === 0.0 ? t('price.free') : number_format($price, 0, '.', ' ') . ' ₸';
}

/** Renders 5 stars (full / empty) for a rating from 0 to 5. */
function stars_html(float $rating): string
{
    $full = (int) round($rating);
    $html = '<span class="stars" aria-hidden="true">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= '<span class="' . ($i <= $full ? 'star-on' : 'star-off') . '">★</span>';
    }
    return $html . '</span>';
}
