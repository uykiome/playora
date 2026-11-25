<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? t('title.home');
$bodyClass = $bodyClass ?? '';
$mainClass = $mainClass ?? '';
$activePage = $activePage ?? '';
$flash = flash_message();
$lang = current_lang();
?>
<!doctype html>
<html lang="<?= e($lang) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b0c10">
    <meta name="description" content="<?= e(t('meta.description')) ?>">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Manrope:wght@400;500;600;700;800&family=Unbounded:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
    /* If an image fails to load, try the next mirror; if all fail, show the colored placeholder. */
    function pxCoverFail(img){var l=(img.dataset.fallbacks||'').split('|').filter(Boolean);
      if(l.length){img.dataset.fallbacks=l.slice(1).join('|');img.src=l[0];return;}
      img.style.display='none';var p=img.closest('.has-cover');if(p)p.classList.remove('has-cover');}
    </script>
    <script src="assets/js/main.js" defer></script>
</head>
<body id="top" class="<?= e($bodyClass) ?>">
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="index.php" aria-label="<?= e(t('aria.home')) ?>">
            <span class="brand-word">playora</span>
        </a>
        <nav class="main-nav" aria-label="<?= e(t('aria.mainNav')) ?>">
            <a class="<?= $activePage === 'home' ? 'active' : '' ?>" href="index.php"><?= e(t('nav.home')) ?></a>
            <a href="index.php#catalog"><?= e(t('nav.catalog')) ?></a>
            <a href="index.php#categories"><?= e(t('nav.genres')) ?></a>
            <a class="<?= $activePage === 'help' ? 'active' : '' ?>" href="help.php"><?= e(t('nav.help')) ?></a>
            <?php if (is_admin()): ?>
                <a href="games.php">Админ-панель</a>
            <?php endif; ?>
            <?php if (is_logged_in()): ?>
                <a class="mobile-nav-only" href="favorites.php"><?= e(t('nav.favorites')) ?></a>
                <a class="mobile-nav-only" href="favorites.php"><?= e($_SESSION['username'] ?? t('nav.account')) ?></a>
            <?php else: ?>
                <a class="mobile-nav-only" href="login.php"><?= e(t('nav.login')) ?></a>
                <a class="mobile-nav-only" href="register.php"><?= e(t('nav.register')) ?></a>
            <?php endif; ?>
        </nav>
        <div class="nav-actions">
            <label class="language-control" aria-label="<?= e(t('aria.language')) ?>">
                <span aria-hidden="true">◎</span>
                <select id="languageSelect" class="language-select" aria-label="<?= e(t('aria.language')) ?>">
                    <option value="kk" <?= $lang === 'kk' ? 'selected' : '' ?>>ҚАЗ</option>
                    <option value="ru" <?= $lang === 'ru' ? 'selected' : '' ?>>РУС</option>
                    <option value="en" <?= $lang === 'en' ? 'selected' : '' ?>>ENG</option>
                </select>
            </label>
            <?php if (is_logged_in()): ?>
                <a class="nav-login" href="favorites.php"><?= e(t('nav.favorites')) ?></a>
                <span class="user-chip"><?= e($_SESSION['username'] ?? 'Player') ?></span>
                <form class="logout-form" action="logout.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <button class="nav-login logout-button" type="submit"><?= e(t('nav.logout')) ?></button>
                </form>
            <?php else: ?>
                <a class="nav-login" href="login.php"><?= e(t('nav.login')) ?></a>
                <a class="button button-small nav-register" href="register.php"><?= e(t('nav.register')) ?></a>
            <?php endif; ?>
        </div>
        <button class="menu-toggle" type="button" aria-label="<?= e(t('aria.menu')) ?>" aria-expanded="false">☰</button>
    </div>
</header>
<main class="<?= e($mainClass) ?>">
