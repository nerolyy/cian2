<?php include __DIR__ . '/includes/header.php'; ?>
<?php include __DIR__ . '/includes/db.php'; ?>
<?php

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

<main class="container my-4">
    <nav class="breadcrumbs mb-2">
        <a href="#">Недвижимость</a> в <a href="#">Москве</a>
    </nav>
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <h1 class="page-title m-0">Снять помещение свободного назначения в Москве</h1>
    </div>

    <div class="row g-4 mt-4">
        <div class="col-12 col-lg-8">
            <?php
           
            $where = [];
            $params = [];
            if (!empty($_GET['purpose'])) { $where[] = 'purpose = :purpose'; $params[':purpose'] = $_GET['purpose']; }
            if (!empty($_GET['metro'])) { $where[] = 'metro = :metro'; $params[':metro'] = $_GET['metro']; }
            if (!empty($_GET['min_price'])) { $where[] = 'price_per_month >= :min_price'; $params[':min_price'] = (int)$_GET['min_price']; }
            if (!empty($_GET['max_price'])) { $where[] = 'price_per_month <= :max_price'; $params[':max_price'] = (int)$_GET['max_price']; }
            if (!empty($_GET['min_area'])) { $where[] = 'area_sqm >= :min_area'; $params[':min_area'] = (float)$_GET['min_area']; }
            if (!empty($_GET['max_area'])) { $where[] = 'area_sqm <= :max_area'; $params[':max_area'] = (float)$_GET['max_area']; }
            $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';
           
            $statStmt = $pdo->prepare('SELECT COUNT(*) AS cnt, AVG(price_per_month) AS avg_price FROM properties' . $whereSql);
            $statStmt->execute($params);
            $stats = $statStmt->fetch() ?: ['cnt'=>0,'avg_price'=>0];
            $foundCount = (int)($stats['cnt'] ?? 0);
            $avgPrice = (int)round((float)($stats['avg_price'] ?? 0));
            ?>
            <div class="stats-bar d-flex flex-wrap gap-4">
                <div class="stat"><span class="text-muted">Найдено</span> <span class="value"><?php echo number_format($foundCount, 0, '.', ' '); ?></span> объявлений</div>
                <div class="stat"><span class="text-muted">Средняя цена</span> <span class="value"><?php echo number_format($avgPrice, 0, '.', ' '); ?> ₽</span> в месяц</div>
            </div>
            <form class="row g-2 mb-3" method="get">
                <div class="col-12 col-md-2">
                    <?php $purposeRows = $pdo->query('SELECT name FROM purposes ORDER BY name ASC')->fetchAll(); ?>
                    <select class="form-select" name="purpose">
                        <option value="">Любое назначение</option>
                        <?php foreach ($purposeRows as $pr): $nm=$pr['name']; ?>
                            <option value="<?php echo htmlspecialchars($nm); ?>" <?php echo (($_GET['purpose'] ?? '')===$nm)?'selected':''; ?>><?php echo htmlspecialchars(ucfirst($nm)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <select class="form-select" name="metro" id="metroFilter">
                        <option value="">Любое метро</option>
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
                <div class="card card-offer overflow-hidden">
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
                            <?php if (isset($_SESSION['user'])): ?>
                                <button class="btn btn-sm btn-outline-secondary position-absolute top-0 end-0 m-3 save-property-btn" 
                                        data-property-id="<?php echo (int)$p['id']; ?>" 
                                        style="z-index: 10;">
                                    <i class="bi bi-heart"></i>
                                </button>
                            <?php endif; ?>
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
        
        
        <div class="col-12 col-lg-4">
            <div class="recommendations-section">
                <h3 class="recommendations-title mb-4">
                    <i class="bi bi-star-fill me-2"></i>
                    Могут подойти
                </h3>
                
                <div id="recommendationsContainer">
                <?php
                
                $randomStmt = $pdo->prepare('SELECT * FROM properties ORDER BY RAND() LIMIT 4');
                $randomStmt->execute();
                $randomProperties = $randomStmt->fetchAll();
                
                foreach ($randomProperties as $rec): ?>
                    <div class="recommendation-card mb-3">
                        <div class="row g-0">
                            <div class="col-4">
                                <div class="recommendation-image">
                                    <?php
                                    $recImgs = $pdo->prepare('SELECT image_url FROM property_images WHERE property_id = :id ORDER BY sort_order ASC, id ASC LIMIT 1');
                                    $recImgs->execute([':id'=>$rec['id']]);
                                    $recImage = $recImgs->fetch();
                                    ?>
                                    <a href="property.php?id=<?php echo (int)$rec['id']; ?>">
                                        <img src="<?php echo htmlspecialchars(assetUrl($recImage['image_url'] ?? $rec['image_url'])); ?>" 
                                             alt="<?php echo htmlspecialchars($rec['title']); ?>" 
                                             class="img-fluid rounded">
                                    </a>
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="recommendation-content p-2">
                                    <h6 class="recommendation-title mb-1">
                                        <a href="property.php?id=<?php echo (int)$rec['id']; ?>" 
                                           class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars(mb_substr($rec['title'], 0, 40) . (mb_strlen($rec['title']) > 40 ? '...' : '')); ?>
                                        </a>
                                    </h6>
                                    <div class="recommendation-meta small text-muted mb-1">
                                        <?php echo htmlspecialchars($rec['address']); ?>
                                    </div>
                                    <div class="recommendation-meta small text-muted mb-2">
                                        <?php echo rtrim(rtrim(number_format($rec['area_sqm'], 1, ',', ' '), '0'), ','); ?> м² • 
                                        м. <?php echo htmlspecialchars($rec['metro'] ?: '—'); ?>
                                    </div>
                                    <div class="recommendation-price fw-bold text-primary">
                                        <?php echo number_format((int)$rec['price_per_month'], 0, '.', ' '); ?> ₽/мес.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
                
                <div class="text-center mt-3">
                    <button id="refreshRecommendations" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        <span class="btn-text">Обновить рекомендации</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php if (isset($_SESSION['user'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    initSaveButtons();
    
    
    loadMetroFilter();
    
    function initSaveButtons() {
        const saveButtons = document.querySelectorAll('.save-property-btn');
        
        saveButtons.forEach(btn => {
            const propertyId = btn.dataset.propertyId;
            
            
            checkSavedStatus(propertyId, btn);
            
            // Добавляем обработчик клика
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleSaved(propertyId, btn);
            });
        });
    }
    
    function checkSavedStatus(propertyId, button) {
        fetch(`api/check_saved.php?property_id=${propertyId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateButtonState(button, data.is_saved);
                }
            })
            .catch(error => console.error('Ошибка проверки статуса:', error));
    }
    
    function toggleSaved(propertyId, button) {
        const isCurrentlySaved = button.classList.contains('saved');
        
        const url = 'api/saved_properties.php';
        const method = isCurrentlySaved ? 'DELETE' : 'POST';
        const body = JSON.stringify({ property_id: parseInt(propertyId) });
        
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: body
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateButtonState(button, !isCurrentlySaved);
                showNotification(data.message, 'success');
            } else {
                showNotification(data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showNotification('Произошла ошибка', 'error');
        });
    }
    
    function updateButtonState(button, isSaved) {
        const icon = button.querySelector('i');
        
        if (isSaved) {
            button.classList.remove('btn-outline-secondary');
            button.classList.add('btn-danger', 'saved');
            icon.className = 'bi bi-heart-fill';
        } else {
            button.classList.remove('btn-danger', 'saved');
            button.classList.add('btn-outline-secondary');
            icon.className = 'bi bi-heart';
        }
    }
    
    function showNotification(message, type) {
        // Создаем уведомление
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
        `;
        
        document.body.appendChild(notification);
        
        // Удаляем уведомление через 3 секунды
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    function loadMetroFilter() {
        const metroFilter = document.getElementById('metroFilter');
        const currentValue = '<?php echo htmlspecialchars($_GET['metro'] ?? ''); ?>';
        
        if (!metroFilter) return;
        
        fetch('api/metro_stations.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateMetroFilter(data.lines, currentValue);
                }
            })
            .catch(error => {
                console.error('Ошибка загрузки станций метро:', error);
            });
    }
    
    function populateMetroFilter(lines, currentValue) {
        const metroFilter = document.getElementById('metroFilter');
        if (!metroFilter) return;
        
        // Очищаем селект
        metroFilter.innerHTML = '<option value="">Любое метро</option>';
        
        // Добавляем станции по линиям
        lines.forEach(line => {
            const optgroup = document.createElement('optgroup');
            optgroup.label = line.line_name;
            optgroup.style.color = line.line_color;
            
            line.stations.forEach(station => {
                const option = document.createElement('option');
                option.value = station.name;
                option.textContent = station.name;
                
                // Выбираем текущее значение если есть
                if (station.name === currentValue) {
                    option.selected = true;
                }
                
                optgroup.appendChild(option);
            });
            
            metroFilter.appendChild(optgroup);
        });
    }
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
