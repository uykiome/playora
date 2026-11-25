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

// ---------------------------------------------------------------------------
// Roles / sessions
// ---------------------------------------------------------------------------

function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

function require_admin(): void
{
    if (!is_admin()) {
        $_SESSION['flash'] = 'Доступ только для администратора.';
        redirect(is_logged_in() ? 'index.php' : 'login.php');
    }
}

/** Starts a logged-in session for a row of the users table. */
function start_user_session(array $user, string $flashKey): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = (string) $user['username'];
    $_SESSION['email'] = (string) $user['email'];
    $_SESSION['role'] = ($user['role'] ?? 'user') === 'admin' ? 'admin' : 'user';
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['flash'] = t($flashKey);
}

// ---------------------------------------------------------------------------
// Game images
// ---------------------------------------------------------------------------

/** Public URL for a stored image: absolute URLs are kept, local paths are relative to the site root. */
function image_public_url(string $path): string
{
    return preg_match('#^https?://#i', $path) ? $path : ltrim($path, '/');
}

/** Cover candidates in priority order: uploaded cover, then several Steam CDN mirrors. */
function cover_sources(array $game, string $kind = 'header'): array
{
    $list = [];
    if (!empty($game['cover_image'])) {
        $list[] = image_public_url((string) $game['cover_image']);
    }
    if (!empty($game['steam_app_id'])) {
        $id = (int) $game['steam_app_id'];
        $file = $kind === 'hero' ? 'library_hero.jpg' : 'header.jpg';
        $list[] = "https://cdn.cloudflare.steamstatic.com/steam/apps/{$id}/{$file}";
        $list[] = "https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/{$id}/{$file}";
        $list[] = "https://cdn.akamai.steamstatic.com/steam/apps/{$id}/{$file}";
        $list[] = "https://steamcdn-a.akamaihd.net/steam/apps/{$id}/{$file}";
    }
    return $list;
}

function has_cover(array $game): bool
{
    return !empty($game['cover_image']) || !empty($game['steam_app_id']);
}

/** <img> that automatically tries the next mirror if the image fails to load. */
function cover_img(array $game, string $kind = 'header'): string
{
    $sources = cover_sources($game, $kind);
    if (!$sources) {
        return '';
    }
    $first = array_shift($sources);
    return '<img class="cover-img" src="' . e($first) . '" data-fallbacks="' . e(implode('|', $sources))
        . '" alt="" loading="lazy" decoding="async" referrerpolicy="no-referrer" onerror="pxCoverFail(this)">';
}

function game_genres(): array
{
    return ['action', 'combat', 'horror', 'adventure', 'rpg', 'multiplayer', 'sandbox', 'simulation', 'strategy'];
}
