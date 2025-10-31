<?php
// api/get_comercial.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

try {
    $stmt = $pdo->query("SELECT nombre FROM comerciales WHERE nombre IS NOT NULL ORDER BY nombre");
    $comerciales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['comerciales' => $comerciales]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['comerciales' => []]);
}
?>