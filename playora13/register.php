<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
if (is_logged_in()) redirect('index.php');

$pageTitle = t('title.register');
$bodyClass = 'auth-page';
$mainClass = 'auth-main auth-center';
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
                start_user_session([
                    'id' => (int) $pdo->lastInsertId(),
                    'username' => $usernameValue,
                    'email' => $emailValue,
                    'role' => 'user',
                ], 'register.success');
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
<section class="auth-card">
    <div class="auth-tabs" role="tablist">
        <a href="login.php" role="tab" aria-selected="false"><?= e(t('auth.tabLogin')) ?></a>
        <a class="active" href="register.php" role="tab" aria-selected="true"><?= e(t('auth.tabRegister')) ?></a>
    </div>
    <div class="auth-card-heading">
        <h1><?= e(t('register.welcome')) ?></h1>
        <p><?= e(t('register.subtitle')) ?></p>
    </div>
    <?php require __DIR__ . '/includes/google_button.php'; ?>
    <div class="auth-divider"><span><?= e(t('auth.divider')) ?></span></div>
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
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
