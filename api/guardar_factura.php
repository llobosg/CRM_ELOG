<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin_finanzas') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id_ppl = (int)($data['id_ppl'] ?? 0);
    $numero = $data['numero'] ?? '';
    $emision = $data['emision'] ?? '';
    $vencimiento = $data['vencimiento'] ?? '';
    $estado = $data['estado'] ?? 'emitida';
    $notas = $data['notas'] ?? '';
    $rut_cliente = $data['rut'] ?? '';

    if (!$id_ppl || !$numero || !$emision || !$vencimiento) {
        throw new Exception('Datos incompletos');
    }

    $pdo->beginTransaction();

    // Obtener datos del prospecto
    $stmt_p = $pdo->prepare("SELECT concatenado, razon_social, total_venta FROM prospectos WHERE id_ppl = ?");
    $stmt_p->execute([$id_ppl]);
    $prospecto = $stmt_p->fetch();
    if (!$prospecto) throw new Exception('Prospecto no encontrado');

    // Verificar si ya existe factura
    $stmt_check = $pdo->prepare("SELECT id_factura FROM facturas WHERE id_ppl = ?");
    $stmt_check->execute([$id_ppl]);
    $factura_existente = $stmt_check->fetch();

    if ($factura_existente) {
        $stmt = $pdo->prepare("
            UPDATE facturas 
            SET numero_factura = ?, fecha_emision = ?, fecha_vencimiento = ?, 
                estado = ?, notas = ?
            WHERE id_ppl = ?
        ");
        $stmt->execute([$numero, $emision, $vencimiento, $estado, $notas, $id_ppl]);
        $mensaje = 'Factura actualizada correctamente';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO facturas (
                id_ppl, concatenado, rut_cliente, razon_social, total_venta,
                fecha_emision, fecha_vencimiento, estado, numero_factura, notas, creado_por
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $id_ppl,
            $prospecto['concatenado'],
            $rut_cliente,
            $prospecto['razon_social'],
            $prospecto['total_venta'],
            $emision,
            $vencimiento,
            $estado,
            $numero,
            $notas,
            $_SESSION['user_id']
        ]);
        $mensaje = 'Factura registrada correctamente';
    }

    // ✅ Liberar crédito si estado = "pagada"
    if ($estado === 'pagada') {
        $stmt_update = $pdo->prepare("
            UPDATE clientes 
            SET usado_credito = GREATEST(0, usado_credito - ?) 
            WHERE rut = ?
        ");
        $stmt_update->execute([$prospecto['total_venta'], $rut_cliente]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => $mensaje]);

} catch (Exception $e) {
    $pdo->rollback();
    error_log("guardar_factura error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al procesar la factura']);
}
?>