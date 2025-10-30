<?php
// api/get_contactos.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

try {
    $rut = $_GET['rut'] ?? '';
    if (!$rut) {
        echo json_encode(['contactos' => []]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM contactos WHERE rut_cliente = ?");
    $stmt->execute([$rut]);
    $contactos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['contactos' => $contactos]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['contactos' => [], 'error' => 'Error al cargar contactos']);
}
?>