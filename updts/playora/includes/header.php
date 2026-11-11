<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? 'Playora — ойындар әлемі';
$bodyClass = $bodyClass ?? '';
$mainClass = $mainClass ?? '';
$activePage = $activePage ?? '';
$flash = flash_message();
?>
<!doctype html>
<html lang="kk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b0c10">
    <meta name="description" content="Playora — компьютерлік ойындар каталогы. Ойындарды ізде, жанры бойынша сүз және таңдаулыларыңа сақта.">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/main.js" defer></script>
</head>
<body id="top" class="<?= e($bodyClass) ?>">
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="index.php" aria-label="Playora home">
            <span class="brand-mark">P</span>
            <span>playora<span class="brand-dot">.</span></span>
        </a>
        <nav class="main-nav" aria-label="Main navigation">
            <a class="<?= $activePage === 'home' ? 'active' : '' ?>" href="index.php" data-i18n="navHome">Басты бет</a>
            <a href="index.php#catalog" data-i18n="navCatalog">Каталог</a>
            <a href="index.php#categories" data-i18n="navCategories">Жанрлар</a>
            <a href="index.php#about" data-i18n="navAbout">Жоба туралы</a>
            <?php if (is_logged_in()): ?>
                <a class="mobile-nav-only" href="favorites.php" data-i18n="navFavorites">Таңдаулылар</a>
                <a class="mobile-nav-only" href="favorites.php" data-i18n="navProfile"><?= e($_SESSION['username'] ?? 'Аккаунт') ?></a>
            <?php else: ?>
                <a class="mobile-nav-only" href="login.php" data-i18n="navLogin">Кіру</a>
                <a class="mobile-nav-only" href="register.php" data-i18n="navRegister">Тіркелу</a>
            <?php endif; ?>
        </nav>
        <div class="nav-actions">
            <label class="language-control" aria-label="Choose language">
                <span aria-hidden="true">◎</span>
                <select id="languageSelect" class="language-select" aria-label="Choose interface language">
                    <option value="kk">ҚАЗ</option>
                    <option value="ru">РУС</option>
                    <option value="en">ENG</option>
                </select>
            </label>
            <?php if (is_logged_in()): ?>
                <a class="nav-login" href="favorites.php" data-i18n="navFavorites">Таңдаулылар</a>
                <span class="user-chip"><?= e($_SESSION['username'] ?? 'Player') ?></span>
                <form class="logout-form" action="logout.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <button class="nav-login logout-button" type="submit" data-i18n="navLogout">Шығу</button>
                </form>
            <?php else: ?>
                <a class="nav-login" href="login.php" data-i18n="navLogin">Кіру</a>
                <a class="button button-small nav-register" href="register.php" data-i18n="navRegister">Тіркелу</a>
            <?php endif; ?>
        </div>
        <button class="menu-toggle" type="button" aria-label="Open menu" aria-expanded="false">☰</button>
    </div>
</header>
<main class="<?= e($mainClass) ?>">
<?php if ($flash): ?>
    <div class="container"><p class="flash-message" role="status"><?= e($flash) ?></p></div>
<?php endif; ?>
