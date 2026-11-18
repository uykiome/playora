<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$pageTitle = t('title.home');
$bodyClass = 'home-page';
$activePage = 'home';

$search = trim((string) ($_GET['q'] ?? ''));
if (mb_strlen($search) > 80) {
    $search = mb_substr($search, 0, 80);
}
$genre = trim((string) ($_GET['genre'] ?? 'all'));
$sort = (string) ($_GET['sort'] ?? 'rating');
$page = max(1, (int) ($_GET['page'] ?? 1));

$allowedGenres = ['all', 'action', 'combat', 'horror', 'adventure', 'rpg', 'multiplayer', 'sandbox', 'simulation', 'strategy'];
if (!in_array($genre, $allowedGenres, true)) {
    $genre = 'all';
}
$sortColumns = [
    'rating' => 'rating DESC, id ASC',
    'newest' => 'release_year DESC, id ASC',
    'oldest' => 'release_year ASC, id ASC',
    'az' => 'title ASC',
    'price' => 'price ASC, id ASC',
];
if (!isset($sortColumns[$sort])) {
    $sort = 'rating';
}
$orderBy = $sortColumns[$sort];
$current = ['q' => $search, 'genre' => $genre, 'sort' => $sort];

$games = [];
$featured = null;
$favoriteIds = [];
$totalGames = 0;
$totalPages = 1;

if ($pdo) {
    try {
        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(title LIKE :search_title OR publisher LIKE :search_publisher OR description LIKE :search_description)';
            $like = '%' . $search . '%';
            $params['search_title'] = $like;
            $params['search_publisher'] = $like;
            $params['search_description'] = $like;
        }
        if ($genre !== 'all') {
            $where[] = 'genre = :genre';
            $params['genre'] = $genre;
        }
        $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM games' . $whereSql);
        $countStmt->execute($params);
        $totalGames = (int) $countStmt->fetchColumn();
        $totalPages = max(1, (int) ceil($totalGames / GAMES_PER_PAGE));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * GAMES_PER_PAGE;

        $stmt = $pdo->prepare('SELECT * FROM games' . $whereSql . ' ORDER BY ' . $orderBy . ' LIMIT :limit OFFSET :offset');
        foreach ($params as $name => $value) {
            $stmt->bindValue(':' . $name, $value);
        }
        $stmt->bindValue(':limit', GAMES_PER_PAGE, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $games = $stmt->fetchAll();

        $featured = $pdo->query('SELECT * FROM games WHERE is_featured = 1 ORDER BY rating DESC LIMIT 1')->fetch() ?: null;
        if (!$featured) {
            $featured = $pdo->query('SELECT * FROM games ORDER BY rating DESC LIMIT 1')->fetch() ?: null;
        }

        if (is_logged_in()) {
            $favStmt = $pdo->prepare('SELECT game_id FROM favorites WHERE user_id = :user_id');
            $favStmt->execute(['user_id' => (int) $_SESSION['user_id']]);
            $favoriteIds = array_map('intval', $favStmt->fetchAll(PDO::FETCH_COLUMN));
        }
    } catch (PDOException $exception) {
        error_log('Playora catalog error: ' . $exception->getMessage());
        $dbError = t('db.error');
        $pdo = null;
    }
}

$filtersActive = $search !== '' || $genre !== 'all';
$returnTo = catalog_url(['page' => $page], $current);
require __DIR__ . '/includes/header.php';
?>
<section class="spotlight-section">
    <div class="container">
        <div class="spotlight-topline"><span class="live-mark"></span><span><?= e(t('home.heroEyebrow')) ?></span><span class="spotlight-line"></span><span class="spotlight-index">PLAYORA / 001</span></div>
        <div class="spotlight-stage">
            <div class="spotlight-art <?= $featured ? 'theme-' . e($featured['theme']) : 'theme-cyber' ?>" <?php if ($featured && $featured['steam_app_id']): ?>style="background-image:linear-gradient(90deg,#080a10e8,#080a1060),url('https://cdn.akamai.steamstatic.com/steam/apps/<?= (int) $featured['steam_app_id'] ?>/library_hero.jpg')"<?php endif; ?>></div>
            <div class="spotlight-copy">
                <p class="spotlight-kicker"><?= e(t('home.heroKicker')) ?></p>
                <h1><?= $featured ? e($featured['title']) : e(t('home.heroFallbackTitle')) ?></h1>
                <p class="spotlight-description"><?= $featured ? e(game_description($featured)) : e(t('home.heroFallbackText')) ?></p>
                <div class="spotlight-meta">
                    <?php if ($featured): ?><span class="hero-rating">★ <?= e((string) $featured['rating']) ?></span><span><?= e(genre_label($featured['genre'])) ?></span><span><?= (int) $featured['release_year'] ?></span><?php endif; ?>
                </div>
                <div class="spotlight-actions">
                    <?php if ($featured): ?><a class="button" href="game.php?id=<?= e($featured['slug']) ?>"><span><?= e(t('home.viewGame')) ?></span><span>↗</span></a><?php endif; ?>
                    <a class="spotlight-save" href="#catalog"><?= e(t('home.seeCatalog')) ?></a>
                </div>
            </div>
            <span class="spotlight-watermark">P.</span>
        </div>
    </div>
