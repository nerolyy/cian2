<?php
/**
 * Конфигурация Google reCAPTCHA
 * 
 * Для получения ключей:
 * 1. Перейдите на https://www.google.com/recaptcha/admin
 * 2. Зарегистрируйте новый сайт
 * 3. Выберите тип reCAPTCHA v2 "Я не робот"
 * 4. Укажите домен вашего сайта
 * 5. Скопируйте Site Key и Secret Key сюда
 */

// Публичный ключ (Site Key) - используется на клиенте
define('RECAPTCHA_SITE_KEY', '6LfWTHIsAAAAAIgDf9MpnMGCg1qzBMZQyIUGb9lY');

// Секретный ключ (Secret Key) - используется на сервере
define('RECAPTCHA_SECRET_KEY', '6LfWTHIsAAAAALwfr26WDG2GQ-4B_tQ8LEG2KxoO');

// URL для проверки reCAPTCHA
define('RECAPTCHA_VERIFY_URL', 'https://www.google.com/recaptcha/api/siteverify');
?>

