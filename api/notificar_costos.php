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
    $rol = $data['rol'] ?? '';

    // Validar estado
    if (!in_array($estado, ['solicitado', 'completado', 'revisado'])) {
        throw new Exception('Estado de costos no válido');
    }

    // Validar permisos
    if ($rol === 'comercial' && !in_array($estado, ['solicitado', 'revisado'])) {
        throw new Exception('Acción no permitida para Comercial');
    }
    if ($rol === 'pricing' && $estado !== 'completado') {
        throw new Exception('Acción no permitida para Pricing');
    }

    // Actualizar estado en BD
    $stmt = $pdo->prepare("
        UPDATE servicios 
        SET estado_costos = ?, 
            solicitado_por = CASE WHEN ? = 'solicitado' THEN ? ELSE solicitado_por END,
            completado_por = CASE WHEN ? = 'completado' THEN ? ELSE completado_por END,
            fecha_solicitado = CASE WHEN ? = 'solicitado' THEN NOW() ELSE fecha_solicitado END,
            fecha_completado = CASE WHEN ? = 'completado' THEN NOW() ELSE fecha_completado END
        WHERE id_srvc = ?
    ");
    $stmt->execute([
        $estado,
        $estado, $usuarioId,
        $estado, $usuarioId,
        $estado,
        $estado,
        $idSrvc
    ]);

    // === Enviar notificación (modo de prueba: log en lugar de mail) ===
    if ($estado === 'solicitado') {
        error_log("[NOTIFICACIÓN] ✉️ Comercial solicitó costos para servicio $idSrvc. Notificar a Pricing.");
        $mensaje = "Solicitud de costos enviada al equipo de Pricing.";
    } elseif ($estado === 'completado') {
        error_log("[NOTIFICACIÓN] ✉️ Pricing completó costos para servicio $idSrvc. Notificar al Comercial.");
        $mensaje = "Costos completados. Notificación enviada al Comercial.";
    } else {
        $mensaje = "Costos aprobados por el Comercial.";
    }

    echo json_encode(['success' => true, 'message' => $mensaje]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>