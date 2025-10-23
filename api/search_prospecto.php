<?php
header('Content-Type: application/json');

require_once '../config.php';

$term = $_GET['term'] ?? '';
if (!$term) {
    echo json_encode(['prospecto' => null]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM prospectos WHERE razon_social LIKE ? LIMIT 1");
    $stmt->execute(["%$term%"]);
    $prospecto = $stmt->fetch();

    if (!$prospecto) {
        echo json_encode(['prospecto' => null]);
        exit;
    }

    $stmt_serv = $pdo->prepare("SELECT * FROM servicios WHERE id_prospect = ?");
    $stmt_serv->execute([$prospecto['id_ppl']]);
    $servicios = $stmt_serv->fetchAll();

    echo json_encode([
        'prospecto' => $prospecto,
        'servicios' => $servicios
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Error en búsqueda']);
}