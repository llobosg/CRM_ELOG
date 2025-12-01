<?php
// api/get_contacto_primario.php

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

$rut_cliente = $_GET['rut_cliente'] ?? null;

if (!$rut_cliente) {
    echo json_encode(['success' => false, 'message' => 'RUT de cliente no proporcionado.']);
    exit;
}

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        SELECT nom_contacto, email
        FROM contactos
        WHERE rut_cliente = ? AND primario = 'S'
        LIMIT 1
    ");
    $stmt->execute([$rut_cliente]);
    $contacto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($contacto) {
        echo json_encode(['success' => true, 'contacto' => $contacto]);
    } else {
        echo json_encode(['success' => true, 'contacto' => null]); // Éxito, pero sin contacto encontrado
    }

} catch (PDOException $e) {
    error_log("Error en get_contacto_primario.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
}
?>