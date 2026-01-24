<?php
// api/get_comercial.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$stmt = $pdo->query("SELECT id_comercial, nombre FROM comerciales WHERE nombre IS NOT NULL ORDER BY nombre");
$comerciales = $stmt->fetchAll(PDO::FETCH_ASSOC); // ← Devuelve array de objetos {id_comercial, nombre}
echo json_encode(['comerciales' => $comerciales]);
?>