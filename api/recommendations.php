<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');


include __DIR__ . '/../includes/db.php';


function assetUrl($path) {
    if (!$path) return '';
    $path = str_replace('\\\\', '/', $path);
    $path = str_replace('\\', '/', $path);
    if (preg_match('~^(https?:)?//|^/~i', $path)) { return $path; }
    $appRoot = '/cian2'; 
    return rtrim($appRoot, '/') . '/' . ltrim($path, '/');
}

try {
    
    $stmt = $pdo->prepare('SELECT * FROM properties ORDER BY RAND() LIMIT 4');
    $stmt->execute();
    $properties = $stmt->fetchAll();
    
    $recommendations = [];
    
    foreach ($properties as $property) {
       
        $imgStmt = $pdo->prepare('SELECT image_url FROM property_images WHERE property_id = :id ORDER BY sort_order ASC, id ASC LIMIT 1');
        $imgStmt->execute([':id' => $property['id']]);
        $image = $imgStmt->fetch();
        
        $recommendations[] = [
            'id' => (int)$property['id'],
            'title' => $property['title'],
            'address' => $property['address'],
            'metro' => $property['metro'] ?: '—',
            'area_sqm' => (float)$property['area_sqm'],
            'price_per_month' => (int)$property['price_per_month'],
            'image_url' => assetUrl($image['image_url'] ?? $property['image_url']),
            'title_short' => mb_substr($property['title'], 0, 40) . (mb_strlen($property['title']) > 40 ? '...' : ''),
            'area_formatted' => rtrim(rtrim(number_format($property['area_sqm'], 1, ',', ' '), '0'), ','),
            'price_formatted' => number_format((int)$property['price_per_month'], 0, '.', ' ')
        ];
    }
    
    echo json_encode([
        'success' => true,
        'recommendations' => $recommendations,
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка получения рекомендаций: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>

