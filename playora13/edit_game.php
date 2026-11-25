<?php
// Admin: edit a game (Update).
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/admin_games.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$pageTitle = 'Редактировать игру — Playora';
$bodyClass = 'admin-page';
$errors = [];
$game = null;
$images = [];

if ($pdo && $id) {
    $stmt = $pdo->prepare('SELECT * FROM games WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $game = $stmt->fetch() ?: null;
}
if (!$game) {
    $_SESSION['flash'] = 'Игра не найдена.';
    redirect('games.php');
}
$oldCover = (string) ($game['cover_image'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = t('err.csrfExpired');
    } else {
        [$data, $errors] = admin_collect_game($pdo, $_POST, (int) $game['id']);
        if (!$errors) {
            if (isset($_POST['remove_cover'])) {
                $data['cover_image'] = '';
            }
            $uploaded = admin_upload_image($_FILES['cover_file'] ?? ['error' => UPLOAD_ERR_NO_FILE], $data['slug'], $errors);
            if ($uploaded !== null) {
                $data['cover_image'] = $uploaded;
            }
        }
        if (!$errors) {
            try {
                $pdo->beginTransaction();
                $update = $pdo->prepare(
                    'UPDATE games SET slug = :slug, title = :title, genre = :genre, release_year = :year, description = :d, description_ru = :dru, description_en = :den,
                        publisher = :pub, platforms = :plat, game_mode = :mode, rating = :rating, price = :price, steam_app_id = :steam, cover_image = :cover,
                        theme = :theme, code = :code, is_featured = :feat WHERE id = :id'
                );
                $update->execute([
                    'slug' => $data['slug'], 'title' => $data['title'], 'genre' => $data['genre'], 'year' => $data['release_year'],
                    'd' => $data['description'], 'dru' => $data['description_ru'] ?: null, 'den' => $data['description_en'] ?: null,
                    'pub' => $data['publisher'], 'plat' => $data['platforms'], 'mode' => $data['game_mode'],
                    'rating' => number_format((float) $data['rating'], 1, '.', ''), 'price' => number_format((float) $data['price'], 2, '.', ''),
                    'steam' => $data['steam_app_id'] !== '' ? (int) $data['steam_app_id'] : null,
                    'cover' => $data['cover_image'] !== '' ? $data['cover_image'] : null,
                    'theme' => $data['theme'], 'code' => $data['code'], 'feat' => $data['is_featured'], 'id' => (int) $game['id'],
                ]);

                // delete ticked screenshots
                $toDelete = array_filter(array_map('intval', (array) ($_POST['delete_images'] ?? [])));
                foreach ($toDelete as $imageId) {
                    $row = $pdo->prepare('SELECT image_url FROM game_images WHERE id = :i AND game_id = :g');
                    $row->execute(['i' => $imageId, 'g' => (int) $game['id']]);
                    $url = $row->fetchColumn();
                    if ($url !== false) {
                        $pdo->prepare('DELETE FROM game_images WHERE id = :i')->execute(['i' => $imageId]);
                        admin_delete_local_file((string) $url);
                    }
                }
                admin_add_screenshots($pdo, (int) $game['id'], $data['slug'], $errors);
                $pdo->commit();

                if ($oldCover !== '' && $oldCover !== $data['cover_image']) {
                    admin_delete_local_file($oldCover);
                }
                $_SESSION['flash'] = 'Изменения сохранены.';
                if (!$errors) {
                    redirect('games.php');
                }
                $stmt = $pdo->prepare('SELECT * FROM games WHERE id = :id LIMIT 1');
                $stmt->execute(['id' => (int) $game['id']]);
                $game = $stmt->fetch() ?: $game;
            } catch (PDOException $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('Playora edit_game error: ' . $exception->getMessage());
                $errors[] = 'Не удалось сохранить изменения. Попробуйте ещё раз.';
                $game = array_merge($game, $data);
            }
        } else {
            $game = array_merge($game, $data);
        }
    }
}

$imgStmt = $pdo->prepare('SELECT id, image_url FROM game_images WHERE game_id = :g ORDER BY sort_order ASC, id ASC');
$imgStmt->execute(['g' => (int) $game['id']]);
$images = $imgStmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<div class="container admin-wrap">
    <a class="back-link" href="games.php">← К списку игр</a>
    <h1 class="admin-title">Редактировать: <?= e((string) $game['title']) ?></h1>
    <p><a class="text-link" href="game.php?id=<?= e((string) $game['slug']) ?>" target="_blank" rel="noopener">Открыть страницу игры ↗</a></p>
    <?php admin_render_form($game, $images, $errors, 'Сохранить изменения', true); ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
