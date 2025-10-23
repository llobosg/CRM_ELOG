<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $id_ppl = (int)($input['id_ppl'] ?? 0);
    $campo = $input['campo'] ?? '';
    $valor = $input['valor'] ?? '';

    if (!$id_ppl) {
        throw new Exception('ID de prospecto inválido');
    }
    if (!in_array($campo, ['notas_comerciales', 'notas_operaciones'])) {
        throw new Exception('Campo no permitido');
    }

    $stmt = $pdo->prepare("UPDATE prospectos SET $campo = ? WHERE id_ppl = ?");
    $stmt->execute([$valor, $id_ppl]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}