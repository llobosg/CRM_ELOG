<?php
// api/get_lugares_por_medio.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

$medio_parametro = $_GET['medio'] ?? '';
if (!$medio_parametro) {
    echo json_encode(['lugares' => []]);
    exit;
}

// --- CORRECCIÓN: Mapear medios específicos al genérico ---
$medio_para_busqueda = $medio_parametro;
if ($medio_parametro === 'Marítimo FCL' || $medio_parametro === 'Marítimo LCL') {
    $medio_para_busqueda = 'Marítimo';
} elseif ($medio_parametro === 'Aéreo Internacional' || $medio_parametro === 'Aéreo Nacional') { // Ejemplo adicional
    $medio_para_busqueda = 'Aéreo'; // Ejemplo adicional
}
// Puedes añadir más mapeos según sea necesario
// elseif ($medio_parametro === 'Terrestre Regional') { $medio_para_busqueda = 'Terrestre'; }
// --- FIN CORRECCIÓN ---

try {
    // Consulta: obtener detalle_lugar y pais_lugar basado en el medio mapeado
    $stmt = $pdo->prepare("
        SELECT detalle_lugar, pais_lugar 
        FROM lugares 
        WHERE medio_transporte = ?
        ORDER BY detalle_lugar
    ");
    $stmt->execute([$medio_para_busqueda]); // Usar el valor mapeado
    $lugares = [];
    while ($row = $stmt->fetch()) {
        $lugares[] = [
            'lugar' => $row['detalle_lugar'],
            'pais' => $row['pais_lugar']
        ];
    }
    echo json_encode(['lugares' => $lugares]);
} catch (Exception $e) {
    error_log("Error en get_lugares_por_medio.php: " . $e->getMessage());
    echo json_encode(['lugares' => []]);
}
?>