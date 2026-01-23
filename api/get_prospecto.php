<?php
// /api/get_prospecto.php
header('Content-Type: application/json; charset=utf-8');

// Evitar salida de errores en producción
error_reporting(0);

try {
    require_once __DIR__ . '/../config.php';

    $id = $_GET['id'] ?? null;
    if (!$id) {
        throw new Exception('ID de prospecto no especificado');
    }

    // === Cargar prospecto ===
    $stmt = $pdo->prepare("SELECT * FROM prospectos WHERE id_ppl = ?");
    $stmt->execute([$id]);
    $prospecto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$prospecto) {
        throw new Exception('Prospecto no encontrado');
    }

    // === Cargar servicios con costo y venta calculados desde costos_servicios ===
    $stmt = $pdo->prepare("
        SELECT 
            s.*,
            COALESCE(cs_data.total_costo, 0) AS costo,
            COALESCE(cs_data.total_venta, 0) AS venta
        FROM servicios s
        LEFT JOIN (
            SELECT 
                cs.id_servicio,
                SUM(cs.qty * cs.costo) AS total_costo,
                SUM(cs.qty * cs.tarifa) AS total_venta
            FROM costos_servicios cs
            GROUP BY cs.id_servicio
        ) cs_data ON s.id_srvc = cs_data.id_servicio
        WHERE s.id_prospect = ?
        ORDER BY s.id_srvc
    ");
    $stmt->execute([$id]);
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'prospecto' => $prospecto,
        'servicios' => $servicios
    ]);

    // === Cargar costos y gastos por servicio ===
    $serviciosConDetalles = [];
    foreach ($servicios as $s) {
        // Costos
        $stmtCostos = $pdo->prepare("SELECT * FROM costos_servicios WHERE id_servicio = ?");
        $stmtCostos->execute([$s['id_srvc']]);
        $costos = $stmtCostos->fetchAll(PDO::FETCH_ASSOC);

        // Gastos locales
        $stmtGastos = $pdo->prepare("SELECT * FROM gastos_locales_detalle WHERE id_servicio = ?");
        $stmtGastos->execute([$s['id_srvc']]);
        $gastos = $stmtGastos->fetchAll(PDO::FETCH_ASSOC);

        $serviciosConDetalles[] = array_merge($s, [
            'costos' => $costos,
            'gastos_locales' => $gastos
        ]);
    }

    echo json_encode([
        'success' => true,
        'prospecto' => $prospecto,
        'servicios' => $serviciosConDetalles
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>