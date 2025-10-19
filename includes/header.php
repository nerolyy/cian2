<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Снять помещение свободного назначения в Москве</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php
    
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
    $segments = explode('/', trim($scriptName, '/'));
    $appRoot = '/' . ($segments[0] ?? '');
    ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($appRoot); ?>/css/style.css">
</head>
<body>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$currentUser = $_SESSION['user'] ?? null;
?>
<header class="site-header border-bottom">
    <div class="container py-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <a class="navbar-brand fw-bold text-dark m-0" href="<?php echo htmlspecialchars($appRoot); ?>/index.php">Недвижимость</a>
            
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if ($currentUser): ?>
                <a class="btn btn-primary btn-sm" href="<?php echo htmlspecialchars($appRoot); ?>/add_property.php">
                    <i class="bi bi-plus-circle me-1"></i>
                    Добавить объявление
                </a>
                <?php if (($currentUser['role'] ?? 'user') === 'admin'): ?>
                    <a class="btn btn-outline-primary btn-sm" href="<?php echo htmlspecialchars($appRoot); ?>/admin/index.php">Админ</a>
                <?php endif; ?>
                <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($appRoot); ?>/profile.php">Кабинет</a>
                <span class="small text-muted d-none d-md-inline"><?php echo htmlspecialchars($currentUser['email']); ?></span>
                <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($appRoot); ?>/logout.php">Выйти</a>
            <?php else: ?>
                <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($appRoot); ?>/login.php">Войти</a>
                <a class="btn btn-primary btn-sm" href="<?php echo htmlspecialchars($appRoot); ?>/register.php">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</header>

