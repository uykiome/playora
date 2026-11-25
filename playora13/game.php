<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$slug = trim((string) ($_GET['id'] ?? ''));
$game = null;
$isFavorite = false;
$images = [];
$reviews = [];
$myReview = null;
$ratingCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
$reviewsError = null;
$userId = is_logged_in() ? (int) $_SESSION['user_id'] : 0;

if ($pdo && $slug !== '') {
    $stmt = $pdo->prepare('SELECT * FROM games WHERE slug = :slug LIMIT 1');
    $stmt->execute(['slug' => $slug]);
    $game = $stmt->fetch() ?: null;

    if ($game && $userId) {
        $fav = $pdo->prepare('SELECT id FROM favorites WHERE user_id = :user_id AND game_id = :game_id LIMIT 1');
        $fav->execute(['user_id' => $userId, 'game_id' => (int) $game['id']]);
        $isFavorite = (bool) $fav->fetch();
    }

    if ($game) {
        try {
            $imgStmt = $pdo->prepare('SELECT image_url FROM game_images WHERE game_id = :game_id ORDER BY sort_order ASC, id ASC LIMIT 12');
            $imgStmt->execute(['game_id' => (int) $game['id']]);
            $images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);

            $countStmt = $pdo->prepare('SELECT rating, COUNT(*) AS cnt FROM reviews WHERE game_id = :game_id GROUP BY rating');
            $countStmt->execute(['game_id' => (int) $game['id']]);
            foreach ($countStmt->fetchAll() as $row) {
                $ratingCounts[(int) $row['rating']] = (int) $row['cnt'];
            }

            $listStmt = $pdo->prepare(
                'SELECT r.id, r.user_id, r.rating, r.comment, r.created_at, r.updated_at, u.username
                 FROM reviews r
                 INNER JOIN users u ON u.id = r.user_id
                 WHERE r.game_id = :game_id
                 ORDER BY (r.user_id = :me) DESC, r.created_at DESC
                 LIMIT 30'
            );
            $listStmt->execute(['game_id' => (int) $game['id'], 'me' => $userId]);
            $reviews = $listStmt->fetchAll();
            foreach ($reviews as $review) {
                if ((int) $review['user_id'] === $userId) {
                    $myReview = $review;
                    break;
                }
            }
        } catch (PDOException $exception) {
            error_log('Playora game page error: ' . $exception->getMessage());
            $reviewsError = t('db.error');
        }
    }
}

$reviewTotal = array_sum($ratingCounts);
$reviewAverage = $reviewTotal > 0
    ? array_sum(array_map(static fn (int $stars, int $count): int => $stars * $count, array_keys($ratingCounts), $ratingCounts)) / $reviewTotal
    : 0.0;

