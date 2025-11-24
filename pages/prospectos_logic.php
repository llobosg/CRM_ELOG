<?php
// pages/prospectos_logic.php
// ✅ Solo gestiona el prospecto. Los servicios se guardan desde el modal.
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

        $pdo->commit();

        $mensaje_exito = $modo_update
            ? 'Prospecto actualizado correctamente'
            : 'Prospecto creado correctamente';

        $redirect_url = $_SERVER['PHP_SELF'] . "?page=prospectos&exito=" . urlencode($mensaje_exito) . "&id_ppl=" . $id_ppl;
        header("Location: " . $redirect_url);
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