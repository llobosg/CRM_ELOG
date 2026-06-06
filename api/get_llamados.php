<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

try {
    $id_prospecto = $_GET['id_prospecto'] ?? null;
    if (!$id_prospecto) throw new Exception('ID de prospecto requerido');
    
    $stmt = $pdo->prepare("
        SELECT * FROM llamados 
        WHERE id_prospecto = ? 
        ORDER BY fecha DESC, hora DESC
    ");
    $stmt->execute([$id_prospecto]);
    $llamados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'llamados' => $llamados]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>