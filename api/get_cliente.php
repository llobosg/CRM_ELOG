<?php
// api/get_cliente.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

try {
    $rut = $_GET['rut'] ?? '';
    if (!$rut) {
        echo json_encode(['existe' => false]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE rut = ?");
    $stmt->execute([$rut]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        echo json_encode(['existe' => true, 'cliente' => $cliente]);
    } else {
        echo json_encode(['existe' => false]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['existe' => false, 'error' => 'Error al buscar cliente']);
}
?>