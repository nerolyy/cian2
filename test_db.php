<?php
// Тест подключения к базе данных
echo "<h2>Тест подключения к MySQL</h2>";

$dbHost = '127.0.0.1';
$dbPort = '8889';
$dbName = 'sss';
$dbUser = 'root';
$dbPass = 'root';

echo "<p>Попытка подключения к: {$dbHost}:{$dbPort}</p>";
echo "<p>База данных: {$dbName}</p>";
echo "<p>Пользователь: {$dbUser}</p>";

$dsn = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";

try {
    // Сначала попробуем подключиться без указания базы данных
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "<p style='color: green;'>✓ Подключение к MySQL успешно!</p>";
    
    // Проверим, существует ли база данных
    $stmt = $pdo->query("SHOW DATABASES LIKE 'sss'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ База данных 'sss' существует</p>";
        
        // Подключимся к базе данных
        $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        echo "<p style='color: green;'>✓ Подключение к базе данных 'sss' успешно!</p>";
        
        // Проверим таблицы
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Найденные таблицы: " . implode(', ', $tables) . "</p>";
        
    } else {
        echo "<p style='color: red;'>✗ База данных 'sss' не найдена</p>";
        echo "<p>Нужно создать базу данных. Выполните в phpMyAdmin или командной строке:</p>";
        echo "<pre>CREATE DATABASE sss CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</pre>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Ошибка подключения: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Возможные причины:</p>";
    echo "<ul>";
    echo "<li>MAMP не запущен</li>";
    echo "<li>MySQL не работает</li>";
    echo "<li>Неправильный порт (попробуйте 3306 вместо 8889)</li>";
    echo "<li>Неправильные учетные данные</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='index.php'>← Вернуться на главную</a></p>";
?>




