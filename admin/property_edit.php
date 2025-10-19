<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/db.php'; ?>
<?php include __DIR__ . '/../includes/auth.php'; ?>
<?php authRequireAdmin(); ?>
<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$data = [
    'title' => '', 'address' => '', 'metro' => '', 'floor' => '', 'purpose' => '',
    'area_sqm' => '', 'price_per_month' => '', 'image_url' => '', 'contact_phone' => '', 'description' => ''
];
$gallery = [];
$errors = [];
if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM properties WHERE id = :id');
    $stmt->execute([':id'=>$id]);
    $row = $stmt->fetch();
    if ($row) { $data = $row; }
    
    $ig = $pdo->prepare('SELECT image_url FROM property_images WHERE property_id = :id ORDER BY sort_order ASC, id ASC');
    $ig->execute([':id'=>$id]);
    $gallery = array_map(fn($r) => $r['image_url'], $ig->fetchAll());
}

$purposeRows = $pdo->query("SELECT id, name FROM purposes ORDER BY name ASC")->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    
    if ($payload[':title'] === '') { $errors[] = 'Укажите заголовок'; }
    if ($payload[':address'] === '') { $errors[] = 'Укажите адрес'; }
    if ($payload[':purpose'] === '') { $errors[] = 'Выберите назначение'; }
    if ($payload[':area_sqm'] <= 0) { $errors[] = 'Площадь должна быть больше 0'; }
    if ($payload[':price_per_month'] <= 0) { $errors[] = 'Цена должна быть больше 0'; }
    if ($payload[':contact_phone'] !== '') {
        $clean = preg_replace('/[^\d+]/', '', $payload[':contact_phone']);
        if (!preg_match('/^(\+7\d{10}|8\d{10})$/', $clean)) {
            $errors[] = 'Телефон должен быть российским: +7XXXXXXXXXX или 8XXXXXXXXXX';
        } else {
            $payload[':contact_phone'] = $clean;
        }
    }
    if ($isEdit) {
        $sql = 'UPDATE properties SET title=:title,address=:address,metro=:metro,floor=:floor,purpose=:purpose,area_sqm=:area_sqm,price_per_month=:price_per_month,image_url=:image_url,contact_phone=:contact_phone,description=:description,lessor_type=:lessor_type,lessor_name=:lessor_name WHERE id=:id';
        $payload[':id'] = $id;
    } else {
        $sql = 'INSERT INTO properties (title,address,metro,floor,purpose,area_sqm,price_per_month,image_url,contact_phone,description,lessor_type,lessor_name) VALUES (:title,:address,:metro,:floor,:purpose,:area_sqm,:price_per_month,:image_url,:contact_phone,:description,:lessor_type,:lessor_name)';
    }
    if (!$errors) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($payload);
        
        $propId = $isEdit ? $id : (int)$pdo->lastInsertId();
        if ($propId > 0) {
            $pdo->prepare('DELETE FROM property_images WHERE property_id = :id')->execute([':id'=>$propId]);
            if ($galleryUrls) {
                $ins = $pdo->prepare('INSERT INTO property_images (property_id,image_url,sort_order) VALUES (:pid,:url,:ord)');
                $ord = 0;
                foreach ($galleryUrls as $u) {
                    $ins->execute([':pid'=>$propId, ':url'=>$u, ':ord'=>$ord++]);
                }
            }
        }
        header('Location: index.php');
        exit;
    }
}
?>
<main class="container my-4" style="max-width:900px;">
    <h2 class="mb-4" style="font-size: 2rem; font-weight: 700; color: #1e293b;"><?php echo $isEdit ? 'Редактировать объект' : 'Новый объект'; ?></h2>
    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
    <form method="post" class="row g-4">
        <div class="col-md-6"><label class="form-label fw-bold">Заголовок<input class="form-control" name="title" value="<?php echo htmlspecialchars($data['title']); ?>" style="min-height: 48px; font-size: 1rem;"></label></div>
        <div class="col-md-6"><label class="form-label fw-bold">Адрес<input class="form-control" name="address" value="<?php echo htmlspecialchars($data['address']); ?>" style="min-height: 48px; font-size: 1rem;"></label></div>
        <div class="col-md-4">
            <label class="form-label fw-bold">Метро</label>
            <select class="form-select" name="metro" id="metroSelect" style="min-height: 48px; font-size: 1rem;">
                <option value="">— выберите станцию —</option>
            </select>
            <input type="text" class="form-control d-none" name="metro_text" id="metroText" 
                   value="<?php echo htmlspecialchars($data['metro']); ?>" 
                   placeholder="Введите название станции метро"
                   style="min-height: 48px; font-size: 1rem;">
            <div class="form-text">Начните вводить название станции для поиска</div>
        </div>
        <div class="col-md-4"><label class="form-label fw-bold">Этаж<input class="form-control" name="floor" value="<?php echo htmlspecialchars($data['floor']); ?>" style="min-height: 48px; font-size: 1rem;"></label></div>
        <div class="col-md-4"><label class="form-label fw-bold">Назначение
            <select class="form-select" name="purpose" style="min-height: 48px; font-size: 1rem;">
                <option value="">— выберите —</option>
                <?php foreach ($purposeRows as $pr): ?>
                    <option value="<?php echo htmlspecialchars($pr['name']); ?>" <?php echo ($data['purpose']??'')===$pr['name']?'selected':''; ?>><?php echo htmlspecialchars($pr['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </label></div>
        <div class="col-md-4"><label class="form-label fw-bold">Кто сдаёт
            <select class="form-select" name="lessor_type" style="min-height: 48px; font-size: 1rem;">
                <option value="owner" <?php echo ($data['lessor_type'] ?? 'owner')==='owner'?'selected':''; ?>>Собственник</option>
                <option value="company" <?php echo ($data['lessor_type'] ?? '')==='company'?'selected':''; ?>>Компания</option>
            </select>
        </label></div>
        <div class="col-md-4"><label class="form-label fw-bold">Название компании (если компания)
            <input class="form-control" name="lessor_name" value="<?php echo htmlspecialchars($data['lessor_name'] ?? ''); ?>" placeholder="" style="min-height: 48px; font-size: 1rem;">
        </label></div>
        <div class="col-md-4"><label class="form-label fw-bold">Площадь, м²<input class="form-control" type="number" step="0.1" name="area_sqm" value="<?php echo htmlspecialchars($data['area_sqm']); ?>" style="min-height: 48px; font-size: 1rem;"></label></div>
        <div class="col-md-4"><label class="form-label fw-bold">Цена, ₽/мес<input class="form-control" type="number" name="price_per_month" value="<?php echo htmlspecialchars($data['price_per_month']); ?>" style="min-height: 48px; font-size: 1rem;"></label></div>
        <div class="col-md-4"><label class="form-label fw-bold">Фото (основное)<input class="form-control" name="image_url" value="<?php echo htmlspecialchars($data['image_url']); ?>" style="min-height: 48px; font-size: 1rem;"></label></div>
        <div class="col-md-4"><label class="form-label fw-bold">Телефон контакта<input class="form-control" name="contact_phone" value="<?php echo htmlspecialchars($data['contact_phone'] ?? ''); ?>" placeholder="+7 900 000-00-00" style="min-height: 48px; font-size: 1rem;"></label></div>
        <div class="col-12"><label class="form-label fw-bold">Галерея фоток 
            <textarea class="form-control" name="gallery" rows="5" placeholder="фотки добавляются через данный знак /" style="font-size: 1rem; padding: 0.75rem;"><?php echo htmlspecialchars(implode("\n", $gallery)); ?></textarea>
        </label></div>
        <div class="col-12"><label class="form-label fw-bold">Описание
            <textarea class="form-control" name="description" rows="6" placeholder="" style="font-size: 1rem; padding: 0.75rem;"><?php echo htmlspecialchars($data['description'] ?? ''); ?></textarea>
        </label></div>
        <div class="col-12 d-flex gap-3">
            <button class="btn btn-primary btn-lg" type="submit" style="padding: 0.75rem 2rem; font-size: 1.1rem; font-weight: 600;"><?php echo $isEdit ? 'Сохранить' : 'Создать'; ?></button>
            <a class="btn btn-secondary btn-lg" href="index.php" style="padding: 0.75rem 2rem; font-size: 1.1rem; font-weight: 600;">Отмена</a>
        </div>
    </form>
   
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const metroSelect = document.getElementById('metroSelect');
    const metroText = document.getElementById('metroText');
    const currentValue = '<?php echo htmlspecialchars($data['metro']); ?>';
    
    
    loadMetroStations();
    
    function loadMetroStations() {
        console.log('Загружаем станции метро...');
        fetch('../api/metro_stations.php')
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
        fetch(`../api/metro_stations.php?search=${encodeURIComponent(query)}`)
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

<?php include __DIR__ . '/../includes/footer.php'; ?>

