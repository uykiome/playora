<?php
// Receives the Google Sign-In response (redirect mode), verifies the ID token and logs the user in.
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

function google_fail(): never
{
    redirect('login.php?google=failed');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || GOOGLE_CLIENT_ID === '' || !$pdo) {
    google_fail();
}

// Google's double-submit CSRF check: cookie and POST field must match.
$cookieToken = (string) ($_COOKIE['g_csrf_token'] ?? '');
$postToken = (string) ($_POST['g_csrf_token'] ?? '');
$credential = (string) ($_POST['credential'] ?? '');
if ($cookieToken === '' || !hash_equals($cookieToken, $postToken) || $credential === '') {
    google_fail();
}

// Ask Google to validate the token (signature + expiry), then check audience and e-mail.
$url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode($credential);
$body = false;
if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8, CURLOPT_SSL_VERIFYPEER => true]);
    $body = curl_exec($ch);
    curl_close($ch);
} else {
    $body = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 8]]));
}
$info = is_string($body) ? json_decode($body, true) : null;

if (!is_array($info)
    || ($info['aud'] ?? '') !== GOOGLE_CLIENT_ID
    || !in_array($info['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true)
    || (int) ($info['exp'] ?? 0) < time()
    || !in_array($info['email_verified'] ?? false, [true, 'true'], true)
    || empty($info['email']) || empty($info['sub'])) {
    google_fail();
}

$googleId = (string) $info['sub'];
$email = strtolower((string) $info['email']);

try {
    $stmt = $pdo->prepare('SELECT id, username, email, role, google_id FROM users WHERE google_id = :gid OR email = :email LIMIT 1');
    $stmt->execute(['gid' => $googleId, 'email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        if (empty($user['google_id'])) {
            $link = $pdo->prepare('UPDATE users SET google_id = :gid WHERE id = :id');
            $link->execute(['gid' => $googleId, 'id' => $user['id']]);
        }
    } else {
        $name = (string) ($info['name'] ?? strstr($email, '@', true));
        $name = trim((string) preg_replace('/[^\p{L}\p{N}_. -]/u', '', $name));
        if (mb_strlen($name) < 3) {
            $name = 'player' . random_int(100, 999);
        }
        $name = mb_substr($name, 0, 30);
        $insert = $pdo->prepare('INSERT INTO users (username, email, password_hash, google_id) VALUES (:u, :e, NULL, :g)');
        $insert->execute(['u' => $name, 'e' => $email, 'g' => $googleId]);
        $user = ['id' => (int) $pdo->lastInsertId(), 'username' => $name, 'email' => $email, 'role' => 'user'];
    }
    start_user_session($user, 'login.success');
    redirect('index.php');
} catch (PDOException $exception) {
    error_log('Playora google login error: ' . $exception->getMessage());
    google_fail();
}
