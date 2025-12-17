<?php
// api/get_estado_credito_cliente.php
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
// require_once __DIR__ . '/../includes/auth_check.php'; // Asegúrate de incluir esto si es necesario para esta API

$rut_cliente = $_GET['rut'] ?? null; // El parámetro se llama 'rut'

if (!$rut_cliente) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'RUT de cliente no proporcionado.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT estado_credito FROM clientes WHERE rut = ? LIMIT 1");
    $stmt->execute([$rut_cliente]);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($fila) {
        // Devolver el estado de crédito
        echo json_encode(['success' => true, 'estado_credito' => $fila['estado_credito']]);
    } else {
        // Cliente no encontrado
        echo json_encode(['success' => true, 'estado_credito' => null, 'message' => 'Cliente no encontrado.']); // Devolver éxito pero sin estado
    }
} catch (PDOException $e) {
    error_log("Error en get_estado_credito_cliente.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno al consultar el estado de crédito.']);
}
?>