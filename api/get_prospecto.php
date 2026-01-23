<?php
// /api/get_prospecto.php
header('Content-Type: application/json; charset=utf-8');

// Evitar salida de errores en producción
error_reporting(0);

try {
    require_once __DIR__ . '/../config.php';

    $id = $_GET['id'] ?? null;
    if (!$id) {
        throw new Exception('ID de prospecto no especificado');
    }

    // === Cargar prospecto ===
    $stmt = $pdo->prepare("SELECT * FROM prospectos WHERE id_ppl = ?");
    $stmt->execute([$id]);
    $prospecto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$prospecto) {
        throw new Exception('Prospecto no encontrado');
    }

    // === Cargar servicios con costo y venta calculados desde costos_servicios ===
    $stmt = $pdo->prepare("
        SELECT 
            s.id_ppl,
            s.id_srvc,
            s.id_prospect,
            s.servicio,
            s.nombre_corto,
            s.tipo,
            s.trafico,
            s.sub_trafico,
            s.base_calculo,
            s.moneda,
            s.tarifa,
            s.iva,
            s.estado,
            s.costo AS costo_servicio,
            s.venta AS venta_servicio,
            s.costogastoslocalesdestino,
            s.ventasgastoslocalesdestino,
            s.desconsolidac,
            s.commodity,
            s.origen,
            s.pais_origen,
            s.destino,
            s.pais_destino,
            s.transito,
            s.frecuencia,
            s.lugar_carga,
            s.sector,
            s.mercancia,
            s.bultos,
            s.peso,
            s.volumen,
            s.dimensiones,
            s.agente,
            s.aol,
            s.aod,
            s.transportador,
            s.incoterm,
            s.ref_cliente,
            s.proveedor_nac,
            s.tipo_cambio,
            s.ciudad,
            s.pais,
            s.direc_serv,
            s.estado_costos,  -- ✅ Campo explícito
            s.nota_srvc,
            s.solicitado_por,
            s.fecha_solicitado,
            s.completado_por,
            s.fecha_completado,
            s.revisado_por,
            s.fecha_revisado,
            s.validez,
            COALESCE(cs_data.total_costo, 0) AS costo,
            COALESCE(cs_data.total_venta, 0) AS venta
        FROM servicios s
        LEFT JOIN (
            SELECT 
                cs.id_servicio,
                SUM(cs.qty * cs.costo) AS total_costo,
                SUM(cs.qty * cs.tarifa) AS total_venta
            FROM costos_servicios cs
            GROUP BY cs.id_servicio
        ) cs_data ON s.id_srvc = cs_data.id_servicio
        WHERE s.id_prospect = ?
        ORDER BY s.id_srvc
    ");
    $stmt->execute([$id]);
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("DEBUG ESTADO COSTOS: " . json_encode(array_column($servicios, 'estado_costos')));

    // === Cargar costos y gastos por servicio ===
    $serviciosConDetalles = [];
    foreach ($servicios as $s) {
        // Costos
        $stmtCostos = $pdo->prepare("SELECT * FROM costos_servicios WHERE id_servicio = ?");
        $stmtCostos->execute([$s['id_srvc']]);
        $costos = $stmtCostos->fetchAll(PDO::FETCH_ASSOC);

        // Gastos locales
        $stmtGastos = $pdo->prepare("SELECT * FROM gastos_locales_detalle WHERE id_servicio = ?");
        $stmtGastos->execute([$s['id_srvc']]);
        $gastos = $stmtGastos->fetchAll(PDO::FETCH_ASSOC);

        // ✅ Preservar los campos costo y venta calculados
        $serviciosConDetalles[] = array_merge($s, [
            'costos' => $costos,
            'gastos_locales' => $gastos
        ]);
    }

    echo json_encode([
        'success' => true,
        'prospecto' => $prospecto,
        'servicios' => $serviciosConDetalles
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>