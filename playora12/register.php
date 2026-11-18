<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
if (is_logged_in()) redirect('index.php');

$pageTitle = t('title.register');
$bodyClass = 'auth-page';
$mainClass = 'auth-main register-layout';
$errors = [];
$usernameValue = '';
$emailValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameValue = trim((string) ($_POST['username'] ?? ''));
    $emailValue = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirmPassword'] ?? '');

    if (!verify_csrf()) $errors[] = t('err.csrfExpired');
    if (!preg_match('/^[\p{L}\p{N}_. -]{3,30}$/u', $usernameValue)) $errors[] = t('register.errUsername');
    if (!filter_var($emailValue, FILTER_VALIDATE_EMAIL)) $errors[] = t('err.email');
    if (strlen($password) < 8 || strlen($password) > 72) $errors[] = t('register.errPasswordLength');
    if ($password !== $confirmPassword) $errors[] = t('register.errMismatch');
    if (!$pdo) $errors[] = $dbError ?? t('err.dbUnavailable');

    if (!$errors) {
        try {
            $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $check->execute(['email' => $emailValue]);
            if ($check->fetch()) {
                $errors[] = t('register.errExists');
            } else {
                $insert = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)');
                $insert->execute([
                    'username' => $usernameValue,
                    'email' => $emailValue,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $pdo->lastInsertId();
                $_SESSION['username'] = $usernameValue;
                $_SESSION['email'] = $emailValue;
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['flash'] = t('register.success');
                redirect('index.php');
            }
        } catch (PDOException $exception) {
            error_log('Playora registration error: ' . $exception->getMessage());
            $errors[] = t('register.failed');
        }
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="auth-decoration">
    <div class="auth-orbit"></div>
    <p class="eyebrow"><span class="pulse-dot"></span> <?= e(t('register.eyebrow')) ?></p>
    <h1><?= e(t('register.heroA')) ?><br><span><?= e(t('register.heroB')) ?></span></h1>
    <p><?= e(t('register.heroText')) ?></p>
    <div class="auth-mini-card"><span>NEW PLAYER / 001</span><strong>CREATE<br>YOUR <span>LEGACY.</span></strong></div>
</div>
<section class="auth-card">
    <div class="auth-card-heading">
        <span class="auth-icon">＋</span>
        <p class="eyebrow"><?= e(t('register.cardEyebrow')) ?></p>
        <h2><?= e(t('register.title')) ?></h2>
        <p><?= e(t('register.subtitle')) ?></p>
    </div>
    <form id="registerForm" class="auth-form" method="post" action="register.php" data-mismatch="<?= e(t('register.errMismatch')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label for="registerName"><?= e(t('register.username')) ?></label>
        <input id="registerName" name="username" type="text" placeholder="<?= e(t('register.usernamePlaceholder')) ?>" minlength="3" maxlength="30" autocomplete="username" required value="<?= e($usernameValue) ?>">
        <label for="registerEmail"><?= e(t('form.email')) ?></label>
        <input id="registerEmail" name="email" type="email" placeholder="name@example.com" autocomplete="email" required value="<?= e($emailValue) ?>">
        <label for="registerPassword"><?= e(t('form.password')) ?></label>
        <input id="registerPassword" name="password" type="password" placeholder="<?= e(t('register.passwordPlaceholder')) ?>" minlength="8" maxlength="72" autocomplete="new-password" required>
        <label for="confirmPassword"><?= e(t('register.confirm')) ?></label>
        <input id="confirmPassword" name="confirmPassword" type="password" placeholder="<?= e(t('register.confirmPlaceholder')) ?>" minlength="8" maxlength="72" autocomplete="new-password" required>
        <button class="button button-full" type="submit"><?= e(t('register.submit')) ?> <span>→</span></button>
        <?php if ($errors): ?><div class="form-message" role="alert"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
    </form>
    <p class="auth-switch"><?= e(t('register.haveAccount')) ?> <a href="login.php"><?= e(t('login.title')) ?></a></p>
    <p class="prototype-note"><?= e(t('register.note')) ?></p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
