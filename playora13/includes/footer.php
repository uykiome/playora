</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <p>© <?= date('Y') ?> Playora</p>
    </div>
</footer>
<div class="toast" id="toast" role="status" aria-live="polite" data-message="<?= e($flash ?? '') ?>"></div>
<?php if (!empty($flash)): ?>
    <noscript><div class="container"><p class="flash-message" role="status"><?= e($flash) ?></p></div></noscript>
<?php endif; ?>
</body>
</html>