</section>

<section class="quick-find-section">
    <div class="container">
        <div class="quick-find-heading">
            <div>
                <p class="eyebrow"><?= e(t('home.discoverEyebrow')) ?></p>
                <h2><?= e(t('home.discoverTitle')) ?></h2>
            </div>
            <p><?= e(t('home.discoverText')) ?></p>
        </div>
        <form class="search-panel" method="get" action="index.php#catalog">
            <input type="hidden" name="genre" value="<?= e($genre) ?>">
            <input type="hidden" name="sort" value="<?= e($sort) ?>">
            <label class="live-search-box" for="gameSearch">
                <span class="search-symbol">⌕</span>
                <input id="gameSearch" name="q" type="search" maxlength="80" value="<?= e($search) ?>" placeholder="<?= e(t('home.searchPlaceholder')) ?>">
                <button class="search-submit" type="submit"><?= e(t('home.searchButton')) ?></button>
            </label>
            <div class="quick-filter-label"><?= e(t('home.quickFilters')) ?></div>
            <div class="filter-chips">
                <?php foreach (['all', 'action', 'combat', 'horror', 'adventure', 'rpg', 'multiplayer'] as $key): ?>
                    <a class="filter-chip <?= $genre === $key ? 'active' : '' ?>" href="<?= e(catalog_url(['genre' => $key, 'page' => 1], $current)) ?>#catalog"><?= e(t('genre.' . $key)) ?></a>
                <?php endforeach; ?>
            </div>
        </form>
    </div>
</section>

