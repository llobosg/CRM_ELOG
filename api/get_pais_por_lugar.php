<?php
require_once __DIR__ . '/../config.php';
$lugar = $_GET['lugar'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT pais_lugar FROM lugares WHERE detalle_lugar = ? LIMIT 1");
    $stmt->execute([$lugar]);
    $pais = $stmt->fetchColumn();
    echo json_encode(['pais' => $pais ?: '']);
} catch (Exception $e) {
    echo json_encode(['pais' => '']);
}
?>