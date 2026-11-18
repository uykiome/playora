</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <p>© <?= date('Y') ?> <?= e(t('footer.note')) ?></p>
        <div class="footer-links">
            <a href="help.php" class="footer-help"><?= e(t('footer.help')) ?></a>
            <a href="#top" class="back-top"><?= e(t('footer.top')) ?></a>
        </div>
    </div>
</footer>
<div class="toast" id="toast" role="status" aria-live="polite" data-message="<?= e($flash ?? '') ?>"></div>
<?php if (!empty($flash)): ?>
    <noscript><div class="container"><p class="flash-message" role="status"><?= e($flash) ?></p></div></noscript>
<?php endif; ?>
</body>
</html>
