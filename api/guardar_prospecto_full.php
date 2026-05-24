<?php
ob_start(); // 🔥 captura cualquier salida basura
header('Content-Type: application/json');

require_once '../config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

try {

    // =========================
    // INPUTS
    // =========================
    $razon_social = trim($_POST['razon_social'] ?? '');
    $rut          = trim($_POST['rut_empresa'] ?? '');
    $direccion    = trim($_POST['direccion'] ?? '');
    $pais         = trim($_POST['pais'] ?? '');
    $fono         = trim($_POST['fono_empresa'] ?? '');
    $contacto     = trim($_POST['contacto'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $id_comercial = isset($_POST['id_comercial']) && $_POST['id_comercial'] !== '' ? (int) $_POST['id_comercial'] : null;

    $estado       = $_POST['estado'] ?? 'Pendiente';
    $fecha        = $_POST['fecha_alta'] ?? date('Y-m-d');

    error_log("ID_COMERCIAL INPUTs: " . var_export($id_comercial, true));

    if (!$razon_social) {
        throw new Exception('Razón social es obligatoria');
    }

    $pdo->beginTransaction();

    // =========================
    // 1. CLIENTE
    // =========================
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE rut = ?");
    $stmt->execute([$rut]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {

        $id_cliente = $cliente['id_cliente'];

        // UPDATE SIN telefono ❌
        $stmt = $pdo->prepare("
            UPDATE clientes 
            SET razon_social = ?, direccion = ?, pais = ?
            WHERE id_cliente = ?
        ");

        $stmt->execute([
            $razon_social,
            $direccion,
            $pais,
            $id_cliente
        ]);

    } else {

        // INSERT SIN telefono ❌
        $stmt = $pdo->prepare("
            INSERT INTO clientes (
                razon_social,
                rut,
                direccion,
                pais,
                id_comercial,
                fecha_creacion
            )
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $razon_social,
            $rut ?: null,
            $direccion,
            $pais,
            $id_comercial
        ]);

        $id_cliente = $pdo->lastInsertId();
    }

    // =========================
    // 2. CONTACTO (USANDO rut_cliente ✅)
    // =========================
    if ($contacto && $rut) {

        // limpiar primarios
        $pdo->prepare("UPDATE contactos SET primario = 'N' WHERE rut_cliente = ?")
            ->execute([$rut]);

        // insertar contacto
        $stmt = $pdo->prepare("
            INSERT INTO contactos (
                rut_cliente,
                nom_contacto,
                email,
                fono_contacto,
                primario
            )
            VALUES (?, ?, ?, ?, 'S')
        ");

        $stmt->execute([
            $rut,
            $contacto,
            $email,
            $fono
        ]);
    }

    // =========================
    // 3. PROSPECTO
    // =========================
    $stmt = $pdo->prepare("
        INSERT INTO prospectos (
            razon_social,
            rut_empresa,
            fono_empresa,
            pais,
            direccion,
            operacion,
            tipo_oper,
            fecha_alta,
            estado,
            concatenado,
            booking,
            notas_comerciales,
            notas_operaciones,
            id_comercial,
            nombre
        ) VALUES (
            :razon_social,
            :rut_empresa,
            :fono_empresa,
            :pais,
            :direccion,
            :operacion,
            :tipo_oper,
            :fecha_alta,
            :estado,
            :concatenado,
            :booking,
            :notas_comerciales,
            :notas_operaciones,
            :id_comercial,
            :nombre
        )
    ");

    $stmt->execute([
        ':razon_social' => $_POST['razon_social'] ?? null,
        ':rut_empresa' => $_POST['rut_empresa'] ?? null,
        ':fono_empresa' => $_POST['fono_empresa'] ?? null,
        ':pais' => $_POST['pais'] ?? null,
        ':direccion' => $_POST['direccion'] ?? null,
        ':operacion' => $_POST['operacion'] ?? null,
        ':tipo_oper' => $_POST['tipo_oper'] ?? null,
        ':fecha_alta' => $_POST['fecha_alta'] ?? date('Y-m-d'),
        ':estado' => $_POST['estado'] ?? 'Pendiente',
        ':concatenado' => $_POST['concatenado'] ?? null,
        ':booking' => $_POST['booking'] ?? null,
        ':notas_comerciales' => $_POST['notas_comerciales'] ?? null,
        ':notas_operaciones' => $_POST['notas_operaciones'] ?? null,
        ':id_comercial' => $id_comercial,
        ':nombre' => $_POST['nombre'] ?? null
    ]);
    error_log("ID_COMERCIAL PROSPECTO: " . var_export($id_comercial, true));

    $id_prospecto = $pdo->lastInsertId();

    $pdo->commit();

    ob_end_clean();

    echo json_encode([
        'ok' => true,
        'id_prospecto' => $id_prospecto,
        'id_cliente' => $id_cliente,
        'id_ppl' => $id_prospecto,
        'concatenado' => $_POST['concatenado'] ?? null
    ]);

    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // 🔥 limpiar cualquier salida previa (HTML, warnings, etc)
    ob_clean();

    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);

    exit;
}