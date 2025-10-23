<?php
header('Content-Type: application/json');
require_once '../config.php'; // Ajusta la ruta según tu estructura

$rut = $_GET['rut'] ?? '';

if (!$rut) {
    echo json_encode(['success' => false, 'message' => 'No se proporcionó RUT']);
    exit;
}

// Limpiar RUT para comparar (sin puntos ni guiones)
$cleanRut = preg_replace('/[^0-9Kk]/', '', $rut);

$stmt = $pdo->prepare("SELECT * FROM prospectos WHERE REPLACE(REPLACE(rut_empresa, '.', ''), '-', '') = ?");
$stmt->execute([$cleanRut]);
$prospecto = $stmt->fetch(PDO::FETCH_ASSOC);

if ($prospecto) {
    // Obtener servicios
    $stmtServ = $pdo->prepare("SELECT * FROM servicios WHERE id_prospect = ?");
    $stmtServ->execute([$prospecto['id_ppl']]);
    $servicios = $stmtServ->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'prospecto' => $prospecto,
        'servicios' => $servicios
    ]);
} else {
    echo json_encode(['success' => false]);
}
?>