<?php
header('Content-Type: application/json');
require_once '../config.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    $id_servicio = $input['id_servicio'] ?? '';

    if (!$id_servicio) {
        throw new Exception('ID de servicio requerido');
    }

    if ($action === 'save') {
        $data = $input['costo'] ?? [];
        $stmt = $pdo->prepare("
            INSERT INTO costos_servicios (
                id_servicio, concepto, moneda, qty, costo, total_costo, tarifa, total_tarifa, aplica
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $id_servicio,
            $data['concepto'],
            $data['moneda'],
            $data['qty'],
            $data['costo'],
            $data['total_costo'],
            $data['tarifa'],
            $data['total_tarifa'],
            $data['aplica']
        ]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    } 
    elseif ($action === 'update') {
        $id = $input['id'] ?? '';
        $data = $input['costo'] ?? [];
        $stmt = $pdo->prepare("
            UPDATE costos_servicios 
            SET concepto = ?, moneda = ?, qty = ?, costo = ?, total_costo = ?, tarifa = ?, total_tarifa = ?, aplica = ?
            WHERE id = ? AND id_servicio = ?
        ");
        $stmt->execute([
            $data['concepto'],
            $data['moneda'],
            $data['qty'],
            $data['costo'],
            $data['total_costo'],
            $data['tarifa'],
            $data['total_tarifa'],
            $data['aplica'],
            $id,
            $id_servicio
        ]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'delete') {
        $id = $input['id'] ?? '';
        $stmt = $pdo->prepare("DELETE FROM costos_servicios WHERE id = ? AND id_servicio = ?");
        $stmt->execute([$id, $id_servicio]);
        echo json_encode(['success' => true]);
    }
    else {
        throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>