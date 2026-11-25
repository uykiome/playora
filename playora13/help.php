<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$pageTitle = t('title.help');
$bodyClass = 'help-page';
$activePage = 'help';
$mailto = 'mailto:' . SUPPORT_EMAIL . '?subject=' . rawurlencode(t('help.mailSubject'));
require __DIR__ . '/includes/header.php';
?>
<div class="container help-section">
    <a class="back-link" href="index.php"><?= e(t('help.backHome')) ?></a>
    <div class="help-hero">
        <div>
            <p class="eyebrow"><?= e(t('help.eyebrow')) ?></p>
            <h1><?= e(t('help.titleA')) ?> <span><?= e(t('help.titleB')) ?></span></h1>
            <p class="help-intro"><?= e(t('help.intro')) ?></p>
        </div>
        <div class="help-contact-card">
            <span class="help-contact-icon">✉</span>
            <span class="fact-label"><?= e(t('help.emailLabel')) ?></span>
            <a class="help-email" href="<?= e($mailto) ?>"><?= e(SUPPORT_EMAIL) ?></a>
            <a class="button" href="<?= e($mailto) ?>"><?= e(t('help.writeUs')) ?> <span>↗</span></a>
            <?php if (SUPPORT_PHONE !== ''): ?>
                <span class="fact-label help-phone-label"><?= e(t('help.phoneLabel')) ?></span>
                <a class="help-email" href="tel:<?= e(preg_replace('/[^0-9+]/', '', SUPPORT_PHONE)) ?>"><?= e(SUPPORT_PHONE) ?></a>
            <?php endif; ?>
        </div>
    </div>

    <div class="help-grid">
        <section class="help-tips">
            <h2><?= e(t('help.tipsTitle')) ?></h2>
            <ul>
                <?php foreach (['help.tip1', 'help.tip2', 'help.tip3', 'help.tip4'] as $tipKey): ?>
                    <li><?= e(t($tipKey)) ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
        <section class="help-faq">
            <h2><?= e(t('help.faqTitle')) ?></h2>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <details class="faq-item">
                    <summary><?= e(t('help.q' . $i)) ?></summary>
                    <p><?= e(t('help.a' . $i)) ?></p>
                </details>
            <?php endfor; ?>
        </section>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
