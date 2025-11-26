<?php
// api/get_saldo_credito.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

try {
    $rut = $_GET['rut'] ?? '';
    if (!$rut) {
        throw new Exception('RUT no proporcionado');
    }

    // ✅ Usar los nombres reales de las columnas
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(monto_credito, 0) as monto_credito,
            COALESCE(usado_credito, 0) as usado_credito,
            COALESCE(saldo_credito, 0) as saldo_credito
        FROM clientes 
        WHERE rut = ?
    ");
    $stmt->execute([$rut]);
    $cliente = $stmt->fetch();

    if (!$cliente) {
        throw new Exception('Cliente no encontrado');
    }

    // ✅ Devolver el saldo disponible
    echo json_encode([
        'success' => true,
        'monto_credito' => (float)$cliente['monto_credito'],
        'usado_credito' => (float)$cliente['usado_credito'],
        'saldo_credito' => (float)$cliente['saldo_credito']
    ]);

} catch (Exception $e) {
    error_log("get_saldo_credito error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>