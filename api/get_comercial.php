<?php
// api/get_comercial.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

// Usa fetchColumn() para obtener solo la columna 'nombre'
$stmt = $pdo->query("SELECT nombre FROM comerciales WHERE nombre IS NOT NULL ORDER BY nombre");
$comerciales = $stmt->fetchAll(PDO::FETCH_COLUMN); // ← Devuelve array de strings
echo json_encode(['comerciales' => $comerciales]);
?>