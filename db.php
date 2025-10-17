<?php
// Простое подключение к MySQL через PDO
// Значения можно переопределить через переменные окружения
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '8889';
$dbName = getenv('DB_NAME') ?: 'sss';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: 'root';

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    // Runtime-safe migration: ensure users.phone exists
    try {
        $col = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'phone'")->fetch();
        if (!$col) {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `phone` VARCHAR(20) NULL AFTER `name`");
        }
        $col2 = $pdo->query("SHOW COLUMNS FROM `properties` LIKE 'contact_phone'")->fetch();
        if (!$col2) {
            $pdo->exec("ALTER TABLE `properties` ADD COLUMN `contact_phone` VARCHAR(20) NULL AFTER `image_url`");
        }
        $col3 = $pdo->query("SHOW COLUMNS FROM `properties` LIKE 'description'")->fetch();
        if (!$col3) {
            $pdo->exec("ALTER TABLE `properties` ADD COLUMN `description` TEXT NULL AFTER `contact_phone`");
        }
        $col4 = $pdo->query("SHOW COLUMNS FROM `properties` LIKE 'lessor_type'")->fetch();
        if (!$col4) {
            $pdo->exec("ALTER TABLE `properties` ADD COLUMN `lessor_type` ENUM('owner','company') NOT NULL DEFAULT 'owner' AFTER `description`");
        }
        $col5 = $pdo->query("SHOW COLUMNS FROM `properties` LIKE 'lessor_name'")->fetch();
        if (!$col5) {
            $pdo->exec("ALTER TABLE `properties` ADD COLUMN `lessor_name` VARCHAR(190) NULL AFTER `lessor_type`");
        }
        // Ensure property_images table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `property_images` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `property_id` INT UNSIGNED NOT NULL,
            `image_url` VARCHAR(500) NOT NULL,
            `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            KEY `idx_property` (`property_id`),
            CONSTRAINT `fk_property_images_property` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        // Purposes dictionary: create and seed from distinct existing values
        $pdo->exec("CREATE TABLE IF NOT EXISTS `purposes` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(100) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_name` (`name`)
        ) ENGINE=InnoDB");
        try {
            $distinct = $pdo->query("SELECT DISTINCT purpose FROM properties WHERE purpose IS NOT NULL AND purpose <> ''")->fetchAll();
            if ($distinct) {
                $ins = $pdo->prepare("INSERT IGNORE INTO purposes (name) VALUES (:n)");
                foreach ($distinct as $r) { $ins->execute([':n'=>$r['purpose']]); }
            }
        } catch (Throwable $e) { /* ignore */ }
    } catch (Throwable $migrE) {
        // Silent: do not block app if cannot migrate
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo '<pre>Ошибка подключения к базе данных: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</pre>";
    exit;
}
?>


