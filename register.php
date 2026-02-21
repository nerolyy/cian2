<?php include __DIR__ . '/includes/header.php'; ?>
<?php include __DIR__ . '/includes/db.php'; ?>
<?php include __DIR__ . '/includes/auth.php'; ?>
<?php
require_once __DIR__ . '/includes/recaptcha.php';

$errors = [];
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка reCAPTCHA (только если настроена)
    if (isRecaptchaConfigured()) {
        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
        $recaptchaResult = verifyRecaptcha($recaptchaResponse, getClientIp());
        
        if (!$recaptchaResult['success']) {
            $errors[] = 'Пожалуйста, подтвердите, что вы не робот';
        }
    }
    
    if (empty($errors)) {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        if ($phone === '') { $phone = null; }
        [$ok, $errs] = authRegister($pdo, $email, $password, $name, $phone);
        if ($ok) {
            authLogin($pdo, $email, $password);
            header('Location: index.php');
            exit;
        } else {
            $errors = array_merge($errors, $errs);
        }
    }
}
?>
<main class="container my-5" style="max-width:560px;">
    <h2 class="mb-3">Регистрация</h2>
    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
    <form method="post" class="vstack gap-3">
        <input class="form-control" type="text" name="name" placeholder="Имя" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
        <input class="form-control" type="text" name="phone" placeholder="Телефон" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
        <input class="form-control" type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        <input class="form-control" type="password" name="password" placeholder="Пароль (мин. 6)" required>
        <?php
        // Отображаем виджет reCAPTCHA, если настроена
        if (isRecaptchaConfigured()) {
            echo '<div class="g-recaptcha" data-sitekey="' . htmlspecialchars(RECAPTCHA_SITE_KEY) . '"></div>';
        }
        ?>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Создать аккаунт</button>
            <a class="btn btn-outline-secondary" href="login.php">У меня уже есть аккаунт</a>
        </div>
    </form>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>

