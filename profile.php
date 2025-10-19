<?php include __DIR__ . '/includes/header.php'; ?>
<?php include __DIR__ . '/includes/db.php'; ?>
<?php include __DIR__ . '/includes/auth.php'; ?>
<?php authRequireLogin(); authUpdateCurrentUserSession($pdo); $user = authCurrentUser(); ?>
<?php
$errors = [];
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($phone === '') { $phone = null; }
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($newPassword !== '' || $confirmPassword !== '') {
        if (strlen($newPassword) < 6) {
            $errors[] = 'Пароль должен быть не менее 6 символов';
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Пароли не совпадают';
        }
    }

    if (!$errors) {
        try {
            if ($newPassword !== '') {
                $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare('UPDATE users SET name = :name, phone = :phone, password_hash = :ph WHERE id = :id');
                $stmt->execute([':name'=>$name, ':phone'=>$phone, ':ph'=>$hash, ':id'=>$user['id']]);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET name = :name, phone = :phone WHERE id = :id');
                $stmt->execute([':name'=>$name, ':phone'=>$phone, ':id'=>$user['id']]);
            }
            authUpdateCurrentUserSession($pdo);
            $success = 'Профиль обновлён';
        } catch (Throwable $e) {
            $errors[] = 'Не удалось сохранить изменения';
        }
    }
}

$user = authCurrentUser();
?>
<main class="container my-5" style="max-width:720px;">
    <h2 class="mb-4">Личный кабинет</h2>

    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
    <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Профиль</h5>
                    <form method="post" class="vstack gap-3">
                        <div>
                            <label class="form-label">Email</label>
                            <input class="form-control" type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        </div>
                        <div>
                            <label class="form-label">Имя</label>
                            <input class="form-control" type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
                        </div>
                        <div>
                            <label class="form-label">Телефон</label>
                            <input class="form-control" type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+7 900 000-00-00">
                        </div>
                        <hr>
                        <div>
                            <label class="form-label">Новый пароль</label>
                            <input class="form-control" type="password" name="new_password" placeholder="Оставьте пустым, чтобы не менять">
                        </div>
                        <div>
                            <label class="form-label">Повторите пароль</label>
                            <input class="form-control" type="password" name="confirm_password">
                        </div>
                        <div>
                            <button class="btn btn-primary" type="submit">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-5">
            <?php if (($user['role'] ?? 'user') === 'admin'): ?>
                <div class="card border-primary">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Админ панель</h5>
                        <p class="text-muted small">Доступны инструменты управления.</p>
                        <div class="d-grid gap-2">
                            <?php
                            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
                            $segments = explode('/', trim($scriptName, '/'));
                            $appRoot = '/' . ($segments[0] ?? '');
                            ?>
                            <a class="btn btn-outline-primary" href="<?php echo htmlspecialchars($appRoot); ?>/admin/index.php">Управление объявлениями</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Статус</h6>
                        <p class="text-muted small m-0">Обычный пользователь</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>


