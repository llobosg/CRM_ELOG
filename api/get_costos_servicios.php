<?php
header('Content-Type: application/json');
require_once '../config.php';
$id = $_GET['id'] ?? '';
if (!$id) exit('{"costos":[]}');
$stmt = $pdo->prepare("SELECT * FROM costos_servicios WHERE id_servicio = ?");
$stmt->execute([$id]);
echo json_encode(['costos' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
?>