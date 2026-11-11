<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php');
if (!verify_csrf()) {
    $_SESSION['flash'] = 'Қауіпсіздік тексерісі өтпеді. Қайталап көріңіз.';
    redirect('index.php');
}
if (!is_logged_in()) {
    $_SESSION['flash'] = 'Таңдаулыларды пайдалану үшін жүйеге кіріңіз.';
    redirect('login.php');
}
$gameId = filter_input(INPUT_POST, 'game_id', FILTER_VALIDATE_INT);
$returnTo = (string)($_POST['return_to'] ?? 'index.php');
if (!preg_match('/^(index\.php|favorites\.php|game\.php\?id=[a-z0-9-]+)$/', $returnTo)) $returnTo = 'index.php';
if (!$pdo || !$gameId) {
    $_SESSION['flash'] = 'Ойынды сақтау мүмкін болмады.';
    redirect('index.php');
}
try {
    $check = $pdo->prepare('SELECT id FROM favorites WHERE user_id = :user_id AND game_id = :game_id LIMIT 1');
    $check->execute(['user_id' => (int)$_SESSION['user_id'], 'game_id' => $gameId]);
    if ($check->fetch()) {
        $delete = $pdo->prepare('DELETE FROM favorites WHERE user_id = :user_id AND game_id = :game_id');
        $delete->execute(['user_id' => (int)$_SESSION['user_id'], 'game_id' => $gameId]);
        $_SESSION['flash'] = 'Ойын таңдаулылардан алынды.';
    } else {
        $insert = $pdo->prepare('INSERT INTO favorites (user_id, game_id) VALUES (:user_id, :game_id)');
        $insert->execute(['user_id' => (int)$_SESSION['user_id'], 'game_id' => $gameId]);
        $_SESSION['flash'] = 'Ойын таңдаулыларға қосылды.';
    }
} catch (PDOException $exception) {
    error_log('Playora favorite error: ' . $exception->getMessage());
    $_SESSION['flash'] = 'Таңдаулыларды жаңарту кезінде қате пайда болды.';
}
redirect($returnTo);
