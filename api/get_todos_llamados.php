<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

try {
    $filtro_sql = '';
    $params = [];
    
    if ($_SESSION['rol'] === 'comercial') {
        $filtro_sql = 'WHERE l.id_comercial = ?';
        $params = [$_SESSION['user_id']];
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            l.id_llamado,          -- ✅ Agregar ID
            l.nombre_comercial AS comercial,
            l.razon_social AS cliente,
            CONCAT(l.fecha, ' ', l.hora) AS fecha_completa,
            l.tipo_gestion AS tipo_llamado,
            l.nota,
            l.created_at
        FROM llamados l
        $filtro_sql
        ORDER BY l.fecha DESC, l.hora DESC
    ");
    $stmt->execute($params);
    $llamados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'llamados' => $llamados]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>