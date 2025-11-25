<?php
// api/eliminar_servicio.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $idSrvc = $data['id_srvc'] ?? '';

    if (empty($idSrvc) || !is_string($idSrvc)) {
        throw new Exception('ID de servicio inválido');
    }

    // Verificar que el servicio exista
    $stmt = $pdo->prepare("SELECT id_srvc FROM servicios WHERE id_srvc = ?");
    $stmt->execute([$idSrvc]);
    if (!$stmt->fetch()) {
        throw new Exception('Servicio no encontrado');
    }

    // Eliminar costos y gastos asociados
    $pdo->prepare("DELETE FROM costos_servicios WHERE id_servicio = ?")->execute([$idSrvc]);
    $pdo->prepare("DELETE FROM gastos_locales_detalle WHERE id_servicio = ?")->execute([$idSrvc]);

    // Eliminar el servicio
    $stmt = $pdo->prepare("DELETE FROM servicios WHERE id_srvc = ?");
    $stmt->execute([$idSrvc]);

    echo json_encode(['success' => true, 'message' => 'Servicio eliminado correctamente']);
} catch (Exception $e) {
    error_log("Error en eliminar_servicio.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>