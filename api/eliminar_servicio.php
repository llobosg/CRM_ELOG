<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

$id_srvc = $_POST['id_srvc'] ?? '';

if (!$id_srvc) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM servicios WHERE id_srvc = ?");
    $stmt->execute([$id_srvc]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}