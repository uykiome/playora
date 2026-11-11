<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
require_login();
$pageTitle = 'Таңдаулылар — Playora';
$bodyClass = 'home-page';
$games = [];
if ($pdo) {
    $stmt = $pdo->prepare('SELECT g.* FROM favorites f INNER JOIN games g ON g.id = f.game_id WHERE f.user_id = :user_id ORDER BY f.created_at DESC');
    $stmt->execute(['user_id' => (int)$_SESSION['user_id']]);
    $games = $stmt->fetchAll();
}
require __DIR__ . '/includes/header.php';
?>
<section class="content-section catalog-section"><div class="container"><div class="section-heading modern-section-heading"><div><p class="eyebrow">PLAYORA / YOUR LIBRARY</p><h2>Таңдаулы <span>ойындар</span></h2></div></div>
<?php if (!$pdo): ?><div class="notice notice-error"><?= e($dbError) ?></div><?php elseif (!$games): ?><div class="empty-state"><span>♡</span><h3>Әзірге таңдаулы ойын жоқ</h3><p>Каталогтан ұнаған ойыныңды таңдап сақта.</p><a class="button button-small" href="index.php#catalog">Каталогқа өту</a></div><?php else: ?><div class="catalog-grid">
<?php foreach ($games as $game): ?><article class="game-tile"><a class="game-tile-art theme-<?= e($game['theme']) ?>" href="game.php?id=<?= e($game['slug']) ?>"><span class="poster-code"><?= e($game['code']) ?></span><span class="poster-bottom-line"><?= e($game['title']) ?></span><span class="card-year"><?= (int)$game['release_year'] ?></span></a><div class="game-tile-info"><div class="game-tile-title-row"><div><p class="game-tile-genres"><?= e(ucfirst($game['genre'])) ?></p><a class="game-tile-title" href="game.php?id=<?= e($game['slug']) ?>"><?= e($game['title']) ?></a></div></div><div class="game-tile-bottom"><span class="game-price">★ <?= e((string)$game['rating']) ?></span><form action="toggle_favorite.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="game_id" value="<?= (int)$game['id'] ?>"><input type="hidden" name="return_to" value="favorites.php"><button class="favorite-button is-favorite" type="submit" aria-label="Таңдаулылардан алу">♥</button></form></div></div></article><?php endforeach; ?>
</div><?php endif; ?></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
