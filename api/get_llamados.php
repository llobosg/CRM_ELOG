<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

try {
    $id_llamado = $_GET['id'] ?? null;
    if (!$id_llamado) throw new Exception('ID de llamado requerido');
    
    $stmt = $pdo->prepare("SELECT * FROM llamados WHERE id_llamado = ?");
    $stmt->execute([$id_llamado]);
    $llamado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$llamado) throw new Exception('Llamado no encontrado');
    
    echo json_encode(['success' => true, 'llamado' => $llamado]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>