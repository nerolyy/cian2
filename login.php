<?php include __DIR__ . '/includes/header.php'; ?>
<?php include __DIR__ . '/includes/db.php'; ?>
<?php include __DIR__ . '/includes/auth.php'; ?>
<?php
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (authLogin($pdo, $email, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Неверный email или пароль';
    }
}
?>
<main class="container my-5" style="max-width:560px;">
    <h2 class="mb-3">Вход</h2>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" class="vstack gap-3">
        <input class="form-control" type="email" name="email" placeholder="Email" required>
        <input class="form-control" type="password" name="password" placeholder="Пароль" required>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Войти</button>
            <a class="btn btn-outline-secondary" href="register.php">Регистрация</a>
        </div>
    </form>
    <p class="small text-muted mt-3">Админ (seed): admin@example.com / admin123</p>
    <p class="small text-muted">Пароль админа задается в database.sql. Измените в проде.</p>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>

