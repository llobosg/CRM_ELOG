<?php
// api/buscar_cliente_inteligente.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

try {
    $term = $_GET['term'] ?? '';
    if (!$term) {
        echo json_encode([]);
        exit;
    }
    $search = "%{$term}%";
    $stmt = $pdo->prepare("
        SELECT rut, razon_social, giro, nombre_comercial
        FROM clientes
        WHERE 
            rut LIKE ? OR
            razon_social LIKE ? OR
            giro LIKE ? OR
            nombre_comercial LIKE ?
        ORDER BY razon_social
        LIMIT 10
    ");
    $stmt->execute([$search, $search, $search, $search]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([]);
}
?>