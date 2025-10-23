<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM comerciales WHERE id_comercial = ?");
    $stmt->execute([$id]);
    $comercial = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($comercial) {
        echo json_encode(['comercial' => $comercial]);
    } else {
        echo json_encode(['comercial' => null]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error']);
}