<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function authCurrentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function authRequireAdmin(): void {
    $user = authCurrentUser();
    if (!$user || ($user['role'] ?? 'user') !== 'admin') {
        header('Location: login.php');
        exit;
    }
}

function authLogin(PDO $pdo, string $email, string $password): bool {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    if ($user && $password === ($user['password'] ?? '')) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'phone' => $user['phone'] ?? null,
            'role' => $user['role'],
        ];
        return true;
    }
    return false;
}

function authRegister(PDO $pdo, string $email, string $password, string $name = '', ?string $phone = null): array {
    $errors = [];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Некорректный email'; }
    if (strlen($password) < 6) { $errors[] = 'Минимальная длина пароля 6 символов'; }
    if ($errors) { return [false, $errors]; }
    try {
        $stmt = $pdo->prepare('INSERT INTO users (email,password,name,phone) VALUES (:email,:password,:name,:phone)');
        $stmt->execute([':email'=>$email,':password'=>$password,':name'=>$name,':phone'=>$phone]);
    } catch (Throwable $e) {
        if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
            return [false, ['Email уже зарегистрирован']];
        }
        return [false, ['Ошибка регистрации']];
    }
    return [true, []];
}

function authLogout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function authRequireLogin(): void {
    if (!authCurrentUser()) {
        header('Location: login.php');
        exit;
    }
}

function authUpdateCurrentUserSession(PDO $pdo): void {
    $u = authCurrentUser();
    if (!$u) { return; }
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id'=>$u['id']]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'phone' => $user['phone'] ?? null,
            'role' => $user['role'],
        ];
    }
}
?>

