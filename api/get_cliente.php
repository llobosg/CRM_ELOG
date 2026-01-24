<?php
// /api/get_cliente.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$rut = $_GET['rut'] ?? '';
if (!$rut) {
    echo json_encode(['existe' => false, 'message' => 'RUT no proporcionado']);
    exit;
}

// Limpiar RUT (eliminar puntos y guión)
$rut_limpio = preg_replace('/[^0-9kK]/', '', strtolower($rut));

try {
    $stmt = $pdo->prepare("SELECT 
        rut, razon_social, nacional_extranjero, pais, direccion, 
        comuna, ciudad, giro, fecha_creacion, 
        id_comercial, nombre_comercial,  -- ✅ Campos clave
        tipo_vida, fecha_vida, rubro, potencial_usd,
        fecha_alta_credito, plazo_dias, estado_credito, 
        monto_credito, usado_credito, saldo_credito
    FROM clientes 
    WHERE rut = ?");
    $stmt->execute([$rut_limpio]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        // Asegurar que id_comercial sea entero (o null)
        $cliente['id_comercial'] = $cliente['id_comercial'] ? (int)$cliente['id_comercial'] : null;
        echo json_encode(['existe' => true, 'cliente' => $cliente]);
    } else {
        echo json_encode(['existe' => false, 'message' => 'Cliente no encontrado']);
    }
    error_log("CLIENTE CONSULTADO - RUT: {$rut_limpio}, ID_COMERCIAL: " . ($cliente['id_comercial'] ?? 'NULL'));
} catch (Exception $e) {
    error_log("Error en get_cliente.php: " . $e->getMessage());
    echo json_encode(['existe' => false, 'message' => 'Error al consultar cliente']);
}
?>