if (!$game) {
    http_response_code(404);
}
$pageTitle = $game ? $game['title'] . ' — Playora' : t('title.notfound');
$bodyClass = 'game-page';
require __DIR__ . '/includes/header.php';
?>
<div class="container game-detail-section">
    <a class="back-link" href="index.php#catalog"><?= e(t('game.back')) ?></a>
    <?php if (!$pdo): ?>
        <div class="notice notice-error"><?= e($dbError ?? t('err.dbUnavailable')) ?></div>
    <?php elseif (!$game): ?>
        <div class="empty-state">
            <h1><?= e(t('game.notFoundTitle')) ?></h1>
            <p><?= e(t('game.notFoundText')) ?></p>
            <a class="button" href="index.php"><?= e(t('fav.toCatalog')) ?></a>
        </div>
    <?php else:
        ?>
        <section class="detail-hero">
            <div class="detail-art theme-<?= e($game['theme']) ?> <?= has_cover($game) ? 'has-cover' : '' ?>"><?= cover_img($game, 'hero') ?><span><?= e($game['code']) ?></span></div>
            <div class="detail-content">
                <p class="eyebrow"><?= e(t('game.eyebrow')) ?></p>
                <div class="detail-title-row">
                    <div>
                        <h1><?= e($game['title']) ?></h1>
                        <p class="detail-publisher"><?= e($game['publisher']) ?></p>
                    </div>
                    <div class="detail-rating"><span>★</span><strong><?= e((string) $game['rating']) ?></strong><small><?= e(t('game.ratingLabel')) ?></small></div>
                </div>
                <div class="detail-tags">
                    <span><?= e(genre_label($game['genre'])) ?></span>
                    <span><?= (int) $game['release_year'] ?></span>
                    <span><?= e(platforms_label($game['platforms'])) ?></span>
                </div>
                <p class="detail-description"><?= e(game_description($game)) ?></p>
                <div class="detail-price-row">
                    <div><span class="fact-label"><?= e(t('price.label')) ?></span><strong><?= e(format_price((float) $game['price'])) ?></strong></div>
                </div>
                <div class="detail-actions">
                    <?php if (is_logged_in()): ?>
                        <form action="toggle_favorite.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="game_id" value="<?= (int) $game['id'] ?>">
                            <input type="hidden" name="return_to" value="game.php?id=<?= e($game['slug']) ?>">
                            <button class="button" type="submit"><?= e(t($isFavorite ? 'game.removeFavorite' : 'game.addFavorite')) ?></button>
                        </form>
                    <?php else: ?>
                        <a class="button" href="login.php"><?= e(t('game.loginToFavorite')) ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php if ($images): ?>
        <section class="detail-gallery-section">
            <div class="section-heading"><div><p class="eyebrow"><?= e(t('gallery.eyebrow')) ?></p><h2><?= e(t('gallery.titleA')) ?> <span><?= e(t('gallery.titleB')) ?></span></h2></div></div>
            <div class="gallery-wrap">
                <button class="gallery-nav gallery-prev" type="button" aria-label="<?= e(t('gallery.prev')) ?>">‹</button>
                <div class="gallery-grid" data-gallery data-close="<?= e(t('gallery.close')) ?>" data-prev="<?= e(t('gallery.prev')) ?>" data-next="<?= e(t('gallery.next')) ?>">
                    <?php foreach ($images as $index => $imageUrl): ?>
                        <button class="gallery-item" type="button" data-full="<?= e(image_public_url((string) $imageUrl)) ?>">
                            <img src="<?= e(image_public_url((string) $imageUrl)) ?>" alt="<?= e(t('gallery.alt', ['title' => $game['title'], 'n' => $index + 1])) ?>" loading="lazy" referrerpolicy="no-referrer">
                        </button>
                    <?php endforeach; ?>
                </div>
                <button class="gallery-nav gallery-next" type="button" aria-label="<?= e(t('gallery.next')) ?>">›</button>
            </div>
        </section>
        <?php endif; ?>

        <section class="detail-facts-section">
            <div class="section-heading"><div><p class="eyebrow"><?= e(t('game.factsEyebrow')) ?></p><h2><?= e(t('game.factsTitleA')) ?> <span><?= e(t('game.factsTitleB')) ?></span></h2></div></div>
            <div class="detail-facts-modern">
                <div class="detail-fact"><span><?= e(t('game.genre')) ?></span><strong><?= e(genre_label($game['genre'])) ?></strong></div>
                <div class="detail-fact"><span><?= e(t('game.year')) ?></span><strong><?= (int) $game['release_year'] ?></strong></div>
                <div class="detail-fact"><span><?= e(t('game.publisher')) ?></span><strong><?= e($game['publisher']) ?></strong></div>
                <div class="detail-fact"><span><?= e(t('game.platforms')) ?></span><strong><?= e(platforms_label($game['platforms'])) ?></strong></div>
                <div class="detail-fact"><span><?= e(t('game.mode')) ?></span><strong><?= e(game_mode_label($game['game_mode'])) ?></strong></div>
            </div>
        </section>

        <section class="reviews-section" id="reviews">
            <div class="section-heading"><div><p class="eyebrow"><?= e(t('reviews.eyebrow')) ?></p><h2><?= e(t('reviews.titleA')) ?> <span><?= e(t('reviews.titleB')) ?></span></h2></div></div>

            <?php if ($reviewsError): ?>
                <div class="notice notice-error"><?= e($reviewsError) ?></div>
            <?php else: ?>
                <div class="rating-summary">
                    <div class="rating-big">
                        <strong><?= $reviewTotal > 0 ? e(number_format($reviewAverage, 1, '.', '')) : '—' ?></strong>
                        <?= stars_html($reviewAverage) ?>
                        <small><?= e(t('reviews.count', ['n' => $reviewTotal])) ?></small>
                    </div>
                    <div class="rating-bars" role="list">
                        <?php for ($star = 5; $star >= 1; $star--):
                            $percent = $reviewTotal > 0 ? (int) round($ratingCounts[$star] / $reviewTotal * 100) : 0; ?>
                            <div class="bar-row" role="listitem">
                                <span class="bar-label"><?= $star ?> ★</span>
                                <span class="bar-track"><span class="bar-fill" style="width:<?= $percent ?>%"></span></span>
                                <span class="bar-count"><?= $ratingCounts[$star] ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <?php if (is_logged_in()): ?>
                    <form class="review-form" id="reviewForm" method="post" action="review_action.php">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="game_id" value="<?= (int) $game['id'] ?>">
                        <input type="hidden" name="action" value="save">
                        <h3><?= e(t($myReview ? 'reviews.formEdit' : 'reviews.formNew')) ?></h3>
                        <fieldset class="rating-input">
                            <legend><?= e(t('reviews.yourRating')) ?></legend>
                            <div class="star-picker">
                                <?php for ($star = 5; $star >= 1; $star--): ?>
                                    <input type="radio" id="star<?= $star ?>" name="rating" value="<?= $star ?>" required <?= $myReview && (int) $myReview['rating'] === $star ? 'checked' : '' ?>>
                                    <label for="star<?= $star ?>" title="<?= e(t('reviews.starsAria', ['n' => $star])) ?>"><span class="sr-only"><?= e(t('reviews.starsAria', ['n' => $star])) ?></span>★</label>
                                <?php endfor; ?>
                            </div>
                        </fieldset>
                        <label for="reviewComment"><?= e(t('reviews.commentLabel')) ?></label>
                        <textarea id="reviewComment" name="comment" rows="4" maxlength="<?= REVIEW_MAX_LENGTH ?>" placeholder="<?= e(t('reviews.commentPlaceholder')) ?>"><?= e($myReview['comment'] ?? '') ?></textarea>
                        <div class="review-actions">
                            <button class="button" type="submit"><?= e(t($myReview ? 'reviews.update' : 'reviews.post')) ?></button>
                        </div>
                    </form>
                    <?php if ($myReview): ?>
                        <form class="review-delete-form" method="post" action="review_action.php" data-confirm="<?= e(t('reviews.deleteConfirm')) ?>">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="game_id" value="<?= (int) $game['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button class="text-danger" type="submit">🗑 <?= e(t('reviews.delete')) ?></button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="review-login-prompt"><?= e(t('reviews.loginPrompt')) ?> <a class="text-link" href="login.php"><?= e(t('nav.login')) ?> <span>→</span></a></p>
                <?php endif; ?>

                <h3 class="reviews-list-title"><?= e(t('reviews.listTitle')) ?></h3>
                <?php if (!$reviews): ?>
                    <div class="empty-state review-empty"><span>★</span><p><?= e(t('reviews.empty')) ?></p></div>
                <?php else: ?>
                    <div class="review-list">
                        <?php foreach ($reviews as $review):
                            $isMine = (int) $review['user_id'] === $userId;
                            $wasEdited = $review['updated_at'] !== $review['created_at'];
                        ?>
                            <article class="review-item <?= $isMine ? 'is-mine' : '' ?>">
                                <div class="review-avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr((string) $review['username'], 0, 1))) ?></div>
                                <div class="review-body">
                                    <div class="review-head">
                                        <strong><?= e($review['username']) ?></strong>
                                        <?php if ($isMine): ?><span class="review-badge"><?= e(t('reviews.mine')) ?></span><?php endif; ?>
                                        <time datetime="<?= e(date('Y-m-d', strtotime((string) $review['created_at']))) ?>"><?= e(date('d.m.Y', strtotime((string) $review['created_at']))) ?></time>
                                        <?php if ($wasEdited): ?><span class="review-edited">· <?= e(t('reviews.edited')) ?></span><?php endif; ?>
                                    </div>
                                    <div class="review-stars" role="img" aria-label="<?= e(t('reviews.starsAria', ['n' => (int) $review['rating']])) ?>"><?= stars_html((float) $review['rating']) ?></div>
                                    <?php if ((string) $review['comment'] !== ''): ?><p class="review-text"><?= nl2br(e((string) $review['comment'])) ?></p><?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
