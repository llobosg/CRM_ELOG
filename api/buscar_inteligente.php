<?php
// api/buscar_inteligente.php
header('Content-Type: application/json; charset=utf-8');

// Suprimir errores en producción
if (!defined('DEVELOPMENT')) {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Intentar cargar la configuración
try {
    require_once __DIR__ . '/../config.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al cargar la configuración']);
    exit;
}

if (!isset($pdo) || !$pdo instanceof PDO) {
    http_response_code(500);
    echo json_encode(['error' => 'Conexión a base de datos no disponible']);
    exit;
}

$term = $_GET['term'] ?? '';
if (empty(trim($term))) {
    echo json_encode([]);
    exit;
}

try {
    $searchTerm = "%{$term}%";

    $stmt = $pdo->prepare("
        SELECT 
            p.id_ppl, 
            p.concatenado, 
            p.razon_social, 
            p.rut_empresa,
            COALESCE(c.nombre, '') AS nombre_comercial,
            COALESCE(c.apellido, '') AS apellido_comercial
        FROM prospectos p
        LEFT JOIN comerciales c ON p.id_comercial = c.id_comercial
        WHERE 
            p.razon_social LIKE ? OR
            p.rut_empresa LIKE ? OR
            p.concatenado LIKE ? OR
            c.nombre LIKE ? OR
            c.apellido LIKE ?
        LIMIT 10
    ");

    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Registrar error en logs si es necesario
    error_log("Error en buscar_inteligente.php: " . $e->getMessage());
    
    if (defined('DEVELOPMENT') && DEVELOPMENT) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    } else {
        http_response_code(500);
        echo json_encode([]);
    }
}
?>