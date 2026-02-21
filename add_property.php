<?php include 'includes/header.php'; ?>
<?php include 'includes/db.php'; ?>
<?php include 'includes/auth.php'; ?>
<?php require_once __DIR__ . '/includes/recaptcha.php'; ?>
<?php authRequireLogin(); ?>

<?php

$purposeRows = $pdo->query("SELECT id, name FROM purposes ORDER BY name ASC")->fetchAll();


$data = [
    'title' => '', 'address' => '', 'metro' => '', 'floor' => '', 'purpose' => '',
    'area_sqm' => '', 'price_per_month' => '', 'image_url' => '', 'contact_phone' => '', 'description' => '',
    'lessor_type' => 'owner', 'lessor_name' => ''
];
$gallery = [];
$errors = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка reCAPTCHA (только если настроена)
    if (isRecaptchaConfigured()) {
        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
        $recaptchaResult = verifyRecaptcha($recaptchaResponse, getClientIp());
        
        if (!$recaptchaResult['success']) {
            $errors[] = 'Пожалуйста, подтвердите, что вы не робот';
        }
    }
    
    $payload = [
        ':title' => trim($_POST['title'] ?? ''),
        ':address' => trim($_POST['address'] ?? ''),
        ':metro' => trim($_POST['metro'] ?? $_POST['metro_text'] ?? ''),
        ':floor' => trim($_POST['floor'] ?? ''),
        ':purpose' => trim($_POST['purpose'] ?? ''),
        ':area_sqm' => (float)($_POST['area_sqm'] ?? 0),
        ':price_per_month' => (int)($_POST['price_per_month'] ?? 0),
        ':image_url' => trim($_POST['image_url'] ?? ''),
        ':contact_phone' => trim($_POST['contact_phone'] ?? ''),
        ':description' => trim($_POST['description'] ?? ''),
        ':lessor_type' => ($_POST['lessor_type'] ?? 'owner') === 'company' ? 'company' : 'owner',
        ':lessor_name' => trim($_POST['lessor_name'] ?? ''),
    ];
    
    
    $galleryInput = trim($_POST['gallery'] ?? '');
    $galleryUrls = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $galleryInput)), fn($u) => $u !== ''));

    
    if ($payload[':title'] === '') { $errors[] = 'Укажите заголовок объявления'; }
    if ($payload[':address'] === '') { $errors[] = 'Укажите адрес объекта'; }
    if ($payload[':purpose'] === '') { $errors[] = 'Выберите назначение помещения'; }
    if ($payload[':area_sqm'] <= 0) { $errors[] = 'Площадь должна быть больше 0'; }
    if ($payload[':price_per_month'] <= 0) { $errors[] = 'Цена должна быть больше 0'; }
    if ($payload[':contact_phone'] === '') { $errors[] = 'Укажите контактный телефон'; }
    
    
    if ($payload[':contact_phone'] !== '') {
        $clean = preg_replace('/[^\d+]/', '', $payload[':contact_phone']);
        if (!preg_match('/^(\+7\d{10}|8\d{10})$/', $clean)) {
            $errors[] = 'Телефон должен быть российским: +7XXXXXXXXXX или 8XXXXXXXXXX';
        } else {
            $payload[':contact_phone'] = $clean;
        }
    }
    
    
    if ($payload[':image_url'] === '') { $errors[] = 'Укажите URL основного изображения'; }
    
    
    if (!$errors) {
        $sql = 'INSERT INTO properties (title,address,metro,floor,purpose,area_sqm,price_per_month,image_url,contact_phone,description,lessor_type,lessor_name) VALUES (:title,:address,:metro,:floor,:purpose,:area_sqm,:price_per_month,:image_url,:contact_phone,:description,:lessor_type,:lessor_name)';
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($payload);
        
        $propId = (int)$pdo->lastInsertId();
        if ($propId > 0) {
            
            if ($galleryUrls) {
                $ins = $pdo->prepare('INSERT INTO property_images (property_id,image_url,sort_order) VALUES (:pid,:url,:ord)');
                $ord = 0;
                foreach ($galleryUrls as $u) {
                    $ins->execute([':pid'=>$propId, ':url'=>$u, ':ord'=>$ord++]);
                }
            }
            
            
            header('Location: property.php?id=' . $propId);
            exit;
        } else {
            $errors[] = 'Ошибка при сохранении объявления';
        }
    }
    
    
    $data = array_merge($data, $_POST);
    $gallery = $galleryUrls;
}
?>

