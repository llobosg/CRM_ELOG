<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $tipo = $_GET['tipo'] ?? '';
    if (!in_array($tipo, ['Costo', 'Ventas'])) {
        echo json_encode(['gastos' => []], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare("SELECT gasto FROM gastos_locales WHERE tipo = ? ORDER BY gasto ASC");
    $stmt->execute([$tipo]);
    $gastos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['gastos' => $gastos], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['gastos' => []], JSON_UNESCAPED_UNICODE);
}