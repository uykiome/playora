<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$pageTitle = 'Playora — ойындар әлемі';
$bodyClass = 'home-page';
$activePage = 'home';
$search = trim((string) ($_GET['q'] ?? ''));
$genre = trim((string) ($_GET['genre'] ?? 'all'));
$sort = (string) ($_GET['sort'] ?? 'rating');
$allowedGenres = ['all', 'action', 'combat', 'horror', 'adventure', 'rpg', 'multiplayer', 'sandbox', 'simulation', 'strategy'];
if (!in_array($genre, $allowedGenres, true)) $genre = 'all';
$sortColumns = ['rating' => 'rating DESC', 'newest' => 'release_year DESC', 'oldest' => 'release_year ASC', 'az' => 'title ASC', 'price' => 'price ASC'];
$orderBy = $sortColumns[$sort] ?? $sortColumns['rating'];
$games = [];
$featured = null;
$favoriteIds = [];
if ($pdo) {
    $where = [];
    $params = [];
    if ($search !== '') {
        $where[] = '(title LIKE :search OR publisher LIKE :search OR description LIKE :search)';
        $params['search'] = '%' . $search . '%';
    }
    if ($genre !== 'all') {
        $where[] = 'genre = :genre';
        $params['genre'] = $genre;
    }
    $sql = 'SELECT * FROM games' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY ' . $orderBy;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $games = $stmt->fetchAll();
    $featuredStmt = $pdo->query('SELECT * FROM games WHERE is_featured = 1 ORDER BY rating DESC LIMIT 1');
    $featured = $featuredStmt->fetch() ?: ($games[0] ?? null);
    if (is_logged_in()) {
        $favStmt = $pdo->prepare('SELECT game_id FROM favorites WHERE user_id = :user_id');
        $favStmt->execute(['user_id' => (int) $_SESSION['user_id']]);
        $favoriteIds = array_map('intval', $favStmt->fetchAll(PDO::FETCH_COLUMN));
    }
}
require __DIR__ . '/includes/header.php';
?>
<section class="spotlight-section">
    <div class="container">
        <div class="spotlight-topline"><span class="live-mark"></span><span data-i18n="heroEyebrow">ТАҢДАЛҒАН ОЙЫНДАР</span><span class="spotlight-line"></span><span class="spotlight-index">PLAYORA / 001</span></div>
        <div class="spotlight-stage">
            <div class="spotlight-art <?= $featured ? 'theme-' . e($featured['theme']) : 'theme-cyber' ?>" <?php if ($featured && $featured['steam_app_id']): ?>style="background-image:linear-gradient(90deg,#080a10e8,#080a1060),url('https://cdn.akamai.steamstatic.com/steam/apps/<?= (int) $featured['steam_app_id'] ?>/library_hero.jpg')"<?php endif; ?>></div>
            <div class="spotlight-copy">
                <p class="spotlight-kicker" data-i18n="heroKicker">PLAYORA ТАҢДАУЫ</p>
                <h1><?= $featured ? e($featured['title']) : 'Өз әлеміңді аш.' ?></h1>
                <p class="spotlight-description"><?= $featured ? e($featured['description']) : 'Жаңа ойындар мен есте қалатын оқиғаларды тап.' ?></p>
                <div class="spotlight-meta">
                    <?php if ($featured): ?><span class="hero-rating">★ <?= e((string) $featured['rating']) ?></span><span><?= e(ucfirst($featured['genre'])) ?></span><span><?= (int) $featured['release_year'] ?></span><?php endif; ?>
                </div>
                <div class="spotlight-actions">
                    <?php if ($featured): ?><a class="button" href="game.php?id=<?= e($featured['slug']) ?>"><span data-i18n="heroDetails">Ойынды көру</span><span>↗</span></a><?php endif; ?>
                    <a class="spotlight-save" href="#catalog" data-i18n="seeCatalog">Каталогты көру</a>
                </div>
            </div>
            <span class="spotlight-watermark">P.</span>
        </div>
    </div>
</section>
<section class="quick-find-section">
    <div class="container">
        <div class="quick-find-heading"><div><p class="eyebrow" data-i18n="discoverEyebrow">ІЗДЕУ ЖӘНЕ ТАБУ</p><h2 data-i18n="discoverTitle">Қандай ойын іздеп жүрсің?</h2></div><p data-i18n="discoverDescription">Атын жаз немесе жанрды таңда — нәтижелер бірден жаңарады.</p></div>
        <form class="search-panel" method="get" action="index.php">
            <label class="live-search-box" for="gameSearch"><span class="search-symbol">⌕</span><input id="gameSearch" name="q" type="search" value="<?= e($search) ?>" placeholder="Ойын атауын енгізіңіз" data-i18n-placeholder="searchPlaceholder"><button class="search-submit" type="submit">Іздеу</button></label>
            <div class="quick-filter-label" data-i18n="quickFilters">ЖЫЛДАМ СҮЗГІЛЕР</div>
            <div class="filter-chips">
                <?php $chips = ['all'=>'Барлық ойын','action'=>'Экшн','combat'=>'Боевик','horror'=>'Хоррор','adventure'=>'Приключения','rpg'=>'RPG','multiplayer'=>'Мультиплеер']; foreach ($chips as $key=>$label): ?>
                    <a class="filter-chip <?= $genre === $key ? 'active' : '' ?>" href="index.php?genre=<?= e($key) ?><?= $search !== '' ? '&q=' . urlencode($search) : '' ?>"><?= e($label) ?></a>
                <?php endforeach; ?>
            </div>
        </form>
    </div>
