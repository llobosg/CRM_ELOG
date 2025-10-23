<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

try {
    $id_prospect = (int)($_POST['id_prospect'] ?? 0);
    $concatenado = $_POST['concatenado_serv'] ?? '';

    if (!$id_prospect || !$concatenado) {
        throw new Exception("Datos incompletos: id_prospect o concatenado faltantes");
    }

    // Verificar que el prospecto exista
    $stmt_check = $pdo->prepare("SELECT id_ppl FROM prospectos WHERE id_ppl = ?");
    $stmt_check->execute([$id_prospect]);
    if (!$stmt_check->fetch()) {
        throw new Exception("Prospecto no encontrado");
    }

    // Generar id_srvc: concatenado + correlativo
    $stmt_last = $pdo->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(id_srvc, '-', -1) AS UNSIGNED)) as max_id FROM servicios WHERE id_prospect = ?");
    $stmt_last->execute([$id_prospect]);
    $last = $stmt_last->fetch();
    $correlativo = str_pad(($last['max_id'] ?? 0) + 1, 2, '0', STR_PAD_LEFT);
    $id_srvc = "{$concatenado}-{$correlativo}";

    // Insertar servicio
    $stmt = $pdo->prepare("INSERT INTO servicios (
        id_srvc, id_prospect, servicio, nombre_corto, tipo, trafico, sub_trafico,
        base_calculo, moneda, tarifa, iva, estado, costo, venta,
        costogastoslocalesdestino, ventasgastoslocalesdestino
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $id_srvc,
        $id_prospect,
        $_POST['servicio'] ?? '',
        $_POST['nombre_corto'] ?? '',
        $_POST['tipo'] ?? '',
        $_POST['trafico'] ?? '',
        $_POST['sub_trafico'] ?? '',
        $_POST['base_calculo'] ?? '',
        $_POST['moneda'] ?? 'CLP',
        $_POST['tarifa'] ?? 0,
        $_POST['iva'] ?? 19,
        $_POST['estado'] ?? 'Activo',
        $_POST['costo'] ?? 0,
        $_POST['venta'] ?? 0,
        $_POST['costogastoslocalesdestino'] ?? 0,
        $_POST['ventasgastoslocalesdestino'] ?? 0,
    ]);

    // Obtener servicios actualizados
    $stmt_serv = $pdo->prepare("SELECT * FROM servicios WHERE id_prospect = ?");
    $stmt_serv->execute([$id_prospect]);
    $servicios = $stmt_serv->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'servicios' => $servicios
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}