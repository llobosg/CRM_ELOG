<?php
// pages/prospectos_logic.php

// 🔥 NUEVO: Manejo de eliminación vía GET
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'eliminar' && isset($_GET['id'])) {
    require_once __DIR__ . '/../config.php';

    // Verificación de sesión manual
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['user_id']) || empty($_SESSION['rol'])) {
        header('Location: /login.php');
        exit;
    }

    $rol = $_SESSION['rol'] ?? '';
    if ($rol !== 'admin' && $rol !== 'comercial') {
        http_response_code(403);
        exit('Acceso denegado.');
    }

    $id_ppl = (int)$_GET['id'];

    try {
        $pdo->beginTransaction();

        // 1. Obtener todos los id_srvc asociados al prospecto
        $stmt = $pdo->prepare("SELECT id_srvc FROM servicios WHERE id_ppl = ?");
        $stmt->execute([$id_ppl]);
        $servicios = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($servicios)) {
            $ids_servicios = implode(',', array_map('intval', $servicios));

            // 2. Eliminar costos_servicios
            $pdo->exec("DELETE FROM costos_servicios WHERE id_servicio IN ($ids_servicios)");

            // 3. Eliminar gastos_locales_detalle
            $pdo->exec("DELETE FROM gastos_locales_detalle WHERE id_servicio IN ($ids_servicios)");

            // 4. Eliminar servicios
            $pdo->exec("DELETE FROM servicios WHERE id_ppl = $id_ppl");
        }

        // 5. Eliminar prospecto
        $stmt = $pdo->prepare("DELETE FROM prospectos WHERE id_ppl = ?");
        $stmt->execute([$id_ppl]);

        $pdo->commit();

        header('Location: /?page=prospectos_listas&exito=Prospecto+eliminado+correctamente');
        exit;

    } catch (Exception $e) {
        $pdo->rollback();
        error_log("Error al eliminar prospecto ID $id_ppl: " . $e->getMessage());
        header('Location: /?page=prospectos_listas&error=Error+al+eliminar+el+prospecto');
        exit;
    }
}

