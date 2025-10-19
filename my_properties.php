<?php include 'includes/header.php'; ?>
<?php include 'includes/db.php'; ?>
<?php include 'includes/auth.php'; ?>
<?php authRequireLogin(); ?>

<?php
function assetUrl($path) {
    if (!$path) return '';
    $path = str_replace('\\\\', '/', $path);
    $path = str_replace('\\', '/', $path);
    if (preg_match('~^(https?:)?//|^/~i', $path)) { return $path; }
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
    $segments = explode('/', trim($scriptName, '/'));
    $appRoot = '/' . ($segments[0] ?? '');
    return rtrim($appRoot, '/') . '/' . ltrim($path, '/');
}
?>

<?php
$currentUser = authCurrentUser();
$userId = $currentUser['id'];


$stmt = $pdo->prepare('SELECT * FROM properties WHERE contact_phone = :phone ORDER BY created_at DESC');
$stmt->execute([':phone' => $currentUser['phone'] ?? '']);
$properties = $stmt->fetchAll();


if (isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    $deleteStmt = $pdo->prepare('DELETE FROM properties WHERE id = :id AND contact_phone = :phone');
    $deleteStmt->execute([':id' => $deleteId, ':phone' => $currentUser['phone'] ?? '']);
    
    if ($deleteStmt->rowCount() > 0) {
        header('Location: my_properties.php?deleted=1');
        exit;
    }
}
?>

<main class="container my-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Главная</a></li>
                    <li class="breadcrumb-item"><a href="profile.php">Кабинет</a></li>
                    <li class="breadcrumb-item active">Мои объявления</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="page-title mb-0">
                    <i class="bi bi-house-door me-2"></i>
                    Мои объявления
                </h1>
                <a href="add_property.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Добавить объявление
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    Объявление успешно удалено
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($properties)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card-offer text-center py-5">
                    <div class="card-body">
                        <i class="bi bi-house-door text-muted" style="font-size: 4rem;"></i>
                        <h3 class="mt-3 text-muted">У вас пока нет объявлений</h3>
                        <p class="text-muted mb-4">Создайте свое первое объявление и начните сдавать помещение в аренду</p>
                        <a href="add_property.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle me-2"></i>
                            Добавить объявление
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="stats-bar p-3 mb-4">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="stat">
                                <span class="value text-primary"><?php echo count($properties); ?></span>
                                <span class="text-muted">Всего объявлений</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat">
                                <span class="value text-success"><?php echo number_format(array_sum(array_column($properties, 'price_per_month'))); ?> ₽</span>
                                <span class="text-muted">Общая стоимость</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat">
                                <span class="value text-info"><?php echo number_format(array_sum(array_column($properties, 'area_sqm')), 1); ?> м²</span>
                                <span class="text-muted">Общая площадь</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <?php foreach ($properties as $property): ?>
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card-offer h-100">
                        <div class="row g-0 h-100">
                            <div class="col-12 image-col">
                                <?php
                                $imgStmt = $pdo->prepare('SELECT image_url FROM property_images WHERE property_id = :id ORDER BY sort_order ASC, id ASC LIMIT 1');
                                $imgStmt->execute([':id'=>$property['id']]);
                                $img = $imgStmt->fetch();
                                $imageUrl = $img['image_url'] ?? $property['image_url'];
                                ?>
                                <a href="property.php?id=<?php echo (int)$property['id']; ?>">
                                    <?php if (!empty($imageUrl)): ?>
                                        <img src="<?php echo htmlspecialchars(assetUrl($imageUrl)); ?>" 
                                             alt="<?php echo htmlspecialchars($property['title']); ?>" 
                                             class="thumb">
                                    <?php else: ?>
                                        <div class="thumb d-flex align-items-center justify-content-center bg-light">
                                            <div class="text-center text-muted">
                                                <i class="bi bi-image" style="font-size: 2rem;"></i>
                                                <div class="small mt-2">Нет изображения</div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="col-12">
                                <div class="p-3">
                                    <h5 class="card-title mb-2">
                                        <a href="property.php?id=<?php echo (int)$property['id']; ?>" 
                                           class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($property['title']); ?>
                                        </a>
                                    </h5>
                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        <?php echo htmlspecialchars($property['address']); ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($property['purpose']); ?></span>
                                        <span class="text-muted small">
                                            <?php echo rtrim(rtrim(number_format($property['area_sqm'], 1, ',', ' '), '0'), ','); ?> м²
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="fw-bold text-primary fs-5">
                                            <?php echo number_format((int)$property['price_per_month'], 0, '.', ' '); ?> ₽/мес
                                        </span>
                                        <span class="text-muted small">
                                            <?php echo date('d.m.Y', strtotime($property['created_at'])); ?>
                                        </span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="property.php?id=<?php echo (int)$property['id']; ?>" 
                                           class="btn btn-outline-primary btn-sm flex-fill">
                                            <i class="bi bi-eye me-1"></i>
                                            Просмотр
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                onclick="confirmDelete(<?php echo (int)$property['id']; ?>, '<?php echo htmlspecialchars($property['title']); ?>')">
                                            <i class="bi bi-trash me-1"></i>
                                            Удалить
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>


<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить объявление <strong id="deleteTitle"></strong>?</p>
                <p class="text-danger small">Это действие нельзя отменить.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="delete_id" id="deleteId">
                    <button type="submit" class="btn btn-danger">Удалить</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, title) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteTitle').textContent = title;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>
