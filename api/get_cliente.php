<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$rut = $_GET['rut'] ?? '';
if (!$rut) {
    echo json_encode(['existe' => false, 'message' => 'RUT no proporcionado']);
    exit;
}

$rut_limpio = preg_replace('/[^0-9kK]/', '', strtolower($rut));

try {
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE rut = ?");
    $stmt->execute([$rut_limpio]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        // Asegurar que id_comercial sea entero
        if (isset($cliente['id_comercial']) && $cliente['id_comercial'] !== null) {
            $cliente['id_comercial'] = (int)$cliente['id_comercial'];
        } else {
            $cliente['id_comercial'] = null;
        }
        
        echo json_encode(['existe' => true, 'cliente' => $cliente]);
    } else {
        echo json_encode(['existe' => false, 'message' => 'Cliente no encontrado']);
    }
} catch (Exception $e) {
    error_log("Error get_cliente.php: " . $e->getMessage());
    echo json_encode(['existe' => false, 'message' => 'Error al consultar cliente']);
}
?>