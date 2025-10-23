<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT DISTINCT operacion FROM operacion ORDER BY operacion");
    $operaciones = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['operaciones' => $operaciones]); // ← clave: "operaciones"
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>