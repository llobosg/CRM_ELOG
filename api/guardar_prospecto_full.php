<?php
header('Content-Type: application/json');
require_once '../config.php'; // tu conexión PDO: $pdo

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
    $id_comercial = $_POST['id_comercial'] ?? null;

    $estado       = $_POST['estado'] ?? 'Pendiente';
    $fecha        = $_POST['fecha_alta'] ?? date('Y-m-d');

    // =========================
    // VALIDACIONES
    // =========================
    if (!$razon_social) {
        throw new Exception('Razón social es obligatoria');
    }

    // =========================
    // INICIAR TRANSACCIÓN
    // =========================
    $pdo->beginTransaction();

    // =========================
    // 1. BUSCAR CLIENTE
    // =========================
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE rut = ?");
    $stmt->execute([$rut]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        $id_cliente = $cliente['id_cliente'];

        // =========================
        // MERGE (actualizar si cambió info)
        // =========================
        $stmt = $pdo->prepare("
            UPDATE clientes 
            SET razon_social = ?, direccion = ?, pais = ?, telefono = ?
            WHERE id_cliente = ?
        ");
        $stmt->execute([$razon_social, $direccion, $pais, $fono, $id_cliente]);

    } else {

        // =========================
        // CREAR NUEVO CLIENTE
        // =========================
        $stmt = $pdo->prepare("
            INSERT INTO clientes (razon_social, rut, direccion, pais, telefono, id_comercial, fecha_creacion)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $razon_social,
            $rut ?: null,
            $direccion,
            $pais,
            $fono,
            $id_comercial
        ]);

        $id_cliente = $pdo->lastInsertId();
    }

    // =========================
    // 2. CONTACTO PRIMARIO
    // =========================
    if ($contacto) {

        // eliminar primarios anteriores
        $pdo->prepare("UPDATE contactos SET primario = 'N' WHERE id_cliente = ?")
            ->execute([$id_cliente]);

        // insertar nuevo
        $stmt = $pdo->prepare("
            INSERT INTO contactos (id_cliente, nom_contacto, email, fono, primario)
            VALUES (?, ?, ?, ?, 'S')
        ");
        $stmt->execute([$id_cliente, $contacto, $email, $fono]);
    }

    // =========================
    // 3. CREAR PROSPECTO
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
    ':id_comercial' => $_POST['id_comercial'] ?? null,
    ':nombre' => $_POST['nombre'] ?? null
]);

    $id_prospecto = $pdo->lastInsertId();

    // =========================
    // COMMIT
    // =========================
    $pdo->commit();

    echo json_encode([
        'ok' => true,
        'id_prospecto' => $id_prospecto,
        'id_cliente' => $id_cliente
    ]);

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}