// ✅ Resto del código original (modo POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modo'])) {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../includes/auth_check.php';

    try {
        $pdo->beginTransaction();

        $id_ppl = (int)($_POST['id_ppl'] ?? 0);
        $modo_update = ($id_ppl > 0);

        if ($modo_update) {
            $stmt_orig = $pdo->prepare("SELECT estado FROM prospectos WHERE id_ppl = ?");
            $stmt_orig->execute([$id_ppl]);
            $estado_anterior = $stmt_orig->fetchColumn();
        }

        // Manejo de fecha_alta
        $fecha_alta = $_POST['fecha_alta'] ?? date('Y-m-d');
        if ($modo_update && isset($_POST['estado']) && $estado_anterior !== $_POST['estado']) {
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
        if ($modo_update) {
            $stmt_id = $pdo->prepare("SELECT id_prospect FROM prospectos WHERE id_ppl = ?");
            $stmt_id->execute([$id_ppl]);
            $id_prospect = (int)$stmt_id->fetchColumn();
        } else {
            $ultimo = $pdo->query("SELECT MAX(id_prospect) as max_id FROM prospectos")->fetch();
            $id_prospect = (int)($ultimo['max_id'] ?? 0) + 1;
        }
        $correlativo = str_pad($id_prospect, 2, '0', STR_PAD_LEFT);
        $concatenado = $prefijo . $fecha_actual . '-' . $correlativo;

        // === Preparar datos del prospecto ===
        // ✅ Usar el user_id de la sesión como id_comercial
        $id_comercial = $_SESSION['user_id'] ?? null;
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

        // === Actualizar crédito si pasa a CerradoOK ===
        if ($_POST['estado'] === 'CerradoOK' && ($estado_anterior ?? 'Pendiente') !== 'CerradoOK') {
            $stmt_venta = $pdo->prepare("SELECT COALESCE(SUM(venta), 0) as total_venta FROM servicios WHERE id_prospect = ?");
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

        // === Procesar servicios (si se envían) ===
        if (isset($_POST['servicios_json']) && !empty($_POST['servicios_json'])) {
            $servicios_data = json_decode($_POST['servicios_json'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error al decodificar servicios JSON: ' . json_last_error_msg());
            }

            if (is_array($servicios_data) && !empty($servicios_data)) {
                // 1. Eliminar servicios anteriores (y sus costos/gastos asociados) para este prospecto
                $pdo->prepare("DELETE FROM servicios WHERE id_prospect = ?")->execute([$id_ppl]);
                $pdo->prepare("DELETE FROM costos_servicios WHERE id_servicio IN (SELECT id_srvc FROM servicios WHERE id_prospect = ?)")->execute([$id_ppl]);
                $pdo->prepare("DELETE FROM gastos_locales_detalle WHERE id_servicio IN (SELECT id_srvc FROM servicios WHERE id_prospect = ?)")->execute([$id_ppl]);

                // Generar base para los IDs de servicios
                $base_servicio = preg_replace('/-\d+$/', '', $concatenado); // Quitar el correlativo final del prospecto

                foreach ($servicios_data as $s) {
                    // --- Asegurar valores numéricos ---
                    $costo = (float)($s['costo'] ?? 0);
                    $venta = (float)($s['venta'] ?? 0);
                    $costogasto = (float)($s['costogastoslocalesdestino'] ?? 0);
                    $ventagasto = (float)($s['ventasgastoslocalesdestino'] ?? 0);

                    // --- Generar ID de servicio ---
                    $id_srvc_json = $s['id_srvc'] ?? null;
                    if (!$id_srvc_json || strpos($id_srvc_json, 'TEMP_') === 0) {
                        $stmt_last = $pdo->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(id_srvc, '-', -1) AS UNSIGNED)) as max_id FROM servicios WHERE id_prospect = ?");
                        $stmt_last->execute([$id_ppl]);
                        $last = $stmt_last->fetch();
                        $correlativo_srvc = str_pad(((int)($last['max_id'] ?? 0) + 1), 2, '0', STR_PAD_LEFT);
                        $id_srvc = "{$base_servicio}-{$correlativo_srvc}";
                    } else {
                        $id_srvc = $id_srvc_json;
                    }

                    // --- Cálculo de totales por moneda para gastos locales ---
                    $cgld_usd = 0; $cgld_eur = 0; $cgld_clp = 0;
                    $vgld_usd = 0; $vgld_eur = 0; $vgld_clp = 0;

                    $gastos_locales_servicio = $s['gastos_locales'] ?? [];
                    foreach ($gastos_locales_servicio as $g) {
                        $monto = (float)($g['monto'] ?? 0);
                        $tipo = $g['tipo'] ?? '';
                        $moneda = $g['moneda'] ?? 'CLP';

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

                    $pgld_usd = $vgld_usd - $cgld_usd;
                    $ppgld_usd = $vgld_usd > 0 ? (($vgld_usd - $cgld_usd) / $vgld_usd * 100) : 0;
                    $pgld_eur = $vgld_eur - $cgld_eur;
                    $ppgld_eur = $vgld_eur > 0 ? (($vgld_eur - $cgld_eur) / $vgld_eur * 100) : 0;
                    $pgld_clp = $vgld_clp - $cgld_clp;
                    $ppgld_clp = $vgld_clp > 0 ? (($vgld_clp - $cgld_clp) / $vgld_clp * 100) : 0;

                    // --- INSERT EN servicios ---
                    // === Preparar datos del servicio ===
                    $servicio_data = [
                        'id_ppl' => $id_ppl,
                        'id_srvc' => $id_srvc,
                        'id_prospect' => $id_ppl,
                        'servicio' => $s['servicio'] ?? '',
                        'nombre_corto' => $s['nombre_corto'] ?? '',
                        'tipo' => $s['tipo'] ?? '',
                        'trafico' => $s['trafico'] ?? '',
                        'sub_trafico' => $s['sub_trafico'] ?? '',
                        'base_calculo' => $s['base_calculo'] ?? '',
                        'moneda' => $s['moneda'] ?? 'USD',
                        'tarifa' => (float)($s['tarifa'] ?? 0),
                        'iva' => (float)($s['iva'] ?? 19),
                        'estado' => $s['estado'] ?? 'Activo',
                        'costo' => (float)($s['costo'] ?? 0),
                        'venta' => (float)($s['venta'] ?? 0),
                        'costogastoslocalesdestino' => (float)($s['costogastoslocalesdestino'] ?? 0),
                        'ventasgastoslocalesdestino' => (float)($s['ventasgastoslocalesdestino'] ?? 0),
                        'ciudad' => $s['ciudad'] ?? '',
                        'pais' => $s['pais'] ?? '',
                        'direc_serv' => $s['direc_serv'] ?? '',
                        'tipo_cambio' => (float)($s['tipo_cambio'] ?? 1),
                        'commodity' => $s['commodity'] ?? '',
                        'origen' => $s['origen'] ?? '',
                        'pais_origen' => $s['pais_origen'] ?? '',
                        'destino' => $s['destino'] ?? '',
                        'pais_destino' => $s['pais_destino'] ?? '',
                        'transito' => $s['transito'] ?? '',
                        'frecuencia' => $s['frecuencia'] ?? '',
                        'lugar_carga' => $s['lugar_carga'] ?? '',
                        'sector' => $s['sector'] ?? '',
                        'mercancia' => $s['mercancia'] ?? '',
                        'bultos' => (int)($s['bultos'] ?? 0),
                        'peso' => (float)($s['peso'] ?? 0),
                        'volumen' => $s['volumen'] ?? '0.00',
                        'dimensiones' => $s['dimensiones'] ?? '',
                        'agente' => $s['agente'] ?? '',
                        'aol' => $s['aol'] ?? '',
                        'aod' => $s['aod'] ?? '',
                        'transportador' => $s['transportador'] ?? '',
                        'incoterm' => $s['incoterm'] ?? '',
                        'ref_cliente' => $s['ref_cliente'] ?? '',
                        'proveedor_nac' => $s['proveedor_nac'] ?? '',
                        'desconsolidac' => '0',
                        'estado_costos' => $s['estado_costos'] ?? 'pendiente',
                        'solicitado_por' => $s['solicitado_por'] ?? null,
                        'completado_por' => $s['completado_por'] ?? null,
                        'fecha_solicitado' => $s['fecha_solicitado'] ?? null,
                        'fecha_completado' => $s['fecha_completado'] ?? null,
                        'revisado_por' => $s['revisado_por'] ?? null,
                        'fecha_revisado' => $s['fecha_revisado'] ?? null,
                        'nota_srvc' => $s['nota_srvc'] ?? '',
                        // === Campos nuevos de gastos locales ===
                        'cgld_usd' => (float)($s['cgld_usd'] ?? 0),
                        'cgld_eur' => (float)($s['cgld_eur'] ?? 0),
                        'cgld_clp' => (float)($s['cgld_clp'] ?? 0),
                        'vgld_usd' => (float)($s['vgld_usd'] ?? 0),
                        'vgld_eur' => (float)($s['vgld_eur'] ?? 0),
                        'vgld_clp' => (float)($s['vgld_clp'] ?? 0),
                        'pgld_usd' => (float)($s['pgld_usd'] ?? 0),
                        'pgld_eur' => (float)($s['pgld_eur'] ?? 0),
                        'pgld_clp' => (float)($s['pgld_clp'] ?? 0),
                        'ppgld_usd' => (float)($s['ppgld_usd'] ?? 0),
                        'ppgld_eur' => (float)($s['ppgld_eur'] ?? 0),
                        'ppgld_clp' => (float)($s['ppgld_clp'] ?? 0),
                        // === Campo de fecha al final ===
                        'validez' => $s['validez'] ?? null
                    ];
                    // Validar fecha
                    $validez = $s['validez'] ?? null;
                    if ($validez === '' || $validez === '0000-00-00') {
                        $validez = null;
                    }
                    // Verificar si es una fecha válida
                    if ($validez && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $validez)) {
                        $validez = null;
                    }

                    // Agregar al array
                    $servicio_data['validez'] = $validez;

                    // === Construir consulta dinámica ===
                    $columns = implode(', ', array_keys($servicio_data));
                    $placeholders = ':' . implode(', :', array_keys($servicio_data));

                    // Depuración: ver los valores que se envían
                    error_log("DEBUG servicio_data: " . json_encode($servicio_data));

                    $stmt = $pdo->prepare("INSERT INTO servicios ($columns) VALUES ($placeholders)");
                    $stmt->execute($servicio_data);

                    // --- Insertar Costos ---
                    $costos = $s['costos'] ?? [];
                    foreach ($costos as $c) {
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

                    // --- Insertar Gastos Locales ---
                    $gastos_locales = $s['gastos_locales'] ?? [];
                    foreach ($gastos_locales as $g) {
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
                }
            }
        }

        $pdo->commit();

        $mensaje_exito = $modo_update
            ? 'Prospecto actualizado correctamente'
            : 'Prospecto creado correctamente';

        // ✅ Obtener el estado actual para la redirección
        $estado_actual = $_POST['estado'] ?? 'Pendiente';

        // ✅ Redirigir con id_ppl Y estado_guardado
        header("Location: ?page=prospectos&id_ppl=" . urlencode($id_ppl) . "&estado_guardado=" . urlencode($estado_actual));
        exit;

    } catch (Exception $e) {
        $pdo->rollback();
        $mensajeUsuario = "Error al guardar el prospecto: " . $e->getMessage();
        $redirect_url = $_SERVER['PHP_SELF'] . "?page=prospectos&error=" . urlencode($mensajeUsuario);
        header("Location: " . $redirect_url);
        exit;
    }
}
?>