<?php
// Admin panel: list, search, edit and delete games.
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/admin_games.php';
require_admin();

// Delete (POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!verify_csrf()) {
        $_SESSION['flash'] = t('flash.csrf');
    } elseif ($pdo) {
        $gameId = filter_input(INPUT_POST, 'game_id', FILTER_VALIDATE_INT);
        if ($gameId) {
            try {
                $row = $pdo->prepare('SELECT title, cover_image FROM games WHERE id = :id');
                $row->execute(['id' => $gameId]);
                $game = $row->fetch();
                if ($game) {
                    $imgs = $pdo->prepare('SELECT image_url FROM game_images WHERE game_id = :id');
                    $imgs->execute(['id' => $gameId]);
                    $localFiles = $imgs->fetchAll(PDO::FETCH_COLUMN);
                    $pdo->prepare('DELETE FROM games WHERE id = :id')->execute(['id' => $gameId]); // reviews, favorites, images cascade
                    foreach ($localFiles as $path) {
                        admin_delete_local_file((string) $path);
                    }
                    admin_delete_local_file((string) $game['cover_image']);
                    $_SESSION['flash'] = 'Игра «' . $game['title'] . '» удалена.';
                }
            } catch (PDOException $exception) {
                error_log('Playora delete game error: ' . $exception->getMessage());
                $_SESSION['flash'] = 'Не удалось удалить игру.';
            }
        }
    }
    redirect('games.php');
}

$pageTitle = 'Управление играми — Playora';
$bodyClass = 'admin-page';
$search = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;
$games = [];
$total = 0;

if ($pdo) {
    $where = '';
    $params = [];
    if ($search !== '') {
        $where = ' WHERE title LIKE :q OR publisher LIKE :q2';
        $params = ['q' => '%' . $search . '%', 'q2' => '%' . $search . '%'];
    }
    $count = $pdo->prepare('SELECT COUNT(*) FROM games' . $where);
    $count->execute($params);
    $total = (int) $count->fetchColumn();
    $pages = max(1, (int) ceil($total / $perPage));
    $page = min($page, $pages);
    $stmt = $pdo->prepare('SELECT id, slug, title, genre, release_year, price, rating, steam_app_id, cover_image FROM games' . $where . ' ORDER BY id DESC LIMIT :l OFFSET :o');
    foreach ($params as $k => $v) {
        $stmt->bindValue(':' . $k, $v);
    }
    $stmt->bindValue(':l', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':o', ($page - 1) * $perPage, PDO::PARAM_INT);
    $stmt->execute();
    $games = $stmt->fetchAll();
}
$pages = $pages ?? 1;
$genreLabels = admin_genre_labels();
require __DIR__ . '/includes/header.php';
?>
<div class="container admin-wrap">
    <div class="admin-head">
        <div>
            <p class="eyebrow">АДМИН-ПАНЕЛЬ</p>
            <h1 class="admin-title">Управление играми <span class="admin-count"><?= $total ?></span></h1>
        </div>
        <a class="button" href="add_game.php">+ Добавить игру</a>
    </div>

    <form class="admin-search" method="get" action="games.php">
        <input type="search" name="q" value="<?= e($search) ?>" placeholder="Поиск по названию или издателю" maxlength="80">
        <button class="button button-small" type="submit">Найти</button>
        <?php if ($search !== ''): ?><a class="reset-link" href="games.php">✕ Сбросить</a><?php endif; ?>
    </form>

    <?php if (!$pdo): ?>
        <div class="notice notice-error"><?= e($dbError ?? t('err.dbUnavailable')) ?></div>
    <?php elseif (!$games): ?>
        <div class="empty-state"><h3>Ничего не найдено</h3></div>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th></th><th>Название</th><th>Жанр</th><th>Год</th><th>Цена</th><th>★</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($games as $g): ?>
                    <tr>
                        <td class="admin-cover-cell"><div class="admin-cover <?= has_cover($g) ? 'has-cover' : '' ?>"><?= cover_img($g) ?></div></td>
                        <td><a href="game.php?id=<?= e($g['slug']) ?>" target="_blank" rel="noopener"><strong><?= e($g['title']) ?></strong></a></td>
                        <td><?= e($genreLabels[$g['genre']] ?? $g['genre']) ?></td>
                        <td><?= (int) $g['release_year'] ?></td>
                        <td><?= e(format_price((float) $g['price'])) ?></td>
                        <td><?= e((string) $g['rating']) ?></td>
                        <td class="admin-row-actions">
                            <a class="button button-small" href="edit_game.php?id=<?= (int) $g['id'] ?>">Изменить</a>
                            <form method="post" action="games.php" data-confirm="Удалить игру «<?= e($g['title']) ?>»? Отзывы и избранное этой игры тоже будут удалены.">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="game_id" value="<?= (int) $g['id'] ?>">
                                <button class="button button-small button-danger" type="submit">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($pages > 1): ?>
            <nav class="pagination" aria-label="Страницы">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <a class="page-link <?= $i === $page ? 'active' : '' ?>" href="games.php?<?= e(http_build_query(array_filter(['q' => $search, 'page' => $i > 1 ? $i : null]))) ?>"><?= $i ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
