<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php'

try {
    // Solo admin_finanzas
    if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin_finanzas') {
        echo json_encode(['prospectos' => []]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT 
            p.id_ppl, p.concatenado, p.rut_empresa, p.razon_social, 
            p.total_venta, p.fecha_alta,
            f.id_factura, f.numero_factura, f.estado as estado_factura
        FROM prospectos p
        LEFT JOIN facturas f ON p.id_ppl = f.id_ppl
        WHERE p.estado = 'CerradoOK'
        ORDER BY p.fecha_alta DESC
    ");
    $stmt->execute();
    $prospectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['prospectos' => $prospectos]);
} catch (Exception $e) {
    error_log("get_prospectos_cerrados error: " . $e->getMessage());
    echo json_encode(['prospectos' => []]);
}
?>