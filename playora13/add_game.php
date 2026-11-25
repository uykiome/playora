<?php
// Admin: add a new game (Create).
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/admin_games.php';
require_admin();

$pageTitle = 'Добавить игру — Playora';
$bodyClass = 'admin-page';
$errors = [];
$game = admin_defaults();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = t('err.csrfExpired');
    } elseif (!$pdo) {
        $errors[] = $dbError ?? t('err.dbUnavailable');
    } else {
        [$game, $errors] = admin_collect_game($pdo, $_POST, null);
        if (!$errors) {
            $coverPath = admin_upload_image($_FILES['cover_file'] ?? ['error' => UPLOAD_ERR_NO_FILE], $game['slug'], $errors);
            if ($coverPath !== null) {
                $game['cover_image'] = $coverPath;
            }
        }
        if (!$errors) {
            try {
                $pdo->beginTransaction();
                $insert = $pdo->prepare(
                    'INSERT INTO games (slug, title, genre, release_year, description, description_ru, description_en, publisher, platforms, game_mode, rating, price, steam_app_id, cover_image, theme, code, is_featured)
                     VALUES (:slug, :title, :genre, :year, :d, :dru, :den, :pub, :plat, :mode, :rating, :price, :steam, :cover, :theme, :code, :feat)'
                );
                $insert->execute([
                    'slug' => $game['slug'], 'title' => $game['title'], 'genre' => $game['genre'], 'year' => $game['release_year'],
                    'd' => $game['description'], 'dru' => $game['description_ru'] ?: null, 'den' => $game['description_en'] ?: null,
                    'pub' => $game['publisher'], 'plat' => $game['platforms'], 'mode' => $game['game_mode'],
                    'rating' => number_format((float) $game['rating'], 1, '.', ''), 'price' => number_format((float) $game['price'], 2, '.', ''),
                    'steam' => $game['steam_app_id'] !== '' ? (int) $game['steam_app_id'] : null,
                    'cover' => $game['cover_image'] !== '' ? $game['cover_image'] : null,
                    'theme' => $game['theme'], 'code' => $game['code'], 'feat' => $game['is_featured'],
                ]);
                $gameId = (int) $pdo->lastInsertId();
                admin_add_screenshots($pdo, $gameId, $game['slug'], $errors);
                $pdo->commit();
                $_SESSION['flash'] = 'Игра «' . $game['title'] . '» добавлена.';
                redirect($errors ? 'edit_game.php?id=' . $gameId : 'games.php');
            } catch (PDOException $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('Playora add_game error: ' . $exception->getMessage());
                $errors[] = 'Не удалось сохранить игру. Проверьте данные и попробуйте ещё раз.';
            }
        }
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="container admin-wrap">
    <a class="back-link" href="games.php">← К списку игр</a>
    <h1 class="admin-title">Добавить игру</h1>
    <?php admin_render_form($game, [], $errors, 'Добавить игру', false); ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
