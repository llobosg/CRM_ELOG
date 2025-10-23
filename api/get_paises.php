<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

try {
    // Usa la misma fuente que en el formulario principal
    $stmt = $pdo->query("SELECT DISTINCT pais FROM prospectos WHERE pais IS NOT NULL AND pais != '' ORDER BY pais");
    $paises = $stmt->fetchAll(PDO::FETCH_COLUMN);
    // O usa countriesnow.space si prefieres:
    // $response = file_get_contents('https://countriesnow.space/api/v0.1/countries');
    // $data = json_decode($response, true);
    // $paises = $data['data'] ?? [];
    echo json_encode(['paises' => $paises]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>