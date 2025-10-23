<?php
// api/get_conceptos_costos.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

try {
    $stmt = $pdo->query("SELECT DISTINCT concepto FROM conceptos_costos WHERE concepto IS NOT NULL AND concepto != '' ORDER BY concepto");
    $conceptos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['conceptos' => $conceptos]);
} catch (Exception $e) {
    error_log("Error en get_conceptos_costos.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error al cargar conceptos']);
}
?>