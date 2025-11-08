<?php
// api/get_saldo_credito.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

try {
    $rut = $_GET['rut'] ?? '';
    if (!$rut) {
        echo json_encode(['error' => 'RUT no especificado']);
        exit;
    }
    // Limpiar RUT
    $rutLimpio = preg_replace('/[^0-9Kk]/', '', strtoupper($rut));
    if (strlen($rutLimpio) < 8) {
        echo json_encode(['error' => 'RUT inválido']);
        exit;
    }
    // Consultar saldo
    $stmt = $pdo->prepare("SELECT saldo_credito FROM clientes WHERE rut = ?");
    $stmt->execute([$rutLimpio]);
    $saldo = $stmt->fetchColumn();
    if ($saldo === false) {
        echo json_encode(['error' => 'Cliente no encontrado']);
    } else {
        echo json_encode(['saldo_credito' => (float)$saldo]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar el crédito']);
}
?>