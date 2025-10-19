<?php include __DIR__ . '/includes/header.php'; ?>
<?php include __DIR__ . '/includes/db.php'; ?>
<?php include __DIR__ . '/includes/auth.php'; ?>
<?php authRequireLogin(); authUpdateCurrentUserSession($pdo); $user = authCurrentUser(); ?>
<?php
$errors = [];
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($phone === '') { $phone = null; }
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($newPassword !== '' || $confirmPassword !== '') {
        if (strlen($newPassword) < 6) {
            $errors[] = 'Пароль должен быть не менее 6 символов';
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Пароли не совпадают';
        }
    }

    if (!$errors) {
        try {
            if ($newPassword !== '') {
                $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare('UPDATE users SET name = :name, phone = :phone, password_hash = :ph WHERE id = :id');
                $stmt->execute([':name'=>$name, ':phone'=>$phone, ':ph'=>$hash, ':id'=>$user['id']]);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET name = :name, phone = :phone WHERE id = :id');
                $stmt->execute([':name'=>$name, ':phone'=>$phone, ':id'=>$user['id']]);
            }
            authUpdateCurrentUserSession($pdo);
            $success = 'Профиль обновлён';
        } catch (Throwable $e) {
            $errors[] = 'Не удалось сохранить изменения';
        }
    }
}

