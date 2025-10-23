<?php
// api/get_aplicaciones_costos.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

try {
    // Obtener el medio de transporte desde la URL
    $medio = $_GET['medio'] ?? '';
    
    // Si no hay medio, devolver todas las aplicaciones
    if (empty($medio)) {
        $stmt = $pdo->query("SELECT DISTINCT aplica FROM aplicacion_costos WHERE aplica IS NOT NULL AND aplica != '' ORDER BY aplica");
    } else {
        // Filtrar por medio_transporte
        $stmt = $pdo->prepare("SELECT DISTINCT aplica FROM aplicacion_costos WHERE medio_transporte = ? AND aplica IS NOT NULL AND aplica != '' ORDER BY aplica");
        $stmt->execute([$medio]);
    }
    
    $aplicaciones = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['aplicaciones' => $aplicaciones]);
    
} catch (Exception $e) {
    error_log("Error en get_aplicaciones_costos.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error al cargar aplicaciones']);
}
?>