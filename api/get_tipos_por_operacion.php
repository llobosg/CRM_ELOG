<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

$operacion = $_GET['operacion'] ?? '';
if (!$operacion) {
    echo json_encode(['tipos' => []]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT DISTINCT tipo_oper FROM operacion WHERE operacion = ? ORDER BY tipo_oper");
    $stmt->execute([$operacion]);
    $tipos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['tipos' => $tipos]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>