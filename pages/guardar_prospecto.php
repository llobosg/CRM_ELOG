<?php
require_once '../config/db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);

$cliente = $input['cliente'];
$prospecto = $input['prospecto'];

$conn->begin_transaction();

try {

    // =========================================
    // 1. BUSCAR CLIENTE EXISTENTE (ANTI DUP)
    // =========================================
    $stmt = $conn->prepare("
        SELECT id_cliente 
        FROM clientes 
        WHERE email = ? OR telefono = ?
        LIMIT 1
    ");
    $stmt->bind_param("ss", $cliente['email'], $cliente['telefono']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        // 🟡 CLIENTE EXISTE → USAR ID
        $id_cliente = $row['id_cliente'];

        // 🔁 MERGE AUTOMÁTICO (actualiza si viene info nueva)
        $stmtUpdate = $conn->prepare("
            UPDATE clientes 
            SET nombre = COALESCE(NULLIF(?, ''), nombre),
                email = COALESCE(NULLIF(?, ''), email),
                telefono = COALESCE(NULLIF(?, ''), telefono)
            WHERE id_cliente = ?
        ");
        $stmtUpdate->bind_param("ssi", 
            $cliente['nombre'], 
            $cliente['email'], 
            $cliente['telefono'], 
            $id_cliente
        );
        $stmtUpdate->execute();

    } else {

        // 🟢 CREAR NUEVO CLIENTE
        $stmtInsert = $conn->prepare("
            INSERT INTO clientes (nombre, email, telefono)
            VALUES (?, ?, ?)
        ");
        $stmtInsert->bind_param("sss", 
            $cliente['nombre'], 
            $cliente['email'], 
            $cliente['telefono']
        );
        $stmtInsert->execute();

        $id_cliente = $stmtInsert->insert_id;
    }

    // =========================================
    // 2. CREAR PROSPECTO
    // =========================================
    $stmtPros = $conn->prepare("
        INSERT INTO prospectos 
        (id_cliente, titulo, origen, estado, monto, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");

    $stmtPros->bind_param("isssd",
        $id_cliente,
        $prospecto['titulo'],
        $prospecto['origen'],
        $prospecto['estado'],
        $prospecto['monto']
    );

    $stmtPros->execute();

    // =========================================
    // COMMIT
    // =========================================
    $conn->commit();

    echo json_encode([
        "success" => true,
        "id_cliente" => $id_cliente
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}