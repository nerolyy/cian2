<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/db.php'; ?>
<?php include __DIR__ . '/../includes/auth.php'; ?>
<?php authRequireAdmin(); ?>
<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: users.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute([':id'=>$id]);
$u = $stmt->fetch();
if (!$u) { header('Location: users.php'); exit; }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';
    if ($phone !== '') {
        $clean = preg_replace('/[^\d+]/', '', $phone);
        if (!preg_match('/^(\+7\d{10}|8\d{10})$/', $clean)) {
            $errors[] = 'Телефон должен быть российским: +7XXXXXXXXXX или 8XXXXXXXXXX';
        } else {
            $phone = $clean;
        }
    }
    if (!$errors) {
        $upd = $pdo->prepare('UPDATE users SET name = :name, phone = :phone, role = :role WHERE id = :id');
        $upd->execute([':name'=>$name, ':phone'=>$phone, ':role'=>$role, ':id'=>$id]);
        header('Location: users.php');
        exit;
    }
}
?>
<main class="container my-4" style="max-width:720px;">
    <h2 class="mb-3">Редактировать пользователя #<?php echo (int)$u['id']; ?></h2>
    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
    <form method="post" class="vstack gap-3">
        <div>
            <label class="form-label">Email</label>
            <input class="form-control" value="<?php echo htmlspecialchars($u['email']); ?>" disabled>
        </div>
        <div>
            <label class="form-label">Имя</label>
            <input class="form-control" name="name" value="<?php echo htmlspecialchars($u['name'] ?? ''); ?>">
        </div>
        <div>
            <label class="form-label">Телефон</label>
            <input class="form-control" name="phone" value="<?php echo htmlspecialchars($u['phone'] ?? ''); ?>" placeholder="+7XXXXXXXXXX">
        </div>
        <div>
            <label class="form-label">Роль</label>
            <select class="form-select" name="role">
                <option value="user" <?php echo ($u['role'] ?? 'user')==='user'?'selected':''; ?>>Пользователь</option>
                <option value="admin" <?php echo ($u['role'] ?? '')==='admin'?'selected':''; ?>>Администратор</option>
            </select>
        </div>
        <div>
            <button class="btn btn-primary" type="submit">Сохранить</button>
            <a class="btn btn-secondary" href="users.php">Отмена</a>
        </div>
    </form>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>


