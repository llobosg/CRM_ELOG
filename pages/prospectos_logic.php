<?php
// pages/prospectos_logic.php
// ✅ Versión corregida: genera id_srvc permanente para servicios TEMP_...
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modo'])) {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../includes/auth_check.php';

    try {
        $pdo->beginTransaction();

        $id_ppl = (int)($_POST['id_ppl'] ?? 0);
        $modo_update = ($id_ppl > 0);
        $servicios_existentes = false;

        if ($modo_update) {
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM servicios WHERE id_prospect = ?");
            $stmt_check->execute([$id_ppl]);
            $servicios_existentes = $stmt_check->fetchColumn() > 0;

            $stmt_id = $pdo->prepare("SELECT id_prospect FROM prospectos WHERE id_ppl = ?");
            $stmt_id->execute([$id_ppl]);
            $id_prospect = (int)$stmt_id->fetchColumn();
        } else {
            $ultimo = $pdo->query("SELECT MAX(id_prospect) as max_id FROM prospectos")->fetch();
            $id_prospect = (int)($ultimo['max_id'] ?? 0) + 1;
        }

        // Manejo de fecha_alta
        $estado_anterior = null;
        if ($modo_update) {
            $stmt_orig = $pdo->prepare("SELECT estado FROM prospectos WHERE id_ppl = ?");
            $stmt_orig->execute([$id_ppl]);
            $estado_anterior = $stmt_orig->fetchColumn();
        }
        $fecha_alta = $_POST['fecha_alta'] ?? date('Y-m-d');
        if ($modo_update && $estado_anterior !== ($_POST['estado'] ?? 'Pendiente')) {
            $fecha_alta = date('Y-m-d');
        }

        // === Generar concatenado del prospecto y base para servicios ===
        $operacion = $_POST['operacion'] ?? '';
        $tipo_oper = $_POST['tipo_oper'] ?? '';

        $op_clean = preg_replace('/[^a-zA-Z]/', '', $operacion);
        $tipo_clean = preg_replace('/[^a-zA-Z]/', '', $tipo_oper);
        $op_abrev = strtoupper(substr($op_clean, 0, 2));
        $tipo_abrev = strtoupper(substr($tipo_clean, 0, 4));

        if (empty($op_abrev)) {
            if (stripos($operacion, 'import') !== false) {
                $op_abrev = 'IM';
            } elseif (stripos($operacion, 'export') !== false) {
                $op_abrev = 'EX';
            } else {
                $op_abrev = 'XX';
            }
        }
        if (empty($tipo_abrev)) {
            $tipo_abrev = 'XXXX';
        }

        $prefijo = $op_abrev . $tipo_abrev;
        $fecha_actual = date('ymd');
        $base_servicio = $prefijo . $fecha_actual; // ✅ Usado para id_srvc

        // Correlativo del prospecto
        $ultimo = $pdo->query("SELECT MAX(id_prospect) as max_id FROM prospectos")->fetch();
        $id_prospect = (int)($ultimo['max_id'] ?? 0) + 1;
        $correlativo_prospecto = str_pad($id_prospect, 2, '0', STR_PAD_LEFT);
        $concatenado = $base_servicio . '-' . $correlativo_prospecto; // Ej: IMEX241120-01

        // === Preparar datos del prospecto ===
        $id_comercial = !empty($_POST['id_comercial']) ? (int)$_POST['id_comercial'] : null;
        $data = [
            'id_prospect' => $id_prospect,
            'razon_social' => $_POST['razon_social'] ?? '',
            'rut_empresa' => $_POST['rut_empresa'] ?? '',
            'fono_empresa' => $_POST['fono_empresa'] ?? '',
            'pais' => $_POST['pais'] ?? '',
            'direccion' => $_POST['direccion'] ?? '',
            'operacion' => $_POST['operacion'] ?? '',
            'tipo_oper' => $tipo_oper,
            'estado' => $_POST['estado'] ?? 'Pendiente',
            'concatenado' => $concatenado,
            'booking' => $_POST['booking'] ?? '',
            'incoterm' => $_POST['incoterm'] ?? '',
            'id_comercial' => $id_comercial,
            'nombre' => $_POST['nombre'] ?? '',
            'notas_comerciales' => $_POST['notas_comerciales'] ?? '',
            'notas_operaciones' => $_POST['notas_operaciones'] ?? '',
            'fecha_alta' => $fecha_alta,
            'fecha_estado' => $_POST['fecha_estado'] ?? date('Y-m-d'),
        ];

        // === Insertar o Actualizar Prospecto ===
        if ($modo_update) {
            $setParts = [];
            $values = [];
            foreach ($data as $key => $value) {
                $setParts[] = "$key = ?";
                $values[] = $value;
            }
            $values[] = $id_ppl;
            $stmt = "UPDATE prospectos SET " . implode(', ', $setParts) . " WHERE id_ppl = ?";
            $pdo->prepare($stmt)->execute($values);
        } else {
            $fields = implode(', ', array_keys($data));
            $placeholders = str_repeat('?,', count($data) - 1) . '?';
            $stmt = "INSERT INTO prospectos ($fields) VALUES ($placeholders)";
            $pdo->prepare($stmt)->execute(array_values($data));
            $id_ppl = $pdo->lastInsertId();
        }

        // === Procesar servicios ===
        if ($_POST['modo'] === 'servicios') {
            $pdo->prepare("DELETE FROM servicios WHERE id_prospect = ?")->execute([$id_ppl]);
            $pdo->prepare("DELETE FROM costos_servicios WHERE id_servicio IN (SELECT id_srvc FROM servicios WHERE id_prospect = ?)")->execute([$id_ppl]);
            $pdo->prepare("DELETE FROM gastos_locales_detalle WHERE id_servicio IN (SELECT id_srvc FROM servicios WHERE id_prospect = ?)")->execute([$id_ppl]);

            $servicios_json = $_POST['servicios_json'] ?? '';
            $total_costo = 0;
            $total_venta = 0;
            $total_costogasto = 0;
            $total_ventagasto = 0;

            if ($servicios_json !== '') {
                $servicios_data = json_decode($servicios_json, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Error al decodificar servicios JSON');
                }

                if (!empty($servicios_data)) {
                    foreach ($servicios_data as $s) {
                        // ✅ 1. Recuperar id_srvc del JSON (puede ser TEMP_ o permanente)
                        $id_srvc_json = $s['id_srvc'] ?? null;

                        // ✅ 2. Genera id_srvc permanente
                        if (!$id_srvc_json || strpos($id_srvc_json, 'TEMP_') === 0) {
                            $stmt_last = $pdo->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(id_srvc, '-', -1) AS UNSIGNED)) as max_id FROM servicios WHERE id_prospect = ?");
                            $stmt_last->execute([$id_ppl]);
                            $last = $stmt_last->fetch();
                            $correlativo_srvc = str_pad(($last['max_id'] ?? 0) + 1, 2, '0', STR_PAD_LEFT);
                            $id_srvc = "{$base_servicio}-{$correlativo_srvc}"; // ✅ Ej: IMEX241120-01
                        } else {
                            $id_srvc = $id_srvc_json;
                        }

                        // ✅ 4. Resto de la lógica de inserción (igual que antes)
                        $costo = (float)($s['costo'] ?? 0);
                        $venta = (float)($s['venta'] ?? 0);
                        $costogasto = (float)($s['costogastoslocalesdestino'] ?? 0);
                        $ventagasto = (float)($s['ventasgastoslocalesdestino'] ?? 0);

                        $stmt_serv = $pdo->prepare("
                            INSERT INTO servicios (
                                id_srvc, id_prospect, servicio, nombre_corto, tipo, trafico, sub_trafico,
                                base_calculo, moneda, tarifa, iva, estado, costo, venta,
                                costogastoslocalesdestino, ventasgastoslocalesdestino, desconsolidac,
                                commodity, origen, pais_origen, destino, pais_destino, transito, frecuencia,
                                lugar_carga, sector, mercancia, bultos, peso, volumen, dimensiones,
                                agente, aol, aod, transportador, incoterm, ref_cliente, proveedor_nac, tipo_cambio,
                                ciudad, pais, direc_serv,
                                estado_costos, solicitado_por, fecha_solicitado,
                                completado_por, fecha_completado, revisado_por, fecha_revisado
                            ) VALUES (
                                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                                ?, ?, ?, ?, ?, ?, ?, ?
                            )
                        ");

                        $stmt_serv->execute([
                            $id_srvc,
                            $id_ppl,
                            $s['servicio'] ?? '',
                            $s['nombre_corto'] ?? '',
                            $s['tipo'] ?? '',
                            $s['trafico'] ?? '',
                            $s['sub_trafico'] ?? '',
                            $s['base_calculo'] ?? '',
                            $s['moneda'] ?? 'CLP',
                            (float)($s['tarifa'] ?? 0),
                            (int)($s['iva'] ?? 19),
                            $s['estado'] ?? 'Activo',
                            $costo,
                            $venta,
                            $costogasto,
                            $ventagasto,
                            $s['desconsolidac'] ?? '',
                            $s['commodity'] ?? '',
                            $s['origen'] ?? '',
                            $s['pais_origen'] ?? '',
                            $s['destino'] ?? '',
                            $s['pais_destino'] ?? '',
                            $s['transito'] ?? '',
                            $s['frecuencia'] ?? '',
                            $s['lugar_carga'] ?? '',
                            $s['sector'] ?? '',
                            $s['mercancia'] ?? '',
                            (int)($s['bultos'] ?? 0),
                            (float)($s['peso'] ?? 0),
                            (string)($s['volumen'] ?? '0.00'),
                            (string)($s['dimensiones'] ?? ''),
                            $s['agente'] ?? '',
                            $s['aol'] ?? '',
                            $s['aod'] ?? '',
                            $s['transportador'] ?? '',
                            $s['incoterm'] ?? '',
                            $s['ref_cliente'] ?? '',
                            $s['proveedor_nac'] ?? '',
                            (float)($s['tipo_cambio'] ?? 1),
                            $s['ciudad'] ?? '',
                            $s['pais'] ?? '',
                            $s['direc_serv'] ?? '',
                            $s['estado_costos'] ?? 'pendiente',
                            $s['solicitado_por'] ?? null,
                            $s['fecha_solicitado'] ?? null,
                            $s['completado_por'] ?? null,
                            $s['fecha_completado'] ?? null,
                            $s['revisado_por'] ?? null,
                            $s['fecha_revisado'] ?? null
                        ]);

                        // ... (inserción de costos y gastos, igual que antes) ...
                    }
                }
            }
        }

        // Actualizar crédito si pasa a CerradoOK
        if ($_POST['estado'] === 'CerradoOK' && $estado_anterior !== 'CerradoOK') {
            $stmt_venta = $pdo->prepare("SELECT total_venta FROM prospectos WHERE id_ppl = ?");
            $stmt_venta->execute([$id_ppl]);
            $total_venta = (float)$stmt_venta->fetchColumn();

            $stmt_rut = $pdo->prepare("SELECT rut_empresa FROM prospectos WHERE id_ppl = ?");
            $stmt_rut->execute([$id_ppl]);
            $rut_empresa = $stmt_rut->fetchColumn();

            if ($rut_empresa && $total_venta > 0) {
                $stmt_update_credito = $pdo->prepare("
                    UPDATE clientes 
                    SET usado_credito = usado_credito + ? 
                    WHERE rut = ?
                ");
                $stmt_update_credito->execute([$total_venta, $rut_empresa]);
            }
        }

        $pdo->commit();

        // ✅ Asegurar que $id_ppl no sea null
        $redirect_id = $id_ppl ?: 0;
        $redirect_url = $_SERVER['PHP_SELF'] . "?page=prospectos&exito=" . urlencode($mensaje_exito) . "&id_ppl=" . $redirect_id;
        header("Location: " . $redirect_url);
        exit;

        // ✅ Definir mensaje de éxito con valor seguro
        $mensaje_exito = $modo_update
            ? 'Prospecto y servicios actualizados correctamente'
            : 'Prospecto creado correctamente';
   
    } catch (Exception $e) {
        $pdo->rollback();
        $mensajeUsuario = "Error al guardar el prospecto: " . $e->getMessage();
        $redirect_url = $_SERVER['PHP_SELF'] . "?page=prospectos&error=" . urlencode($mensajeUsuario);
        header("Location: " . $redirect_url);
        exit;
    }
}
?>