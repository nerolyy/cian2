<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/db.php'; ?>
<?php include __DIR__ . '/../includes/auth.php'; ?>
<?php authRequireAdmin(); ?>
<main class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Админ-панель</h2>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="purposes.php">Добавление назначений для помещений</a>
            <a class="btn btn-outline-secondary" href="metro_stations.php">Станции метро</a>
            <a class="btn btn-outline-secondary" href="users.php">Пользователи</a>
            <a class="btn btn-primary" href="property_edit.php">Добавить объект</a>
        </div>
    </div>
    <?php
    $stmt = $pdo->query('SELECT * FROM properties ORDER BY id DESC');
    $rows = $stmt->fetchAll();
    ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Заголовок</th><th>Назначение</th><th>м²</th><th>Цена</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?php echo (int)$r['id']; ?></td>
                    <td><?php echo htmlspecialchars($r['title']); ?></td>
                    <td><?php echo htmlspecialchars($r['purpose']); ?></td>
                    <td><?php echo rtrim(rtrim(number_format($r['area_sqm'], 2, ',', ' '), '0'), ','); ?></td>
                    <td><?php echo number_format((int)$r['price_per_month'], 0, '.', ' '); ?> ₽</td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary" href="property_edit.php?id=<?php echo (int)$r['id']; ?>">Изменить</a>
                        <a class="btn btn-sm btn-outline-danger" href="property_delete.php?id=<?php echo (int)$r['id']; ?>" onclick="return confirm('Удалить объект?')">Удалить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>