<main class="container my-4" style="max-width:900px;">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Главная</a></li>
                    <li class="breadcrumb-item active">Добавить объявление</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <h1 class="page-title mb-4">
                <i class="bi bi-plus-circle me-2"></i>
                Добавить объявление
            </h1>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger">
                    <h6 class="alert-heading">Исправьте следующие ошибки:</h6>
                    <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                            <li><?php echo htmlspecialchars($e); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card-offer">
                <div class="card-body p-4">
                    <form method="post" class="row g-3">
                        
                        <div class="col-12">
                            <h5 class="mb-3 text-primary">
                                <i class="bi bi-info-circle me-2"></i>
                                Основная информация
                            </h5>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Заголовок объявления *</label>
                            <input class="form-control" name="title" value="<?php echo htmlspecialchars($data['title']); ?>" 
                                   placeholder="Например: Сдается офис в центре города" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Адрес объекта *</label>
                            <input class="form-control" name="address" value="<?php echo htmlspecialchars($data['address']); ?>" 
                                   placeholder="Например: ул. Тверская, д. 1" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Ближайшее метро</label>
                            <select class="form-select" name="metro" id="metroSelect">
                                <option value="">— выберите станцию —</option>
                            </select>
                            <input type="text" class="form-control d-none" name="metro_text" id="metroText" 
                                   value="<?php echo htmlspecialchars($data['metro']); ?>" 
                                   placeholder="Введите название станции метро">
                            <div class="form-text">Начните вводить название станции для поиска</div>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Этаж</label>
                            <input class="form-control" name="floor" value="<?php echo htmlspecialchars($data['floor']); ?>" 
                                   placeholder="Например: 3">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Назначение *</label>
                            <select class="form-select" name="purpose" required>
                                <option value="">— выберите назначение —</option>
                                <?php foreach ($purposeRows as $pr): ?>
                                    <option value="<?php echo htmlspecialchars($pr['name']); ?>" 
                                            <?php echo ($data['purpose']??'')===$pr['name']?'selected':''; ?>>
                                        <?php echo htmlspecialchars($pr['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        
                        <div class="col-12 mt-4">
                            <h5 class="mb-3 text-primary">
                                <i class="bi bi-rulers me-2"></i>
                                Характеристики
                            </h5>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Площадь, м² *</label>
                            <input class="form-control" type="number" step="0.1" name="area_sqm" 
                                   value="<?php echo htmlspecialchars($data['area_sqm']); ?>" 
                                   placeholder="Например: 50.5" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Цена аренды, ₽/мес *</label>
                            <input class="form-control" type="number" name="price_per_month" 
                                   value="<?php echo htmlspecialchars($data['price_per_month']); ?>" 
                                   placeholder="Например: 100000" required>
                        </div>

                        
                        <div class="col-12 mt-4">
                            <h5 class="mb-3 text-primary">
                                <i class="bi bi-person-lines-fill me-2"></i>
                                Контактная информация
                            </h5>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Кто сдает *</label>
                            <select class="form-select" name="lessor_type" required>
                                <option value="owner" <?php echo ($data['lessor_type'] ?? 'owner')==='owner'?'selected':''; ?>>Собственник</option>
                                <option value="company" <?php echo ($data['lessor_type'] ?? '')==='company'?'selected':''; ?>>Компания</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Название компании</label>
                            <input class="form-control" name="lessor_name" 
                                   value="<?php echo htmlspecialchars($data['lessor_name'] ?? ''); ?>" 
                                   placeholder="ООО Ромашка (если сдает компания)">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-bold">Контактный телефон *</label>
                            <input class="form-control" name="contact_phone" 
                                   value="<?php echo htmlspecialchars($data['contact_phone'] ?? ''); ?>" 
                                   placeholder="+7 900 000-00-00" required>
                        </div>

                        
                        <div class="col-12 mt-4">
                            <h5 class="mb-3 text-primary">
                                <i class="bi bi-images me-2"></i>
                                Изображения
                            </h5>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-bold">Основное фото *</label>
                            <input class="form-control" name="image_url" 
                                   value="<?php echo htmlspecialchars($data['image_url']); ?>" 
                                   placeholder="https://example.com/photo.jpg" required>
                            <div class="form-text">Укажите URL основного изображения объекта</div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-bold">Галерея фотографий</label>
                            <textarea class="form-control" name="gallery" rows="5" 
                                      placeholder="Добавьте URL дополнительных фотографий, каждое с новой строки:&#10;https://example.com/photo1.jpg&#10;https://example.com/photo2.jpg"><?php echo htmlspecialchars(implode("\n", $gallery)); ?></textarea>
                            <div class="form-text">Каждый URL с новой строки</div>
                        </div>

                       
                        <div class="col-12 mt-4">
                            <h5 class="mb-3 text-primary">
                                <i class="bi bi-file-text me-2"></i>
                                Описание
                            </h5>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-bold">Описание объекта</label>
                            <textarea class="form-control" name="description" rows="6" 
                                      placeholder="Опишите особенности объекта, удобства, расположение и другие важные детали..."><?php echo htmlspecialchars($data['description'] ?? ''); ?></textarea>
                        </div>

                        <?php
                        // Отображаем виджет reCAPTCHA, если настроена
                        if (isRecaptchaConfigured()) {
                            echo '<div class="col-12 mt-3">';
                            echo '<div class="g-recaptcha" data-sitekey="' . htmlspecialchars(RECAPTCHA_SITE_KEY) . '"></div>';
                            echo '</div>';
                        }
                        ?>
                        
                        <div class="col-12 mt-4">
                            <div class="d-flex gap-3">
                                <button class="btn btn-primary btn-lg" type="submit">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Опубликовать объявление
                                </button>
                                <a class="btn btn-outline-secondary btn-lg" href="index.php">
                                    <i class="bi bi-arrow-left me-2"></i>
                                    Отмена
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const metroSelect = document.getElementById('metroSelect');
    const currentValue = '<?php echo htmlspecialchars($data['metro']); ?>';
    
    
    loadMetroStations();
    
    function loadMetroStations() {
        console.log('Загружаем станции метро...');
        fetch('api/metro_stations.php')
            .then(response => {
                console.log('Ответ получен:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Данные получены:', data);
                if (data.success) {
                    populateMetroSelect(data.lines);
                    console.log('Станции загружены успешно');
                } else {
                    console.error('Ошибка загрузки станций метро:', data.error);
                    showMetroError('Ошибка загрузки станций: ' + (data.error || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                console.error('Ошибка загрузки станций метро:', error);
                showMetroError('Не удалось загрузить станции метро. Проверьте подключение к интернету.');
            });
    }
    
    function showMetroError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-warning mt-2';
        errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>' + message + 
                            ' <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="switchToTextInput()">Использовать текстовое поле</button>';
        metroSelect.parentNode.appendChild(errorDiv);
        
        
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.parentNode.removeChild(errorDiv);
            }
        }, 10000);
    }
    
    
    window.switchToTextInput = function() {
        const metroSelect = document.getElementById('metroSelect');
        const metroText = document.getElementById('metroText');
        
        if (metroSelect && metroText) {
            metroSelect.classList.add('d-none');
            metroText.classList.remove('d-none');
            metroText.focus();
            
            
            if (metroSelect.value) {
                metroText.value = metroSelect.value;
            }
        }
    };
    
    function populateMetroSelect(lines) {
        
        metroSelect.innerHTML = '<option value="">— выберите станцию —</option>';
        
        
        lines.forEach(line => {
            const optgroup = document.createElement('optgroup');
            optgroup.label = line.line_name;
            optgroup.style.color = line.line_color;
            
            line.stations.forEach(station => {
                const option = document.createElement('option');
                option.value = station.name;
                option.textContent = station.name;
                
                
                if (station.name === currentValue) {
                    option.selected = true;
                }
                
                optgroup.appendChild(option);
            });
            
            metroSelect.appendChild(optgroup);
        });
    }
    
    let searchTimeout;
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.className = 'form-control mt-2';
    searchInput.placeholder = 'Поиск станции...';
    searchInput.style.display = 'none';
    
    metroSelect.parentNode.appendChild(searchInput);
    
    metroSelect.addEventListener('focus', function() {
        searchInput.style.display = 'block';
        searchInput.focus();
    });
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => {
                searchMetroStations(query);
            }, 300);
        } else if (query.length === 0) {
            loadMetroStations();
        }
    });
    
    function searchMetroStations(query) {
        fetch(`api/metro_stations.php?search=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateMetroSelect(data.lines);
                }
            })
            .catch(error => {
                console.error('Ошибка поиска станций:', error);
            });
    }
    
    
    document.addEventListener('click', function(e) {
        if (!metroSelect.contains(e.target) && !searchInput.contains(e.target)) {
            searchInput.style.display = 'none';
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
