<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

$trafico = $_GET['trafico'] ?? '';
if (!$trafico) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT subtrafico FROM trafico WHERE trafico = ? AND subtrafico IS NOT NULL AND TRIM(subtrafico) != '' GROUP BY subtrafico");
    $stmt->execute([$trafico]);
    $subtraficos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Limpiar y asegurar que sean strings no vacíos
    $subtraficos = array_map('trim', $subtraficos);
    $subtraficos = array_filter($subtraficos, 'strlen');

    // Devolver como array indexed (no asociativo)
    echo json_encode(array_values($subtraficos));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([]);
}