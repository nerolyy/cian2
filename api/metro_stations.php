<?php
header('Content-Type: application/json');
include '../includes/db.php';

try {
    $line = $_GET['line'] ?? null;
    $search = $_GET['search'] ?? null;
    
    $sql = 'SELECT * FROM metro_stations WHERE is_active = 1';
    $params = [];
    
    if ($line) {
        $sql .= ' AND line_name = :line';
        $params[':line'] = $line;
    }
    
    if ($search) {
        $sql .= ' AND name LIKE :search';
        $params[':search'] = '%' . $search . '%';
    }
    
    $sql .= ' ORDER BY line_name, sort_order, name';
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $stations = $stmt->fetchAll();
    
    $groupedStations = [];
    foreach ($stations as $station) {
        $lineName = $station['line_name'];
        if (!isset($groupedStations[$lineName])) {
            $groupedStations[$lineName] = [
                'line_name' => $lineName,
                'line_color' => $station['line_color'],
                'stations' => []
            ];
        }
        $groupedStations[$lineName]['stations'][] = [
            'id' => $station['id'],
            'name' => $station['name'],
            'sort_order' => $station['sort_order']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'lines' => array_values($groupedStations)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка получения станций метро'
    ]);
}
?>
