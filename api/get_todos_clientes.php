<?php
// api/get_todos_clientes.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

try {
    $stmt = $pdo->query("SELECT rut, razon_social FROM clientes ORDER BY razon_social");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['clientes' => $clientes]);
} catch (Exception $e) {
    echo json_encode(['clientes' => []]);
}
?>