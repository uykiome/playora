<?php
// "Continue with Google" button. Real Google Sign-In when GOOGLE_CLIENT_ID is set (see README.md),
// otherwise a styled button that explains that Google sign-in is not configured yet.
declare(strict_types=1);

if (GOOGLE_CLIENT_ID !== ''):
    $gScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $gBase = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? ''))), '/');
    $gLoginUri = $gScheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $gBase . '/google_login.php';
?>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <div id="g_id_onload" data-client_id="<?= e(GOOGLE_CLIENT_ID) ?>" data-login_uri="<?= e($gLoginUri) ?>" data-ux_mode="redirect" data-auto_prompt="false"></div>
    <div class="google-slot">
        <div class="g_id_signin" data-type="standard" data-shape="rectangular" data-theme="filled_black" data-text="continue_with" data-size="large" data-logo_alignment="left" data-width="360"></div>
    </div>
<?php else: ?>
    <button class="social-button" type="button" onclick="document.getElementById('googleSoon').hidden = false;">
        <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true"><path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9.1 3.5l6.8-6.8C35.8 2.4 30.3 0 24 0 14.6 0 6.5 5.4 2.6 13.2l7.9 6.1C12.4 13.6 17.7 9.5 24 9.5z"/><path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v9h12.7c-.6 3-2.3 5.5-4.8 7.2l7.5 5.8c4.4-4.1 7.1-10.1 7.1-17.5z"/><path fill="#FBBC05" d="M10.5 28.7c-.5-1.4-.8-3-.8-4.7s.3-3.2.8-4.7l-7.9-6.1C.9 16.4 0 20.1 0 24s.9 7.6 2.6 10.8l7.9-6.1z"/><path fill="#34A853" d="M24 48c6.5 0 11.9-2.1 15.9-5.8l-7.5-5.8c-2.1 1.4-4.9 2.3-8.4 2.3-6.3 0-11.6-4.1-13.5-9.8l-7.9 6.1C6.5 42.6 14.6 48 24 48z"/></svg>
        <span><?= e(t('auth.google')) ?></span>
    </button>
    <p id="googleSoon" class="form-message soft-note" hidden><?= e(t('auth.googleSoon')) ?></p>
<?php endif; ?>
