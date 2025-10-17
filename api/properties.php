<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

$where = [];
$params = [];
if (!empty($_GET['purpose'])) { $where[] = 'purpose = :purpose'; $params[':purpose'] = $_GET['purpose']; }
if (!empty($_GET['min_price'])) { $where[] = 'price_per_month >= :min_price'; $params[':min_price'] = (int)$_GET['min_price']; }
if (!empty($_GET['max_price'])) { $where[] = 'price_per_month <= :max_price'; $params[':max_price'] = (int)$_GET['max_price']; }
if (!empty($_GET['min_area'])) { $where[] = 'area_sqm >= :min_area'; $params[':min_area'] = (float)$_GET['min_area']; }
if (!empty($_GET['max_area'])) { $where[] = 'area_sqm <= :max_area'; $params[':max_area'] = (float)$_GET['max_area']; }
$whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

$stmt = $pdo->prepare('SELECT id, title, address, price_per_month, area_sqm FROM properties' . $whereSql . ' ORDER BY id DESC LIMIT 200');
$stmt->execute($params);
$rows = $stmt->fetchAll();
echo json_encode(['items'=>$rows], JSON_UNESCAPED_UNICODE);
?>



