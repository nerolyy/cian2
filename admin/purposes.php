<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/db.php'; ?>
<?php include __DIR__ . '/../includes/auth.php'; ?>
<?php authRequireAdmin(); ?>
<?php
// Handle create/update/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            $stmt = $pdo->prepare('INSERT IGNORE INTO purposes (name) VALUES (:n)');
            $stmt->execute([':n'=>$name]);
        }
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($id > 0 && $name !== '') {
            $stmt = $pdo->prepare('UPDATE purposes SET name = :n WHERE id = :id');
            $stmt->execute([':n'=>$name, ':id'=>$id]);
        }
    }
    header('Location: purposes.php');
    exit;
}
if (($_GET['action'] ?? '') === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare('DELETE FROM purposes WHERE id = :id')->execute([':id'=>$id]);
    }
    header('Location: purposes.php');
    exit;
}

$rows = $pdo->query('SELECT * FROM purposes ORDER BY name ASC')->fetchAll();
?>
<main class="container my-4" style="max-width:800px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Справочник назначений</h2>
        <a class="btn btn-outline-secondary" href="index.php">← К объектам</a>
    </div>

    <form class="row g-2 mb-4" method="post">
        <input type="hidden" name="action" value="create">
        <div class="col-8 col-md-9">
            <input class="form-control" type="text" name="name" placeholder="Новое назначение, например: пекарня" required>
        </div>
        <div class="col-4 col-md-3 d-grid">
            <button class="btn btn-primary" type="submit">Добавить</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th style="width:60px;">ID</th><th>Название</th><th style="width:160px;"></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?php echo (int)$r['id']; ?></td>
                    <td>
                        <form class="d-flex gap-2" method="post">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                            <input class="form-control" type="text" name="name" value="<?php echo htmlspecialchars($r['name']); ?>">
                            <button class="btn btn-outline-primary" type="submit">Сохранить</button>
                        </form>
                    </td>
                    <td class="text-end">
                        <a class="btn btn-outline-danger btn-sm" href="purposes.php?action=delete&id=<?php echo (int)$r['id']; ?>" onclick="return confirm('Удалить назначение?');">Удалить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>