<section class="content-section category-section" id="categories">
    <div class="container">
        <div class="section-heading modern-section-heading">
            <div>
                <p class="eyebrow"><?= e(t('home.categoryEyebrow')) ?></p>
                <h2><?= e(t('home.categoryTitleA')) ?> <span><?= e(t('home.categoryTitleB')) ?></span></h2>
            </div>
            <p class="section-description"><?= e(t('home.categoryText')) ?></p>
        </div>
        <div class="genre-tiles">
            <?php
            $genreIcons = ['action' => '⚔', 'combat' => '✦', 'horror' => '☠', 'adventure' => '⌖', 'rpg' => '✧', 'multiplayer' => '⌘'];
            foreach ($genreIcons as $key => $icon): ?>
                <a class="genre-tile tile-<?= e($key) ?>" href="index.php?genre=<?= e($key) ?>#catalog">
                    <span class="genre-icon"><?= e($icon) ?></span>
                    <span><?= e(t('genre.' . $key)) ?></span>
                    <small><?= e(t('tile.' . $key)) ?></small>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="content-section catalog-section" id="catalog">
    <div class="container">
        <div class="section-heading modern-section-heading">
            <div>
                <p class="eyebrow"><?= e(t('home.catalogEyebrow')) ?></p>
                <h2><?= e(t('home.catalogTitleA')) ?> <span><?= e(t('home.catalogTitleB')) ?></span></h2>
            </div>
            <form class="catalog-controls" method="get" action="index.php#catalog">
                <input type="hidden" name="q" value="<?= e($search) ?>">
                <input type="hidden" name="genre" value="<?= e($genre) ?>">
                <label for="sortGames"><?= e(t('home.sortLabel')) ?></label>
                <select id="sortGames" name="sort" onchange="this.form.submit()">
                    <?php foreach (['rating' => 'home.sortRating', 'newest' => 'home.sortNewest', 'oldest' => 'home.sortOldest', 'az' => 'home.sortAz', 'price' => 'home.sortPrice'] as $value => $labelKey): ?>
                        <option value="<?= e($value) ?>" <?= $sort === $value ? 'selected' : '' ?>><?= e(t($labelKey)) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if (!$pdo): ?>
            <div class="notice notice-error"><?= e($dbError ?? t('err.dbUnavailable')) ?></div>
        <?php else: ?>
            <div class="results-row">
                <p class="results-line" aria-live="polite"><?= e(t('home.resultsCount', ['n' => $totalGames])) ?></p>
                <?php if ($filtersActive): ?><a class="reset-link" href="index.php#catalog">✕ <?= e(t('home.resetFilters')) ?></a><?php endif; ?>
            </div>

            <?php if ($games): ?>
                <div class="catalog-grid">
                    <?php foreach ($games as $game):
                        $cover = $game['steam_app_id'] ? 'https://cdn.akamai.steamstatic.com/steam/apps/' . (int) $game['steam_app_id'] . '/header.jpg' : '';
                        $isFav = in_array((int) $game['id'], $favoriteIds, true);
                    ?>
                        <article class="game-tile">
                            <a class="game-tile-art theme-<?= e($game['theme']) ?>" href="game.php?id=<?= e($game['slug']) ?>" <?php if ($cover): ?>style="background-image:linear-gradient(180deg,#07090a15,#07090ac9),url('<?= e($cover) ?>')"<?php endif; ?>>
                                <span class="poster-code"><?= e($game['code']) ?></span>
                                <span class="poster-bottom-line"><?= e($game['title']) ?></span>
                                <span class="card-year"><?= (int) $game['release_year'] ?></span>
                            </a>
                            <div class="game-tile-info">
                                <div class="game-tile-title-row">
                                    <div>
                                        <p class="game-tile-genres"><?= e(genre_label($game['genre'])) ?></p>
                                        <a href="game.php?id=<?= e($game['slug']) ?>" class="game-tile-title"><?= e($game['title']) ?></a>
                                    </div>
                                    <span class="game-rating">★ <?= e((string) $game['rating']) ?></span>
                                </div>
                                <div class="game-tile-bottom">
                                    <span class="game-price <?= (float) $game['price'] === 0.0 ? 'is-free' : '' ?>"><?= e(format_price((float) $game['price'])) ?></span>
                                    <?php if (is_logged_in()): ?>
                                        <form action="toggle_favorite.php" method="post">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="game_id" value="<?= (int) $game['id'] ?>">
                                            <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                                            <button class="favorite-button <?= $isFav ? 'is-favorite' : '' ?>" type="submit" aria-label="<?= e(t($isFav ? 'home.removeFavorite' : 'home.addFavorite')) ?>"><?= $isFav ? '♥' : '♡' ?></button>
                                        </form>
                                    <?php else: ?>
                                        <a class="favorite-button" href="login.php" aria-label="<?= e(t('home.loginToFavorite')) ?>">♡</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="pagination" aria-label="<?= e(t('home.pagination')) ?>">
                        <?php if ($page > 1): ?><a class="page-link" href="<?= e(catalog_url(['page' => $page - 1], $current)) ?>#catalog"><?= e(t('home.prev')) ?></a><?php endif; ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++):
                            if ($i !== 1 && $i !== $totalPages && abs($i - $page) > 2) {
                                if (abs($i - $page) === 3) echo '<span class="page-gap">…</span>';
                                continue;
                            } ?>
                            <a class="page-link <?= $i === $page ? 'active' : '' ?>" href="<?= e(catalog_url(['page' => $i], $current)) ?>#catalog" aria-label="<?= e(t('home.page', ['n' => $i])) ?>" <?= $i === $page ? 'aria-current="page"' : '' ?>><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?><a class="page-link" href="<?= e(catalog_url(['page' => $page + 1], $current)) ?>#catalog"><?= e(t('home.next')) ?></a><?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <span>⌕</span>
                    <h3><?= e(t('home.emptyTitle')) ?></h3>
                    <p><?= e(t('home.emptyText')) ?></p>
                    <a class="button button-small" href="index.php#catalog"><?= e(t('home.resetFilters')) ?></a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="catalog-bottom"><a class="text-link" href="#top"><?= e(t('home.toTop')) ?></a></div>
    </div>
</section>

<section class="about-section" id="about">
    <div class="container about-inner">
        <div>
            <p class="eyebrow"><?= e(t('home.aboutEyebrow')) ?></p>
            <h2><?= e(t('home.aboutTitleA')) ?><br><span><?= e(t('home.aboutTitleB')) ?></span></h2>
        </div>
        <div class="about-copy">
            <p><?= e(t('home.aboutText')) ?></p>
            <?php if (!is_logged_in()): ?>
                <a class="text-link" href="register.php"><?= e(t('home.createAccount')) ?> <span>→</span></a>
            <?php else: ?>
                <a class="text-link" href="favorites.php"><?= e(t('home.goFavorites')) ?> <span>→</span></a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
