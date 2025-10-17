<?php include __DIR__ . '/includes/header.php'; ?>
<?php include __DIR__ . '/includes/db.php'; ?>
<?php
// Resolve image URL relative to app root from header ($appRoot)
function assetUrl($path) {
    global $appRoot;
    if (!$path) return '';
    $path = str_replace('\\\\', '/', $path);
    $path = str_replace('\\', '/', $path);
    if (preg_match('~^(https?:)?//|^/~i', $path)) { return $path; }
    $root = isset($appRoot) ? $appRoot : '';
    return rtrim($root, '/') . '/' . ltrim($path, '/');
}
?>
<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php');
    exit;
}
$stmt = $pdo->prepare('SELECT * FROM properties WHERE id = :id LIMIT 1');
$stmt->execute([':id'=>$id]);
$p = $stmt->fetch();
if (!$p) {
    http_response_code(404);
}
?>
<main class="container my-4">
    <?php if (!$p): ?>
        <div class="alert alert-warning">Объект не найден</div>
    <?php else: ?>
    <nav class="breadcrumbs mb-2">
        <a href="index.php">Недвижимость</a> / <span><?php echo htmlspecialchars($p['title']); ?></span>
    </nav>
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="mb-3">
                <?php
                $imgs = $pdo->prepare('SELECT image_url FROM property_images WHERE property_id = :id ORDER BY sort_order ASC, id ASC');
                $imgs->execute([':id'=>$p['id']]);
                $images = $imgs->fetchAll();
                ?>
                <?php if ($images): ?>
                <div id="propPageCarousel_<?php echo (int)$p['id']; ?>" class="carousel slide">
                    <div class="carousel-inner">
                        <?php foreach ($images as $i => $im): ?>
                            <div class="carousel-item<?php echo $i===0 ? ' active' : ''; ?>">
                                <img class="w-100 rounded prop-hero" src="<?php echo htmlspecialchars(assetUrl($im['image_url'])); ?>" alt="<?php echo htmlspecialchars($p['title']); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#propPageCarousel_<?php echo (int)$p['id']; ?>" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Prev</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#propPageCarousel_<?php echo (int)$p['id']; ?>" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
                <?php else: ?>
                    <img class="w-100 rounded" src="<?php echo htmlspecialchars(assetUrl($p['image_url'])); ?>" alt="<?php echo htmlspecialchars($p['title']); ?>">
                <?php endif; ?>
            </div>
            <h1 class="h4 mb-2"><?php echo htmlspecialchars($p['title']); ?></h1>
            <div class="text-muted mb-3"><?php echo htmlspecialchars($p['address']); ?></div>
            <div class="d-flex flex-wrap gap-3 mb-3">
                <div>м. <?php echo htmlspecialchars($p['metro'] ?: '—'); ?></div>
                <div><?php echo htmlspecialchars($p['floor'] ?: '—'); ?></div>
                <div><?php echo rtrim(rtrim(number_format($p['area_sqm'], 2, ',', ' '), '0'), ','); ?> м²</div>
            </div>
            <div class="mb-4">
                <h5>Описание</h5>
                <?php if (!empty($p['description'])): ?>
                    <div><?php echo nl2br(htmlspecialchars($p['description'])); ?></div>
                <?php else: ?>
                    <p>Помещение под: <?php echo htmlspecialchars($p['purpose'] ?: 'свободное назначение'); ?>.</p>
                <?php endif; ?>
            </div>
        </div>
        <aside class="col-12 col-lg-4">
            <div class="p-3 border rounded">
                <div class="h4 mb-2"><?php echo number_format((int)$p['price_per_month'], 0, '.', ' '); ?> ₽/мес.</div>
                <div class="small text-muted mb-3">Цена за объект</div>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" type="button" onclick="this.nextElementSibling?.classList.remove('d-none'); this.classList.add('d-none');">Показать телефон</button>
                    <div class="fw-semibold d-none"><?php echo htmlspecialchars($p['contact_phone'] ?: 'Телефон не указан'); ?></div>
                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#chatModal" data-prop-title="<?php echo htmlspecialchars($p['title']); ?>">Написать сообщение</button>
                </div>
                <div class="small text-muted mt-3">
                    <?php if (($p['lessor_type'] ?? 'owner') === 'company'): ?>Компания<?php else: ?>Собственник<?php endif; ?>
                    <?php if (!empty($p['lessor_name'])): ?> • <?php echo htmlspecialchars($p['lessor_name']); ?><?php endif; ?>
                </div>
                <div class="d-flex align-items-center gap-2 small mt-1">
                    <span class="text-success">✓</span>
                    <span class="text-muted">Документы агента проверены</span>
                </div>
            </div>
        </aside>
    </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>


