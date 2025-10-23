<?php
require_once __DIR__ . '/../config.php';

try {
    $stmt = $pdo->query("SELECT nombre_pnac FROM proveedor_pnac WHERE nombre_pnac IS NOT NULL ORDER BY nombre_pnac");
    $proveedores = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['proveedores' => $proveedores]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al cargar proveedores']);
}
?>