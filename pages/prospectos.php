
<?php
    define('DEVELOPMENT', true);
    file_put_contents('php://stderr', "MÉTODO: " . $_SERVER['REQUEST_METHOD'] . "\n");
    file_put_contents('php://stderr', "servicios_json: " . ($_POST['servicios_json'] ?? 'VACÍO') . "\n");
    require_once __DIR__ . '/../config.php';
    require_once './includes/auth_check.php';
    // === Solo procesar si hay POST y tiene datos mínimos ===
    if ($_POST && isset($_POST['modo'])) {
        // Determinar modo: 'prospecto' (solo prospecto) o 'servicios' (prospecto + servicios)
        $modo = $_POST['modo'] ?? 'prospecto';
        file_put_contents('php://stderr', "🔍 [PHP] Modo de guardado: " . $modo . "\n");
        file_put_contents('php://stderr', "📦 [PHP] servicios_json: " . ($_POST['servicios_json'] ?? 'VACÍO') . "\n");

        try {
            $pdo->beginTransaction();
            $id_ppl = (int)($_POST['id_ppl'] ?? 0);
            $tipo_oper = $_POST['tipo_oper'] ?? '';
            $modo_update = ($id_ppl > 0);
            $servicios_existentes = false;

            if ($modo_update) {
                // Verificar si ya tiene servicios
                $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM servicios WHERE id_prospect = ?");
                $stmt_check->execute([$id_ppl]);
                $servicios_existentes = $stmt_check->fetchColumn() > 0;

                // Si ya tenía servicios, mantener su tipo_oper original
                if ($servicios_existentes) {
                    $stmt_orig = $pdo->prepare("SELECT tipo_oper FROM prospectos WHERE id_ppl = ?");
                    $stmt_orig->execute([$id_ppl]);
                    $tipo_oper_original = $stmt_orig->fetchColumn();
                    $tipo_oper = $tipo_oper_original ?: $tipo_oper;
                }

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

            // Extraer solo letras y convertir a mayúsculas
            $op_clean = preg_replace('/[^a-zA-Z]/', '', $operacion);
            $tipo_clean = preg_replace('/[^a-zA-Z]/', '', $tipo_oper);

            // Tomar primeras 2 letras de operación y primeras 4 de tipo_oper
            $op_abrev = strtoupper(substr($op_clean, 0, 2));
            $tipo_abrev = strtoupper(substr($tipo_clean, 0, 4));

            // Valores por defecto si están vacíos
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

            // === Solo procesar servicios si el modo es 'servicios' ===
            if ($modo === 'servicios') {
                file_put_contents('php://stderr', "🗑️ [PHP] Eliminando servicios anteriores para id_ppl: " . $id_ppl . "\n");
                $pdo->prepare("DELETE FROM servicios WHERE id_prospect = ?")->execute([$id_ppl]);
                $servicios_json = $_POST['servicios_json'] ?? '';
                $total_costo = 0;
                $total_venta = 0;
                $total_costogasto = 0;
                $total_ventagasto = 0;
                if ($servicios_json) {
                    file_put_contents('php://stderr', "📦 [PHP] Procesando servicios_json: " . $servicios_json . "\n");
                    $servicios_data = json_decode($servicios_json, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new Exception('Error al decodificar servicios JSON: ' . json_last_error_msg());
                    }
                    if (!is_array($servicios_data)) {
                        throw new Exception('Formato inválido en servicios JSON');
                    }
                    file_put_contents('php://stderr', "✅ [PHP] Número de servicios a insertar: " . count($servicios_data) . "\n");
                    $stmt_serv = $pdo->prepare("INSERT INTO servicios (
                        id_srvc, id_prospect, servicio, nombre_corto, tipo, trafico, sub_trafico,
                        base_calculo, moneda, tarifa, iva, estado, costo, venta,
                        costogastoslocalesdestino, ventasgastoslocalesdestino, desconsolidac,
                        commodity, origen, pais_origen, destino, pais_destino, transito, frecuencia,
                        lugar_carga, sector, mercancia, bultos, peso, volumen, dimensiones,
                        agente, aol, aod, aerolinea, naviera, terrestre, ref_cliente, proveedor_nac, tipo_cambio,
                        ciudad, pais, direc_serv
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    foreach ($servicios_data as $i => $s) {
                        file_put_contents('php://stderr', "🔧 [PHP] Procesando servicio #" . $i . ": " . json_encode($s) . "\n");
                        // === LOGGING DE COSTOS ===
                        file_put_contents('php://stderr', "🔍 [PHP] Servicio #" . $i . " costos: " . json_encode($s['costos'] ?? []) . "\n");

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

                        // === Insertar costos asociados ===
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
                        // === Insertar gastos locales asociados ===
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
                    file_put_contents('php://stderr', "✅ [PHP] Totales actualizados para id_ppl: " . $id_ppl . "\n");
                }
            }

            $pdo->commit();
            header("Location: " . $_SERVER['PHP_SELF'] . "?exito=1&concatenado=" . urlencode($concatenado) . "&id_ppl=" . $id_ppl);
            exit;

        } catch (Exception $e) {
            try { $pdo->rollback(); } catch (Exception $ex) {}
            $mensajeUsuario = "Error al guardar el prospecto.";
            if (defined('DEVELOPMENT') && DEVELOPMENT) {
                $mensajeUsuario = $e->getMessage();
            }
            header("Location: " . $_SERVER['PHP_SELF'] . "?error=" . urlencode($mensajeUsuario));
            exit;
        }
    }
    /// Obtener datos auxiliares
    $comerciales = $pdo->query("SELECT * FROM comerciales")->fetchAll();
    $traficos = $pdo->query("SELECT * FROM trafico")->fetchAll();
    $incoterms = $pdo->query("SELECT * FROM incoterm")->fetchAll();
?>

<!-- Mini consola de depuración -->
<div id="debug-trace" style="margin: 1rem; padding: 0.5rem; background: #f0f8ff; border: 1px solid #87ceeb; border-radius: 4px; font-size: 0.85rem; display: none;"></div>

<!-- ========   Para búsqueda inteligente  ==== -->
<!-- Espacio entre menú y búsqueda inteligente -->
<div style="height: 4rem;"></div>
<div style="margin: 1rem 0;">
    <label><i class="fas fa-search"></i> Búsqueda Inteligente v2.1</label>
    <input 
        type="text" 
        id="busqueda-inteligente" 
        placeholder="Buscar por Concatenado, Razón Social, RUT o Comercial..."
        style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px;"
    />
    <div id="resultados-busqueda" style="position: absolute; background: white; border: 1px solid #ddd; border-radius: 8px; max-height: 300px; overflow-y: auto; width: 95%; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.1); display: none;"></div>
</div>

<!-- ========                                    INICIO FORM              =================== --->
<!-- ========   SECCIÓN PROSPECTO   ======= -->
<form method="POST" id="form-prospecto" action="">
    <!-- Mini consola de depuración -->
    <div id="debug-trace" style="margin: 1rem; padding: 0.5rem; background: #f0f8ff; border: 1px solid #87ceeb; border-radius: 4px; font-size: 0.85rem; display: none;"></div>
    
    <!-- Campos ocultos -->
    <input type="hidden" name="id_ppl" id="id_ppl" />
    <input type="hidden" name="id_prospect" id="id_prospect" />
    
    <!-- Sección Prospecto -->
    <div class="card" style="margin-bottom: 2rem;">
        <h3 style="margin:0 0 1rem 0; color:#3a4f63; font-size:1.1rem;"><i class="fas fa-user"></i> Datos del Prospecto</h3>

        <!-- Fila 1: RUT, Razón Social, Teléfono, Fecha Alta -->
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">RUT Empresa *</label>
            <input type="text" name="rut_empresa" required style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" />

            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Razón Social *</label>
            <input type="text" name="razon_social" required style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" />

            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Teléfono</label>
            <input type="tel" name="fono_empresa" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" />

            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Fecha</label>
            <input type="date" name="fecha_alta" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" value="<?= date('Y-m-d') ?>" />
        </div>

        <!-- Fila 2: País, Dirección, Estado -->
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <!-- País -->
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">País</label>
            <select name="pais" id="pais" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
                <option value="">Seleccionar país</option>
                <!-- Llenado por JS -->
            </select>

            <!-- Dirección -->
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Dirección</label>
            <input type="text" name="direccion" id="direccion" 
                style="grid-column: span 3; width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" />

            <!-- Estado -->
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Estado</label>
            <select name="estado" id="estado" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
                <option value="Pendiente">Pendiente</option>
                <option value="Enviado">Enviado</option>
                <option value="Devuelto_pendiente">Devuelto_pendiente</option>
                <option value="CerradoOK">CerradoOK</option>
                <option value="Rechazado">Rechazado</option>
            </select>
        </div>

        <!-- Fila 3: Operación, Tipo Operación, Concatenado -->
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Operación</label>
            <select name="operacion" id="operacion" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" required>
                <option value="">Seleccionar</option>
            </select>

            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Tipo Operación</label>
            <select name="tipo_oper" id="tipo_oper" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" required>
                <option value="">Seleccionar</option>
            </select>

            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Concatenado</label>
            <input type="text" name="concatenado" id="concatenado" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; font-weight: bold; box-sizing: border-box;" readonly />

        </div>

        <!-- Fila 4: Booking, Comercial ID, Nombre, Incoterm + Botón Eliminar Prospecto -->
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <!-- ... los 6 campos anteriores (Booking, Comercial ID, Nombre, Incoterm) ... -->
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Booking</label>
            <input type="text" name="booking" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" />
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Comercial ID</label>
            <input type="number" name="id_comercial" id="id_comercial" min="1" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" />
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Nombre</label>
            <input type="text" name="nombre" id="nombre" readonly style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; background: #f8f9fa; box-sizing: border-box;" />
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Incoterm</label>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <input type="text" name="incoterm" style="flex: 1; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" />
                <button type="button" id="btn-eliminar-prospecto" class="btn-delete" 
                        style="background: #dc3545; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 0.85rem; display: none;"
                        onclick="eliminarProspecto()">
                    🗑️ Eliminar
                </button>
            </div>
        </div>

        <!-- Espacio entre Prospecto y Servicios -->
        <div style="height: 1rem;"></div>
        <button type="button" id="btn-eliminar-prospecto" class="btn-delete" 
                style="background: #dc3545; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 0.85rem; display: none;"
                onclick="eliminarProspecto()">
            🗑️
        </button>
    </div>

    <!-- TABLA SERVICIOS -->
    <div class="card">
        <h3><i class="fas fa-truck"></i> <i class="fas fa-plane"></i> <i class="fas fa-ship"></i> Servicios Asociados</h3>
        <!-- Botones de acción + Volver + Grabar Todo -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem;">
            <div style="display: flex; gap: 1rem;">
                <button type="button" class="btn-add" style="font-size: 0.8rem;" onclick="abrirModalServicio()" id="btn-agregar-servicio" disabled>
                    <i class="fas fa-plus"></i> Agregar
                </button>
                <button type="button" class="btn-comment" onclick="abrirModalComercial()" style="font-size: 0.8rem;"><i class="fas fa-comments"></i> Comerciales</button>
                <button type="button" class="btn-comment" onclick="abrirModalOperaciones()" style="font-size: 0.8rem;"><i class="fas fa-clipboard-list"></i> Operaciones</button>
                <button type="button" id="btn-volver" class="btn-secondary" 
                        style="font-size: 0.8rem; background-color: #6c757d; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; display: none;">
                    <i class="fas fa-undo"></i> Volver
                </button>
            </div>
            <!-- Botón Grabar Todo -----  Reemplazar el contenedor actual por este -->
            <div id="contenedor-boton-prospecto" style="display: flex;">
                <button type="button" class="btn-primary" id="btn-save-all" style="min-width: 120px; padding: 0.6rem 1rem;">
                    <!-- Texto gestionado por JS -->
                </button>
            </div>
        </div>

        <!-- Modal de confirmación personalizado -->
        <div id="modal-confirm" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10000;">
            <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:1.5rem; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.2); text-align:center; max-width:400px;">
                <p style="margin:0 0 1.2rem 0; font-size:1rem;">¿Desea volver sin guardar los cambios?</p>
                <div style="display:flex; gap:0.8rem; justify-content:center;">
                    <button type="button" id="btn-confirm-no" style="padding:0.5rem 1.2rem; background:#6c757d; color:white; border:none; border-radius:4px; cursor:pointer;">Cancelar</button>
                    <button type="button" id="btn-confirm-yes" style="padding:0.5rem 1.2rem; background:#009966; color:white; border:none; border-radius:4px; cursor:pointer;">Aceptar</button>
                </div>
            </div>
        </div>
        <div class="table-container">
            <table id="tabla-servicios">
                <thead>
                    <tr>
                        <th style="width: 25%;">Servicio</th>
                        <th>Tráfico</th>
                        <th>Base Cálculo</th>
                        <th>Moneda</th>
                        <th>Tarifa</th>
                        <th>Costo</th>
                        <th>Venta</th>
                        <th>GDC</th>
                        <th>GDV</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="servicios-body"></tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="5" style="text-align: right; font-weight: bold;">Totales:</td>
                        <td id="total-costo">0.00</td>
                        <td id="total-venta">0.00</td>
                        <td id="total-costogasto">0.00</td>
                        <td id="total-ventagasto">0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <!-- Modal: Servicio -->
    <div id="modal-servicio" class="modal">
        <div class="modal-content" style="max-width: 1500px; width: 95%;">
            <h3>
                <i class="fas fa-box"></i> Agregar Servicio para 
                <span style="color: #007bff; font-weight: bold;" id="serv_titulo_concatenado">-</span>
            </h3>
            <span class="close" onclick="cerrarModalServicio()" style="cursor:pointer;">&times;</span>

            <!-- Campos ocultos -->
            <input type="hidden" id="id_prospect_serv" name="id_prospect_serv" />
            <input type="hidden" id="concatenado_serv" name="concatenado_serv" />
            <input type="hidden" id="id_srvc_actual" />

            <!-- Formulario en grid de 8 columnas -->
            <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 0.8rem; margin-top: 1.2rem; align-items: center;">
                <!-- Fila 1 -->
                <label style="font-size: 0.9rem;">Servicio</label>
                <input type="text" id="serv_servicio" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label style="font-size: 0.9rem;">Medio Transporte</label>
                <select id="serv_medio_transporte" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                    <option value="">Seleccionar</option>
                </select>
                <label style="font-size: 0.9rem;">Commodity</label>
                <select id="serv_commodity" style="grid-column: span 3; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                    <option value="">Seleccionar</option>
                </select>

                <!-- Fila 2 -->
                <label style="font-size: 0.9rem;">Origen</label>
                <select id="serv_origen" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                    <option value="">Seleccionar</option>
                </select>
                <label style="font-size: 0.9rem;">País Origen</label>
                <input type="text" id="serv_pais_origen" readonly style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; background: #f9f9f9;" />
                <label style="font-size: 0.9rem;">Destino</label>
                <select id="serv_destino" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                    <option value="">Seleccionar</option>
                </select>
                <label style="font-size: 0.9rem;">País Destino</label>
                <input type="text" id="serv_pais_destino" readonly style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; background: #f9f9f9;" />

                <!-- Fila 3 -->
                <label style="font-size: 0.9rem;">Tránsito</label>
                <input type="text" id="serv_transito" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label style="font-size: 0.9rem;">Frecuencia</label>
                <input type="text" id="serv_frecuencia" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label style="font-size: 0.9rem;">Lugar Carga</label>
                <input type="text" id="serv_lugar_carga" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label style="font-size: 0.9rem;">Sector</label>
                <input type="text" id="serv_sector" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />

                <!-- Fila 4 -->
                <label style="font-size: 0.9rem;">Mercancía</label>
                <input type="text" id="serv_mercancia" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label style="font-size: 0.9rem;">Bultos</label>
                <input type="number" id="serv_bultos" min="1" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label style="font-size: 0.9rem;">Peso (kg)</label>
                <input type="number" id="serv_peso" step="0.01" min="0" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label style="font-size: 0.9rem;">Volumen (m³)</label>
                <input type="number" id="serv_volumen" step="0.01" min="0" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />

                <!-- Fila 5 -->
                <label style="font-size: 0.9rem;">Dimensiones</label>
                <input type="text" id="serv_dimensiones" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" placeholder="Ej: 120x80x90 cm" />

                <label style="font-size: 0.9rem;">Moneda</label>
                <select id="serv_moneda" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="CLP">CLP</option>
                </select>

                <label style="font-size: 0.9rem;">Tipo Cambio</label>
                <input type="number" id="serv_tipo_cambio" step="0.01" min="0" value="1" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />

                <label style="font-size: 0.9rem;">Proveedor Nac</label>
                <select id="serv_proveedor_nac" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                    <option value="">Seleccionar</option>
                    <!-- Se llenará desde API -->
                </select>

                <!-- Fila 6 -->
                <label style="font-size: 0.9rem;">AOL</label>
                <input type="text" id="serv_aol" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" maxlength="4" />

                <label style="font-size: 0.9rem;">AOD</label>
                <input type="text" id="serv_aod" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" maxlength="4" />

                <label style="font-size: 0.9rem;">Desconsolidación</label>
                <input type="text" id="serv_desconsolidacion" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />

                <label style="font-size: 0.9rem;">Agente</label>
                <select id="serv_agente" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                    <option value="">Seleccionar</option>
                    <!-- Se llenará desde API -->
                </select>

                <!-- Fila 7 -->
                <label style="font-size: 0.9rem;">Aerolínea</label>
                <input type="text" id="serv_aerolinea" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />

                <label style="font-size: 0.9rem;">Terrestre</label>
                <input type="text" id="serv_terrestre" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />

                <label style="font-size: 0.9rem;">Marítimo</label>
                <input type="text" id="serv_maritimo" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />

                <label style="font-size: 0.9rem;">Ref. Cliente</label>
                <input type="text" id="serv_ref_cliente" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
            </div>

            <!-- Botones de acción del modal Servicio -->
            <div class="modal-footer" style="text-align: right; margin-top: 1.5rem; gap: 0.8rem;">
                <!-- Dentro del Modal Servicio, en la sección de botones -->
                <button type="button" class="btn-comment" id="btn-costos-servicio"
                        style="background: #231b92ff; color: white; font-size: 0.8rem; margin-top: 1rem; 
                            cursor: pointer !important; 
                            pointer-events: auto !important; 
                            position: relative; 
                            z-index: 2;">
                    <i class="fas fa-calculator"></i> Costos - Ventas
                </button>
                <!-- Dentro del Modal Gastos Locales, en la sección de botones -->
                <button type="button" class="btn-comment" id="btn-gastos-locales"
                        style="background: #8a2be2; color: white; font-size: 0.8rem; margin-top: 1rem; 
                            cursor: pointer !important; pointer-events: auto !important;">
                    <i class="fas fa-file-invoice-dollar"></i> Gastos Locales
                </button>
                <button type="button" onclick="cerrarModalServicioConConfirmacion()" style="background: #6c757d; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; cursor: pointer;">
                    Volver
                </button>
                <button type="button" onclick="guardarServicio()" style="background: #009966; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; cursor: pointer;">
                    Agregar Servicio
                </button>
            </div>
        </div>
    </div>

    <!-- Servicios en formato JSON -->
    <input type="hidden" name="servicios_json" id="servicios_json" />

    <!-- ===============================     CIERRE DEL FORM ======================= --->
</form>

<!-- Modal: Resultados de Búsqueda -->
<div id="modal-resultados" class="modal">
    <div class="modal-content" style="max-width:800px;position:relative;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h3><i class="fas fa-search"></i> Resultados de Búsqueda</h3>
            <button onclick="cerrarModalResultados()" style="background:#ccc;border:none;padding:0.5rem 1rem;border-radius:6px;cursor:pointer;">Volver</button>
        </div>
        <div class="table-container">
            <table id="tabla-resultados">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Razón Social</th>
                        <th>RUT</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="resultados-body"></tbody>
            </table>
        </div>
    </div>
</div>
<!-- Modal: Notas Comerciales -->
<div id="modal-comercial" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <h3><i class="fas fa-comments"></i> Notas Comerciales</h3>
        <span class="close" onclick="cerrarModalComercial()" style="cursor:pointer;">&times;</span>
        <textarea id="notas_comerciales_input" rows="6" placeholder="Escribe tus comentarios comerciales..." 
                  style="width:100%; padding: 0.8rem; margin: 1rem 0; border-radius: 6px; border: 1px solid #ccc; font-size: 0.9rem;"></textarea>
        <div class="modal-footer" style="text-align: right; gap: 0.5rem;">
            <button type="button" onclick="cerrarModalComercial()" style="background:#6c757d;">Cerrar</button>
            <button type="button" onclick="guardarNotasComerciales()" style="background:#009966;">Guardar</button>
        </div>
    </div>
</div>
<!-- Modal: Notas Operaciones -->
<div id="modal-operaciones" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <h3><i class="fas fa-clipboard-list"></i> Notas Operaciones</h3>
        <span class="close" onclick="cerrarModalOperaciones()" style="cursor:pointer;">&times;</span>
        <textarea id="notas_operaciones_input" rows="6" placeholder="Escribe tus comentarios de operaciones..." 
                  style="width:100%; padding: 0.8rem; margin: 1rem 0; border-radius: 6px; border: 1px solid #ccc; font-size: 0.9rem;"></textarea>
        <div class="modal-footer" style="text-align: right; gap: 0.5rem;">
            <button type="button" onclick="cerrarModalOperaciones()" style="background:#6c757d;">Cerrar</button>
            <button type="button" onclick="guardarNotasOperaciones()" style="background:#009966;">Guardar</button>
        </div>
    </div>
</div>
<!-- Modal de confirmación personalizado -->
<div id="modal-confirm" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10000;">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:1.5rem; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.2); text-align:center; max-width:400px;">
        <p style="margin:0 0 1.2rem 0; font-size:1rem;">¿Desea volver sin guardar los cambios?</p>
        <div style="display:flex; gap:0.8rem; justify-content:center;">
            <button type="button" id="btn-confirm-no" style="padding:0.5rem 1.2rem; background:#6c757d; color:white; border:none; border-radius:4px; cursor:pointer;">Cancelar</button>
            <button type="button" id="btn-confirm-yes" style="padding:0.5rem 1.2rem; background:#009966; color:white; border:none; border-radius:4px; cursor:pointer;">Aceptar</button>
        </div>
    </div>
</div>

<!-- ========================    Submodal: Costos/Ventas/Gastos    ============================================= -->
<!-- Submodal: Costos/Ventas/Gastos -->
<div id="submodal-costos" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:11000;">
    <div class="modal-content" style="max-width: 1400px; width: 95%; margin: 1.5rem auto; background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
        <h3><i class="fas fa-calculator"></i> Costos, Ventas y Gastos</h3>
        <span class="close" onclick="cerrarSubmodalCostos()" style="cursor:pointer; float:right; font-size:1.8rem; margin-top:-5px;">&times;</span>

        <!-- Formulario de entrada -->
        <div style="display: grid; grid-template-columns: repeat(10, 1fr); gap: 0.7rem; margin: 1.2rem 0; align-items: center; background: #f8f9fa; padding: 1rem; border-radius: 6px;">
            <select id="costo_concepto" style="grid-column: span 2; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; width: 100%;">
                <option value="">Seleccionar concepto</option>
                <!-- Se llenará desde API -->
            </select>
            <input type="text" id="costo_moneda" readonly style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; background: #e9ecef; text-align: center; width: 80px;" />
            <input type="number" id="costo_qty" step="0.01" min="0" placeholder="Qty" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; text-align: right; width: 80px;" />
            <input type="number" id="costo_costo" step="0.01" min="0" placeholder="Costo" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; background-color: #fff9db; text-align: right; width: 80px;" />
            <input type="text" id="costo_total_costo" readonly placeholder="Total Costo" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; background-color: #fff9db; text-align: right; width: 80px;" />
            <input type="number" id="costo_tarifa" step="0.01" min="0" placeholder="Tarifa" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; background-color: #e6f7ff; text-align: right; width: 80px;" />
            <input type="text" id="costo_total_tarifa" readonly placeholder="Total Tarifa" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; background-color: #e6f7ff; text-align: right; width: 80px;" />
            <select id="costo_aplica" style="grid-column: span 2; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; width: 100%;">
                <option value="">Seleccionar aplica</option>
                <!-- Se llenará según medio_transporte -->
            </select>
            <button type="button" onclick="guardarCosto()" style="grid-column: span 1; background: #009966; color: white; border: none; padding: 0.6rem; border-radius: 6px; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 0.3rem;">
                <i class="fas fa-plus"></i> Agregar
            </button>
        </div>

        <!-- Tabla de costos -->
        <div class="table-container" style="margin-top: 1.2rem; overflow-x: auto;">
            <table id="tabla-costos" style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
                <thead>
                    <tr style="background: #f1f3f5;">
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; font-size: 0.92rem;">Concepto</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; font-size: 0.92rem;">Moneda</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; font-size: 0.92rem;">Qty</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; background-color: #fff9db; font-size: 0.92rem;">Costo</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; background-color: #fff9db; font-size: 0.92rem;">Total Costo</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; background-color: #e6f7ff; font-size: 0.92rem;">Tarifa</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; background-color: #e6f7ff; font-size: 0.92rem;">Total Tarifa</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; font-size: 0.92rem;">Aplica</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; font-size: 0.92rem;">Acción</th>
                    </tr>
                </thead>
                <tbody id="costos-body"></tbody>
                <tfoot>
                    <tr style="font-weight: normal; background: #f9fafcff;">
                        <td colspan="4" style="padding: 0.6rem; text-align: right; border: 1px solid #ddd;">TOTAL COSTO:</td>
                        <td id="total-costo-costos" style="padding: 0.6rem; text-align: right; border: 1px solid #ddd; background-color: #fff9db;">0.00</td>
                        <td style="padding: 0.6rem; text-align: right; border: 1px solid #ddd;">TOTAL TARIFA:</td>
                        <td id="total-tarifa-costos" style="padding: 0.6rem; text-align: right; border: 1px solid #ddd; background-color: #e6f7ff;">0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Botón Volver -->
        <div style="text-align: right; margin-top: 1.5rem;">
            <button type="button" onclick="cerrarSubmodalCostos()" style="background: #6c757d; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; font-size: 0.95rem;">
                <i class="fas fa-arrow-left"></i> Volver
            </button>
        </div>
    </div>
</div>

<!-- ======================================================================================================= -->
<!-- Submodal: Gastos Locales -->
<div id="submodal-gastos-locales" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:11000;">
    <div class="modal-content" style="max-width: 1400px; width: 95%; margin: 1.5rem auto; background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
        <h3><i class="fas fa-file-invoice-dollar"></i> Gastos Locales</h3>
        <span class="close" onclick="cerrarSubmodalGastosLocales()" style="cursor:pointer; float:right; font-size:1.8rem; margin-top:-5px;">&times;</span>
        
        <!-- Formulario de entrada -->
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 0.7rem; margin: 1.2rem 0; align-items: center; background: #f8f9fa; padding: 1rem; border-radius: 6px;">
            <select id="gasto_tipo" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem;">
                <option value="">Tipo</option>
                <option value="Costo">Costo</option>
                <option value="Ventas">Ventas</option>
            </select>
            <select id="gasto_gasto" style="grid-column: span 2; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem;">
                <option value="">Gastos</option>
                <!-- Se llenará dinámicamente -->
            </select>
            <select id="gasto_moneda" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem;">
                <option value="USD">USD</option>
                <option value="CLP">CLP</option>
                <option value="EUR">EUR</option>
            </select>
            <input type="number" id="gasto_monto" step="0.01" min="0" placeholder="Monto" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; text-align: right;" />
            <select id="gasto_afecto" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem;">
                <option value="SI">SI</option>
                <option value="NO">NO</option>
            </select>
            <input type="number" id="gasto_iva" step="0.01" min="0" placeholder="IVA %" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; text-align: right;" />
            <button type="button" onclick="guardarGastoLocal()" style="grid-column: span 1; background: #009966; color: white; border: none; padding: 0.6rem; border-radius: 6px; font-size: 0.9rem;">
                <i class="fas fa-plus"></i> Agregar
            </button>
        </div>

        <!-- Tabla de gastos locales -->
        <div class="table-container" style="margin-top: 1.2rem; overflow-x: auto;">
            <table id="tabla-gastos-locales" style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
                <thead>
                    <tr style="background: #f1f3f5;">
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd;">Tipo</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd;">Gastos</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd;">Moneda</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd;">Monto</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd;">Afecto</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd;">IVA %</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd;">Acción</th>
                    </tr>
                </thead>
                <tbody id="gastos-locales-body"></tbody>
            </table>
        </div>

        <!-- Cuadro totalizador -->
        <div style="display: grid; grid-template-columns: repeat(4, max-content); gap: 1.5rem 2rem; margin: 1.5rem 0; padding: 1rem; background: #f8f9fa; border-radius: 6px; justify-content: start; align-items: center;">
            <div><strong>TOTAL VENTA:</strong></div>
            <div id="total-venta-gastos" style="font-weight: bold; text-align: right; min-width: 80px;">0.00</div>
            
            <div><strong>TOTAL COSTO:</strong></div>
            <div id="total-costo-gastos" style="font-weight: bold; text-align: right; min-width: 80px;">0.00</div>
            
            <div><strong>PROFIT LOCAL:</strong></div>
            <div id="profit-local" style="font-weight: bold; text-align: right; min-width: 80px;">0.00</div>
            
            <div><strong>PROFIT %:</strong></div>
            <div id="profit-porcentaje" style="font-weight: bold; text-align: right; min-width: 80px;">0.00 %</div>
        </div>

        <!-- Botones de acción -->
        <div style="text-align: right; margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 0.8rem;">
            <button type="button" onclick="cerrarSubmodalGastosLocales()" style="background: #6c757d; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; font-size: 0.95rem;">
                <i class="fas fa-arrow-left"></i> Volver
            </button>
        </div>
    </div>
</div>
    
<!-- Botón de eliminación si rol no es admin  -->
<td>
    <?php
        $esAdmin = false;
        if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
            $esAdmin = true;
        }
    ?>
</td>
<!-- === VARIABLE GLOBAL DE ROL (única vez, antes del script principal) === -->
<?php
$rol_usuario = 'comercial'; // valor por defecto
if (isset($_SESSION['rol'])) {
    $rol_usuario = $_SESSION['rol'];
}
?>
<script>
    const USER_ROLE = '<?php echo htmlspecialchars($rol_usuario); ?>';
    console.log('✅ Rol cargado:', USER_ROLE);
</script>

<!-- ******************************************************************************************** -->
<!-- *************************************      SCRIPT     ************************************** -->
<script>
    // Variables globales para el modal de servicios
    let servicioData = null;
    // === VARIABLES GLOBALES ===
    let searchInput, btnBuscar, mensaje, btnNuevo;
    let servicios = [];
    let modoEdicion = false;
    let estadoProspecto = 'Pendiente'; // valor por defecto
    
    // === variables submodal Servicios ===
    let tieneServicios = false;
    let costosServicio = []; // Array de costos para el servicio actual
    let idServicioActual = null; // id_srvc del servicio en edición
    let tieneServiciosIniciales = false; // para saber si ya venía con servicios
    let servicioEnEdicion = null; // Variable global para saber si estamos editando

    // === variables submodal Gastos Locales ===
    let gastosLocales = []; // Array de gastos locales para el servicio actual

    // === Cargar gastos locales según el tipo seleccionado ===
    function cargarGastosPorTipo() {
        const tipo = document.getElementById('gasto_tipo').value;
        const selectGasto = document.getElementById('gasto_gasto');
        if (!tipo) {
            selectGasto.innerHTML = '<option value="">Seleccione tipo primero</option>';
            return;
        }
        fetch(`api/get_gastos_locales.php?tipo=${encodeURIComponent(tipo)}`)
            .then(res => res.json())
            .then(data => {
                selectGasto.innerHTML = '<option value="">Seleccionar gasto</option>';
                (data.gastos || []).forEach(gasto => {
                    const opt = document.createElement('option');
                    opt.value = gasto;
                    opt.textContent = gasto;
                    selectGasto.appendChild(opt);
                });
            })
            .catch(err => {
                console.error('Error al cargar gastos locales:', err);
                error('⚠️ No se pudieron cargar los gastos');
            });
    }

    function abrirSubmodalGastosLocales() {
        const modalServicio = document.getElementById('modal-servicio');
        if (!modalServicio || modalServicio.style.display === 'none') {
            error('❌ Abra primero el modal de Servicio');
            return;
        }
        // ✅ Cargar desde el servicio en memoria
        if (servicioEnEdicion !== null && servicios[servicioEnEdicion]) {
            gastosLocales = Array.isArray(servicios[servicioEnEdicion].gastos_locales) 
                ? [...servicios[servicioEnEdicion].gastos_locales] 
                : [];
        } else {
            gastosLocales = [];
        }
        const monedaServicio = document.getElementById('serv_moneda')?.value || 'USD';
        document.getElementById('gasto_moneda').value = monedaServicio;
        cargarGastosPorTipo(); // Carga inicial con "Costo" por defecto o vacío
        actualizarTablaGastosLocales();
        document.getElementById('submodal-gastos-locales').style.display = 'block';
    }

    function eliminarProspecto() {
        const idPpl = document.getElementById('id_ppl')?.value;
        if (!idPpl || idPpl === '0') {
            error('❌ No hay prospecto seleccionado para eliminar');
            return;
        }

        if (servicios && servicios.length > 0) {
            error('⚠️ No se puede eliminar el prospecto porque tiene servicios asociados.\n\nPrimero elimine todos los servicios.');
            return;
        }

        mostrarConfirmacion(
            '¿Está seguro de eliminar este prospecto?\nEsta acción no se puede deshacer.',
            () => {
                fetch('api/eliminar_prospecto.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_ppl: idPpl })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        exito('✅ Prospecto eliminado correctamente');
                        nuevoFormulario();
                        document.getElementById('btn-eliminar-prospecto').style.display = 'none';
                    } else {
                        error('❌ ' + (data.message || 'Error al eliminar el prospecto'));
                    }
                })
                .catch(err => {
                    console.error('Error al eliminar prospecto:', err);
                    error('❌ Error de conexión al eliminar el prospecto');
                });
            }
        );
    }

    // === Funciones de botón Volver ===
    function mostrarBotonVolver() {
        const btn = document.getElementById('btn-volver');
        if (btn) btn.style.display = 'inline-block';
    }
    function ocultarBotonVolver() {
        const btn = document.getElementById('btn-volver');
        if (btn) btn.style.display = 'none';
    }
    
    // Notificaciones
    function mostrarNotificacion(mensaje, tipo = 'info') {
        const toast = document.getElementById('toast');
        const msgElement = document.getElementById('toast-message');
        if (!toast || !msgElement) return;
        msgElement.textContent = mensaje;
        toast.className = 'toast';
        let icono = 'fa-info-circle';
        switch (tipo) {
            case 'exito': 
                toast.classList.add('success'); 
                icono = 'fa-check-circle'; 
                break;
            case 'error': 
                toast.classList.add('error'); 
                icono = 'fa-times-circle'; 
                break;
            case 'warning': 
                toast.classList.add('warning'); 
                icono = 'fa-exclamation-triangle'; 
                break;
            default: 
                toast.classList.add('info');
        }
        const iconElement = toast.querySelector('i');
        if (iconElement) iconElement.className = `fas ${icono}`;
        toast.classList.add('show');
        // === Tiempo para despliegue de notificaciones ====
        setTimeout(() => toast.classList.remove('show'), 5000);
    }
    const exito = (msg) => mostrarNotificacion(msg, 'exito');
    const error = (msg) => mostrarNotificacion(msg, 'error');
    const warning = (msg) => mostrarNotificacion(msg, 'warningi');
    const info = (msg) => mostrarNotificacion(msg, 'info');
    
    // === FUNCIÓN DE DIAGNÓSTICO ===
    function logTrace(mensaje, nivel = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        const msg = `[TRACE ${timestamp}] ${mensaje}`;
        console.log(msg);
        // Opcional: mostrar en pantalla
        const debugEl = document.getElementById('debug-trace');
        if (debugEl) {
            debugEl.style.display = 'block';
            debugEl.innerHTML += `<div style="color: ${nivel === 'error' ? 'red' : nivel === 'warning' ? 'orange' : 'black'};">${msg}</div>`;
        }
    }    

    // === GESTIÓN DEL BOTONES Y FUNCIONES DE FORMULARIO ===
    function habilitarBotonAgregar() {
        const idPpl = document.getElementById('id_ppl')?.value;
        const btn = document.getElementById('btn-agregar-servicio');
        if (btn) {
            btn.disabled = !idPpl || idPpl === '0';
        }
    }

    // Tabla de servicios
    function actualizarTabla() {
        const tbody = document.getElementById('servicios-body');
        if (!tbody) return;
        tbody.innerHTML = '';
        let total_costo = 0, total_venta = 0, total_costogasto = 0, total_ventagasto = 0;
        servicios.forEach((s, index) => {
            const costo = parseFloat(s.costo) || 0;
            const venta = parseFloat(s.venta) || 0;
            const costogasto = parseFloat(s.costogastoslocalesdestino) || 0;
            const ventagasto = parseFloat(s.ventasgastoslocalesdestino) || 0;
            const tarifa = parseFloat(s.tarifa) || 0;
            total_costo += costo;
            total_venta += venta;
            total_costogasto += costogasto;
            total_ventagasto += ventagasto;
            const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="padding: 0.5rem; text-align: left; border-bottom: 1px solid #eee;">${s.servicio}</td>
                    <td style="padding: 0.5rem; text-align: center; border-bottom: 1px solid #eee;">${s.trafico}</td>
                    <td style="padding: 0.5rem; text-align: right; border-bottom: 1px solid #eee;">${s.base_calculo || ''}</td>
                    <td style="padding: 0.5rem; text-align: center; border-bottom: 1px solid #eee;">${s.moneda}</td>
                    <td style="padding: 0.5rem; text-align: right; border-bottom: 1px solid #eee;">${(parseFloat(s.tarifa) || 0).toFixed(2)}</td>
                    <td style="padding: 0.5rem; text-align: right; border-bottom: 1px solid #eee;">${costo.toFixed(2)}</td>
                    <td style="padding: 0.5rem; text-align: right; border-bottom: 1px solid #eee;">${venta.toFixed(2)}</td>
                    <td style="padding: 0.5rem; text-align: right; border-bottom: 1px solid #eee;">${costogasto.toFixed(2)}</td>
                    <td style="padding: 0.5rem; text-align: right; border-bottom: 1px solid #eee;">${ventagasto.toFixed(2)}</td>
                    <td style="padding: 0.5rem; text-align: center; border-bottom: 1px solid #eee;">
                        <button type="button" class="btn-edit" onclick="editarServicio(${index})" style="margin-right: 0.5rem;">✏️</button>
                        <button type="button" class="btn-delete" onclick="eliminarServicio(${index})">🗑️</button>
                    </td>
                `;
            tbody.appendChild(tr);
        });
        document.getElementById('total-costo').textContent = total_costo.toFixed(2);
        document.getElementById('total-venta').textContent = total_venta.toFixed(2);
        document.getElementById('total-costogasto').textContent = total_costogasto.toFixed(2);
        document.getElementById('total-ventagasto').textContent = total_ventagasto.toFixed(2);
    }

    // === ELIMINAR SERVICIOS ===
    function eliminarServicio(index) {
        const servicio = servicios[index];
        if (servicio && Array.isArray(servicio.costos) && servicio.costos.length > 0) {
            error('⚠️ No se puede eliminar el servicio porque tiene costos asociados.\n\nPrimero elimine todos los costos.');
            return;
        }

        mostrarConfirmacion('¿Eliminar servicio?', () => {
            servicios.splice(index, 1);
            actualizarTabla();
            exito('✅ Servicio eliminado');
            actualizarBotonEliminarProspecto();
        });
    }

     // === BOTÓN ELIMINAR OCULTA/MUESTRA  ===
    function actualizarBotonEliminarProspecto() {
        const btn = document.getElementById('btn-eliminar-prospecto');
        const idPpl = document.getElementById('id_ppl')?.value;
        const tieneServicios = servicios && servicios.length > 0;
        if (btn && idPpl && idPpl !== '0' && !tieneServicios) {
            btn.style.display = 'inline-block';
        } else {
            btn.style.display = 'none';
        }
    }

    // === MODALES COMERCIAL Y OPERACIONES ===
    function cerrarModalComercial() {
        document.getElementById('modal-comercial').style.display = 'none';
    }
    function abrirModalComercial() {
        const input = document.querySelector('input[name="notas_comerciales"]');
        const valorActual = input ? input.value : '';
        document.getElementById('notas_comerciales_input').value = valorActual;
        document.getElementById('modal-comercial').style.display = 'flex';
    }
    function cerrarModalOperaciones() {
        document.getElementById('modal-operaciones').style.display = 'none';
    }
    function abrirModalOperaciones() {
        const input = document.querySelector('input[name="notas_operaciones"]');
        const valorActual = input ? input.value : '';
        document.getElementById('notas_operaciones_input').value = valorActual;
        document.getElementById('modal-operaciones').style.display = 'flex';
    }

    function guardarNotasComerciales() {
        const idPpl = document.getElementById('id_ppl')?.value;
        if (!idPpl || idPpl === '0') {
            error('❌ No se puede guardar la nota: prospecto no válido');
            return;
        }
        const valor = document.getElementById('notas_comerciales_input').value.trim();
        fetch('api/guardar_nota.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_ppl: idPpl, campo: 'notas_comerciales', valor: valor })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                exito('✅ Notas comerciales guardadas');
                // Actualizar campo oculto
                let input = document.querySelector('input[name="notas_comerciales"]');
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'notas_comerciales';
                    document.getElementById('form-prospecto').appendChild(input);
                }
                input.value = valor;
            } else {
                error('❌ ' + (data.message || 'Error al guardar'));
            }
            cerrarModalComercial();
        })
        .catch(err => {
            console.error('Error al guardar nota:', err);
            error('❌ Error de conexión al guardar la nota');
        });
    }

    function guardarNotasOperaciones() {
        const idPpl = document.getElementById('id_ppl')?.value;
        if (!idPpl || idPpl === '0') {
            error('❌ No se puede guardar la nota: prospecto no válido');
            return;
        }
        const valor = document.getElementById('notas_operaciones_input').value.trim();
        fetch('api/guardar_nota.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_ppl: idPpl, campo: 'notas_operaciones', valor: valor })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                exito('✅ Notas de operaciones guardadas');
                // Actualizar campo oculto
                let input = document.querySelector('input[name="notas_operaciones"]');
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'notas_operaciones';
                    document.getElementById('form-prospecto').appendChild(input);
                }
                input.value = valor;
            } else {
                error('❌ ' + (data.message || 'Error al guardar'));
            }
            cerrarModalOperaciones();
        })
        .catch(err => {
            console.error('Error al guardar nota:', err);
            error('❌ Error de conexión al guardar la nota');
        });
    }

    // Editar servicio (no implementado en este fragmento)
    function editarServicio(index) {
        abrirModalServicio(index);
    }

    // Modales: Resultados y Servicio
    function abrirModalResultados() {
        document.getElementById('modal-resultados').style.display = 'flex';
    }
    function cerrarModalResultados() {
        document.getElementById('modal-resultados').style.display = 'none';
    }
    function mostrarResultados(resultados) {
        const tbody = document.getElementById('resultados-body');
        if (!tbody) return;
        tbody.innerHTML = '';
        resultados.forEach(p => {
            const tr = document.createElement('tr');
            const btn = document.createElement('button');
            btn.className = 'btn-select';
            btn.textContent = 'Seleccionar';

            btn.addEventListener('click', () => {
                if (p.id_ppl) {
                    seleccionarProspecto(p.id_ppl);
                } else {
                    console.error('❌ id_ppl no válido:', p);
                    error('❌ Prospecto sin ID');
                }
            });
            tr.innerHTML = `
                <td>${p.concatenado || ''}</td>
                <td>${p.razon_social || ''}</td>
                <td>${p.rut_empresa || ''}</td>
                <td>${p.estado || ''}</td>
                <td></td>
            `;
            tr.cells[4].appendChild(btn); // Añadir botón a la última celda
            tbody.appendChild(tr);
        });
    }

    // AbrirModal Servicio
    function abrirModalServicio(index = null) {
        const idPpl = document.getElementById('id_ppl').value;
        const concatInput = document.querySelector('input[name="concatenado"]');
        const concatenado = concatInput ? concatInput.value : '';
        if (!idPpl || !concatenado) {
            error('❌ Debe guardar el prospecto primero para generar el código de concatenado.');
            return;
        }

        // ✅ Asignar id_prospect desde el prospecto (no desde un campo oculto)
        const idProspect = document.getElementById('id_prospect')?.value || idPpl;

        // Reiniciar el modal
        document.getElementById('id_prospect_serv').value = idProspect;
        document.getElementById('concatenado_serv').value = concatenado;
        document.getElementById('serv_titulo_concatenado').textContent = concatenado;

        // Modo edición
        if (index !== null) {
            servicioEnEdicion = index;
            const s = servicios[index];

            // Inicializar costos del servicio
            costosServicio = Array.isArray(s.costos) ? [...s.costos] : [];

            // Función para rellenar los campos después de cargar los selects
            const rellenarCampos = () => {
                document.getElementById('serv_servicio').value = s.servicio || '';
                document.getElementById('serv_commodity').value = s.commodity || '';
                document.getElementById('serv_medio_transporte').value = s.trafico || '';
                document.getElementById('serv_origen').value = s.origen || '';
                document.getElementById('serv_pais_origen').value = s.pais_origen || '';
                document.getElementById('serv_destino').value = s.destino || '';
                document.getElementById('serv_pais_destino').value = s.pais_destino || '';
                document.getElementById('serv_transito').value = s.transito || '';
                document.getElementById('serv_frecuencia').value = s.frecuencia || '';
                document.getElementById('serv_lugar_carga').value = s.lugar_carga || '';
                document.getElementById('serv_sector').value = s.sector || '';
                document.getElementById('serv_mercancia').value = s.mercancia || '';
                document.getElementById('serv_bultos').value = s.bultos || '';
                document.getElementById('serv_peso').value = s.peso || '';
                document.getElementById('serv_volumen').value = s.volumen || '';
                document.getElementById('serv_dimensiones').value = s.dimensiones || '';
                document.getElementById('serv_moneda').value = s.moneda || 'CLP';
                document.getElementById('serv_proveedor_nac').value = s.proveedor_nac || '';
                document.getElementById('serv_tipo_cambio').value = s.tipo_cambio || 1;
                document.getElementById('serv_ref_cliente').value = s.ref_cliente || '';
                document.getElementById('serv_desconsolidacion').value = s.desconsolidac || '';
                document.getElementById('serv_aol').value = s.aol || '';
                document.getElementById('serv_aod').value = s.aod || '';
                document.getElementById('serv_agente').value = s.agente || '';
                document.getElementById('serv_aerolinea').value = s.aerolinea || '';
                document.getElementById('serv_terrestre').value = s.terrestre || '';
                document.getElementById('serv_maritimo').value = s.naviera || '';

                // Dentro de rellenarCampos() en modo edición
                const selectMedio = document.getElementById('serv_medio_transporte');
                if (selectMedio && s.trafico) {
                    selectMedio.value = s.trafico;
                    // Forzar carga de origen/destino
                    selectMedio.dispatchEvent(new Event('change', { bubbles: true }));
                    // Luego, asignar origen/destino (con un pequeño delay)
                    setTimeout(() => {
                        document.getElementById('serv_origen').value = s.origen || '';
                        document.getElementById('serv_destino').value = s.destino || '';
                    }, 300);
                }

                // === Actualizar submodal de costos ===
                actualizarTablaCostos();
            };

            // Cargar datos y luego rellenar
            cargarDatosModalServicio(rellenarCampos);
        } else {
            // Modo nuevo
            servicioEnEdicion = null;
            costosServicio = []; // Reiniciar costos
            // Limpiar campos
            const campos = document.querySelectorAll('#modal-servicio input, #modal-servicio select');
            campos.forEach(campo => {
                if (campo.id !== 'concatenado_serv' && campo.id !== 'id_prospect_serv' && campo.id !== 'id_srvc_actual') {
                    if (campo.type === 'select-one') {
                        campo.selectedIndex = 0;
                    } else {
                        campo.value = '';
                    }
                }
            });
            cargarDatosModalServicio();
        }

        // Mostrar modal
        document.getElementById('modal-servicio').style.display = 'flex';

    }

    // === Submodal Costos/ventas/gastos ======================================================================

    function abrirSubmodalCostos() {
        const modalServicio = document.getElementById('modal-servicio');
        if (!modalServicio || modalServicio.style.display === 'none') {
            error('❌ Abra primero el modal de Servicio');
            return;
        }
        
        // ✅ CORRECCIÓN CLAVE: Sincronizar costosServicio con el servicio en edición
        if (servicioEnEdicion !== null && servicios[servicioEnEdicion]) {
            console.log("🔍 Costos del servicio en edición:", servicios[servicioEnEdicion].costos);
            const servicioActual = servicios[servicioEnEdicion];
            costosServicio = Array.isArray(servicioActual.costos) 
                ? [...servicioActual.costos] 
                : [];
        } else {
            // Modo nuevo servicio
            console.log("🔍 CostosServicio pasa por else:", costosServicio);
            costosServicio = [];
        }
        console.log("🔍 CostosServicio después de sincronizar:", costosServicio);
        
        // Cargar datos
        const monedaServicio = document.getElementById('serv_moneda')?.value || 'USD';
        document.getElementById('costo_moneda').value = monedaServicio;
        cargarConceptosCostos();
        const medioTransporte = document.getElementById('serv_medio_transporte')?.value || '';
        cargarAplicacionesCostos(medioTransporte);
        
        // ✅ Actualizar la tabla ANTES de mostrar el submodal
        actualizarTablaCostos();
        
        // Mostrar submodal
        document.getElementById('submodal-costos').style.display = 'block';

        // ✅ LUEGO ACTUALIZAR LA TABLA (con un pequeño delay para asegurar el DOM)
        setTimeout(() => {
            actualizarTablaCostos();
        }, 50);
    }
    window.abrirSubmodalCostos = abrirSubmodalCostos;

    function cargarConceptosCostos() {
        fetch('api/get_conceptos_costos.php')
            .then(r => r.json())
            .then(data => {
                const sel = document.getElementById('costo_concepto');
                if (!sel) return;
                sel.innerHTML = '<option value="">Seleccionar concepto</option>';
                (data.conceptos || data || []).forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.concepto || c;
                    opt.textContent = c.concepto || c;
                    sel.appendChild(opt);
                });
            })
            .catch(err => {
                console.error('Error al cargar conceptos:', err);
                error('⚠️ No se pudieron cargar los conceptos de costo');
            });
    }

    function cargarAplicacionesCostos(medio) {
        fetch(`api/get_aplicaciones_costos.php?medio=${encodeURIComponent(medio)}`)
            .then(r => r.json())
            .then(data => {
                const sel = document.getElementById('costo_aplica');
                if (!sel) return;
                sel.innerHTML = '<option value="">Seleccionar aplica</option>';
                
                // Manejar tanto array de strings como array de objetos
                const opciones = Array.isArray(data) ? data : (data.aplicaciones || []);
                opciones.forEach(item => {
                    const valor = typeof item === 'string' ? item : item.aplica;
                    if (valor) {
                        const opt = document.createElement('option');
                        opt.value = valor;
                        opt.textContent = valor;
                        sel.appendChild(opt);
                    }
                });
            })
            .catch(err => {
                console.error('Error al cargar aplicaciones:', err);
                error('⚠️ No se pudieron cargar las opciones de "Aplica"');
            });
    }

    ['costo_qty', 'costo_costo', 'costo_tarifa'].forEach(id => {
        document.getElementById(id).addEventListener('input', calcularTotalesCostos);
    });

    function calcularTotalesCostos() {
        const qty = parseFloat(document.getElementById('costo_qty').value) || 0;
        const costo = parseFloat(document.getElementById('costo_costo').value) || 0;
        const tarifa = parseFloat(document.getElementById('costo_tarifa').value) || 0;
        
        document.getElementById('costo_total_costo').value = (qty * costo).toFixed(2);
        document.getElementById('costo_total_tarifa').value = (qty * tarifa).toFixed(2);
    }

    function guardarCosto() {
        const concepto = document.getElementById('costo_concepto').value;
        const aplica = document.getElementById('costo_aplica').value;
        const qty = parseFloat(document.getElementById('costo_qty').value) || 0;
        const costo = parseFloat(document.getElementById('costo_costo').value) || 0;
        const tarifa = parseFloat(document.getElementById('costo_tarifa').value) || 0;
        const moneda = document.getElementById('costo_moneda').value || 'CLP';

        if (!concepto || !aplica) {
            error('❌ Concepto y Aplica son obligatorios');
            return;
        }

        const nuevoCosto = {
            concepto,
            moneda,
            qty,
            costo,
            total_costo: qty * costo,
            tarifa,
            total_tarifa: qty * tarifa,
            aplica
        };

        if (window.indiceCostoEdicion !== undefined) {
            costosServicio[window.indiceCostoEdicion] = nuevoCosto;
            delete window.indiceCostoEdicion;
        } else {
            costosServicio.push(nuevoCosto);
        }

        actualizarTablaCostos();
        // ✅ NUEVO: Recalcular totales del prospecto
        recalcularTotalesProspectoDesdeCostos();
        limpiarFormularioCostos();
        mostrarNotificacion('✅ Costo guardado', 'exito');
    }

    function actualizarTablaCostos() {
        const tbody = document.getElementById('costos-body');
        //const totalCostoEl = document.getElementById('total-costo-costos');
        //const totalTarifaEl = document.getElementById('total-tarifa-costos');
        const totalCostoEl = document.getElementById('total-costo');
        const totalTarifaEl = document.getElementById('total-venta');
        
        if (!tbody || !totalCostoEl || !totalTarifaEl) {
            error('❌ Elementos de tabla de costos no encontrados');
            return;
        }

        tbody.innerHTML = '';
        let totalCosto = 0, totalTarifa = 0;

        costosServicio.forEach((c, i) => {
            const tr = document.createElement('tr');
            const costo = parseFloat(c.costo) || 0;
            const tarifa = parseFloat(c.tarifa) || 0;
            const qty = parseFloat(c.qty) || 0;
            const totalCostoItem = costo * qty;
            const totalTarifaItem = tarifa * qty;

            totalCosto += totalCostoItem;
            totalTarifa += totalTarifaItem;

            tr.innerHTML = `
                <td>${c.concepto}</td>
                <td>${c.moneda}</td>
                <td style="text-align: right;">${qty.toFixed(2)}</td>
                <td style="text-align: right; background-color: #fff9db;">${costo.toFixed(2)}</td>
                <td style="text-align: right; background-color: #fff9db;">${totalCostoItem.toFixed(2)}</td>
                <td style="text-align: right; background-color: #e6f7ff;">${tarifa.toFixed(2)}</td>
                <td style="text-align: right; background-color: #e6f7ff;">${totalTarifaItem.toFixed(2)}</td>
                <td>${c.aplica}</td>
                <td style="text-align: center;">
                    <button type="button" class="btn-edit" onclick="editarCosto(${i})" style="margin-right: 0.3rem; padding: 0.2rem 0.4rem;">✏️</button>
                    <button type="button" class="btn-delete" onclick="eliminarCosto(${i})" style="padding: 0.2rem 0.4rem;">🗑️</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Actualizar totales
        totalCostoEl.textContent = totalCosto.toFixed(2);
        totalTarifaEl.textContent = totalTarifa.toFixed(2);
    }

    function editarCosto(index) {
        const c = costosServicio[index];
        if (!c) return;

        // ✅ Asignar valores reales (incluyendo totales)
        document.getElementById('costo_concepto').value = c.concepto || '';
        document.getElementById('costo_qty').value = (c.qty !== undefined) ? c.qty : '';
        document.getElementById('costo_costo').value = (c.costo !== undefined) ? c.costo : '';
        document.getElementById('costo_tarifa').value = (c.tarifa !== undefined) ? c.tarifa : '';
        document.getElementById('costo_aplica').value = c.aplica || '';

        // ✅ Mostrar TOTALES reales (no recalcular)
        document.getElementById('costo_total_costo').value = (c.total_costo !== undefined) ? c.total_costo.toFixed(2) : '0.00';
        document.getElementById('costo_total_tarifa').value = (c.total_tarifa !== undefined) ? c.total_tarifa.toFixed(2) : '0.00';

        // Guardar índice para edición
        window.indiceCostoEdicion = index;
        const btn = document.querySelector('#submodal-costos button[type="button"]:last-of-type');
        if (btn) btn.innerHTML = '<i class="fas fa-save"></i> Actualizar';
    }

    function eliminarCosto(index) {
        mostrarConfirmacion('¿Eliminar costo?', () => {
            costosServicio.splice(index, 1);
            actualizarTablaCostos();
            recalcularTotalesProspectoDesdeCostos();
            exito('✅ Costo eliminado');
        });
    }

    // Modificar guardarCosto() para soportar edición
    function guardarCosto() {
        const concepto = document.getElementById('costo_concepto').value;
        const moneda = document.getElementById('costo_moneda').value;
        const qty = parseFloat(document.getElementById('costo_qty').value) || 0;
        const costo = parseFloat(document.getElementById('costo_costo').value) || 0;
        const tarifa = parseFloat(document.getElementById('costo_tarifa').value) || 0;
        const aplica = document.getElementById('costo_aplica').value;

        if (!concepto || !aplica) {
            error('❌ Concepto y Aplica son obligatorios');
            return;
        }

        const nuevoCosto = {
            id: Date.now(),
            id_servicio: idServicioActual,
            concepto: concepto,
            moneda: moneda,
            qty: qty,
            costo: costo,
            total_costo: qty * costo,
            tarifa: tarifa,
            total_tarifa: qty * tarifa,
            aplica: aplica
        };

        if (window.indiceCostoEdicion !== undefined) {
            // Modo edición
            costosServicio[window.indiceCostoEdicion] = nuevoCosto;
            delete window.indiceCostoEdicion;
            const btn = document.querySelector('#submodal-costos button[type="button"]:last-of-type');
            if (btn) btn.innerHTML = '<i class="fas fa-plus"></i> Agregar';
        } else {
            // Modo nuevo
            costosServicio.push(nuevoCosto);
        }

        actualizarTablaCostos();
        limpiarFormularioCostos(); // ✅ Ahora limpia TODO
        exito('✅ Costo guardado');
    }

    function cerrarSubmodalCostos() {
        document.getElementById('submodal-costos').style.display = 'none';
        // No se reinicia → se mantiene el estado
    }

    // ====== fin submodal Costos/Gastos/Ventas  =====================================================================

    function cerrarModalServicio() {
        servicioEnEdicion = null; // ← agregar esto
        const modal = document.getElementById('modal-servicio');
        if (modal) modal.style.display = 'none';
    }

    // Cargar ubicación al editar prospecto
    async function cargarUbicacionDesdeProspecto(p) {
        const inputPais = document.getElementById('pais');

        if (!inputPais) {
            console.error('❌ País no encontrado');
            return;
        }

        // 1. Asignar país
        inputPais.value = p.pais || '';
        const paisNombre = (p.pais || '').trim();

        if (!paisNombre) return;
    }

    // === Calcular Concatenado automáticamente ===
    function calcularConcatenado() {
        const operacion = document.getElementById('operacion')?.value || '';
        const tipoOper = document.getElementById('tipo_oper')?.value || '';
        
        if (!operacion || !tipoOper) {
            document.getElementById('concatenado').value = '';
            return;
        }

        // Formar prefijo con Operación + Tipo Operación
        const prefijo = operacion + tipoOper;

        // Fecha actual en formato yymmdd
        const hoy = new Date();
        const yy = String(hoy.getFullYear()).slice(-2);
        const mm = String(hoy.getMonth() + 1).padStart(2, '0');
        const dd = String(hoy.getDate()).padStart(2, '0');
        const fechaCorta = yy + mm + dd;

        // Correlativo: usar id_prospect si existe, o 00 como fallback
        const idProspect = document.getElementById('id_prospect')?.value || '0';
        const correlativo = String(parseInt(idProspect) + 1).padStart(2, '0');

        // Formar concatenado
        const concatenado = `${prefijo}${fechaCorta}-${correlativo}`;
        document.getElementById('concatenado').value = concatenado;
    }

    // === Cargar datos iniciales en el modal de Servicio ===
    function cargarDatosModalServicio(callback = null) {
        let cargas = 0;
        const totalCargas = 4; // commodity, medio_transporte, agente, proveedor_nac

        function verificarCarga() {
            cargas++;
            if (cargas === totalCargas && callback) {
                callback(); // Ejecutar callback cuando todo esté listo
            }
        }

        // === Cargar Commodity ===
        fetch('api/get_commoditys.php')
            .then(res => res.json())
            .then(data => {
                const select = document.getElementById('serv_commodity');
                if (select && data.commoditys) {
                    // Limpiar select
                    select.innerHTML = '<option value="">Seleccionar</option>';
                    
                    // Agregar opciones con ID y nombre
                    data.commoditys.forEach(item => {
                        const opt = document.createElement('option');
                        // Si item es un objeto (nueva estructura)
                        if (typeof item === 'object' && item !== null) {
                            opt.value = item.id_comm;        // ID para guardar
                            opt.textContent = item.commodity; // Nombre para mostrar
                        } 
                        // Si item es un string (estructura antigua - por compatibilidad)
                        else {
                            opt.value = item;
                            opt.textContent = item;
                        }
                        select.appendChild(opt);
                    });
                    
                    // Si estamos en modo edición, seleccionar el valor actual
                    if (servicioData && servicioData.serv_commodity) {
                        select.value = servicioData.serv_commodity;
                    }
                }
                verificarCarga();
            })
            .catch(error => {
                console.error('Error al cargar commoditys:', error);
                verificarCarga();
            });

        // === Cargar Medios de Transporte ===
        fetch('api/get_medios_transporte.php')
            .then(res => res.json())
            .then(data => {
                const select = document.getElementById('serv_medio_transporte');
                if (select && data.medios_transporte) {
                    select.innerHTML = '<option value="">Seleccionar</option>';
                    data.medios_transporte.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item;
                        opt.textContent = item;
                        select.appendChild(opt);
                    });
                }
                verificarCarga();

                // === Listener: Medio Transporte → Origen/Destino (con país) ===
                if (select) {
                    select.addEventListener('change', function () {
                        const medio = this.value;
                        const selectOrigen = document.getElementById('serv_origen');
                        const selectDestino = document.getElementById('serv_destino');
                        if (!medio) {
                            selectOrigen.innerHTML = '<option value="">Seleccionar</option>';
                            selectDestino.innerHTML = '<option value="">Seleccionar</option>';
                            return;
                        }
                        fetch(`api/get_lugares_por_medio.php?medio=${encodeURIComponent(medio)}`)
                            .then(res => res.json())
                            .then(data => {
                                const options = '<option value="">Seleccionar</option>' + 
                                    (data.lugares?.map(item => 
                                        `<option value="${item.lugar}" data-pais="${item.pais || ''}">${item.lugar}</option>`
                                    ).join('') || '');
                                selectOrigen.innerHTML = options;
                                selectDestino.innerHTML = options;
                            })
                            .catch(err => {
                                console.error('Error al cargar lugares:', err);
                                selectOrigen.innerHTML = '<option value="">Error</option>';
                                selectDestino.innerHTML = '<option value="">Error</option>';
                            });
                    });
                }

                // === Listener: Origen → País Origen ===
                const selectOrigen = document.getElementById('serv_origen');
                if (selectOrigen) {
                    selectOrigen.addEventListener('change', function() {
                        const option = this.options[this.selectedIndex];
                        const pais = option ? option.getAttribute('data-pais') || '' : '';
                        document.getElementById('serv_pais_origen').value = pais;
                    });
                }

                // === Listener: Destino → País Destino ===
                const selectDestino = document.getElementById('serv_destino');
                if (selectDestino) {
                    selectDestino.addEventListener('change', function() {
                        const option = this.options[this.selectedIndex];
                        const pais = option ? option.getAttribute('data-pais') || '' : '';
                        document.getElementById('serv_pais_destino').value = pais;
                    });
                }
            });

        // === Cargar Agentes ===
        fetch('api/get_agentes.php')
            .then(res => res.json())
            .then(data => {
                const select = document.getElementById('serv_agente');
                if (select && data.agentes) {
                    select.innerHTML = '<option value="">Seleccionar</option>';
                    data.agentes.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item;
                        opt.textContent = item;
                        select.appendChild(opt);
                    });
                }
                verificarCarga();
            });

        // === Cargar Proveedores Nacionales ===
        fetch('api/get_proveedores_pnac.php')
            .then(res => res.json())
            .then(data => {
                const select = document.getElementById('serv_proveedor_nac');
                if (select && data.proveedores) {
                    select.innerHTML = '<option value="">Seleccionar</option>';
                    data.proveedores.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item;
                        opt.textContent = item;
                        select.appendChild(opt);
                    });
                }
                verificarCarga();
            });
    }

    // === Guardar Servicio (con costos internacionales + gastos locales separados) ===
    // === Guardar Servicio (con costos internacionales + gastos locales separados) ===
    function guardarServicio() {
        const servicio = document.getElementById('serv_servicio').value.trim();
        if (!servicio) {
            error('❌ El campo "Servicio" es obligatorio');
            return;
        }

        // === 1. Totales de COSTOS INTERNACIONALES (desde costosServicio) ===
        let totalCostoInternacional = 0, totalVentaInternacional = 0;
        costosServicio.forEach(c => {
            totalCostoInternacional += c.total_costo || 0;
            totalVentaInternacional += c.total_tarifa || 0;
        });

        // === 2. Totales de GASTOS LOCALES (desde gastosLocales) ===
        let totalCostoGastosLocales = 0, totalVentaGastosLocales = 0;
        if (typeof gastosLocales !== 'undefined' && Array.isArray(gastosLocales)) {
            gastosLocales.forEach(g => {
                if (g.tipo === 'Costo') totalCostoGastosLocales += g.monto || 0;
                if (g.tipo === 'Ventas') totalVentaGastosLocales += g.monto || 0;
            });
        }

        // === 3. Obtener ID de servicio ===
        let idSrvc = '';
        if (servicioEnEdicion !== null) {
            idSrvc = servicios[servicioEnEdicion]?.id_srvc || '';
        } else {
            idSrvc = 'TEMP_' + Date.now();
        }

        // === 4. Construir objeto del servicio ===
        const nuevoServicio = {
            id_ppl: document.getElementById('id_ppl').value,
            id_srvc: idSrvc,
            id_prospect: document.getElementById('id_prospect_serv').value,
            servicio: servicio,
            nombre_corto: '',
            tipo: '',
            trafico: document.getElementById('serv_medio_transporte').value.trim(),
            sub_trafico: '',
            base_calculo: 0,
            moneda: document.getElementById('serv_moneda').value,
            tarifa: 0,
            iva: 19,
            estado: 'Activo',
            // === TOTALES PRINCIPALES (solo costos internacionales) ===
            costo: totalCostoInternacional,
            venta: totalVentaInternacional,
            // === TOTALES DE GASTOS LOCALES (para campos en BD) ===
            costogastoslocalesdestino: totalCostoGastosLocales,
            ventasgastoslocalesdestino: totalVentaGastosLocales,
            ciudad: '',
            pais: '',
            direc_serv: '',
            tipo_cambio: parseFloat(document.getElementById('serv_tipo_cambio').value) || 1,
            commodity: document.getElementById('serv_commodity').value.trim(),
            origen: document.getElementById('serv_origen').value.trim(),
            pais_origen: document.getElementById('serv_pais_origen').value.trim(),
            destino: document.getElementById('serv_destino').value.trim(),
            pais_destino: document.getElementById('serv_pais_destino').value.trim(),
            transito: document.getElementById('serv_transito').value.trim(),
            frecuencia: document.getElementById('serv_frecuencia').value.trim(),
            lugar_carga: document.getElementById('serv_lugar_carga').value.trim(),
            sector: document.getElementById('serv_sector').value.trim(),
            mercancia: document.getElementById('serv_mercancia').value.trim(),
            bultos: parseInt(document.getElementById('serv_bultos').value) || 0,
            peso: parseFloat(document.getElementById('serv_peso').value) || 0,
            volumen: parseFloat(document.getElementById('serv_volumen').value) || 0,
            dimensiones: document.getElementById('serv_dimensiones').value.trim(),
            agente: document.getElementById('serv_agente').value.trim(),
            aol: document.getElementById('serv_aol').value.trim(),
            aod: document.getElementById('serv_aod').value.trim(),
            aerolinea: document.getElementById('serv_aerolinea').value.trim(),
            naviera: document.getElementById('serv_maritimo').value.trim(),
            terrestre: document.getElementById('serv_terrestre').value.trim(),
            ref_cliente: document.getElementById('serv_ref_cliente').value.trim(),
            proveedor_nac: document.getElementById('serv_proveedor_nac').value.trim(),
            desconsolidac: document.getElementById('serv_desconsolidacion').value.trim(),
            // === DATOS PARA PERSISTENCIA EN JSON ===
            costos: [...costosServicio],
            gastos_locales: typeof gastosLocales !== 'undefined' ? [...gastosLocales] : []
        };

        // === 5. Guardar en memoria ===
        if (servicioEnEdicion !== null) {
            servicios[servicioEnEdicion] = nuevoServicio;
            exito('✅ Servicio actualizado');
        } else {
            servicios.push(nuevoServicio);
            exito('✅ Servicio agregado');
        }

        // === 6. Actualizar UI ===
        actualizarTabla();
        if (estadoProspecto !== 'CerradoOK') {
            tieneServiciosIniciales = false;
            actualizarVisibilidadBotones();
        }
        cerrarModalServicio();
    }

    // === Cerrar modasl Servicio con notificación confirma abandono
    function cerrarModalServicioConConfirmacion() {
        if (confirm('¿Desea cancelar sin guardar los cambios?')) {
            cerrarModalServicio();
        }
    }

    // Listener global para el botón de costos
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'btn-costos-servicio') {
            console.log('✅ Botón de costos clickeado');
            abrirSubmodalCostos();
        }
    });

    // === Submodal Gastos Locales - Cargar gastos según el tipo seleccionado ===
    function cargarGastosPorTipo() {
        const tipo = document.getElementById('gasto_tipo').value;
        const selectGasto = document.getElementById('gasto_gasto');
        if (!tipo) {
            selectGasto.innerHTML = '<option value="">Seleccione tipo primero</option>';
            return;
        }
        fetch(`api/get_gastos_locales.php?tipo=${encodeURIComponent(tipo)}`)
            .then(res => res.json())
            .then(data => {
                selectGasto.innerHTML = '<option value="">Seleccionar gasto</option>';
                (data.gastos || []).forEach(gasto => {
                    const opt = document.createElement('option');
                    opt.value = gasto;
                    opt.textContent = gasto;
                    selectGasto.appendChild(opt);
                });
            })
            .catch(err => {
                console.error('Error al cargar gastos locales:', err);
                error('⚠️ No se pudieron cargar los gastos');
            });
    }

    // ==================================================================================================
                        // INICIALIZACIÓN (dentro del DOMContentLoaded)
    // ==================================================================================================
    document.addEventListener('DOMContentLoaded', () => {

        // === Inicializar estado global ===
        estadoProspecto = 'Pendiente';
        tieneServiciosIniciales = false;
        servicios = [];

        // Recalcula y actualiza los totales del servicio actual en el array `servicios`
        function recalcularTotalesServicioEnMemoria() {
            if (servicioEnEdicion === null) return; // Solo aplica en edición

            let totalCosto = 0, totalVenta = 0, totalCostoGasto = 0, totalVentaGasto = 0;
            costosServicio.forEach(c => {
                totalCosto += c.total_costo || 0;
                totalVenta += c.total_tarifa || 0;
                totalCostoGasto += c.total_costo || 0;
                totalVentaGasto += c.total_tarifa || 0;
            });

            // Actualizar el servicio en el array
            const servicio = servicios[servicioEnEdicion];
            if (servicio) {
                servicio.costo = totalCosto;
                servicio.venta = totalVenta;
                servicio.costogastoslocalesdestino = totalCostoGasto;
                servicio.ventasgastoslocalesdestino = totalVentaGasto;
                // Opcional: actualizar otros campos si los costos afectan tarifa base, etc.
            }

            // Actualizar la tabla principal
            actualizarTabla();
        }

        actualizarVisibilidadBotones();
        cargarPaises(); // ✅ Nueva función

        function cargarPaises() {
            const selectPais = document.getElementById('pais');
            if (!selectPais) return;

            // Lista de países (puedes usar una API o esta lista estática)
            const paises = [
                "Afganistán", "Albania", "Alemania", "Andorra", "Angola", "Antigua y Barbuda",
                "Arabia Saudita", "Argelia", "Argentina", "Armenia", "Australia", "Austria",
                "Azerbaiyán", "Bahamas", "Bangladés", "Barbados", "Baréin", "Bélgica",
                "Belice", "Benín", "Bielorrusia", "Birmania", "Bolivia", "Bosnia y Herzegovina",
                "Botsuana", "Brasil", "Brunéi", "Bulgaria", "Burkina Faso", "Burundi",
                "Bután", "Cabo Verde", "Camboya", "Camerún", "Canadá", "Catar",
                "Chad", "Chile", "China", "Chipre", "Ciudad del Vaticano", "Colombia",
                "Comoras", "Corea del Norte", "Corea del Sur", "Costa de Marfil", "Costa Rica",
                "Croacia", "Cuba", "Dinamarca", "Dominica", "Ecuador", "Egipto", "El Salvador",
                "Emiratos Árabes Unidos", "Eritrea", "Eslovaquia", "Eslovenia", "España",
                "Estados Unidos", "Estonia", "Etiopía", "Filipinas", "Finlandia", "Fiyi",
                "Francia", "Gabón", "Gambia", "Georgia", "Ghana", "Granada", "Grecia",
                "Guatemala", "Guinea", "Guinea Ecuatorial", "Guinea-Bisáu", "Guyana",
                "Haití", "Honduras", "Hungría", "India", "Indonesia", "Irak", "Irán",
                "Irlanda", "Islandia", "Islas Marshall", "Islas Salomón", "Israel", "Italia",
                "Jamaica", "Japón", "Jordania", "Kazajistán", "Kenia", "Kirguistán", "Kiribati",
                "Kuwait", "Laos", "Lesoto", "Letonia", "Líbano", "Liberia", "Libia", "Liechtenstein",
                "Lituania", "Luxemburgo", "Madagascar", "Malasia", "Malaui", "Maldivas", "Malí",
                "Malta", "Marruecos", "Mauricio", "Mauritania", "México", "Micronesia", "Moldavia",
                "Mónaco", "Mongolia", "Montenegro", "Mozambique", "Namibia", "Nauru", "Nepal",
                "Nicaragua", "Níger", "Nigeria", "Noruega", "Nueva Zelanda", "Omán", "Países Bajos",
                "Pakistán", "Palaos", "Panamá", "Papúa Nueva Guinea", "Paraguay", "Perú", "Polonia",
                "Portugal", "Reino Unido", "República Centroafricana", "República Checa", "República Democrática del Congo",
                "República Dominicana", "Ruanda", "Rumania", "Rusia", "Samoa", "San Cristóbal y Nieves",
                "San Marino", "San Vicente y las Granadinas", "Santa Lucía", "Santo Tomé y Príncipe",
                "Senegal", "Serbia", "Seychelles", "Sierra Leona", "Singapur", "Siria", "Somalia",
                "Sri Lanka", "Suazilandia", "Sudáfrica", "Sudán", "Sudán del Sur", "Suecia", "Suiza",
                "Surinam", "Tailandia", "Tanzania", "Tayikistán", "Timor Oriental", "Togo", "Tonga",
                "Trinidad y Tobago", "Túnez", "Turkmenistán", "Turquía", "Tuvalu", "Ucrania", "Uganda",
                "Uruguay", "Uzbekistán", "Vanuatu", "Venezuela", "Vietnam", "Yemen", "Yibuti", "Zambia", "Zimbabue"
            ];

            selectPais.innerHTML = '<option value="">Seleccionar país</option>';
            paises.forEach(pais => {
                const opt = document.createElement('option');
                opt.value = pais;
                opt.textContent = pais;
                selectPais.appendChild(opt);
            });
        }

        // === CERRAR MODALS AL INICIAR ===
        const modals = ['modal-servicio', 'modal-resultados', 'modal-comercial', 'modal-operaciones'];
        modals.forEach(id => {
            const modal = document.getElementById(id);
            if (modal) modal.style.display = 'none';
        });

        // === HABILITAR BOTÓN AGREGAR ===
        habilitarBotonAgregar();

        // === BÚSQUEDA POR CONCATENADO ===
        const searchInput = document.getElementById('search_concatenado');
        const btnBuscar = document.getElementById('btn-buscar');
        const mensaje = document.getElementById('mensaje-busqueda');
        const btnNuevo = document.getElementById('btn-nuevo');
        if (searchInput && btnBuscar) {
            btnBuscar.addEventListener('click', () => {
                const term = searchInput.value.trim();
                mensaje.style.display = 'none';
                btnNuevo.style.display = 'none';
                if (!term) {
                    mensaje.textContent = 'Ingrese un valor';
                    mensaje.style.display = 'inline';
                    return;
                }
                fetch('api/search_concatenado.php?term=' + encodeURIComponent(term))
                    .then(r => r.json())
                    .then(data => {
                        if (Array.isArray(data) && data.length > 0) {
                            mostrarResultados(data);
                            abrirModalResultados();
                        } else {
                            mensaje.textContent = 'No existen coincidencias';
                            mensaje.style.display = 'inline';
                            btnNuevo.style.display = 'inline';
                            cerrarModalResultados();
                        }
                    })
                    .catch(err => {
                        mensaje.textContent = 'Error al buscar';
                        mensaje.style.display = 'inline';
                        console.error(err);
                    });
            });
            searchInput.addEventListener('keypress', e => {
                if (e.key === 'Enter') btnBuscar.click();
            });
        }
        if (btnNuevo) {
            btnNuevo.addEventListener('click', nuevoFormulario);
        }

        // === COMERCIAL POR ID ===
        const idComercial = document.getElementById('id_comercial');
        if (idComercial) {
            idComercial.addEventListener('blur', function () {
                const id = this.value.trim();
                if (!id) return;
                fetch(`api/get_comercial.php?id=${id}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.comercial) {
                            // Asignar valores
                            const nombreEl = document.getElementById('nombre');
                            const apellidoEl = document.getElementById('apellido');
                            const cargoEl = document.getElementById('cargo');
                            if (nombreEl) nombreEl.value = data.comercial.nombre || '';
                            if (apellidoEl) apellidoEl.value = data.comercial.apellido || '';
                            if (cargoEl) cargoEl.value = data.comercial.cargo || '';
                            // Hacer campos de solo lectura
                            ['nombre', 'apellido', 'cargo'].forEach(id => {
                                const el = document.getElementById(id);
                                if (el) {
                                    el.readOnly = true;
                                    el.style.background = '#f8f9fa';
                                }
                            });
                        } else {
                            if (confirm('No existe comercial. ¿Crear uno nuevo con los datos ingresados?')) {
                                // Permitir edición
                                ['nombre', 'apellido', 'cargo'].forEach(id => {
                                    const el = document.getElementById(id);
                                    if (el) {
                                        el.readOnly = false;
                                        el.style.background = 'white';
                                    }
                                });
                            } else {
                                // Limpiar y bloquear
                                this.value = '';
                                ['nombre', 'apellido', 'cargo'].forEach(id => {
                                    const el = document.getElementById(id);
                                    if (el) {
                                        el.value = '';
                                        el.readOnly = true;
                                        el.style.background = '#f8f9fa';
                                    }
                                });
                            }
                        }
                    })
                    .catch(err => console.error('Error al cargar comercial:', err));
            });
        }

        // === VALIDACIÓN DE RUT (solo formato y validación visual, SIN búsqueda) ===
        const rutInput = document.querySelector('input[name="rut_empresa"]');
        if (rutInput) {
            rutInput.addEventListener('input', function () {
                let value = this.value.replace(/\./g, '').replace('-', '');
                if (value.length > 1) {
                    const body = value.slice(0, -1);
                    const dv = value.slice(-1);
                    const formatted = body.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + (dv ? '-' + dv : '');
                    this.value = formatted;
                }
            });
            rutInput.addEventListener('blur', function () {
                const rutConFormato = this.value.trim();
                if (!rutConFormato) return;
                const rutLimpio = rutConFormato.replace(/\./g, '').replace('-', '').toUpperCase();
                if (!validarRut(rutLimpio)) {
                    this.style.borderColor = '#cc0000';
                    this.title = 'RUT inválido';
                    error('❌ El RUT ingresado no es válido');
                    this.focus();
                } else {
                    this.style.borderColor = '#ccc';
                }
            });
        }

        function validarRut(rut) {
            if (!/^(\d{7,8})([0-9K])$/.test(rut)) return false;
            const cuerpo = rut.slice(0, -1);
            const dv = rut.slice(-1).toUpperCase();
            let suma = 0;
            let multiplo = 2;
            for (let i = cuerpo.length - 1; i >= 0; i--) {
                suma += parseInt(cuerpo[i]) * multiplo;
                multiplo = multiplo < 7 ? multiplo + 1 : 2;
            }
            const dvEsperado = (11 - (suma % 11)).toString();
            const dvCalculado = dvEsperado === '11' ? '0' : dvEsperado === '10' ? 'K' : dvEsperado;
            return dv === dvCalculado;
        }

        function cargarProspectoDesdeAPI(data) {
            const p = data.prospecto;
            const setField = (sel, val) => {
                const el = document.querySelector(sel);
                if (el) el.value = val || '';
            };
            setField('input[name="razon_social"]', p.razon_social);
            setField('input[name="fono_empresa"]', p.fono_empresa);
            setField('input[name="pais"]', p.pais);
            setField('input[name="direccion"]', p.direccion);
            setField('select[name="tipo_oper"]', p.tipo_oper);
            setField('select[name="estado"]', p.estado);
            setField('input[name="booking"]', p.booking);
            setField('input[name="incoterm"]', p.incoterm);
            setField('input[name="id_comercial"]', p.id_comercial);
            setField('input[name="nombre"]', p.nombre);
            setField('input[name="concatenado"]', p.concatenado);
            setField('input[name="fecha_alta"]', p.fecha_alta);
            setField('input[name="fecha_estado"]', p.fecha_estado);
            servicios = Array.isArray(data.servicios) ? data.servicios : [];
            actualizarTabla();
            document.getElementById('btn-text').textContent = 'Actualizar';
            idPplInput.value = p.id_ppl;
            habilitarBotonAgregar();
            ['notas_comerciales', 'notas_operaciones'].forEach(nombre => {
                const valor = p[nombre];
                if (valor) {
                    let input = document.querySelector(`input[name="${nombre}"]`);
                    if (!input) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = nombre;
                        document.getElementById('form-prospecto').appendChild(input);
                    }
                    input.value = valor;
                }
            });
            exito('✅ Datos cargados desde RUT existente');
        }

        // === Inicializar ===
        cargarOperacionesYTipos();
        habilitarBotonAgregar();

        // === INICIALIZAR ESTADO ===
        const idPpl = document.getElementById('id_ppl')?.value;
        modoEdicion = !!idPpl;
        cargarOperacionesYTipos();

        // === CERRAR MODALS AL HACER CLICK FUERA ===
        window.onclick = e => {
            const modals = ['modal-comercial', 'modal-operaciones', 'modal-resultados', 'modal-servicio'];
            modals.forEach(id => {
                const modal = document.getElementById(id);
                if (modal && e.target === modal) modal.style.display = 'none';
            });
        };

        // === CERRAR CON ESCAPE ===
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                cerrarModalComercial();
                cerrarModalOperaciones();
                cerrarModalResultados();
                cerrarModalServicio();
            }
        });
        // === Mostrar notificación y manejar recarga tras guardado exitoso ===
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('exito') === '1') {
            history.replaceState({}, document.title, window.location.pathname);
            
            const concatenado = urlParams.get('concatenado');
            if (concatenado) {
                document.getElementById('concatenado').value = concatenado;
            }
            
            // ✅ Recuperar id_ppl desde la URL o desde el campo oculto
            const idPpl = urlParams.get('id_ppl') || document.getElementById('id_ppl')?.value;
            
            if (idPpl && idPpl !== '0') {
                // Recargar el prospecto y sus servicios
                seleccionarProspecto(idPpl);
                exito('✅ Prospecto y servicios guardados correctamente');
            } else {
                // Es un nuevo prospecto sin id → limpiar formulario
                exito('✅ Prospecto guardado correctamente');
                if (typeof nuevoFormulario === 'function') {
                    nuevoFormulario();
                }
            }
        }
        // === Mostrar botón "Volver" al cargar un prospecto ===
        function mostrarBotonVolver() {
            document.getElementById('btn-volver').style.display = 'inline-block';
        }

        // === Ocultar botón "Volver" al reiniciar formulario ===
        function ocultarBotonVolver() {
            document.getElementById('btn-volver').style.display = 'none';
        }

        // === Vincular botón "Volver" al modal de confirmación ===
        document.getElementById('btn-volver').onclick = function() {
            document.getElementById('modal-confirm').style.display = 'flex';
        };

        // === Confirmar "Sí" ===
        document.getElementById('btn-confirm-yes').onclick = function() {
            document.getElementById('modal-confirm').style.display = 'none';
            nuevoFormulario();
            ocultarBotonVolver();
        };

        // === Confirmar "No" ===
        document.getElementById('btn-confirm-no').onclick = function() {
            document.getElementById('modal-confirm').style.display = 'none';
        };

        // btn costos servicio
        const btnCostos = document.getElementById('btn-costos-servicio');
        if (btnCostos) {
            btnCostos.addEventListener('click', abrirSubmodalCostos);
        }

        // === Submodal Gastos Locales: Listeners robustos ===
        const selectGastoTipo = document.getElementById('gasto_tipo');
        if (selectGastoTipo) {
            selectGastoTipo.addEventListener('change', cargarGastosPorTipo);
        }

        // Listener robusto para Gastos Locales (delegación de eventos)
        document.addEventListener('click', function(e) {
            if (e.target && e.target.id === 'btn-gastos-locales') {
                abrirSubmodalGastosLocales();
            }
        });

        actualizarVisibilidadBotones(); // ← Asegúrate de que esté aquí al final

        // === LISTENER ÚNICO Y DEFINITIVO PARA GRABAR TODO ===
        const btnSaveAll = document.getElementById('btn-save-all');
        if (btnSaveAll) {
            // Asegurar que no haya listeners duplicados
            const clone = btnSaveAll.cloneNode(true);
            btnSaveAll.parentNode.replaceChild(clone, btnSaveAll);

            clone.addEventListener('click', function(e) {
                e.preventDefault();
                const form = document.getElementById('form-prospecto');
                if (!form) {
                    console.error('❌ Formulario no encontrado');
                    return;
                }

                // Limpiar inputs dinámicos anteriores
                ['modo', 'servicios_json'].forEach(name => {
                    const el = form.querySelector(`input[name="${name}"]`);
                    if (el) el.remove();
                });

                // Determinar modo en tiempo real
                const tieneServicios = Array.isArray(servicios) && servicios.length > 0;
                const modo = tieneServicios ? 'servicios' : 'prospecto';

                // Añadir input de modo
                const inputModo = document.createElement('input');
                inputModo.type = 'hidden';
                inputModo.name = 'modo';
                inputModo.value = modo;
                form.appendChild(inputModo);

                // Añadir JSON si hay servicios
                if (tieneServicios) {
                    const serviciosJSON = JSON.stringify(servicios);
                    const inputJSON = document.createElement('input');
                    inputJSON.type = 'hidden';
                    inputJSON.name = 'servicios_json';
                    inputJSON.value = serviciosJSON;
                    form.appendChild(inputJSON);
                    console.log('📤 Enviando servicios:', servicios);
                }

                form.submit();
            });
        }

    });

    // =========  FIN DEL DOMContentLoaded  =======================================================================

    // === Búsqueda inteligente (mejorada) ===
    const busquedaInput = document.getElementById('busqueda-inteligente');
    const resultadosDiv = document.getElementById('resultados-busqueda');

    // === MODAL DE CONFIRMACIÓN PERSONALIZADO ===
    function mostrarConfirmacion(mensaje, callbackSi, callbackNo = null) {
        const modal = document.getElementById('modal-confirm');
        const mensajeEl = modal.querySelector('p');
        const btnSi = document.getElementById('btn-confirm-yes');
        const btnNo = document.getElementById('btn-confirm-no');

        if (!modal || !mensajeEl || !btnSi || !btnNo) {
            console.error('❌ Modal de confirmación no encontrado');
            // Fallback a confirm() si no existe el modal
            if (confirm(mensaje) && callbackSi) callbackSi();
            return;
        }

        mensajeEl.textContent = mensaje;
        modal.style.display = 'flex';

        // Limpiar listeners anteriores
        const limpiar = () => {
            btnSi.onclick = null;
            btnNo.onclick = null;
            modal.style.display = 'none';
        };

        btnSi.onclick = () => {
            limpiar();
            if (callbackSi) callbackSi();
        };

        btnNo.onclick = () => {
            limpiar();
            if (callbackNo) callbackNo();
        };
    }

    if (busquedaInput) {
        busquedaInput.addEventListener('input', async function () {
            const term = this.value.trim();
            resultadosDiv.style.display = 'none';
            if (!term) return;

            try {
                const response = await fetch(`/CRM_Qwen/api/buscar_inteligente.php?term=${encodeURIComponent(term)}`);
                const data = await response.json();

                resultadosDiv.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(p => {
                        const div = document.createElement('div');
                        div.style.padding = '0.8rem';
                        div.style.borderBottom = '1px solid #eee';
                        div.style.cursor = 'pointer';
                        div.style.color = '#333';
                        div.style.fontSize = '0.9rem';
                        div.innerHTML = `
                            <strong>${p.razon_social}</strong><br>
                            <small>
                                ID: ${p.concatenado} | 
                                RUT: ${p.rut_empresa} | 
                                Comercial: ${p.nombre_comercial || ''} ${p.apellido_comercial || ''}
                            </small>
                        `;
                        div.onclick = () => {
                            // ✅ Corregido: usar id_ppl para cargar
                            seleccionarProspecto(p.id_ppl);
                            resultadosDiv.style.display = 'none';
                            busquedaInput.value = '';
                        };
                        resultadosDiv.appendChild(div);
                    });
                    resultadosDiv.style.display = 'block';
                } else {
                    resultadosDiv.innerHTML = '<div style="padding: 0.8rem; color: #666;">No se encontraron coincidencias</div>';
                    resultadosDiv.style.display = 'block';
                }
            } catch (err) {
                console.error('Error en búsqueda:', err);
                resultadosDiv.innerHTML = '<div style="padding: 0.8rem; color: red;">Error al buscar</div>';
                resultadosDiv.style.display = 'block';
            }
        });
    }

    // Cerrar resultados al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#busqueda-inteligente') && !e.target.closest('#resultados-busqueda')) {
            resultadosDiv.style.display = 'none';
        }
    });
    // ==== Cargar Operaciones y Tipos =====
    function cargarOperacionesYTipos() {
            const selectOperacion = document.getElementById('operacion');
            const selectTipoOper = document.getElementById('tipo_oper');
            if (!selectOperacion || !selectTipoOper) return;
            fetch('api/get_operaciones.php')
                .then(res => res.json())
                .then(data => {
                    if (Array.isArray(data.operaciones)) {
                        selectOperacion.innerHTML = '<option value="">Seleccionar</option>';
                        data.operaciones.forEach(op => {
                            const opt = document.createElement('option');
                            opt.value = op;
                            opt.textContent = op;
                            selectOperacion.appendChild(opt);
                        });
                    }
                })
                .catch(err => error('⚠️ No se pudieron cargar las operaciones'));
                selectOperacion.addEventListener('change', function () {
                const operacion = this.value;
                selectTipoOper.disabled = !operacion;
                selectTipoOper.innerHTML = '<option value="">Cargando...</option>';
                if (!operacion) {
                    selectTipoOper.innerHTML = '<option value="">Seleccionar operación</option>';
                    return;
                }
                fetch(`api/get_tipos_por_operacion.php?operacion=${encodeURIComponent(operacion)}`)
                    .then(res => res.json())
                    .then(data => {
                        selectTipoOper.innerHTML = '<option value="">Seleccionar</option>';
                        if (Array.isArray(data.tipos)) {
                            data.tipos.forEach(tipo => {
                                const opt = document.createElement('option');
                                opt.value = tipo;
                                opt.textContent = tipo;
                                selectTipoOper.appendChild(opt);
                            });
                        }
                    })
                    .catch(err => {
                        selectTipoOper.innerHTML = '<option value="">Error</option>';
                    });
            });
    }

    // === Escuchar cambios en Tipo Operación para actualizar Concatenado ===
    const selectTipoOper = document.getElementById('tipo_oper');
    if (selectTipoOper) {
        selectTipoOper.addEventListener('change', calcularConcatenado);
    }

    // ✅ NUEVA FUNCIÓN: Recalcula los totales del prospecto basado en servicios.costos
    function recalcularTotalesProspectoDesdeCostos() {
        // Primero, si estamos editando un servicio, actualizar sus totales desde costosServicio
        if (servicioEnEdicion !== null) {
            let totalCosto = 0, totalVenta = 0;
            costosServicio.forEach(c => {
                totalCosto += (parseFloat(c.qty) || 0) * (parseFloat(c.costo) || 0);
                totalVenta += (parseFloat(c.qty) || 0) * (parseFloat(c.tarifa) || 0);
            });
            // Actualizar el servicio en memoria
            if (servicios[servicioEnEdicion]) {
                servicios[servicioEnEdicion].costo = totalCosto;
                servicios[servicioEnEdicion].venta = totalVenta;
                servicios[servicioEnEdicion].costogastoslocalesdestino = totalCosto;
                servicios[servicioEnEdicion].ventasgastoslocalesdestino = totalVenta;
            }
        }

        // Ahora recalcular totales generales del prospecto
        let total_costo = 0, total_venta = 0, total_costogasto = 0, total_ventagasto = 0;
        servicios.forEach(s => {
            total_costo += parseFloat(s.costo) || 0;
            total_venta += parseFloat(s.venta) || 0;
            total_costogasto += parseFloat(s.costogastoslocalesdestino) || 0;
            total_ventagasto += parseFloat(s.ventasgastoslocalesdestino) || 0;
        });

        // Actualizar la UI
        const updateField = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value.toFixed(2);
        };
        updateField('total-costo', total_costo);
        updateField('total-venta', total_venta);
        updateField('total-costogasto', total_costogasto);
        updateField('total-ventagasto', total_ventagasto);
    }

    function habilitarEdicion() {
        const form = document.getElementById('form-prospecto');
        const inputs = form.querySelectorAll('input:not([type="hidden"]):not([name="concatenado"])');
        const selects = form.querySelectorAll('select');

        inputs.forEach(input => {
            input.readOnly = false;
            input.style.backgroundColor = '';
        });

        selects.forEach(select => {
            select.disabled = false;
        });

        const paisInput = document.getElementById('pais');
        if (paisInput) {
            paisInput.readOnly = false;
            paisInput.style.backgroundColor = '';
        }
    }

    // === FUNCIONES GLOBALES ===
    function enviarFormularioConAjax() {
        const form = document.getElementById('form-prospecto');
        const formData = new FormData(form);

        fetch('index.php?page=prospectos', {  // ← ¡Así debe ser!
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                exito('✅ Prospecto actualizado correctamente');
                if (typeof nuevoFormulario === 'function') {
                    nuevoFormulario();
                }
                // Cerrar modales
                ['modal-servicio', 'modal-comercial', 'modal-operaciones', 'modal-resultados'].forEach(id => {
                    const modal = document.getElementById(id);
                    if (modal) modal.style.display = 'none';
                });
            } else {
                throw new Error('Error en el servidor');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            error('❌ Error al actualizar el prospecto');
        });
    }

    function nuevoFormulario() {
        // Limpiar solo campos visibles
        const inputs = document.querySelectorAll('input:not([type="hidden"]):not([name="concatenado"])');
        const selects = document.querySelectorAll('select');
        inputs.forEach(input => input.value = '');
        selects.forEach(select => select.selectedIndex = 0);

        // ✅ Resetear variables de estado
        estadoProspecto = 'Pendiente';
        tieneServiciosIniciales = false;

        // Reiniciar servicios
        servicios = [];
        actualizarTabla();

        // Limpiar campos ocultos específicos
        document.getElementById('id_ppl').value = '';
        document.getElementById('id_prospect').value = '';

        // Eliminar campos ocultos de notas si existen
        ['notas_comerciales', 'notas_operaciones'].forEach(name => {
            const el = document.querySelector(`input[name="${name}"]`);
            if (el) el.remove();
        });

        // Limpiar búsqueda
        const searchConcat = document.getElementById('search_concatenado');
        const mensajeBusqueda = document.getElementById('mensaje-busqueda');
        if (searchConcat) searchConcat.value = '';
        if (mensajeBusqueda) mensajeBusqueda.style.display = 'none';

        // ✅ Limpiar y habilitar campos de comercial (solo si existen)
        ['nombre', 'apellido', 'cargo'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.value = '';
                el.readOnly = false;
                el.style.background = 'white';
            }
        });

        // Limpiar formulario
        const form = document.getElementById('form-prospecto');

        // Habilitar botón de agregar servicio
        habilitarBotonAgregar();

        // Ocultar botón "Volver"
        ocultarBotonVolver();

        // Actualiza botones
        actualizarVisibilidadBotones();
        document.getElementById('id_ppl').value = '';
        habilitarBotonAgregar(); // ✅ Agrega esta línea

        actualizarBotonEliminarProspecto();
    }

    function habilitarEdicionYActualizar() {
        // === 1. Capturar valores actuales de los campos convertidos a <input readonly> ===
        const valorEstado = document.querySelector('#contenedor-estado input[name="estado"]')?.value || 'Pendiente';
        const valorOperacion = document.querySelector('#contenedor-operacion input[name="operacion"]')?.value || '';
        const valorTipoOper = document.querySelector('#contenedor-tipo-oper input[name="tipo_oper"]')?.value || ''

        // === 2. Habilitar todos los inputs y selects ===
        const form = document.getElementById('form-prospecto');
        const inputs = form.querySelectorAll('input:not([type="hidden"]):not([name="concatenado"])');
        const selects = form.querySelectorAll('select');
        inputs.forEach(input => {
            input.readOnly = false;
            input.style.backgroundColor = '';
        });
        selects.forEach(select => {
            select.disabled = false;
        });

        // === 3. Restaurar ESTADO como <select> ===
        const contenedorEstado = document.getElementById('contenedor-estado');
        if (contenedorEstado) {
            contenedorEstado.innerHTML = `
                <select name="estado" id="estado" style="flex:1; padding:0.5rem; border:1px solid #ccc; border-radius:6px; font-size:0.9rem;">
                    <option value="Pendiente">Pendiente</option>
                    <option value="Enviado">Enviado</option>
                    <option value="Devuelto_pendiente">Devuelto_pendiente</option>
                    <option value="CerradoOK">CerradoOK</option>
                    <option value="Rechazado">Rechazado</option>
                </select>
            `;
            const selectEstado = document.getElementById('estado');
            if (selectEstado) {
                selectEstado.value = valorEstado;
            }
        }

        // === 4. Restaurar OPERACIÓN y TIPO OPERACIÓN ===
        const contenedorOper = document.getElementById('contenedor-operacion');
        const contenedorTipoOper = document.getElementById('contenedor-tipo-oper');

        if (contenedorOper) {
            contenedorOper.innerHTML = `
                <select name="operacion" id="operacion" style="flex:1; padding:0.5rem; border:1px solid #ccc; border-radius:6px; font-size:0.9rem;">
                    <option value="">Cargando...</option>
                </select>
            `;
        }
        if (contenedorTipoOper) {
            contenedorTipoOper.innerHTML = `
                <select name="tipo_oper" id="tipo_oper" style="flex:1; padding:0.5rem; border:1px solid #ccc; border-radius:6px; font-size:0.9rem;">
                    <option value="">Cargando...</option>
                </select>
            `;
        }

        // Cargar operaciones
        fetch('api/get_operaciones.php')
            .then(res => res.json())
            .then(data => {
                const selectOper = document.getElementById('operacion');
                if (!selectOper) return;

                selectOper.innerHTML = '<option value="">Seleccionar</option>';
                if (Array.isArray(data.operaciones)) {
                    data.operaciones.forEach(op => {
                        const opt = document.createElement('option');
                        opt.value = op;
                        opt.textContent = op;
                        selectOper.appendChild(opt);
                    });
                }
                selectOper.value = valorOperacion;

                // === Registrar listener de 'change' + cálculo de Concatenado ===
                const selectTipoOper = document.getElementById('tipo_oper');
                selectOper.addEventListener('change', function () {
                    const operacion = this.value;
                    if (!selectTipoOper) return;

                    selectTipoOper.disabled = !operacion;
                    selectTipoOper.innerHTML = '<option value="">Cargando...</option>';

                    if (!operacion) {
                        selectTipoOper.innerHTML = '<option value="">Seleccionar operación</option>';
                        return;
                    }

                    fetch(`api/get_tipos_por_operacion.php?operacion=${encodeURIComponent(operacion)}`)
                        .then(res => res.json())
                        .then(data => {
                            selectTipoOper.innerHTML = '<option value="">Seleccionar</option>';
                            if (Array.isArray(data.tipos)) {
                                data.tipos.forEach(tipo => {
                                    const opt = document.createElement('option');
                                    opt.value = tipo;
                                    opt.textContent = tipo;
                                    selectTipoOper.appendChild(opt);
                                });
                            }
                            // Asignar valor original si coincide
                            if (data.tipos?.includes(valorTipoOper)) {
                                selectTipoOper.value = valorTipoOper;
                            }
                            // Actualizar Concatenado tras cambio
                            calcularConcatenado();
                        })
                        .catch(err => {
                            selectTipoOper.innerHTML = '<option value="">Error</option>';
                        });
                });

                // Disparar 'change' para cargar tipos según operación original
                if (valorOperacion) {
                    selectOper.dispatchEvent(new Event('change'));
                    // Forzar asignación del valor de tipo_oper (por si acaso)
                    setTimeout(() => {
                        const selectTipo = document.getElementById('tipo_oper');
                        if (selectTipo) {
                            const optionExists = Array.from(selectTipo.options).some(opt => opt.value === valorTipoOper);
                            if (optionExists) {
                                selectTipo.value = valorTipoOper;
                            }
                            // Actualizar Concatenado inicial
                            calcularConcatenado();
                        }
                    }, 400);
                }
            })
            .catch(err => {
                error('⚠️ Error al cargar operaciones');
            });


        // === 6. Actualizar botón ===
        const btn = document.getElementById('btn-save-all');
        if (btn) {
            btn.textContent = 'Actualizar';
            btn.setAttribute('data-mode', 'update');
        }
    }

    function seleccionarProspecto(id) {
        // ✅ Cerrar modal de resultados inmediatamente
        const resultadosDiv = document.getElementById('resultados-busqueda');
        if (resultadosDiv) resultadosDiv.style.display = 'none';

        fetch('api/get_prospecto.php?id=' + id)
            .then(res => res.json())
            .then(data => {
                if (!data.success || !data.prospecto) {
                    alert('Prospecto no encontrado');
                    return;
                }
                const p = data.prospecto;

                // ✅ Recalcular servicios con totales correctos
                const serviciosRecalculados = (data.servicios || []).map(s => {
                    // Recalcular totales desde costos
                    let totalCosto = 0, totalVenta = 0;
                    if (Array.isArray(s.costos)) {
                        s.costos.forEach(c => {
                            totalCosto += parseFloat(c.total_costo) || 0;
                            totalVenta += parseFloat(c.total_tarifa) || 0;
                        });
                    }
                    return {
                        ...s,
                        costo: totalCosto,
                        venta: totalVenta,
                        // Asegurar que los gastos locales estén presentes
                        costogastoslocalesdestino: s.costogastoslocalesdestino || 0,
                        ventasgastoslocalesdestino: s.ventasgastoslocalesdestino || 0,
                        gastos_locales: Array.isArray(s.gastos_locales) ? s.gastos_locales : []
                    };
                });

                servicios = serviciosRecalculados;
                tieneServiciosIniciales = servicios.length > 0;
                estadoProspecto = p.estado || 'Pendiente';
                const tieneServicios = servicios.length > 0;
                const esCerradoOK = (p.estado === 'CerradoOK');

                // === Asignar campos genéricos ===
                const camposGenericos = [
                    'razon_social', 'rut_empresa', 'fono_empresa',
                    'booking', 'incoterm', 'direccion', 'fecha_alta', 'fecha_estado',
                    'nombre', 'apellido', 'cargo', 'concatenado', 'id_comercial'
                ];
                camposGenericos.forEach(name => {
                    const el = document.querySelector(`[name="${name}"]`);
                    if (el && el.tagName === 'INPUT') {
                        el.value = p[name] || '';
                        el.readOnly = true;
                        el.style.backgroundColor = '#f9f9f9';
                    }
                });

                // === Cargar NOTAS ===
                ['notas_comerciales', 'notas_operaciones'].forEach(nombre => {
                    const valor = p[nombre] || '';
                    let input = document.querySelector(`input[name="${nombre}"]`);
                    if (!input) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = nombre;
                        document.getElementById('form-prospecto').appendChild(input);
                    }
                    input.value = valor;
                    document.getElementById(`${nombre}_input`).value = valor;
                });

                // === Asignar PAÍS ===
                const inputPais = document.getElementById('pais');
                if (inputPais && p.pais) {
                    inputPais.value = p.pais;
                    inputPais.readOnly = true;
                    inputPais.style.backgroundColor = '#f9f9f9';
                    setTimeout(() => inputPais.dispatchEvent(new Event('change')), 100);
                }

                // === Asignar OPERACIÓN y TIPO OPERACIÓN ===
                const selectOper = document.getElementById('operacion');
                const selectTipoOper = document.getElementById('tipo_oper');
                if (selectOper) selectOper.value = p.operacion || '';
                if (selectOper && selectTipoOper) {
                    const event = new Event('change', { bubbles: true });
                    selectOper.dispatchEvent(event);
                    setTimeout(() => {
                        if (selectTipoOper) selectTipoOper.value = p.tipo_oper || '';
                    }, 300);
                }

                // === Modo lectura si NO tiene servicios ===
                if (!tieneServicios) {
                    setTimeout(() => {
                        convertirOperacionAModoLectura(p.operacion);
                        convertirTipoOperAModoLectura(p.tipo_oper);
                        convertirEstadoAModoLectura(p.estado);
                    }, 100);
                } else {
                    // ✅ Solo deshabilitar estado si es "CerradoOK"
                    const selectEstado = document.getElementById('estado');
                    if (selectEstado) {
                        selectEstado.value = p.estado;
                        selectEstado.disabled = esCerradoOK;
                    }
                }

                // === Campos ocultos ===
                document.getElementById('id_ppl').value = p.id_ppl || '';
                document.getElementById('id_prospect').value = p.id_prospect || p.id_ppl || '';

                // === Actualizar tabla y botones ===
                actualizarTabla();
                document.getElementById('btn-volver').style.display = 'inline-block';

                // === Habilitar/deshabilitar botones de Notas según estado (NO según tieneServicios) ===
                const btnComercial = document.querySelector('.btn-comment:nth-of-type(1)');
                const btnOperaciones = document.querySelector('.btn-comment:nth-of-type(2)');
                if (btnComercial && btnOperaciones) {
                    btnComercial.disabled = esCerradoOK;
                    btnOperaciones.disabled = esCerradoOK;
                    btnComercial.style.opacity = esCerradoOK ? '0.5' : '1';
                    btnOperaciones.style.opacity = esCerradoOK ? '0.5' : '1';
                    btnComercial.style.cursor = esCerradoOK ? 'not-allowed' : 'pointer';
                    btnOperaciones.style.cursor = esCerradoOK ? 'not-allowed' : 'pointer';
                }

                // El botón de costos siempre debe estar habilitado
                const btnCostos = document.getElementById('btn-costos-servicio');
                if (btnCostos) {
                    btnCostos.disabled = false;
                    btnCostos.style.opacity = '1';
                    btnCostos.style.cursor = 'pointer';
                }

                // Habilitar/deshabilitar botón "Agregar Servicio"
                document.getElementById('btn-agregar-servicio').disabled = esCerradoOK;

                // === Botón "Grabar Todo": ocultar solo si ya tenía servicios ===
                const contenedorBoton = document.getElementById('contenedor-boton-prospecto');
                if (contenedorBoton) {
                    contenedorBoton.style.display = tieneServiciosIniciales ? 'none' : 'flex';
                }

                // Actualizar visibilidad de botones
                actualizarVisibilidadBotones();
                habilitarBotonAgregar();
            })
            .catch(err => {
                console.error('Error al cargar prospecto:', err);
                alert('Error al cargar prospecto');
            });
    }

    // Convertir operación a modo lectura
    function convertirOperacionAModoLectura(valor) {
        const contenedor = document.getElementById('contenedor-operacion');
        if (!contenedor) return;
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'operacion';
        input.value = valor || '';
        input.readOnly = true;
        input.style.cssText = 'flex:1; padding:0.5rem; border:1px solid #ccc; border-radius:6px; font-size:0.9rem; background:#f9f9f9; color:#555;';
        contenedor.innerHTML = '';
        contenedor.appendChild(input);
    }

    // Convertir tipo_oper a modo lectura
    function convertirTipoOperAModoLectura(valor) {
        const contenedor = document.getElementById('contenedor-tipo-oper');
        if (!contenedor) return;
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'tipo_oper';
        input.value = valor || '';
        input.readOnly = true;
        input.style.cssText = 'flex:1; padding:0.5rem; border:1px solid #ccc; border-radius:6px; font-size:0.9rem; background:#f9f9f9; color:#555;';
        contenedor.innerHTML = '';
        contenedor.appendChild(input);
    }

    // Convertir Estado a modo lectura
    function convertirEstadoAModoLectura(valor) {
        const contenedor = document.getElementById('contenedor-estado');
        if (!contenedor) return;
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'estado';
        input.value = valor || '';
        input.readOnly = true;
        input.style.cssText = 'flex:1; padding:0.5rem; border:1px solid #ccc; border-radius:6px; font-size:0.9rem; background:#f9f9f9; color:#555;';
        contenedor.innerHTML = '';
        contenedor.appendChild(input);
    }

    function seleccionarYCerrar(id) {
        seleccionarProspecto(id);
        cerrarModalResultados();
    }

    // Actualizar visibilidad de botones
    function actualizarVisibilidadBotones() {
        const idPpl = document.getElementById('id_ppl')?.value;
        const esNuevo = !idPpl || idPpl === '' || idPpl === '0';
        const esCerradoOK = estadoProspecto === 'CerradoOK';
        const tieneServiciosAhora = servicios.length > 0;
        const btnGrabarTodo = document.getElementById('btn-save-all');
        const contenedorBoton = document.getElementById('contenedor-boton-prospecto');
        if (!btnGrabarTodo || !contenedorBoton) return;

        if (esCerradoOK) {
            contenedorBoton.style.display = 'none';
            return;
        }

        // === Solo actualizar texto y modo (sin clonar) ===
        if (tieneServiciosIniciales) {
            contenedorBoton.style.display = 'none';
        } else if (tieneServiciosAhora) {
            btnGrabarTodo.textContent = 'Grabar Todo';
            btnGrabarTodo.setAttribute('data-modo', 'servicios');
            contenedorBoton.style.display = 'flex';
        } else {
            if (esNuevo) {
                btnGrabarTodo.textContent = 'Grabar Todo';
                btnGrabarTodo.setAttribute('data-modo', 'prospecto');
            } else {
                btnGrabarTodo.textContent = 'Actualizar';
                btnGrabarTodo.setAttribute('data-modo', 'prospecto');
            }
            contenedorBoton.style.display = 'flex';
        }
    }

    function limpiarFormularioCostos() {
        document.getElementById('costo_concepto').selectedIndex = 0;
        document.getElementById('costo_qty').value = '';
        document.getElementById('costo_costo').value = '';
        document.getElementById('costo_tarifa').value = '';
        document.getElementById('costo_aplica').selectedIndex = 0;
        // ✅ Limpiar campos calculados
        document.getElementById('costo_total_costo').value = '0.00';
        document.getElementById('costo_total_tarifa').value = '0.00';
    }

    // === Abrir submodal de Gastos Locales ===
    function abrirSubmodalGastosLocales() {
        const modalServicio = document.getElementById('modal-servicio');
        if (!modalServicio || modalServicio.style.display === 'none') {
            error('❌ Abra primero el modal de Servicio');
            return;
        }
        // Sincronizar con el servicio en edición
        if (servicioEnEdicion !== null && servicios[servicioEnEdicion]) {
            gastosLocales = Array.isArray(servicios[servicioEnEdicion].gastos_locales) 
                ? [...servicios[servicioEnEdicion].gastos_locales] 
                : [];
        }
        cargarGastosPorTipo();
        actualizarTablaGastosLocales();
        document.getElementById('submodal-gastos-locales').style.display = 'block';
    }

    // === Cargar gastos según tipo ===
    function cargarGastosPorTipo() {
        const tipo = document.getElementById('gasto_tipo').value;
        const selectGasto = document.getElementById('gasto_gasto');
        if (!tipo) {
            selectGasto.innerHTML = '<option value="">Seleccione tipo primero</option>';
            return;
        }
        fetch(`api/get_gastos_locales.php?tipo=${encodeURIComponent(tipo)}`)
            .then(res => res.json())
            .then(data => {
                selectGasto.innerHTML = '<option value="">Seleccionar gasto</option>';
                (data.gastos || []).forEach(nombreGasto => {
                    const opt = document.createElement('option');
                    opt.value = nombreGasto;      // ✅ string
                    opt.textContent = nombreGasto; // ✅ string
                    selectGasto.appendChild(opt);
                });
            })
            .catch(err => {
                console.error('Error al cargar gastos locales:', err);
                error('⚠️ No se pudieron cargar los gastos');
            });
    }

    // === Guardar gasto local ===
    function guardarGastoLocal() {
        const tipo = document.getElementById('gasto_tipo').value;
        const gasto = document.getElementById('gasto_gasto').value;
        const moneda = document.getElementById('gasto_moneda').value;
        const monto = parseFloat(document.getElementById('gasto_monto').value) || 0;
        const afecto = document.getElementById('gasto_afecto').value;
        const iva = parseFloat(document.getElementById('gasto_iva').value) || 0;

        if (!tipo || !gasto) {
            error('❌ Tipo y Gasto son obligatorios');
            return;
        }

        const nuevoGasto = { tipo, gasto, moneda, monto, afecto, iva };
        gastosLocales.push(nuevoGasto);
        actualizarTablaGastosLocales();
        limpiarFormularioGastos();
        exito('✅ Gasto local agregado');
    }

    // === Actualizar tabla y totales ===
    function actualizarTablaGastosLocales() {
    const tbody = document.getElementById('gastos-locales-body');
    if (!tbody) return;

    tbody.innerHTML = '';
    let totalVenta = 0, totalCosto = 0;

    gastosLocales.forEach((g, i) => {
        // ✅ Conversión defensiva: si no es número, usa 0
        const monto = typeof g.monto === 'number' ? g.monto : parseFloat(g.monto) || 0;
        const iva = typeof g.iva === 'number' ? g.iva : parseFloat(g.iva) || 0;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${g.tipo || ''}</td>
            <td>${g.gasto || ''}</td>
            <td>${g.moneda || 'CLP'}</td>
            <td style="text-align: right;">${monto.toFixed(2)}</td>
            <td>${g.afecto || 'SI'}</td>
            <td style="text-align: right;">${iva.toFixed(2)}</td>
            <td style="text-align: center;">
                <button type="button" class="btn-delete" onclick="eliminarGastoLocal(${i})" style="padding: 0.2rem 0.4rem;">🗑️</button>
            </td>
        `;
        tbody.appendChild(tr);

        if (g.tipo === 'Ventas') totalVenta += monto;
        if (g.tipo === 'Costo') totalCosto += monto;
    });

    // Actualizar totales
    document.getElementById('total-venta-gastos').textContent = totalVenta.toFixed(2);
    document.getElementById('total-costo-gastos').textContent = totalCosto.toFixed(2);
    const profit = totalVenta - totalCosto;
    const profitPct = totalVenta > 0 ? (profit / totalVenta) * 100 : 0;
    document.getElementById('profit-local').textContent = profit.toFixed(2);
    document.getElementById('profit-porcentaje').textContent = profitPct.toFixed(2) + ' %';
}

    // === Eliminar gasto local ===
    function eliminarGastoLocal(index) {
        mostrarConfirmacion('¿Eliminar gasto local?', () => {
            gastosLocales.splice(index, 1);
            actualizarTablaGastosLocales();
            exito('✅ Gasto local eliminado');
        });
    }

    // === Limpiar formulario ===
    function limpiarFormularioGastos() {
        document.getElementById('gasto_tipo').selectedIndex = 0;
        document.getElementById('gasto_gasto').innerHTML = '<option value="">Gastos</option>';
        document.getElementById('gasto_moneda').value = 'CLP';
        document.getElementById('gasto_monto').value = '';
        document.getElementById('gasto_afecto').value = 'SI';
        document.getElementById('gasto_iva').value = '';
    }

    // === Cerrar submodal ===
    function cerrarSubmodalGastosLocales() {
        // ✅ Guardar gastos locales en el servicio en memoria
        if (servicioEnEdicion !== null && servicios[servicioEnEdicion]) {
            servicios[servicioEnEdicion].gastos_locales = [...gastosLocales];
        }
        document.getElementById('submodal-gastos-locales').style.display = 'none';
    }

    // Asegurar disponibilidad global
    window.seleccionarProspecto = seleccionarProspecto;
    window.seleccionarYCerrar = seleccionarYCerrar;
    window.nuevoFormulario = nuevoFormulario;
    window.abrirSubmodalCostos = abrirSubmodalCostos;
</script>