<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
$slug = trim((string) ($_GET['id'] ?? ''));
$game = null;
$isFavorite = false;
if ($pdo && $slug !== '') {
    $stmt = $pdo->prepare('SELECT * FROM games WHERE slug = :slug LIMIT 1');
    $stmt->execute(['slug' => $slug]);
    $game = $stmt->fetch() ?: null;
    if ($game && is_logged_in()) {
        $fav = $pdo->prepare('SELECT id FROM favorites WHERE user_id = :user_id AND game_id = :game_id LIMIT 1');
        $fav->execute(['user_id' => (int)$_SESSION['user_id'], 'game_id' => (int)$game['id']]);
        $isFavorite = (bool)$fav->fetch();
    }
}
if (!$game) http_response_code(404);
$pageTitle = $game ? $game['title'] . ' — Playora' : 'Ойын табылмады — Playora';
$bodyClass = 'game-page';
require __DIR__ . '/includes/header.php';
?>
<div class="container game-detail-section">
    <a class="back-link" href="index.php#catalog">← Каталогқа оралу</a>
    <?php if (!$pdo): ?><div class="notice notice-error"><?= e($dbError) ?></div>
    <?php elseif (!$game): ?><div class="empty-state"><h1>Ойын табылмады</h1><p>Каталогтан басқа ойынды таңдап көр.</p><a class="button" href="index.php">Каталогқа өту</a></div>
    <?php else: $cover = $game['steam_app_id'] ? 'https://cdn.akamai.steamstatic.com/steam/apps/' . (int)$game['steam_app_id'] . '/library_hero.jpg' : ''; ?>
        <section class="detail-hero">
            <div class="detail-art theme-<?= e($game['theme']) ?>" <?php if ($cover): ?>style="background-image:linear-gradient(180deg,#080a1020,#080a10b0),url('<?= e($cover) ?>')"<?php endif; ?>><span><?= e($game['code']) ?></span></div>
            <div class="detail-content"><p class="eyebrow">ОЙЫН ПРОФИЛІ</p><div class="detail-title-row"><div><h1><?= e($game['title']) ?></h1><p class="detail-publisher"><?= e($game['publisher']) ?></p></div><div class="detail-rating"><span>★</span><strong><?= e((string)$game['rating']) ?></strong><small>Playora рейтингі</small></div></div>
                <div class="detail-tags"><span><?= e(ucfirst($game['genre'])) ?></span><span><?= (int)$game['release_year'] ?></span><span><?= e($game['platforms']) ?></span></div>
                <p class="detail-description"><?= e($game['description']) ?></p>
                <div class="detail-price-row"><div><span class="fact-label">Бағасы</span><strong><?= (float)$game['price'] === 0.0 ? 'Тегін' : number_format((float)$game['price'], 0, '.', ' ') . ' ₸' ?></strong></div><span class="detail-demo-note">Бағасы мысал ретінде көрсетілген</span></div>
                <div class="detail-actions"><?php if (is_logged_in()): ?><form action="toggle_favorite.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="game_id" value="<?= (int)$game['id'] ?>"><input type="hidden" name="return_to" value="game.php?id=<?= e($game['slug']) ?>"><button class="button" type="submit"><?= $isFavorite ? '♥ Таңдаулылардан алу' : '♡ Таңдаулыларға қосу' ?></button></form><?php else: ?><a class="button" href="login.php">Кіру арқылы таңдаулыларға қосу</a><?php endif; ?></div>
            </div>
        </section>
        <section class="detail-facts-section"><div class="section-heading"><div><p class="eyebrow">ҚЫСҚАША МӘЛІМЕТ</p><h2>Ойын <span>ақпараты</span></h2></div></div><div class="detail-facts-modern"><div class="detail-fact"><span>Жанры</span><strong><?= e(ucfirst($game['genre'])) ?></strong></div><div class="detail-fact"><span>Шыққан жылы</span><strong><?= (int)$game['release_year'] ?></strong></div><div class="detail-fact"><span>Баспагер</span><strong><?= e($game['publisher']) ?></strong></div><div class="detail-fact"><span>Платформалар</span><strong><?= e($game['platforms']) ?></strong></div><div class="detail-fact"><span>Ойын режимі</span><strong><?= e($game['game_mode']) ?></strong></div></div></section>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
