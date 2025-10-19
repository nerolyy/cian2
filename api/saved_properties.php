<?php
header('Content-Type: application/json');
include '../includes/db.php';
include '../includes/auth.php';

// Проверяем авторизацию
authRequireLogin();
$currentUser = authCurrentUser();
$userId = $currentUser['id'];

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'POST':
            // Сохранить объявление
            $propertyId = (int)($input['property_id'] ?? 0);
            
            if ($propertyId <= 0) {
                throw new Exception('Неверный ID объявления');
            }
            
            // Проверяем, существует ли объявление
            $stmt = $pdo->prepare('SELECT id FROM properties WHERE id = :id');
            $stmt->execute([':id' => $propertyId]);
            if (!$stmt->fetch()) {
                throw new Exception('Объявление не найдено');
            }
            
            // Проверяем, не сохранено ли уже
            $stmt = $pdo->prepare('SELECT id FROM saved_properties WHERE user_id = :user_id AND property_id = :property_id');
            $stmt->execute([':user_id' => $userId, ':property_id' => $propertyId]);
            if ($stmt->fetch()) {
                throw new Exception('Объявление уже сохранено');
            }
            
            // Сохраняем
            $stmt = $pdo->prepare('INSERT INTO saved_properties (user_id, property_id) VALUES (:user_id, :property_id)');
            $stmt->execute([':user_id' => $userId, ':property_id' => $propertyId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Объявление сохранено'
            ]);
            break;
            
        case 'DELETE':
            // Удалить из сохраненных
            $propertyId = (int)($input['property_id'] ?? 0);
            
            if ($propertyId <= 0) {
                throw new Exception('Неверный ID объявления');
            }
            
            $stmt = $pdo->prepare('DELETE FROM saved_properties WHERE user_id = :user_id AND property_id = :property_id');
            $stmt->execute([':user_id' => $userId, ':property_id' => $propertyId]);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception('Объявление не было сохранено');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Объявление удалено из сохраненных'
            ]);
            break;
            
        case 'GET':
            // Получить список сохраненных объявлений
            $stmt = $pdo->prepare('
                SELECT p.*, sp.created_at as saved_at
                FROM saved_properties sp
                JOIN properties p ON sp.property_id = p.id
                WHERE sp.user_id = :user_id
                ORDER BY sp.created_at DESC
            ');
            $stmt->execute([':user_id' => $userId]);
            $properties = $stmt->fetchAll();
            
            // Добавляем изображения для каждого объявления
            foreach ($properties as &$property) {
                $imgStmt = $pdo->prepare('SELECT image_url FROM property_images WHERE property_id = :id ORDER BY sort_order ASC, id ASC LIMIT 1');
                $imgStmt->execute([':id' => $property['id']]);
                $img = $imgStmt->fetch();
                $property['main_image'] = $img['image_url'] ?? $property['image_url'];
                
                // Если нет изображения, добавляем заглушку
                if (empty($property['main_image'])) {
                    $property['main_image'] = null; // Будет обработано в JavaScript
                }
            }
            
            echo json_encode([
                'success' => true,
                'properties' => $properties
            ]);
            break;
            
        default:
            throw new Exception('Метод не поддерживается');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
