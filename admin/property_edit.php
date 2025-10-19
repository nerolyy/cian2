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
    // load gallery
    $ig = $pdo->prepare('SELECT image_url FROM property_images WHERE property_id = :id ORDER BY sort_order ASC, id ASC');
    $ig->execute([':id'=>$id]);
    $gallery = array_map(fn($r) => $r['image_url'], $ig->fetchAll());
}
// Load purposes dictionary
$purposeRows = $pdo->query("SELECT id, name FROM purposes ORDER BY name ASC")->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        ':title' => trim($_POST['title'] ?? ''),
        ':address' => trim($_POST['address'] ?? ''),
        ':metro' => trim($_POST['metro'] ?? ''),
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

    // Validation rules
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
        // Upsert gallery
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
    <h2 class="mb-3"><?php echo $isEdit ? 'Редактировать объект' : 'Новый объект'; ?></h2>
    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
    <form method="post" class="row g-3">
        <div class="col-md-6"><label class="form-label">Заголовок<input class="form-control" name="title" value="<?php echo htmlspecialchars($data['title']); ?>"></label></div>
        <div class="col-md-6"><label class="form-label">Адрес<input class="form-control" name="address" value="<?php echo htmlspecialchars($data['address']); ?>"></label></div>
        <div class="col-md-4"><label class="form-label">Метро<input class="form-control" name="metro" value="<?php echo htmlspecialchars($data['metro']); ?>"></label></div>
        <div class="col-md-4"><label class="form-label">Этаж<input class="form-control" name="floor" value="<?php echo htmlspecialchars($data['floor']); ?>"></label></div>
        <div class="col-md-4"><label class="form-label">Назначение
            <select class="form-select" name="purpose">
                <option value="">— выберите —</option>
                <?php foreach ($purposeRows as $pr): ?>
                    <option value="<?php echo htmlspecialchars($pr['name']); ?>" <?php echo ($data['purpose']??'')===$pr['name']?'selected':''; ?>><?php echo htmlspecialchars($pr['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </label></div>
        <div class="col-md-4"><label class="form-label">Кто сдаёт
            <select class="form-select" name="lessor_type">
                <option value="owner" <?php echo ($data['lessor_type'] ?? 'owner')==='owner'?'selected':''; ?>>Собственник</option>
                <option value="company" <?php echo ($data['lessor_type'] ?? '')==='company'?'selected':''; ?>>Компания</option>
            </select>
        </label></div>
        <div class="col-md-4"><label class="form-label">Название компании (если компания)
            <input class="form-control" name="lessor_name" value="<?php echo htmlspecialchars($data['lessor_name'] ?? ''); ?>" placeholder="ООО Ромашка">
        </label></div>
        <div class="col-md-4"><label class="form-label">Площадь, м²<input class="form-control" type="number" step="0.1" name="area_sqm" value="<?php echo htmlspecialchars($data['area_sqm']); ?>"></label></div>
        <div class="col-md-4"><label class="form-label">Цена, ₽/мес<input class="form-control" type="number" name="price_per_month" value="<?php echo htmlspecialchars($data['price_per_month']); ?>"></label></div>
        <div class="col-md-4"><label class="form-label">Фото (основное)<input class="form-control" name="image_url" value="<?php echo htmlspecialchars($data['image_url']); ?>"></label></div>
        <div class="col-md-4"><label class="form-label">Телефон контакта<input class="form-control" name="contact_phone" value="<?php echo htmlspecialchars($data['contact_phone'] ?? ''); ?>" placeholder="+7 900 000-00-00"></label></div>
        <div class="col-12"><label class="form-label">Галерея фоток 
            <textarea class="form-control" name="gallery" rows="5" placeholder="фотки добавляются через данный знак /"><?php echo htmlspecialchars(implode("\n", $gallery)); ?></textarea>
        </label></div>
        <div class="col-12"><label class="form-label">Описание
            <textarea class="form-control" name="description" rows="6" placeholder=""><?php echo htmlspecialchars($data['description'] ?? ''); ?></textarea>
        </label></div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary" type="submit"><?php echo $isEdit ? 'Сохранить' : 'Создать'; ?></button>
            <a class="btn btn-secondary" href="index.php">Отмена</a>
        </div>
    </form>
   
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>

