<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $id_ppl = (int)($input['id_ppl'] ?? 0);

    if (!$id_ppl) {
        throw new Exception('ID de prospecto inválido');
    }

    // Verificar que no tenga servicios
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM servicios WHERE id_prospect = ?");
    $stmt->execute([$id_ppl]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('El prospecto tiene servicios asociados y no puede eliminarse');
    }

    // Eliminar prospecto
    $stmt = $pdo->prepare("DELETE FROM prospectos WHERE id_ppl = ?");
    $stmt->execute([$id_ppl]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}