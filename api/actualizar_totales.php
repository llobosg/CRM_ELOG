<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_ppl']) || !isset($data['total_costo'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan parámetros']);
    exit;
}

$id_ppl = (int)$data['id_ppl'];
$total_costo = $data['total_costo'];
$total_venta = $data['total_venta'] ?? 0;
$total_costogasto = $data['total_costogastoslocalesdestino'] ?? 0;
$total_ventagasto = $data['total_ventasgastoslocalesdestino'] ?? 0;

try {
    $stmt = $pdo->prepare("UPDATE prospectos SET 
        total_costo = ?, 
        total_venta = ?, 
        total_costogastoslocalesdestino = ?, 
        total_ventasgastoslocalesdestino = ? 
    WHERE id_ppl = ?");
    $stmt->execute([$total_costo, $total_venta, $total_costogasto, $total_ventagasto, $id_ppl]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar totales: ' . $e->getMessage()]);
}
?>