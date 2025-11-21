<?php
// api/get_saldo_credito.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

try {
    $rut = $_GET['rut'] ?? '';
    if (!$rut) {
        echo json_encode(['error' => 'RUT no especificado']);
        exit;
    }
    $rutLimpio = preg_replace('/[^0-9Kk]/', '', strtoupper($rut));
    if (strlen($rutLimpio) < 8) {
        echo json_encode(['error' => 'RUT inválido']);
        exit;
    }

    // ✅ Calcular saldo dinámicamente
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(linea_credito, 0) - COALESCE(usado_credito, 0) AS saldo
        FROM clientes 
        WHERE rut = ?
    ");
    $stmt->execute([$rutLimpio]);
    $row = $stmt->fetch();
    if (!$row) {
        echo json_encode(['error' => 'Cliente no encontrado']);
    } else {
        echo json_encode(['saldo_credito' => floatval($row['saldo'])]);
    }
} catch (Exception $e) {
    error_log("get_saldo_credito error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar el crédito']);
}
?>