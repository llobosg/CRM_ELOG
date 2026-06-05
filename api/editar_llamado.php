<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || empty($data['id_llamado'])) throw new Exception('ID de llamado requerido');
    
    // Validar que el comercial sea el dueño del llamado
    $stmt = $pdo->prepare("SELECT id_comercial FROM llamados WHERE id_llamado = ?");
    $stmt->execute([$data['id_llamado']]);
    $comercial = $stmt->fetchColumn();
    
    if ($comercial != $_SESSION['user_id'] && $_SESSION['rol'] !== 'admin') {
        throw new Exception('No autorizado para editar este llamado');
    }
    
    // Actualizar llamado
    $stmt = $pdo->prepare("
        UPDATE llamados SET 
            fecha = ?, hora = ?, tipo_gestion = ?, nota = ?
        WHERE id_llamado = ?
    ");
    
    $stmt->execute([
        $data['fecha'],
        $data['hora'],
        $data['tipo_gestion'],
        $data['nota'],
        $data['id_llamado']
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Llamado actualizado correctamente']);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>