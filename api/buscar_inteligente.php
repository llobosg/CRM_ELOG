<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

$term = $_GET['term'] ?? '';
if (!$term) {
    echo json_encode([]);
    exit;
}

// Buscar por razón_social, rut_empresa, nombre_comercial, apellido_comercial, concatenado
$stmt = $pdo->prepare("
    SELECT 
        p.id_ppl, p.concatenado, p.razon_social, p.rut_empresa,
        c.nombre AS nombre_comercial, c.apellido AS apellido_comercial
    FROM prospectos p
    LEFT JOIN comerciales c ON p.id_comercial = c.id_comercial
    WHERE 
        p.razon_social LIKE ? OR
        p.rut_empresa LIKE ? OR
        p.concatenado LIKE ? OR
        c.nombre LIKE ? OR
        c.apellido LIKE ?
    LIMIT 10
");

$searchTerm = "%{$term}%";
$stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);
?>