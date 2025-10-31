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
        SELECT 
            rut,
            razon_social,
            nacional_extranjero,
            pais,
            direccion,
            comuna,
            ciudad,
            giro,
            fecha_creacion,
            nombre_comercial,
            tipo_vida,
            fecha_vida,
            rubro,
            potencial_usd,
            fecha_alta_credito,
            plazo_dias,
            estado_credito,
            monto_credito,
            usado_credito,
            saldo_credito
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
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log("Error en buscar_cliente_inteligente: " . $e->getMessage());
    echo json_encode([]);
}
?>