</section>
<section class="content-section category-section" id="categories">
    <div class="container">
        <div class="section-heading modern-section-heading"><div><p class="eyebrow" data-i18n="categoryEyebrow">КӨҢІЛ-КҮЙІҢЕ ҚАРАЙ</p><h2>Жанрды <span>таңда</span></h2></div><p class="section-description">Өзіңе ұнайтын бағытты таңдап, сол жанрдағы ойындарды қара.</p></div>
        <div class="genre-tiles">
            <?php $genreTiles = ['action'=>['⚔','Экшн','Қарқын мен әрекет'],'combat'=>['✦','Боевик','Шайқас пен стратегия'],'horror'=>['☠','Хоррор','Құпия және қорқыныш'],'adventure'=>['⌖','Приключения','Жаңа әлемдерді зертте'],'rpg'=>['✧','RPG','Өз оқиғаңды жаз'],'multiplayer'=>['⌘','Мультиплеер','Достармен бірге']]; foreach ($genreTiles as $key=>$tile): ?>
                <a class="genre-tile tile-<?= e($key) ?>" href="index.php?genre=<?= e($key) ?>"><span class="genre-icon"><?= e($tile[0]) ?></span><span><?= e($tile[1]) ?></span><small><?= e($tile[2]) ?></small></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="content-section catalog-section" id="catalog">
    <div class="container">
        <div class="section-heading modern-section-heading"><div><p class="eyebrow" data-i18n="catalogEyebrow">ТОЛЫҚ КІТАПХАНА</p><h2>Ойындар <span>каталогы</span></h2></div>
            <form class="catalog-controls" method="get" action="index.php"><input type="hidden" name="q" value="<?= e($search) ?>"><input type="hidden" name="genre" value="<?= e($genre) ?>"><label for="sortGames" data-i18n="sortLabel">Сұрыптау:</label><select id="sortGames" name="sort" onchange="this.form.submit()"><option value="rating" <?= $sort==='rating'?'selected':'' ?>>Рейтинг бойынша</option><option value="newest" <?= $sort==='newest'?'selected':'' ?>>Жаңа → ескі</option><option value="oldest" <?= $sort==='oldest'?'selected':'' ?>>Ескі → жаңа</option><option value="az" <?= $sort==='az'?'selected':'' ?>>Атауы A–Z</option><option value="price" <?= $sort==='price'?'selected':'' ?>>Бағасы бойынша</option></select></form>
        </div>
        <?php if (!$pdo): ?><div class="notice notice-error"><?= e($dbError) ?></div><?php else: ?>
            <p class="results-line" aria-live="polite"><?= count($games) ?> ойын көрсетілді</p>
            <?php if ($games): ?><div class="catalog-grid">
                <?php foreach ($games as $game): $cover = $game['steam_app_id'] ? 'https://cdn.akamai.steamstatic.com/steam/apps/' . (int)$game['steam_app_id'] . '/header.jpg' : ''; ?>
                    <article class="game-tile">
                        <a class="game-tile-art theme-<?= e($game['theme']) ?>" href="game.php?id=<?= e($game['slug']) ?>" <?php if ($cover): ?>style="background-image:linear-gradient(180deg,#07090a15,#07090ac9),url('<?= e($cover) ?>')"<?php endif; ?>><span class="poster-code"><?= e($game['code']) ?></span><span class="poster-bottom-line"><?= e($game['title']) ?></span><span class="card-year"><?= (int)$game['release_year'] ?></span></a>
                        <div class="game-tile-info"><div class="game-tile-title-row"><div><p class="game-tile-genres"><?= e(ucfirst($game['genre'])) ?></p><a href="game.php?id=<?= e($game['slug']) ?>" class="game-tile-title"><?= e($game['title']) ?></a></div><span class="game-rating">★ <?= e((string)$game['rating']) ?></span></div>
                            <div class="game-tile-bottom"><span class="game-price <?= (float)$game['price'] === 0.0 ? 'is-free' : '' ?>"><?= (float)$game['price'] === 0.0 ? 'Тегін' : number_format((float)$game['price'], 0, '.', ' ') . ' ₸' ?></span>
                                <?php if (is_logged_in()): ?><form action="toggle_favorite.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="game_id" value="<?= (int)$game['id'] ?>"><input type="hidden" name="return_to" value="index.php"><button class="favorite-button <?= in_array((int)$game['id'], $favoriteIds, true) ? 'is-favorite' : '' ?>" type="submit" aria-label="Таңдаулыларға қосу"><?= in_array((int)$game['id'], $favoriteIds, true) ? '♥' : '♡' ?></button></form><?php else: ?><a class="favorite-button" href="login.php" aria-label="Кіру">♡</a><?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div><?php else: ?><div class="empty-state"><span>⌕</span><h3>Ойын табылмады</h3><p>Іздеу сөзін немесе жанр сүзгісін өзгертіп көр.</p><a class="button button-small" href="index.php">Сүзгіні тазарту</a></div><?php endif; ?>
        <?php endif; ?>
        <div class="catalog-bottom"><span>Жаңа ойындар каталогқа біртіндеп қосылады.</span><a class="text-link" href="#top">Жоғарыға ↑</a></div>
    </div>
</section>
<section class="about-section" id="about"><div class="container about-inner"><div><p class="eyebrow">PLAYORA ТУРАЛЫ</p><h2>Ойын әлемі —<br><span>бір жерде.</span></h2></div><div class="about-copy"><p>Playora — компьютерлік ойындар туралы ақпаратты қарауға, ойындарды іздеуге және ұнағандарын таңдаулыларға сақтауға арналған оқу жобасы.</p><?php if (!is_logged_in()): ?><a class="text-link" href="register.php">Өз аккаунтыңды жаса <span>→</span></a><?php else: ?><a class="text-link" href="favorites.php">Таңдаулыларыңа өту <span>→</span></a><?php endif; ?></div></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
