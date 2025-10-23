<?php
require_once __DIR__ . '/../config.php';

try {
    $stmt = $pdo->query("SELECT razon_social FROM agentes WHERE razon_social IS NOT NULL ORDER BY razon_social");
    $agentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['agentes' => $agentes]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al cargar agentes']);
}
?>