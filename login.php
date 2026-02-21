<?php include __DIR__ . '/includes/header.php'; ?>
<?php include __DIR__ . '/includes/db.php'; ?>
<?php include __DIR__ . '/includes/auth.php'; ?>
<?php
require_once __DIR__ . '/includes/recaptcha.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка reCAPTCHA (только если настроена)
    if (isRecaptchaConfigured()) {
        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
        $recaptchaResult = verifyRecaptcha($recaptchaResponse, getClientIp());
        
        if (!$recaptchaResult['success']) {
            $error = 'Пожалуйста, подтвердите, что вы не робот';
        }
    }
    
    if (empty($error)) {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (authLogin($pdo, $email, $password)) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Неверный email или пароль';
        }
    }
}
?>
<main class="container my-5" style="max-width:560px;">
    <h2 class="mb-3">Вход</h2>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" class="vstack gap-3">
        <input class="form-control" type="email" name="email" placeholder="Email" required>
        <input class="form-control" type="password" name="password" placeholder="Пароль" required>
        <?php
        // Отображаем виджет reCAPTCHA, если настроена
        if (isRecaptchaConfigured()) {
            echo '<div class="g-recaptcha" data-sitekey="' . htmlspecialchars(RECAPTCHA_SITE_KEY) . '"></div>';
        }
        ?>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Войти</button>
            <a class="btn btn-outline-secondary" href="register.php">Регистрация</a>
        </div>
    </form>
    
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>

