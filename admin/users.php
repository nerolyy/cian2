<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/db.php'; ?>
<?php include __DIR__ . '/../includes/auth.php'; ?>
<?php authRequireAdmin(); ?>
<?php
$stmt = $pdo->query('SELECT id, email, name, phone, role, created_at FROM users ORDER BY id DESC');
$rows = $stmt->fetchAll();
?>
<main class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Пользователи</h2>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Email</th><th>Имя</th><th>Телефон</th><th>Роль</th><th>Создан</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $u): ?>
                <tr>
                    <td><?php echo (int)$u['id']; ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo htmlspecialchars($u['name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($u['phone'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($u['role']); ?></td>
                    <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary" href="user_edit.php?id=<?php echo (int)$u['id']; ?>">Изменить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>


