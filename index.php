<?php ini_set('display_errors', 1); error_reporting(E_ALL); ?>
<?php include __DIR__ . '/includes/header.php'; ?>
<?php include __DIR__ . '/includes/db.php'; ?>
<?php
// Resolve image URL relative to app root from header ($appRoot)
function assetUrl($path) {
    global $appRoot; // defined in includes/header.php
    if (!$path) return '';
    $path = str_replace('\\\\', '/', $path);
    $path = str_replace('\\', '/', $path);
    if (preg_match('~^(https?:)?//|^/~i', $path)) { return $path; }
    $root = isset($appRoot) ? $appRoot : '';
    return rtrim($root, '/') . '/' . ltrim($path, '/');
}
?>

<main class="container my-4">
    <nav class="breadcrumbs mb-2">
        <a href="#">Недвижимость</a> в <a href="#">Москве</a>
    </nav>
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <h1 class="page-title m-0">Снять помещение свободного назначения в Москве</h1>
    </div>

    <div class="row g-4 mt-3">
        <div class="col-12 col-lg-8">
            <?php
            // Build filters first to reuse for stats
            $where = [];
            $params = [];
            if (!empty($_GET['purpose'])) { $where[] = 'purpose = :purpose'; $params[':purpose'] = $_GET['purpose']; }
            if (!empty($_GET['min_price'])) { $where[] = 'price_per_month >= :min_price'; $params[':min_price'] = (int)$_GET['min_price']; }
            if (!empty($_GET['max_price'])) { $where[] = 'price_per_month <= :max_price'; $params[':max_price'] = (int)$_GET['max_price']; }
            if (!empty($_GET['min_area'])) { $where[] = 'area_sqm >= :min_area'; $params[':min_area'] = (float)$_GET['min_area']; }
            if (!empty($_GET['max_area'])) { $where[] = 'area_sqm <= :max_area'; $params[':max_area'] = (float)$_GET['max_area']; }
            $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';
            // Stats
            $statStmt = $pdo->prepare('SELECT COUNT(*) AS cnt, AVG(price_per_month) AS avg_price FROM properties' . $whereSql);
            $statStmt->execute($params);
            $stats = $statStmt->fetch() ?: ['cnt'=>0,'avg_price'=>0];
            $foundCount = (int)($stats['cnt'] ?? 0);
            $avgPrice = (int)round((float)($stats['avg_price'] ?? 0));
            ?>
            <div class="stats-bar p-3 mb-3 d-flex flex-wrap gap-4">
                <div class="stat"><span class="text-muted">Найдено</span> <span class="value"><?php echo number_format($foundCount, 0, '.', ' '); ?></span> объявлений</div>
                <div class="stat"><span class="text-muted">Средняя цена</span> <span class="value"><?php echo number_format($avgPrice, 0, '.', ' '); ?> ₽</span> в месяц</div>
            </div>
            <form class="row g-2 mb-3" method="get">
                <div class="col-12 col-md-3">
                    <?php $purposeRows = $pdo->query('SELECT name FROM purposes ORDER BY name ASC')->fetchAll(); ?>
                    <select class="form-select" name="purpose">
                        <option value="">Любое назначение</option>
                        <?php foreach ($purposeRows as $pr): $nm=$pr['name']; ?>
                            <option value="<?php echo htmlspecialchars($nm); ?>" <?php echo (($_GET['purpose'] ?? '')===$nm)?'selected':''; ?>><?php echo htmlspecialchars(ucfirst($nm)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2"><input class="form-control" type="number" name="min_price" placeholder="Мин. цена" value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>"></div>
                <div class="col-6 col-md-2"><input class="form-control" type="number" name="max_price" placeholder="Макс. цена" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>"></div>
                <div class="col-6 col-md-2"><input class="form-control" type="number" step="0.1" name="min_area" placeholder="Мин. м²" value="<?php echo htmlspecialchars($_GET['min_area'] ?? ''); ?>"></div>
                <div class="col-6 col-md-2"><input class="form-control" type="number" step="0.1" name="max_area" placeholder="Макс. м²" value="<?php echo htmlspecialchars($_GET['max_area'] ?? ''); ?>"></div>
                <div class="col-12 col-md-1 d-grid"><button class="btn btn-primary" type="submit">Фильтр</button></div>
            </form>

            <?php
            $sql = 'SELECT * FROM properties' . $whereSql;
            $sql .= ' ORDER BY price_per_month ASC LIMIT 20';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $list = $stmt->fetchAll();
            foreach ($list as $p): ?>
                <div class="card card-offer overflow-hidden mb-3">
                    <div class="row g-0">
                        <div class="col-12 col-md-6 image-col">
                            <?php
                            $imgs = $pdo->prepare('SELECT image_url FROM property_images WHERE property_id = :id ORDER BY sort_order ASC, id ASC LIMIT 6');
                            $imgs->execute([':id'=>$p['id']]);
                            $images = $imgs->fetchAll();
                            $hasGallery = $images && count($images) > 0;
                            ?>
                            <?php if ($hasGallery): ?>
                            <div class="img-slider" data-prop-id="<?php echo (int)$p['id']; ?>">
                                <?php foreach ($images as $i => $im): ?>
                                    <a class="slide<?php echo $i===0 ? ' active' : ''; ?>" href="property.php?id=<?php echo (int)$p['id']; ?>">
                                        <img class="thumb" src="<?php echo htmlspecialchars(assetUrl($im['image_url'])); ?>" alt="<?php echo htmlspecialchars($p['title']); ?>">
                                    </a>
                                <?php endforeach; ?>
                                <button class="nav prev" type="button" aria-label="Prev" onclick="slidePrev(this)">❮</button>
                                <button class="nav next" type="button" aria-label="Next" onclick="slideNext(this)">❯</button>
                            </div>
                            <?php else: ?>
                                <a href="property.php?id=<?php echo (int)$p['id']; ?>">
                                    <img class="thumb w-100" src="<?php echo htmlspecialchars(assetUrl($p['image_url'])); ?>" alt="<?php echo htmlspecialchars($p['title']); ?>">
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-md-6 p-3 p-md-4 position-relative">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <a class="link-dark text-decoration-none fw-semibold" href="property.php?id=<?php echo (int)$p['id']; ?>"><?php echo htmlspecialchars($p['title']); ?></a>
                                <span class="badge badge-owner rounded-pill px-2 py-1"><?php echo ($p['lessor_type'] ?? 'owner')==='company' ? 'Компания' : 'Собственник'; ?></span>
                            </div>
                            <div class="offer-meta mb-1"><?php echo htmlspecialchars($p['address']); ?></div>
                            <div class="offer-meta mb-2">м. <?php echo htmlspecialchars($p['metro'] ?: '—'); ?> • <?php echo htmlspecialchars($p['purpose'] ?: '—'); ?></div>
                            <div class="h5 mb-3"><?php echo number_format((int)$p['price_per_month'], 0, '.', ' '); ?> ₽/мес.</div>
                            <div class="level-list">
                                <div class="row py-2 align-items-center">
                                    <div class="col-auto"><span><?php echo rtrim(rtrim(number_format($p['area_sqm'], 2, ',', ' '), '0'), ','); ?> м²</span></div>
                                    <div class="col-auto price-strong"><?php echo number_format((int)$p['price_per_month'], 0, '.', ' '); ?> ₽/мес.</div>
                                </div>
                            </div>
                            <div class="mt-3 p-3 border rounded small">
                                <div class="d-grid gap-2 mb-2">
                                    <?php $mask = $p['contact_phone'] ? preg_replace('/(\d{2})(\d{2})$/', '**', $p['contact_phone']) : null; ?>
                                    <button class="btn btn-primary btn-sm" type="button" onclick="this.nextElementSibling?.classList.remove('d-none'); this.classList.add('d-none');">Показать телефон</button>
                                    <div class="fw-semibold <?php echo $p['contact_phone'] ? '' : 'text-muted'; ?> d-none"><?php echo htmlspecialchars($p['contact_phone'] ?: 'Телефон не указан'); ?></div>
                                    <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#chatModal" data-prop-title="<?php echo htmlspecialchars($p['title']); ?>">Написать сообщение</button>
                                </div>
                                
                            </div>
                            <a class="stretched-link" href="property.php?id=<?php echo (int)$p['id']; ?>" aria-label="Открыть"></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
