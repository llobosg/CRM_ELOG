<?php
// api/guardar_servicio.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) throw new Exception('Datos inválidos');

    $modo = $data['modo'] ?? 'crear';
    $id_srvc = $data['id_srvc'] ?? null;
    $id_ppl = (int)($data['id_prospect'] ?? 0);
    if ($id_ppl <= 0) throw new Exception('ID de prospecto inválido');

    // Obtener concatenado del prospecto
    $stmt = $pdo->prepare("SELECT concatenado FROM prospectos WHERE id_ppl = ?");
    $stmt->execute([$id_ppl]);
    $prospecto = $stmt->fetch();
    if (!$prospecto) throw new Exception('Prospecto no encontrado');
    $base = preg_replace('/-\d+$/', '', $prospecto['concatenado']);

    if ($modo === 'editar') {
        // === VALIDAR que el servicio exista y pertenezca al prospecto ===
        if (!$id_srvc) throw new Exception('ID de servicio requerido para edición');
        $stmt = $pdo->prepare("SELECT id_srvc FROM servicios WHERE id_srvc = ? AND id_prospect = ?");
        $stmt->execute([$id_srvc, $id_ppl]);
        if (!$stmt->fetch()) throw new Exception('Servicio no encontrado o no autorizado');

        // === ACTUALIZAR ===
        $sql = "
            UPDATE servicios SET
                servicio = ?, trafico = ?, commodity = ?, origen = ?, pais_origen = ?,
                destino = ?, pais_destino = ?, transito = ?, frecuencia = ?, lugar_carga = ?,
                sector = ?, mercancia = ?, bultos = ?, peso = ?, volumen = ?,
                dimensiones = ?, moneda = ?, tipo_cambio = ?, proveedor_nac = ?,
                desconsolidac = ?, aol = ?, aod = ?, agente = ?, transportador = ?,
                incoterm = ?, ref_cliente = ?,
                costo = ?, venta = ?, costogastoslocalesdestino = ?, ventasgastoslocalesdestino = ?,
                estado_costos = ?
            WHERE id_srvc = ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['servicio'] ?? '',
            $data['trafico'] ?? '',
            $data['commodity'] ?? '',
            $data['origen'] ?? '',
            $data['pais_origen'] ?? '',
            $data['destino'] ?? '',
            $data['pais_destino'] ?? '',
            $data['transito'] ?? '',
            $data['frecuencia'] ?? '',
            $data['lugar_carga'] ?? '',
            $data['sector'] ?? '',
            $data['mercancia'] ?? '',
            (int)($data['bultos'] ?? 0),
            (float)($data['peso'] ?? 0),
            (string)($data['volumen'] ?? '0.00'),
            (string)($data['dimensiones'] ?? ''),
            $data['moneda'] ?? 'CLP',
            (float)($data['tipo_cambio'] ?? 1),
            $data['proveedor_nac'] ?? '',
            '0',
            $data['aol'] ?? '',
            $data['aod'] ?? '',
            $data['agente'] ?? '',
            $data['transportador'] ?? '',
            $data['incoterm'] ?? '',
            $data['ref_cliente'] ?? '',
            (float)($data['costo'] ?? 0),
            (float)($data['venta'] ?? 0),
            (float)($data['costogastoslocalesdestino'] ?? 0),
            (float)($data['ventasgastoslocalesdestino'] ?? 0),
            $data['estado_costos'] ?? 'pendiente',
            $id_srvc
        ]);
        $mensaje = 'Servicio actualizado correctamente';
    } else {
        // === CREAR NUEVO ===
        $stmt = $pdo->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(id_srvc, '-', -1) AS UNSIGNED)) as max_id FROM servicios WHERE id_prospect = ?");
        $stmt->execute([$id_ppl]);
        $last = $stmt->fetch();
        $correlativo = str_pad(($last['max_id'] ?? 0) + 1, 2, '0', STR_PAD_LEFT);
        $id_srvc = "{$base}-{$correlativo}";

        $sql = "
            INSERT INTO servicios (
                id_srvc, id_prospect, servicio, trafico, commodity, origen, pais_origen,
                destino, pais_destino, transito, frecuencia, lugar_carga, sector, mercancia,
                bultos, peso, volumen, dimensiones, moneda, tipo_cambio, proveedor_nac,
                desconsolidac, aol, aod, agente, transportador, incoterm, ref_cliente,
                costo, venta, costogastoslocalesdestino, ventasgastoslocalesdestino,
                estado_costos, solicitado_por, fecha_solicitado,
                completado_por, fecha_completado, revisado_por, fecha_revisado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $id_srvc, $id_ppl,
            $data['servicio'] ?? '',
            $data['trafico'] ?? '',
            $data['commodity'] ?? '',
            $data['origen'] ?? '',
            $data['pais_origen'] ?? '',
            $data['destino'] ?? '',
            $data['pais_destino'] ?? '',
            $data['transito'] ?? '',
            $data['frecuencia'] ?? '',
            $data['lugar_carga'] ?? '',
            $data['sector'] ?? '',
            $data['mercancia'] ?? '',
            (int)($data['bultos'] ?? 0),
            (float)($data['peso'] ?? 0),
            (string)($data['volumen'] ?? '0.00'),
            (string)($data['dimensiones'] ?? ''),
            $data['moneda'] ?? 'CLP',
            (float)($data['tipo_cambio'] ?? 1),
            $data['proveedor_nac'] ?? '',
            '0',
            $data['aol'] ?? '',
            $data['aod'] ?? '',
            $data['agente'] ?? '',
            $data['transportador'] ?? '',
            $data['incoterm'] ?? '',
            $data['ref_cliente'] ?? '',
            (float)($data['costo'] ?? 0),
            (float)($data['venta'] ?? 0),
            (float)($data['costogastoslocalesdestino'] ?? 0),
            (float)($data['ventasgastoslocalesdestino'] ?? 0),
            $data['estado_costos'] ?? 'pendiente',
            null, null, null, null, null, null
        ]);
        $mensaje = 'Servicio creado correctamente';
    }

    echo json_encode([
        'success' => true,
        'id_srvc' => $id_srvc,
        'message' => $mensaje
    ]);

} catch (Exception $e) {
    error_log("Error en guardar_servicio.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>