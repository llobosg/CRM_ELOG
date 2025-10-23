<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT DISTINCT tipo_oper FROM operacion ORDER BY tipo_oper");
    $tipos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['tipos' => $tipos]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>