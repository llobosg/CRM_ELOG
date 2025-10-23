<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

$medio = $_GET['medio'] ?? '';
if (!$medio) {
    echo json_encode(['lugares' => []]);
    exit;
}

try {
    // Consulta: obtener detalle_lugar y pais_lugar
    $stmt = $pdo->prepare("
        SELECT detalle_lugar, pais_lugar 
        FROM lugares 
        WHERE medio_transporte = ?
        ORDER BY detalle_lugar
    ");
    $stmt->execute([$medio]);
    $lugares = [];
    while ($row = $stmt->fetch()) {
        $lugares[] = [
            'lugar' => $row['detalle_lugar'],
            'pais' => $row['pais_lugar']
        ];
    }
    echo json_encode(['lugares' => $lugares]);
} catch (Exception $e) {
    error_log("Error en get_lugares_por_medio.php: " . $e->getMessage());
    echo json_encode(['lugares' => []]);
}
?>