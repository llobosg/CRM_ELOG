<?php
require_once __DIR__ . '/../config.php';

try {
    $stmt = $pdo->query("SELECT DISTINCT tipo FROM medios_transporte WHERE tipo IS NOT NULL AND tipo != '' ORDER BY tipo");
    $medios = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['medios_transporte' => $medios]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al cargar medios de transporte']);
}
?>