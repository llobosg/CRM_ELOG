<?php
// pages/prospectos_logic.php
// Lógica de guardado para prospectos (ejecutada antes de cualquier salida HTML)

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modo'])) {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../includes/auth_check.php';

    try {
        $pdo->beginTransaction();

        $id_ppl = (int)($_POST['id_ppl'] ?? 0);
        $modo_update = ($id_ppl > 0);
        $servicios_existentes = false;

        if ($modo_update) {
            // Verificar si ya tiene servicios
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM servicios WHERE id_prospect = ?");
            $stmt_check->execute([$id_ppl]);
            $servicios_existentes = $stmt_check->fetchColumn() > 0;

            // Obtener id_prospect
            $stmt_id = $pdo->prepare("SELECT id_prospect FROM prospectos WHERE id_ppl = ?");
            $stmt_id->execute([$id_ppl]);
            $id_prospect = (int)$stmt_id->fetchColumn();
        } else {
            // Nuevo prospecto
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

        // === Generar concatenado ===
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
        $correlativo = str_pad($id_prospect + 1, 2, '0', STR_PAD_LEFT);
        $concatenado = $prefijo . $fecha_actual . '-' . $correlativo;

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

        // === Procesar servicios si el modo es 'servicios' ===
        if ($_POST['modo'] === 'servicios') {
            $pdo->prepare("DELETE FROM servicios WHERE id_prospect = ?")->execute([$id_ppl]);
            $pdo->prepare("DELETE FROM costos_servicios WHERE id_servicio IN (SELECT id_srvc FROM servicios WHERE id_prospect = ?)")->execute([$id_ppl]);
            $pdo->prepare("DELETE FROM gastos_locales_detalle WHERE id_servicio IN (SELECT id_srvc FROM servicios WHERE id_prospect = ?)")->execute([$id_ppl]);

            $servicios_json = $_POST['servicios_json'] ?? '';
            $total_costo = 0;
            $total_venta = 0;
            $total_costogasto = 0;
            $total_ventagasto = 0;

            if ($servicios_json) {
                $servicios_data = json_decode($servicios_json, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Error al decodificar servicios JSON');
                }

                $stmt_serv = $pdo->prepare("INSERT INTO servicios (
                    id_srvc, id_prospect, servicio, nombre_corto, tipo, trafico, sub_trafico,
                    base_calculo, moneda, tarifa, iva, estado, costo, venta,
                    costogastoslocalesdestino, ventasgastoslocalesdestino, desconsolidac,
                    commodity, origen, pais_origen, destino, pais_destino, transito, frecuencia,
                    lugar_carga, sector, mercancia, bultos, peso, volumen, dimensiones,
                    agente, aol, aod, aerolinea, naviera, terrestre, ref_cliente, proveedor_nac, tipo_cambio,
                    ciudad, pais, direc_serv
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                foreach ($servicios_data as $s) {
                    $costo = (float)($s['costo'] ?? 0);
                    $venta = (float)($s['venta'] ?? 0);
                    $costogasto = (float)($s['costogastoslocalesdestino'] ?? 0);
                    $ventagasto = (float)($s['ventasgastoslocalesdestino'] ?? 0);

                    $stmt_last = $pdo->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(id_srvc, '-', -1) AS UNSIGNED)) as max_id FROM servicios WHERE id_prospect = ?");
                    $stmt_last->execute([$id_ppl]);
                    $last = $stmt_last->fetch();
                    $correlativo_srvc = str_pad(($last['max_id'] ?? 0) + 1, 2, '0', STR_PAD_LEFT);
                    $id_srvc = "{$concatenado}-{$correlativo_srvc}";

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
                        (float)($s['volumen'] ?? 0),
                        $s['dimensiones'] ?? '',
                        $s['agente'] ?? '',
                        $s['aol'] ?? '',
                        $s['aod'] ?? '',
                        $s['aerolinea'] ?? '',
                        $s['naviera'] ?? '',
                        $s['terrestre'] ?? '',
                        $s['ref_cliente'] ?? '',
                        $s['proveedor_nac'] ?? '',
                        (float)($s['tipo_cambio'] ?? 1),
                        $s['ciudad'] ?? '',
                        $s['pais'] ?? '',
                        $s['direc_serv'] ?? ''
                    ]);

                    // Insertar costos
                    if (!empty($s['costos'])) {
                        $stmt_costo = $pdo->prepare("
                            INSERT INTO costos_servicios (
                                id_servicio, concepto, moneda, qty, costo, total_costo, tarifa, total_tarifa, aplica
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        foreach ($s['costos'] as $c) {
                            $stmt_costo->execute([
                                $id_srvc,
                                $c['concepto'] ?? '',
                                $c['moneda'] ?? 'CLP',
                                (float)($c['qty'] ?? 0),
                                (float)($c['costo'] ?? 0),
                                (float)($c['total_costo'] ?? 0),
                                (float)($c['tarifa'] ?? 0),
                                (float)($c['total_tarifa'] ?? 0),
                                $c['aplica'] ?? ''
                            ]);
                        }
                    }

                    // Insertar gastos locales
                    if (!empty($s['gastos_locales'])) {
                        $stmt_gasto = $pdo->prepare("
                            INSERT INTO gastos_locales_detalle (
                                id_servicio, tipo, gasto, moneda, monto, afecto, iva
                            ) VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        foreach ($s['gastos_locales'] as $g) {
                            $stmt_gasto->execute([
                                $id_srvc,
                                $g['tipo'] ?? '',
                                $g['gasto'] ?? '',
                                $g['moneda'] ?? 'CLP',
                                (float)($g['monto'] ?? 0),
                                $g['afecto'] ?? 'SI',
                                (float)($g['iva'] ?? 0)
                            ]);
                        }
                    }

                    $total_costo += $costo;
                    $total_venta += $venta;
                    $total_costogasto += $costogasto;
                    $total_ventagasto += $ventagasto;
                }

                // Actualizar totales en prospecto
                $pdo->prepare("UPDATE prospectos SET
                    total_costo = ?, total_venta = ?,
                    total_costogastoslocalesdestino = ?, total_ventasgastoslocalesdestino = ?
                WHERE id_ppl = ?")->execute([
                    $total_costo, $total_venta, $total_costogasto, $total_ventagasto, $id_ppl
                ]);
            }
        }

        $pdo->commit();

        // Redirección sin salida previa
        header("Location: " . $_SERVER['PHP_SELF'] . "?page=prospectos&exito=1&concatenado=" . urlencode($concatenado) . "&id_ppl=" . $id_ppl);
        exit;

    } catch (Exception $e) {
        try { $pdo->rollback(); } catch (Exception $ex) {}
        $mensajeUsuario = "Error al guardar el prospecto.";
        if (defined('DEVELOPMENT') && DEVELOPMENT) {
            $mensajeUsuario = $e->getMessage();
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?page=prospectos&error=" . urlencode($mensajeUsuario));
        exit;
    }
}
?>