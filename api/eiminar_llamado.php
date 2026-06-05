<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

try {
    $id_llamado = $_POST['id_llamado'] ?? null;
    if (!$id_llamado) throw new Exception('ID de llamado requerido');
    
    // Validar que el comercial sea el dueño del llamado
    $stmt = $pdo->prepare("SELECT id_comercial FROM llamados WHERE id_llamado = ?");
    $stmt->execute([$id_llamado]);
    $comercial = $stmt->fetchColumn();
    
    if ($comercial != $_SESSION['user_id'] && $_SESSION['rol'] !== 'admin') {
        throw new Exception('No autorizado para eliminar este llamado');
    }
    
    $stmt = $pdo->prepare("DELETE FROM llamados WHERE id_llamado = ?");
    $stmt->execute([$id_llamado]);
    
    echo json_encode(['success' => true, 'message' => 'Llamado eliminado correctamente']);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>