<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
if (is_logged_in()) redirect('index.php');

$pageTitle = 'Жүйеге кіру — Playora';
$bodyClass = 'auth-page';
$mainClass = 'auth-main';
$errors = [];
$emailValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailValue = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if (!verify_csrf()) $errors[] = 'Сұраныс мерзімі аяқталды. Бетті жаңартып, қайта көріңіз.';
    if (!filter_var($emailValue, FILTER_VALIDATE_EMAIL)) $errors[] = 'Электрондық поштаны дұрыс енгізіңіз.';
    if ($password === '') $errors[] = 'Құпиясөзді енгізіңіз.';
    if (!$pdo) $errors[] = $dbError ?? 'Дерекқор қолжетімсіз.';

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('SELECT id, username, email, password_hash FROM users WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $emailValue]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password_hash'])) {
                if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $update = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
                    $update->execute(['hash' => $newHash, 'id' => $user['id']]);
                }
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['flash'] = 'Жүйеге сәтті кірдіңіз!';
                redirect('index.php');
            }
            $errors[] = 'Электрондық пошта немесе құпиясөз қате.';
        } catch (PDOException $exception) {
            error_log('Playora login error: ' . $exception->getMessage());
            $errors[] = 'Кіру кезінде қате пайда болды. Қайта көріңіз.';
        }
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="auth-decoration"><div class="auth-orbit"></div><p class="eyebrow"><span class="pulse-dot"></span> ҚАЙТА КЕЛГЕНІҢЕ ҚУАНЫШТЫМЫЗ</p><h1>Ойын әлемі<br><span>сені күтеді.</span></h1><p>Аккаунтыңа кіріп, өзіңе ұнайтын ойындарды бір жерге жина.</p><div class="auth-mini-card"><span>PLAYORA / MEMBERS</span><strong>READY<br>PLAYER<span>_</span></strong></div></div>
<section class="auth-card"><div class="auth-card-heading"><span class="auth-icon">↗</span><p class="eyebrow">PLAYORA АККАУНТЫ</p><h2 data-i18n="loginTitle">Жүйеге кіру</h2><p>Жалғастыру үшін деректеріңді енгіз.</p></div>
<form id="loginForm" class="auth-form" method="post" action="login.php">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <label for="loginEmail">Электрондық пошта</label><input id="loginEmail" name="email" type="email" placeholder="name@example.com" autocomplete="email" required value="<?= e($emailValue) ?>">
    <div class="label-row"><label for="loginPassword">Құпиясөз</label><button class="small-link" type="button" id="showPassword" aria-controls="loginPassword">Көрсету</button></div>
    <input id="loginPassword" name="password" type="password" placeholder="Құпиясөзіңді енгіз" autocomplete="current-password" required>
    <button class="button button-full" type="submit">Кіру <span>→</span></button>
    <?php if ($errors): ?><div class="form-message" role="alert"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
</form>
<p class="auth-switch">Аккаунтың жоқ па? <a href="register.php">Тіркелу</a></p><p class="prototype-note">Қауіпсіз кіру жүйесі PHP сессиялары мен хештелген құпиясөздерді пайдаланады.</p></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
