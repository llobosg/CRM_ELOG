<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT COALESCE(MAX(id_prospect), 0) + 1 AS proximo_id FROM prospectos");
    $row = $stmt->fetch();
    echo json_encode(['proximo_id' => str_pad($row['proximo_id'], 2, '0', STR_PAD_LEFT)]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>