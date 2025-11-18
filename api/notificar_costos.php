<?php
// api/notificar_costos.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $idSrvc = $data['id_srvc'] ?? '';
    $estado = $data['estado'] ?? '';
    $usuarioId = (int)($data['usuario_id'] ?? 0);

    if (!$idSrvc || !in_array($estado, ['solicitado', 'completado', 'revisado'])) {
        throw new Exception('Datos inválidos para notificación de costos');
    }

    // Actualizar estado en la base de datos
    $stmt = $pdo->prepare("
        UPDATE servicios 
        SET estado_costos = ?, 
            fecha_solicitado = CASE WHEN ? = 'solicitado' THEN NOW() ELSE fecha_solicitado END,
            solicitado_por = CASE WHEN ? = 'solicitado' THEN ? ELSE solicitado_por END
        WHERE id_srvc = ?
    ");
    $stmt->execute([$estado, $estado, $estado, $usuarioId, $idSrvc]);

    // Mensaje según el estado
    $mensaje = match($estado) {
        'solicitado' => 'Solicitud de costos enviada al equipo de Pricing.',
        'completado' => 'Costos marcados como completados.',
        'revisado' => 'Costos aprobados por el Comercial.',
        default => 'Estado actualizado.'
    };

    echo json_encode(['success' => true, 'message' => $mensaje]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>