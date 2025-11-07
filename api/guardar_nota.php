<?php
// api/guardar_nota.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $id_ppl = (int)($input['id_ppl'] ?? 0);
    $campo = $input['campo'] ?? ''; // 'notas_comerciales' o 'notas_operaciones'
    $valor = $input['valor'] ?? '';

    if (!$id_ppl || !in_array($campo, ['notas_comerciales', 'notas_operaciones'])) {
        throw new Exception('Datos inválidos');
    }

    $stmt = $pdo->prepare("UPDATE prospectos SET $campo = ? WHERE id_ppl = ?");
    $stmt->execute([$valor, $id_ppl]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>