$user = authCurrentUser();
?>
<main class="container my-5" style="max-width:1200px;">
    <h2 class="mb-4">Личный кабинет</h2>

    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
    <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

    
    <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                <i class="bi bi-person me-2"></i>Профиль
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="my-properties-tab" data-bs-toggle="tab" data-bs-target="#my-properties" type="button" role="tab">
                <i class="bi bi-house-door me-2"></i>Мои объявления
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="saved-properties-tab" data-bs-toggle="tab" data-bs-target="#saved-properties" type="button" role="tab">
                <i class="bi bi-heart me-2"></i>Сохраненные
            </button>
        </li>
    </ul>

    
    <div class="tab-content" id="profileTabsContent">
        
        <div class="tab-pane fade show active" id="profile" role="tabpanel">
            <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Профиль</h5>
                    <form method="post" class="vstack gap-3">
                        <div>
                            <label class="form-label">Email</label>
                            <input class="form-control" type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        </div>
                        <div>
                            <label class="form-label">Имя</label>
                            <input class="form-control" type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
                        </div>
                        <div>
                            <label class="form-label">Телефон</label>
                            <input class="form-control" type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+7 900 000-00-00">
                        </div>
                        <hr>
                        <div>
                            <label class="form-label">Новый пароль</label>
                            <input class="form-control" type="password" name="new_password" placeholder="Оставьте пустым, чтобы не менять">
                        </div>
                        <div>
                            <label class="form-label">Повторите пароль</label>
                            <input class="form-control" type="password" name="confirm_password">
                        </div>
                        <div>
                            <button class="btn btn-primary" type="submit">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-5">
            <?php if (($user['role'] ?? 'user') === 'admin'): ?>
                <div class="card border-primary">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Админ панель</h5>
                        <p class="text-muted small">Доступны инструменты управления.</p>
                        <div class="d-grid gap-2">
                            <?php
                            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
                            $segments = explode('/', trim($scriptName, '/'));
                            $appRoot = '/' . ($segments[0] ?? '');
                            ?>
                            <a class="btn btn-outline-primary" href="<?php echo htmlspecialchars($appRoot); ?>/admin/index.php">Управление объявлениями</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Мои объявления</h6>
                        <p class="text-muted small mb-3">Управляйте своими объявлениями</p>
                        <div class="d-grid gap-2">
                            <a class="btn btn-outline-primary" href="my_properties.php">
                                <i class="bi bi-house-door me-2"></i>
                                Мои объявления
                            </a>
                            <a class="btn btn-primary" href="add_property.php">
                                <i class="bi bi-plus-circle me-2"></i>
                                Добавить объявление
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
        </div>

        
        <div class="tab-pane fade" id="my-properties" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">Мои объявления</h3>
                <a href="add_property.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Добавить объявление
                </a>
            </div>
            
            <div id="my-properties-content">
                
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                    <p class="mt-3 text-muted">Загрузка ваших объявлений...</p>
                </div>
            </div>
        </div>

       
        <div class="tab-pane fade" id="saved-properties" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">Сохраненные объявления</h3>
                <span class="badge bg-secondary" id="saved-count">0</span>
            </div>
            
            <div id="saved-properties-content">
                
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                    <p class="mt-3 text-muted">Загрузка сохраненных объявлений...</p>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const myPropertiesTab = document.getElementById('my-properties-tab');
    const savedPropertiesTab = document.getElementById('saved-properties-tab');
    
    myPropertiesTab.addEventListener('shown.bs.tab', function() {
        loadMyProperties();
    });
    
    savedPropertiesTab.addEventListener('shown.bs.tab', function() {
        loadSavedProperties();
    });
    
    
    loadSavedPropertiesCount();
    
    
    function initDeleteButtons() {
        const deleteButtons = document.querySelectorAll('button[onclick*="confirmDelete"]');
        deleteButtons.forEach(button => {
            
            const onclickAttr = button.getAttribute('onclick');
            const match = onclickAttr?.match(/confirmDelete\((\d+)/);
            const propertyId = match ? parseInt(match[1]) : null;
            
            if (propertyId) {
                
                button.removeAttribute('onclick');
                
                button.addEventListener('click', function() {
                    confirmDelete(propertyId);
                });
            }
        });
    }
    
   
    function confirmDelete(propertyId, title = '') {
        if (!confirm('Вы уверены, что хотите удалить это объявление?')) return;
        
        
        const content = document.getElementById('my-properties-content');
        const originalContent = content.innerHTML;
        content.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Удаление...</span>
                </div>
                <p class="mt-3 text-muted">Удаление объявления...</p>
            </div>
        `;
        
        
        const formData = new FormData();
        formData.append('delete_id', propertyId);
        
        fetch('my_properties.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
           
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const mainContent = doc.querySelector('main');
            
            if (mainContent) {
                
                const title = mainContent.querySelector('h1');
                const breadcrumb = mainContent.querySelector('nav');
                const addButton = mainContent.querySelector('.d-flex.justify-content-between.align-items-center.mb-4 .btn');
                if (title) title.remove();
                if (breadcrumb) breadcrumb.remove();
                if (addButton) addButton.remove();
                
                content.innerHTML = mainContent.innerHTML;
                
                
                initDeleteButtons();
                
                showNotification('Объявление успешно удалено', 'success');
            } else {
                content.innerHTML = originalContent;
                showNotification('Ошибка при удалении объявления', 'error');
            }
        })
        .catch(error => {
            console.error('Ошибка удаления:', error);
            content.innerHTML = originalContent;
            showNotification('Произошла ошибка при удалении', 'error');
        });
    }
    
    function loadMyProperties() {
        const content = document.getElementById('my-properties-content');
        
        fetch('my_properties.php')
            .then(response => response.text())
            .then(html => {
                
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const mainContent = doc.querySelector('main');
                
                if (mainContent) {
                    
                    const title = mainContent.querySelector('h1');
                    const breadcrumb = mainContent.querySelector('nav');
                    const addButton = mainContent.querySelector('.d-flex.justify-content-between.align-items-center.mb-4 .btn');
                    if (title) title.remove();
                    if (breadcrumb) breadcrumb.remove();
                    if (addButton) addButton.remove();
                    
                    content.innerHTML = mainContent.innerHTML;
                    
                    
                    initDeleteButtons();
                } else {
                    content.innerHTML = '<div class="alert alert-warning">Ошибка загрузки данных</div>';
                }
            })
            .catch(error => {
                console.error('Ошибка загрузки объявлений:', error);
                content.innerHTML = '<div class="alert alert-danger">Ошибка загрузки данных</div>';
            });
    }
    
    function loadSavedProperties() {
        const content = document.getElementById('saved-properties-content');
        
        fetch('api/saved_properties.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.properties.length === 0) {
                        content.innerHTML = `
                            <div class="card-offer text-center py-5">
                                <div class="card-body">
                                    <i class="bi bi-heart text-muted" style="font-size: 4rem;"></i>
                                    <h3 class="mt-3 text-muted">Нет сохраненных объявлений</h3>
                                    <p class="text-muted mb-4">Сохраняйте понравившиеся объявления, нажав на сердечко</p>
                                    <a href="index.php" class="btn btn-primary">
                                        <i class="bi bi-search me-2"></i>
                                        Найти объявления
                                    </a>
                                </div>
                            </div>
                        `;
                    } else {
                        content.innerHTML = generateSavedPropertiesHTML(data.properties);
                    }
                } else {
                    content.innerHTML = '<div class="alert alert-danger">Ошибка загрузки данных</div>';
                }
            })
            .catch(error => {
                console.error('Ошибка загрузки сохраненных объявлений:', error);
                content.innerHTML = '<div class="alert alert-danger">Ошибка загрузки данных</div>';
            });
    }
    
    function loadSavedPropertiesCount() {
        fetch('api/saved_properties.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('saved-count').textContent = data.properties.length;
                }
            })
            .catch(error => console.error('Ошибка подсчета сохраненных:', error));
    }
    
    function generateSavedPropertiesHTML(properties) {
        return `
            <div class="row">
                ${properties.map(property => `
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <div class="card-offer h-100">
                            <div class="row g-0 h-100">
                                <div class="col-12 image-col">
                                    <a href="property.php?id=${property.id}">
                                        ${property.main_image ? 
                                            `<img class="thumb" src="${property.main_image}" alt="${property.title}">` :
                                            `<div class="thumb d-flex align-items-center justify-content-center bg-light">
                                                <div class="text-center text-muted">
                                                    <i class="bi bi-image" style="font-size: 2rem;"></i>
                                                    <div class="small mt-2">Нет изображения</div>
                                                </div>
                                            </div>`
                                        }
                                    </a>
                                </div>
                                <div class="col-12">
                                    <div class="p-3">
                                        <h5 class="card-title mb-2">
                                            <a href="property.php?id=${property.id}" class="text-decoration-none text-dark">
                                                ${property.title}
                                            </a>
                                        </h5>
                                        <p class="text-muted small mb-2">
                                            <i class="bi bi-geo-alt me-1"></i>
                                            ${property.address}
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-primary">${property.purpose}</span>
                                            <span class="text-muted small">
                                                ${property.area_sqm} м²
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="fw-bold text-primary fs-5">
                                                ${parseInt(property.price_per_month).toLocaleString()} ₽/мес
                                            </span>
                                            <span class="text-muted small">
                                                ${new Date(property.saved_at).toLocaleDateString()}
                                            </span>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="property.php?id=${property.id}" class="btn btn-outline-primary btn-sm flex-fill">
                                                <i class="bi bi-eye me-1"></i>
                                                Просмотр
                                            </a>
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeFromSaved(${property.id})">
                                                <i class="bi bi-heart-fill me-1"></i>
                                                Удалить
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }
    
    
    window.removeFromSaved = function(propertyId) {
        if (!confirm('Удалить объявление из сохраненных?')) return;
        
        fetch('api/saved_properties.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ property_id: propertyId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadSavedProperties();
                loadSavedPropertiesCount();
                showNotification(data.message, 'success');
            } else {
                showNotification(data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showNotification('Произошла ошибка', 'error');
        });
    };
    
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>


