<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        throw new Exception('ID de prospecto inválido');
    }

    // === Cargar prospecto ===
    $stmt = $pdo->prepare("SELECT * FROM prospectos WHERE id_ppl = ?");
    $stmt->execute([$id]);
    $prospecto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$prospecto) {
        echo json_encode(['success' => false, 'message' => 'Prospecto no encontrado']);
        exit;
    }

    // === Cargar servicios ===
    $stmt = $pdo->prepare("SELECT * FROM servicios WHERE id_prospect = ? ORDER BY id_srvc");
    $stmt->execute([$id]);
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // === Procesar costos y gastos locales para cada servicio ===
    foreach ($servicios as &$servicio) {
        // --- Costos ---
        $stmt_costos = $pdo->prepare("SELECT * FROM costos_servicios WHERE id_servicio = ? ORDER BY concepto");
        $stmt_costos->execute([$servicio['id_srvc']]);
        $costos = $stmt_costos->fetchAll(PDO::FETCH_ASSOC);
        foreach ($costos as &$c) {
            $c['qty'] = (float)$c['qty'];
            $c['costo'] = (float)$c['costo'];
            $c['total_costo'] = (float)$c['total_costo'];
            $c['tarifa'] = (float)$c['tarifa'];
            $c['total_tarifa'] = (float)$c['total_tarifa'];
        }
        $servicio['costos'] = $costos;

        // --- Gastos locales ---
        $stmt_gastos = $pdo->prepare("SELECT * FROM gastos_locales_detalle WHERE id_servicio = ? ORDER BY tipo, gasto");
        $stmt_gastos->execute([$servicio['id_srvc']]);
        $gastos = $stmt_gastos->fetchAll(PDO::FETCH_ASSOC);
        foreach ($gastos as &$g) {
            // ✅ Conversión explícita a float
            $g['monto'] = (float)$g['monto'];
            $g['iva'] = (float)$g['iva'];
        }
        $servicio['gastos_locales'] = $gastos;
    }

    echo json_encode([
        'success' => true,
        'prospecto' => $prospecto,
        'servicios' => $servicios
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}