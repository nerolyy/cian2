<?php
/**
 * Функции для работы с Google reCAPTCHA
 */

require_once __DIR__ . '/recaptcha_config.php';

/**
 * Проверяет, настроена ли reCAPTCHA
 * 
 * @return bool
 */
function isRecaptchaConfigured(): bool {
    return defined('RECAPTCHA_SITE_KEY') && 
           defined('RECAPTCHA_SECRET_KEY') && 
           RECAPTCHA_SITE_KEY !== 'YOUR_SITE_KEY_HERE' && 
           RECAPTCHA_SECRET_KEY !== 'YOUR_SECRET_KEY_HERE';
}

/**
 * Проверяет ответ reCAPTCHA на сервере
 * 
 * @param string $response Токен ответа от reCAPTCHA
 * @param string|null $remoteIp IP адрес пользователя (опционально)
 * @return array ['success' => bool, 'error' => string|null]
 */
function verifyRecaptcha(string $response, ?string $remoteIp = null): array {
    // Если reCAPTCHA не настроена, пропускаем проверку
    if (!isRecaptchaConfigured()) {
        return ['success' => true, 'error' => null];
    }
    
    if (empty($response)) {
        return ['success' => false, 'error' => 'Отсутствует ответ reCAPTCHA'];
    }
    
    $data = [
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $response,
    ];
    
    if ($remoteIp !== null) {
        $data['remoteip'] = $remoteIp;
    }
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
            'timeout' => 10,
        ],
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents(RECAPTCHA_VERIFY_URL, false, $context);
    
    if ($result === false) {
        return ['success' => false, 'error' => 'Ошибка подключения к серверу reCAPTCHA'];
    }
    
    $json = json_decode($result, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['success' => false, 'error' => 'Ошибка обработки ответа от reCAPTCHA'];
    }
    
    if (isset($json['success']) && $json['success'] === true) {
        return ['success' => true, 'error' => null];
    }
    
    $errorCodes = $json['error-codes'] ?? [];
    $errorMessage = !empty($errorCodes) ? implode(', ', $errorCodes) : 'Неизвестная ошибка';
    
    return ['success' => false, 'error' => $errorMessage];
}

/**
 * Получает IP адрес пользователя
 * 
 * @return string|null
 */
function getClientIp(): ?string {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = explode(',', $ip)[0];
            }
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? null;
}
?>

