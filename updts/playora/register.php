<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
if (is_logged_in()) redirect('index.php');

$pageTitle = 'Тіркелу — Playora';
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

    if (!verify_csrf()) $errors[] = 'Сұраныс мерзімі аяқталды. Бетті жаңартып, қайта көріңіз.';
    if (!preg_match('/^[\p{L}\p{N}_. -]{3,30}$/u', $usernameValue)) $errors[] = 'Пайдаланушы аты 3-тен 30 таңбаға дейін болуы керек.';
    if (!filter_var($emailValue, FILTER_VALIDATE_EMAIL)) $errors[] = 'Электрондық поштаны дұрыс енгізіңіз.';
    if (strlen($password) < 8 || strlen($password) > 72) $errors[] = 'Құпиясөз 8-ден 72 байтқа дейін болуы керек.';
    if ($password !== $confirmPassword) $errors[] = 'Құпиясөздер бір-біріне сәйкес келмейді.';
    if (!$pdo) $errors[] = $dbError ?? 'Дерекқор қолжетімсіз.';

    if (!$errors) {
        try {
            $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $check->execute(['email' => $emailValue]);
            if ($check->fetch()) {
                $errors[] = 'Бұл электрондық пошта бұрын тіркелген.';
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
                $_SESSION['flash'] = 'Аккаунтың сәтті құрылды. Playora-ға қош келдіңіз!';
                redirect('index.php');
            }
        } catch (PDOException $exception) {
            error_log('Playora registration error: ' . $exception->getMessage());
            $errors[] = 'Тіркелу кезінде қате пайда болды. Қайта көріңіз.';
        }
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="auth-decoration"><div class="auth-orbit"></div><p class="eyebrow"><span class="pulse-dot"></span> ӨЗ ОЙЫН ӘЛЕМІҢДІ БАСТА</p><h1>Жаңа деңгейге<br><span>көтеріл.</span></h1><p>Тіркеліп, өзіңе ұнайтын ойындарды таңдаулылар тізіміне жина.</p><div class="auth-mini-card"><span>NEW PLAYER / 001</span><strong>CREATE<br>YOUR <span>LEGACY.</span></strong></div></div>
<section class="auth-card"><div class="auth-card-heading"><span class="auth-icon">＋</span><p class="eyebrow">ҚАУЫМДАСТЫҚҚА ҚОСЫЛ</p><h2 data-i18n="registerTitle">Тіркелу</h2><p>Жаңа аккаунт жасау үшін форманы толтыр.</p></div>
<form id="registerForm" class="auth-form" method="post" action="register.php">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <label for="registerName">Пайдаланушы аты</label><input id="registerName" name="username" type="text" placeholder="Мысалы, player01" minlength="3" maxlength="30" autocomplete="username" required value="<?= e($usernameValue) ?>">
    <label for="registerEmail">Электрондық пошта</label><input id="registerEmail" name="email" type="email" placeholder="name@example.com" autocomplete="email" required value="<?= e($emailValue) ?>">
    <label for="registerPassword">Құпиясөз</label><input id="registerPassword" name="password" type="password" placeholder="Кемінде 8 таңба" minlength="8" maxlength="72" autocomplete="new-password" required>
    <label for="confirmPassword">Құпиясөзді қайтала</label><input id="confirmPassword" name="confirmPassword" type="password" placeholder="Құпиясөзді қайта енгіз" minlength="8" maxlength="72" autocomplete="new-password" required>
    <button class="button button-full" type="submit">Аккаунт жасау <span>→</span></button>
    <?php if ($errors): ?><div class="form-message" role="alert"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
</form>
<p class="auth-switch">Аккаунтың бар ма? <a href="login.php">Жүйеге кіру</a></p>
<p class="prototype-note">Деректер MySQL дерекқорында қауіпсіз түрде сақталады.</p></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
