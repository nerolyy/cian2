<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/db.php'; ?>
<?php include __DIR__ . '/../includes/auth.php'; ?>
<?php authRequireAdmin(); ?>

<?php
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_station'])) {
    $name = trim($_POST['name'] ?? '');
    $lineName = trim($_POST['line_name'] ?? '');
    $lineColor = trim($_POST['line_color'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    
    if (empty($name)) { $errors[] = 'Укажите название станции'; }
    if (empty($lineName)) { $errors[] = 'Укажите название линии'; }
    if (empty($lineColor)) { $errors[] = 'Укажите цвет линии'; }
    
    if (!$errors) {
        try {
            $stmt = $pdo->prepare('INSERT INTO metro_stations (name, line_name, line_color, sort_order) VALUES (:name, :line_name, :line_color, :sort_order)');
            $stmt->execute([
                ':name' => $name,
                ':line_name' => $lineName,
                ':line_color' => $lineColor,
                ':sort_order' => $sortOrder
            ]);
            $success = 'Станция успешно добавлена';
        } catch (Exception $e) {
            $errors[] = 'Ошибка при добавлении станции: ' . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_station'])) {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $lineName = trim($_POST['line_name'] ?? '');
    $lineColor = trim($_POST['line_color'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($name)) { $errors[] = 'Укажите название станции'; }
    if (empty($lineName)) { $errors[] = 'Укажите название линии'; }
    if (empty($lineColor)) { $errors[] = 'Укажите цвет линии'; }
    
    if (!$errors) {
        try {
            $stmt = $pdo->prepare('UPDATE metro_stations SET name = :name, line_name = :line_name, line_color = :line_color, sort_order = :sort_order, is_active = :is_active WHERE id = :id');
            $stmt->execute([
                ':id' => $id,
                ':name' => $name,
                ':line_name' => $lineName,
                ':line_color' => $lineColor,
                ':sort_order' => $sortOrder,
                ':is_active' => $isActive
            ]);
            $success = 'Станция успешно обновлена';
        } catch (Exception $e) {
            $errors[] = 'Ошибка при обновлении станции: ' . $e->getMessage();
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_station'])) {
    $id = (int)($_POST['id'] ?? 0);
    
    try {
        $stmt = $pdo->prepare('DELETE FROM metro_stations WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $success = 'Станция успешно удалена';
    } catch (Exception $e) {
        $errors[] = 'Ошибка при удалении станции: ' . $e->getMessage();
    }
}

$stmt = $pdo->query('SELECT * FROM metro_stations ORDER BY line_name, sort_order, name');
$stations = $stmt->fetchAll();


$linesStmt = $pdo->query('SELECT DISTINCT line_name, line_color FROM metro_stations ORDER BY line_name');
$lines = $linesStmt->fetchAll();
?>

<main class="container my-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Админ панель</a></li>
                    <li class="breadcrumb-item active">Станции метро</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <h1 class="page-title mb-4">
                <i class="bi bi-train-front me-2"></i>
                Управление станциями метро
            </h1>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger">
                    <h6 class="alert-heading">Ошибки:</h6>
                    <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                            <li><?php echo htmlspecialchars($e); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card-offer">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">Все станции метро</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStationModal">
                            <i class="bi bi-plus-circle me-2"></i>
                            Добавить станцию
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Линия</th>
                                    <th>Цвет</th>
                                    <th>Порядок</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stations as $station): ?>
                                    <tr>
                                        <td><?php echo (int)$station['id']; ?></td>
                                        <td><?php echo htmlspecialchars($station['name']); ?></td>
                                        <td><?php echo htmlspecialchars($station['line_name']); ?></td>
                                        <td>
                                            <span class="badge" style="background-color: <?php echo htmlspecialchars($station['line_color']); ?>; color: white;">
                                                <?php echo htmlspecialchars($station['line_color']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo (int)$station['sort_order']; ?></td>
                                        <td>
                                            <?php if ($station['is_active']): ?>
                                                <span class="badge bg-success">Активна</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Неактивна</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editStation(<?php echo htmlspecialchars(json_encode($station)); ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="post" style="display: inline;" 
                                                  onsubmit="return confirm('Удалить станцию <?php echo htmlspecialchars($station['name']); ?>?')">
                                                <input type="hidden" name="id" value="<?php echo (int)$station['id']; ?>">
                                                <button type="submit" name="delete_station" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>


<div class="modal fade" id="addStationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить станцию метро</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Название станции *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Название линии *</label>
                        <input type="text" class="form-control" name="line_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Цвет линии *</label>
                        <input type="color" class="form-control form-control-color" name="line_color" value="#D52B1E" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Порядок сортировки</label>
                        <input type="number" class="form-control" name="sort_order" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" name="add_station" class="btn btn-primary">Добавить</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="editStationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Редактировать станцию метро</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Название станции *</label>
                        <input type="text" class="form-control" name="name" id="editName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Название линии *</label>
                        <input type="text" class="form-control" name="line_name" id="editLineName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Цвет линии *</label>
                        <input type="color" class="form-control form-control-color" name="line_color" id="editLineColor" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Порядок сортировки</label>
                        <input type="number" class="form-control" name="sort_order" id="editSortOrder">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive" value="1">
                            <label class="form-check-label" for="editIsActive">
                                Активна
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" name="edit_station" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editStation(station) {
    document.getElementById('editId').value = station.id;
    document.getElementById('editName').value = station.name;
    document.getElementById('editLineName').value = station.line_name;
    document.getElementById('editLineColor').value = station.line_color;
    document.getElementById('editSortOrder').value = station.sort_order;
    document.getElementById('editIsActive').checked = station.is_active == 1;
    
    new bootstrap.Modal(document.getElementById('editStationModal')).show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
