<?php
// api/validar_rut_cliente.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$rut = $_GET['rut'] ?? '';
if (!$rut) {
    echo json_encode(['existe' => false]);
    exit;
}

// Limpiar RUT (solo dígitos y K)
$rutLimpio = preg_replace('/[^0-9Kk]/', '', strtoupper($rut));
$stmt = $pdo->prepare("SELECT 1 FROM clientes WHERE rut = ?");
$stmt->execute([$rutLimpio]);
echo json_encode(['existe' => (bool)$stmt->fetch()]);
?>