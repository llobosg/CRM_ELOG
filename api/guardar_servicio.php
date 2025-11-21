<?php
// api/guardar_servicio.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) throw new Exception('Datos inválidos');

    $id_ppl = (int)($data['id_prospect'] ?? 0);
    if ($id_ppl <= 0) throw new Exception('ID de prospecto inválido');

    // Obtener el `concatenado` del prospecto
    $stmt = $pdo->prepare("SELECT concatenado FROM prospectos WHERE id_ppl = ?");
    $stmt->execute([$id_ppl]);
    $prospecto = $stmt->fetch();
    if (!$prospecto) throw new Exception('Prospecto no encontrado');
    
    $concatenado = $prospecto['concatenado'];
    // Extraer base: ej. EXAIR251119-02 → EXAIR251119
    $base = preg_replace('/-\d+$/', '', $concatenado);

    // Obtener siguiente correlativo para el servicio
    $stmt = $pdo->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(id_srvc, '-', -1) AS UNSIGNED)) as max_id FROM servicios WHERE id_prospect = ?");
    $stmt->execute([$id_ppl]);
    $last = $stmt->fetch();
    $correlativo = str_pad(($last['max_id'] ?? 0) + 1, 2, '0', STR_PAD_LEFT);
    $id_srvc = "{$base}-{$correlativo}";

    // Validar campos obligatorios
    if (empty($data['servicio'])) throw new Exception('Servicio es obligatorio');

    // Preparar inserción (solo campos reales de la tabla `servicios`)
    $stmt = $pdo->prepare("
        INSERT INTO servicios (
            id_srvc, id_prospect, servicio, trafico, 
            commodity, origen, pais_origen, destino, pais_destino, transito, frecuencia,
            lugar_carga, sector, mercancia, bultos, peso, volumen, dimensiones,
            agente, aol, aod, transportador, incoterm, ref_cliente, proveedor_nac, tipo_cambio,
            moneda, desconsolidac,
            costo, venta, costogastoslocalesdestino, ventasgastoslocalesdestino,
            estado_costos, solicitado_por, fecha_solicitado,
            completado_por, fecha_completado, revisado_por, fecha_revisado
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, 
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?, ?
        )
    ");

    $stmt->execute([
        $id_srvc,
        $id_ppl,
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
        $data['agente'] ?? '',
        $data['aol'] ?? '',
        $data['aod'] ?? '',
        $data['transportador'] ?? '',
        $data['incoterm'] ?? '',
        $data['ref_cliente'] ?? '',
        $data['proveedor_nac'] ?? '',
        (float)($data['tipo_cambio'] ?? 1),
        $data['moneda'] ?? 'CLP',
        $data['desconsolidac'] ?? '0',
        (float)($data['costo'] ?? 0),
        (float)($data['venta'] ?? 0),
        (float)($data['costogastoslocalesdestino'] ?? 0),
        (float)($data['ventasgastoslocalesdestino'] ?? 0),
        $data['estado_costos'] ?? 'pendiente',
        null, // solicitado_por → se llena al notificar
        null, // fecha_solicitado
        null, // completado_por
        null, // fecha_completado
        null, // revisado_por
        null  // fecha_revisado
    ]);

    echo json_encode([
        'success' => true,
        'id_srvc' => $id_srvc,
        'message' => 'Servicio creado correctamente'
    ]);

} catch (Exception $e) {
    error_log("Error en guardar_servicio.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>