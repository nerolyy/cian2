<?php
include __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/auth.php';
authRequireAdmin();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $stmt = $pdo->prepare('DELETE FROM properties WHERE id = :id');
    $stmt->execute([':id'=>$id]);
}
header('Location: index.php');
exit;

