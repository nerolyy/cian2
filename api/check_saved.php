<?php
header('Content-Type: application/json');
include '../includes/db.php';
include '../includes/auth.php';


authRequireLogin();
$currentUser = authCurrentUser();
$userId = $currentUser['id'];

$propertyId = (int)($_GET['property_id'] ?? 0);

if ($propertyId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Неверный ID объявления']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id FROM saved_properties WHERE user_id = :user_id AND property_id = :property_id');
    $stmt->execute([':user_id' => $userId, ':property_id' => $propertyId]);
    $isSaved = $stmt->fetch() !== false;
    
    echo json_encode([
        'success' => true,
        'is_saved' => $isSaved
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка сервера'
    ]);
}
?>
