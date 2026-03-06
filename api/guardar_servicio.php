<?php
// api/guardar_servicio.php

// Capturar cualquier salida previa (por si acaso)
ob_start();

// Establecer encabezado de JSON de antemano (aunque se puede sobrescribir si hay error fatal)
header('Content-Type: application/json');

try {
    // Incluir config y verificar PDO inmediatamente
    require_once __DIR__ . '/../config.php';

    // --- Verificación crítica de la conexión ---
    if (!isset($pdo) || !$pdo instanceof PDO) {
        $errorMessage = "Error crítico: No se pudo establecer la conexión a la base de datos (PDO).";
        error_log("[GUARDAR_SERVICIO] ERROR FATAL: " . $errorMessage);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $errorMessage]);
        exit;
    }
    // --- Fin verificación ---

    error_log('[GUARDAR_SERVICIO] Iniciando proceso...');

    // Iniciar transacción
    $pdo->beginTransaction();

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        error_log("[GUARDAR_SERVICIO] ERROR: Datos inválidos recibidos.");
        throw new Exception('Datos inválidos');
    }
    error_log("[GUARDAR_SERVICIO] Datos recibidos: " . print_r($data, true));

    $modo = $data['modo'] ?? 'crear';
    $id_srvc = $data['id_srvc'] ?? null;
    $id_ppl = (int)($data['id_prospect'] ?? 0);
    if ($id_ppl <= 0) {
        error_log("[GUARDAR_SERVICIO] ERROR: ID de prospecto inválido ({$id_ppl}).");
        throw new Exception('ID de prospecto inválido');
    }
    error_log("[GUARDAR_SERVICIO] Modo: {$modo}, ID Servicio: {$id_srvc}, ID Prospecto: {$id_ppl}");

    // Obtener concatenado del prospecto
    $stmt = $pdo->prepare("SELECT concatenado FROM prospectos WHERE id_ppl = ?");
    $stmt->execute([$id_ppl]);
    $prospecto = $stmt->fetch();
    if (!$prospecto) {
        error_log("[GUARDAR_SERVICIO] ERROR: Prospecto no encontrado para id_ppl: {$id_ppl}");
        throw new Exception('Prospecto no encontrado');
    }
    $base = preg_replace('/-\d+$/', '', $prospecto['concatenado']);
    error_log("[GUARDAR_SERVICIO] Base de ID calculada: {$base}");

    // --- Cálculo de totales por moneda para gastos locales ---
    $gastos = $data['gastos_locales'] ?? [];
    $cgld_usd = 0; $cgld_eur = 0; $cgld_clp = 0;
    $vgld_usd = 0; $vgld_eur = 0; $vgld_clp = 0;

    foreach ($gastos as $g) {
        $monto = (float)($g['monto'] ?? 0);
        $tipo = $g['tipo'] ?? '';
        $moneda = $g['moneda'] ?? 'CLP'; // Valor por defecto

        $esAfecto = ($g['afecto'] ?? 'NO') === 'SI';
        $iva = (float)($g['iva'] ?? 0);
        $subtotal = $esAfecto ? $monto * (1 + $iva / 100) : $monto;

        if ($tipo === 'Costo') {
            switch (strtoupper($moneda)) {
                case 'USD':
                    $cgld_usd += $subtotal;
                    break;
                case 'EUR':
                    $cgld_eur += $subtotal;
                    break;
                case 'CLP':
                default:
                    $cgld_clp += $subtotal;
                    break;
            }
        } elseif ($tipo === 'Ventas') {
            switch (strtoupper($moneda)) {
                case 'USD':
                    $vgld_usd += $subtotal;
                    break;
                case 'EUR':
                    $vgld_eur += $subtotal;
                    break;
                case 'CLP':
                default:
                    $vgld_clp += $subtotal;
                    break;
            }
        }
    }

    // Calcular profit y profit % por moneda
    $pgld_usd = $vgld_usd - $cgld_usd;
    $ppgld_usd = $vgld_usd > 0 ? (($vgld_usd - $cgld_usd) / $vgld_usd * 100) : 0;

    $pgld_eur = $vgld_eur - $cgld_eur;
    $ppgld_eur = $vgld_eur > 0 ? (($vgld_eur - $cgld_eur) / $vgld_eur * 100) : 0;

    $pgld_clp = $vgld_clp - $cgld_clp;
    $ppgld_clp = $vgld_clp > 0 ? (($vgld_clp - $cgld_clp) / $vgld_clp * 100) : 0;

    error_log("[GUARDAR_SERVICIO] Totales Gastos Locales Calculados - CLP: C={$cgld_clp}, V={$vgld_clp}, P={$pgld_clp}, %={$ppgld_clp} | USD: C={$cgld_usd}, V={$vgld_usd}, P={$pgld_usd}, %={$ppgld_usd} | EUR: C={$cgld_eur}, V={$vgld_eur}, P={$pgld_eur}, %={$ppgld_eur}");

    if ($modo === 'editar') {
        error_log("[GUARDAR_SERVICIO] Modo edición.");
        // === VALIDAR que el servicio exista y pertenezca al prospecto ===
        if (!$id_srvc) {
            error_log("[GUARDAR_SERVICIO] ERROR: ID de servicio requerido para edición pero no provisto.");
            throw new Exception('ID de servicio requerido para edición');
        }
        $stmt = $pdo->prepare("SELECT id_srvc FROM servicios WHERE id_srvc = ? AND id_prospect = ?");
        $stmt->execute([$id_srvc, $id_ppl]);
        if (!$stmt->fetch()) {
            error_log("[GUARDAR_SERVICIO] ERROR: Servicio no encontrado o no autorizado ({$id_srvc}, {$id_ppl}).");
            throw new Exception('Servicio no encontrado o no autorizado');
        }

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
                estado_costos = ?, nota_srvc = ?,
                -- Campos nuevos para gastos locales por moneda
                cgld_usd = ?, cgld_eur = ?, cgld_clp = ?,
                vgld_usd = ?, vgld_eur = ?, vgld_clp = ?,
                pgld_usd = ?, pgld_eur = ?, pgld_clp = ?,
                ppgld_usd = ?, ppgld_eur = ?, ppgld_clp = ?,
                --
                solicitado_por = ?, fecha_solicitado = ?,
                completado_por = ?, fecha_completado = ?,
                revisado_por = ?, fecha_revisado = ?,
                --
                validez = ?
            WHERE id_srvc = ?
        ";
        $stmt = $pdo->prepare($sql);
        $params = [
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
            $data['nota_srvc'] ?? '',
            // Valores calculados para nuevos campos
            $cgld_usd, $cgld_eur, $cgld_clp,
            $vgld_usd, $vgld_eur, $vgld_clp,
            $pgld_usd, $pgld_eur, $pgld_clp,
            $ppgld_usd, $ppgld_eur, $ppgld_clp,
            // ---
            $data['solicitado_por'] ?? null,
            $data['fecha_solicitado'] ?? null,
            $data['completado_por'] ?? null,
            $data['fecha_completado'] ?? null,
            $data['revisado_por'] ?? null,
            $data['fecha_revisado'] ?? null,
            $data['validez'] ?? null,
            $id_srvc
        ];

        error_log("[GUARDAR_SERVICIO] Parámetros para UPDATE: " . print_r($params, true));
        $stmt->execute($params);
        error_log("[GUARDAR_SERVICIO] UPDATE ejecutado. Filas afectadas: " . $stmt->rowCount());

        // === ELIMINAR y REINSERTAR costos y gastos ===
        $pdo->prepare("DELETE FROM costos_servicios WHERE id_servicio = ?")->execute([$id_srvc]);
        $pdo->prepare("DELETE FROM gastos_locales_detalle WHERE id_servicio = ?")->execute([$id_srvc]);

        // --- Insertar Costos ---
        $costos = $data['costos'] ?? []; // ✅ Usar $data, no $s
        error_log("[GUARDAR_SERVICIO] Insertando " . count($costos) . " costos para id_srvc: $id_srvc");
        foreach ($costos as $c) {
            error_log("[GUARDAR_SERVICIO] Costo: " . json_encode($c));
            $stmtC = $pdo->prepare("
                INSERT INTO costos_servicios (
                    id_servicio, concepto, moneda, qty, porcentaje_concepto, 
                    costo, total_costo, tarifa, total_tarifa, aplica
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtC->execute([
                $id_srvc,
                $c['concepto'] ?? '',
                $c['moneda'] ?? 'CLP',
                (float)($c['qty'] ?? 0),
                (float)($c['porcentaje_concepto'] ?? 100),
                (float)($c['costo'] ?? 0),
                (float)($c['total_costo'] ?? 0), // ✅ Incluir total_costo
                (float)($c['tarifa'] ?? 0),
                (float)($c['total_tarifa'] ?? 0), // ✅ Incluir total_tarifa
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

        // === 🔥 ACTUALIZACIÓN CLAVE: Forzar estado_costos basado en costos reales (EDICIÓN) ===
        $tieneCostosReales = false;
        foreach ($costos as $c) {
            $qty = (float)($c['qty'] ?? 0);
            $costo = (float)($c['costo'] ?? 0);
            if ($qty > 0 && $costo > 0) {
                $tieneCostosReales = true;
                break;
            }
        }
        $estado_costos_final = $tieneCostosReales ? 'completado' : 'pendiente';

        $stmtUpdateEstado = $pdo->prepare("UPDATE servicios SET estado_costos = ? WHERE id_srvc = ?");
        $stmtUpdateEstado->execute([$estado_costos_final, $id_srvc]);
        error_log("[GUARDAR_SERVICIO] Estado costos actualizado a: {$estado_costos_final} para servicio: {$id_srvc}");
        // === 🔚 FIN ACTUALIZACIÓN CLAVE ===

        $mensaje = 'Servicio actualizado correctamente';
    } else {
        error_log("[GUARDAR_SERVICIO] Modo creación.");
        // === CREAR NUEVO SERVICIO ===
        $stmt = $pdo->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(id_srvc, '-', -1) AS UNSIGNED)) as max_id FROM servicios WHERE id_prospect = ?");
        $stmt->execute([$id_ppl]);
        $last = $stmt->fetch();
        $correlativo = str_pad(($last['max_id'] ?? 0) + 1, 2, '0', STR_PAD_LEFT);
        $id_srvc = "{$base}-{$correlativo}";

        // === INSERT EN servicios ===
        $sql = "
            INSERT INTO servicios (
                id_ppl, id_srvc, id_prospect,
                servicio, nombre_corto, tipo, trafico, sub_trafico,
                base_calculo, moneda, tarifa, iva, estado,
                costo, venta, costogastoslocalesdestino, ventasgastoslocalesdestino, desconsolidac,
                commodity, origen, pais_origen, destino, pais_destino, transito, frecuencia,
                lugar_carga, sector, mercancia, bultos, peso, volumen, dimensiones,
                agente, aol, aod, transportador, incoterm, ref_cliente, proveedor_nac,
                tipo_cambio, ciudad, pais, direc_serv,
                estado_costos, nota_srvc,
                -- Campos nuevos para gastos locales por moneda
                cgld_usd, cgld_eur, cgld_clp,
                vgld_usd, vgld_eur, vgld_clp,
                pgld_usd, pgld_eur, pgld_clp,
                ppgld_usd, ppgld_eur, ppgld_clp,
                --
                solicitado_por, fecha_solicitado,
                completado_por, fecha_completado,
                revisado_por, fecha_revisado, validez
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?
            )
        ";
        $stmt = $pdo->prepare($sql);
        $params = [
            $id_ppl,           // ← id_ppl (int)
            $id_srvc,          // ← id_srvc (string)
            $id_ppl,           // ← id_prospect (int)
            // ... resto de los campos en el mismo orden
            $data['servicio'] ?? '',
            $data['nombre_corto'] ?? '',
            $data['tipo'] ?? '',
            $data['trafico'] ?? '',
            $data['sub_trafico'] ?? '',
            $data['base_calculo'] ?? '',
            $data['moneda'] ?? 'USD',
            (float)($data['tarifa'] ?? 0),
            (float)($data['iva'] ?? 19),
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
            $data['nota_srvc'] ?? '',
            // Gastos locales
            $cgld_usd, $cgld_eur, $cgld_clp,
            $vgld_usd, $vgld_eur, $vgld_clp,
            $pgld_usd, $pgld_eur, $pgld_clp,
            $ppgld_usd, $ppgld_eur, $ppgld_clp,
            // Fechas y usuarios
            $data['solicitado_por'] ?? null,
            $data['fecha_solicitado'] ?? null,
            $data['completado_por'] ?? null,
            $data['fecha_completado'] ?? null,
            $data['revisado_por'] ?? null,
            $data['fecha_revisado'] ?? null,
            $data['validez'] ?? null
        ];

        error_log("[GUARDAR_SERVICIO] Parámetros para INSERT: " . print_r($params, true));
        $stmt->execute($params);
        error_log("[GUARDAR_SERVICIO] INSERT ejecutado. Filas afectadas: " . $stmt->rowCount());

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

        // === 🔥 ACTUALIZACIÓN CLAVE: Forzar estado_costos basado en costos reales (CREACIÓN) ===
        $tieneCostosReales = false;
        foreach ($costos as $c) {
            $qty = (float)($c['qty'] ?? 0);
            $costo = (float)($c['costo'] ?? 0);
            if ($qty > 0 && $costo > 0) {
                $tieneCostosReales = true;
                break;
            }
        }
        $estado_costos_final = $tieneCostosReales ? 'completado' : 'pendiente';

        $stmtUpdateEstado = $pdo->prepare("UPDATE servicios SET estado_costos = ? WHERE id_srvc = ?");
        $stmtUpdateEstado->execute([$estado_costos_final, $id_srvc]);
        error_log("[GUARDAR_SERVICIO] Estado costos actualizado a: {$estado_costos_final} para servicio: {$id_srvc}");
        // === 🔚 FIN ACTUALIZACIÓN CLAVE ===

        $mensaje = 'Servicio creado correctamente';
    }

    $pdo->commit();
    error_log("[GUARDAR_SERVICIO] Transacción confirmada. Mensaje: {$mensaje}");

    // Limpiar cualquier salida previa antes de enviar JSON
    if (ob_get_level()) {
        ob_end_clean();
    }

    echo json_encode([
        'success' => true,
        'id_srvc' => $id_srvc,
        'message' => $mensaje
    ]);

} catch (Exception $e) {
    // Limpiar buffer en caso de error para asegurar solo JSON
    if (ob_get_level()) {
        ob_end_clean();
    }
    $pdo->rollback();
    error_log("[GUARDAR_SERVICIO] ERROR: " . $e->getMessage());
    error_log("[GUARDAR_SERVICIO] Trace: " . $e->getTraceAsString());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Terminar la ejecución limpiamente
exit;
?>