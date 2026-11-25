<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
if (!verify_csrf()) {
    $_SESSION['flash'] = t('flash.csrf');
    redirect('index.php');
}
if (!is_logged_in()) {
    $_SESSION['flash'] = t('flash.loginForReviews');
    redirect('login.php');
}

$userId = (int) $_SESSION['user_id'];
$gameId = filter_input(INPUT_POST, 'game_id', FILTER_VALIDATE_INT);
$action = (string) ($_POST['action'] ?? '');

if (!$pdo || !$gameId || !in_array($action, ['save', 'delete'], true)) {
    $_SESSION['flash'] = t('flash.reviewFailed');
    redirect('index.php');
}

try {
    $gameStmt = $pdo->prepare('SELECT slug FROM games WHERE id = :id LIMIT 1');
    $gameStmt->execute(['id' => $gameId]);
    $slug = $gameStmt->fetchColumn();
    if (!is_string($slug) || !preg_match('/^[a-z0-9-]+$/', $slug)) {
        $_SESSION['flash'] = t('flash.reviewFailed');
        redirect('index.php');
    }
    $backTo = 'game.php?id=' . $slug . '#reviews';

    if ($action === 'delete') {
        $delete = $pdo->prepare('DELETE FROM reviews WHERE user_id = :user_id AND game_id = :game_id');
        $delete->execute(['user_id' => $userId, 'game_id' => $gameId]);
        $_SESSION['flash'] = t('flash.reviewDeleted');
        redirect($backTo);
    }

    // action === 'save': validate the input on the server
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $comment = trim((string) ($_POST['comment'] ?? ''));
    $comment = str_replace("\r\n", "\n", $comment);

    if ($rating === false || $rating === null || $rating < 1 || $rating > 5) {
        $_SESSION['flash'] = t('err.rating');
        redirect($backTo);
    }
    if (mb_strlen($comment) > REVIEW_MAX_LENGTH) {
        $_SESSION['flash'] = t('err.commentLong');
        redirect($backTo);
    }

    $exists = $pdo->prepare('SELECT id FROM reviews WHERE user_id = :user_id AND game_id = :game_id LIMIT 1');
    $exists->execute(['user_id' => $userId, 'game_id' => $gameId]);

    if ($exists->fetch()) {
        $update = $pdo->prepare('UPDATE reviews SET rating = :rating, comment = :comment WHERE user_id = :user_id AND game_id = :game_id');
        $update->execute(['rating' => $rating, 'comment' => $comment, 'user_id' => $userId, 'game_id' => $gameId]);
        $_SESSION['flash'] = t('flash.reviewUpdated');
    } else {
        $insert = $pdo->prepare('INSERT INTO reviews (user_id, game_id, rating, comment) VALUES (:user_id, :game_id, :rating, :comment)');
        $insert->execute(['user_id' => $userId, 'game_id' => $gameId, 'rating' => $rating, 'comment' => $comment]);
        $_SESSION['flash'] = t('flash.reviewCreated');
    }
    redirect($backTo);
} catch (PDOException $exception) {
    error_log('Playora review error: ' . $exception->getMessage());
    $_SESSION['flash'] = t('flash.reviewFailed');
    redirect('index.php');
}
