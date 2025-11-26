<?php
// api/guardar_servicio.php
header('Content-Type: application/json');

// ✅ Eliminamos auth_check.php (validación en index.php)
require_once __DIR__ . '/../config.php';

try {
    // Iniciar transacción
    $pdo->beginTransaction();

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

        // === ACTUALIZAR SERVICIO ===
        $sql = "
            UPDATE servicios SET
                servicio = ?, nombre_corto = ?, tipo = ?, trafico = ?, sub_trafico = ?,
                base_calculo = ?, moneda = ?, tarifa = ?, iva = ?, estado = ?,
                costo = ?, venta = ?, costogastoslocalesdestino = ?, ventasgastoslocalesdestino = ?,
                desconsolidac = ?, commodity = ?, origen = ?, pais_origen = ?,
                destino = ?, pais_destino = ?, transito = ?, frecuencia = ?, lugar_carga = ?,
                sector = ?, mercancia = ?, bultos = ?, peso = ?, volumen = ?,
                dimensiones = ?, agente = ?, aol = ?, aod = ?, transportador = ?,
                incoterm = ?, ref_cliente = ?, proveedor_nac = ?,
                tipo_cambio = ?, ciudad = ?, pais = ?, direc_serv = ?,
                estado_costos = ?
            WHERE id_srvc = ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['servicio'] ?? '',
            $data['nombre_corto'] ?? '',
            $data['tipo'] ?? '',
            $data['trafico'] ?? '',
            $data['sub_trafico'] ?? '',
            $data['base_calculo'] ?? '',
            $data['moneda'] ?? 'CLP',
            (float)($data['tarifa'] ?? 0),
            (int)($data['iva'] ?? 19),
            $data['estado'] ?? 'Activo',
            (float)($data['costo'] ?? 0),
            (float)($data['venta'] ?? 0),
            (float)($data['costogastoslocalesdestino'] ?? 0),
            (float)($data['ventasgastoslocalesdestino'] ?? 0),
            '0',
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
            $data['ciudad'] ?? '',
            $data['pais'] ?? '',
            $data['direc_serv'] ?? '',
            $data['estado_costos'] ?? 'pendiente',
            $id_srvc
        ]);

        // === ELIMINAR y REINSERTAR costos y gastos ===
        $pdo->prepare("DELETE FROM costos_servicios WHERE id_servicio = ?")->execute([$id_srvc]);
        $pdo->prepare("DELETE FROM gastos_locales_detalle WHERE id_servicio = ?")->execute([$id_srvc]);

        // === INSERTAR COSTOS ===
        $costos = $data['costos'] ?? [];
        error_log("[GUARDAR_SERVICIO] Insertando " . count($costos) . " costos para id_srvc: $id_srvc");
        foreach ($costos as $c) {
            error_log("[GUARDAR_SERVICIO] Costo: " . json_encode($c));
            $stmtC = $pdo->prepare("
                INSERT INTO costos_servicios (id_servicio, concepto, moneda, qty, costo, tarifa, aplica)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtC->execute([
                $id_srvc,
                $c['concepto'] ?? '',
                $c['moneda'] ?? 'CLP',
                (float)($c['qty'] ?? 0),
                (float)($c['costo'] ?? 0),
                (float)($c['tarifa'] ?? 0),
                $c['aplica'] ?? ''
            ]);
        }

        // === INSERTAR GASTOS ===
        $gastos = $data['gastos_locales'] ?? [];
        error_log("[GUARDAR_SERVICIO] Insertando " . count($gastos) . " gastos para id_srvc: $id_srvc");
        foreach ($gastos as $g) {
            error_log("[GUARDAR_SERVICIO] Gasto: " . json_encode($g));
            $stmtG = $pdo->prepare("
                INSERT INTO gastos_locales_detalle (id_servicio, tipo, gasto, moneda, monto, afecto, iva)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtG->execute([
                $id_srvc,
                $g['tipo'] ?? '',
                $g['gasto'] ?? '',
                $g['moneda'] ?? 'CLP',
                (float)($g['monto'] ?? 0),
                $g['afecto'] ?? 'NO',
                (float)($g['iva'] ?? 0)
            ]);
        }

        $mensaje = 'Servicio actualizado correctamente';
    } else {
        // === CREAR NUEVO SERVICIO ===
        $stmt = $pdo->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(id_srvc, '-', -1) AS UNSIGNED)) as max_id FROM servicios WHERE id_prospect = ?");
        $stmt->execute([$id_ppl]);
        $last = $stmt->fetch();
        $correlativo = str_pad(($last['max_id'] ?? 0) + 1, 2, '0', STR_PAD_LEFT);
        $id_srvc = "{$base}-{$correlativo}";

        // === INSERT EN servicios ===
        $sql = "
            INSERT INTO servicios (
                id_srvc, id_ppl, id_prospect,
                servicio, nombre_corto, tipo, trafico, sub_trafico,
                base_calculo, moneda, tarifa, iva, estado,
                costo, venta, costogastoslocalesdestino, ventasgastoslocalesdestino, desconsolidac,
                commodity, origen, pais_origen, destino, pais_destino, transito, frecuencia,
                lugar_carga, sector, mercancia, bultos, peso, volumen, dimensiones,
                agente, aol, aod, transportador, incoterm, ref_cliente, proveedor_nac,
                tipo_cambio, ciudad, pais, direc_serv,
                estado_costos,
                solicitado_por, fecha_solicitado,
                completado_por, fecha_completado,
                revisado_por, fecha_revisado
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?

            )
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $id_srvc, $id_ppl, $id_ppl,
            $data['servicio'] ?? '', $data['nombre_corto'] ?? '', $data['tipo'] ?? '', $data['trafico'] ?? '', $data['sub_trafico'] ?? '',
            $data['base_calculo'] ?? '', $data['moneda'] ?? 'CLP', (float)($data['tarifa'] ?? 0), (int)($data['iva'] ?? 19), $data['estado'] ?? 'Activo',
            (float)($data['costo'] ?? 0), (float)($data['venta'] ?? 0), (float)($data['costogastoslocalesdestino'] ?? 0), (float)($data['ventasgastoslocalesdestino'] ?? 0), '0',
            $data['commodity'] ?? '', $data['origen'] ?? '', $data['pais_origen'] ?? '', $data['destino'] ?? '', $data['pais_destino'] ?? '', $data['transito'] ?? '', $data['frecuencia'] ?? '',
            $data['lugar_carga'] ?? '', $data['sector'] ?? '', $data['mercancia'] ?? '', (int)($data['bultos'] ?? 0), (float)($data['peso'] ?? 0), (string)($data['volumen'] ?? '0.00'), (string)($data['dimensiones'] ?? ''),
            $data['agente'] ?? '', $data['aol'] ?? '', $data['aod'] ?? '', $data['transportador'] ?? '', $data['incoterm'] ?? '', $data['ref_cliente'] ?? '', $data['proveedor_nac'] ?? '',
            (float)($data['tipo_cambio'] ?? 1), $data['ciudad'] ?? '', $data['pais'] ?? '', $data['direc_serv'] ?? '',
            $data['estado_costos'] ?? 'pendiente',
            null, null, null, null, null, null
        ]);

        // === INSERTAR COSTOS ===
        $costos = $data['costos'] ?? [];
        error_log("[GUARDAR_SERVICIO] Insertando " . count($costos) . " costos para nuevo servicio: $id_srvc");
        foreach ($costos as $c) {
            error_log("[GUARDAR_SERVICIO] Costo: " . json_encode($c));
            $stmtC = $pdo->prepare("
                INSERT INTO costos_servicios (id_servicio, concepto, moneda, qty, costo, tarifa, aplica)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtC->execute([
                $id_srvc,
                $c['concepto'] ?? '',
                $c['moneda'] ?? 'CLP',
                (float)($c['qty'] ?? 0),
                (float)($c['costo'] ?? 0),
                (float)($c['tarifa'] ?? 0),
                $c['aplica'] ?? ''
            ]);
        }

        // === INSERTAR GASTOS ===
        $gastos = $data['gastos_locales'] ?? [];
        error_log("[GUARDAR_SERVICIO] Insertando " . count($gastos) . " gastos para nuevo servicio: $id_srvc");
        foreach ($gastos as $g) {
            error_log("[GUARDAR_SERVICIO] Gasto: " . json_encode($g));
            $stmtG = $pdo->prepare("
                INSERT INTO gastos_locales_detalle (id_servicio, tipo, gasto, moneda, monto, afecto, iva)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtG->execute([
                $id_srvc,
                $g['tipo'] ?? '',
                $g['gasto'] ?? '',
                $g['moneda'] ?? 'CLP',
                (float)($g['monto'] ?? 0),
                $g['afecto'] ?? 'NO',
                (float)($g['iva'] ?? 0)
            ]);
        }

        $mensaje = 'Servicio creado correctamente';
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'id_srvc' => $id_srvc,
        'message' => $mensaje
    ]);

} catch (Exception $e) {
    $pdo->rollback();
    error_log("Error en guardar_servicio.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>