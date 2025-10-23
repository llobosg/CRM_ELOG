<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

try {
    $id_srvc = $_POST['id_srvc'] ?? '';
    $servicio = $_POST['servicio'] ?? '';
    $costo = $_POST['costo'] ?? 0;

    if (!$id_srvc || !$servicio) {
        throw new Exception("Datos incompletos");
    }

    $stmt = $pdo->prepare("UPDATE servicios SET 
        servicio = ?, nombre_corto = ?, tipo = ?, trafico = ?, sub_trafico = ?,
        base_calculo = ?, moneda = ?, tarifa = ?, iva = ?, estado = ?,
        costo = ?, venta = ?, costogastoslocalesdestino = ?, ventasgastoslocalesdestino = ?
    WHERE id_srvc = ?");

    $stmt->execute([
        $_POST['servicio'],
        $_POST['nombre_corto'],
        $_POST['tipo'],
        $_POST['trafico'],
        $_POST['sub_trafico'],
        $_POST['base_calculo'],
        $_POST['moneda'],
        $_POST['tarifa'],
        $_POST['iva'],
        $_POST['estado'],
        $_POST['costo'],
        $_POST['venta'],
        $_POST['costogastoslocalesdestino'],
        $_POST['ventasgastoslocalesdestino'],
        $id_srvc
    ]);

    // Devolver servicios actualizados
    $stmt_serv = $pdo->prepare("SELECT * FROM servicios WHERE id_prospect = (SELECT id_prospect FROM servicios WHERE id_srvc = ?)");
    $stmt_serv->execute([$id_srvc]);
    $servicios = $stmt_serv->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'servicios' => $servicios]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}