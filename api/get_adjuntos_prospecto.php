<?php
// api/get_adjuntos_prospecto.php
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

$id_prospect = $_GET['id_prospect'] ?? null;

if (!$id_prospect) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de prospecto no proporcionado.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id_adjunto, nombre_archivo, ruta_archivo, tipo_mime, tamano_bytes, fecha_subida
        FROM adjuntos_prospectos
        WHERE id_prospect = ?
        ORDER BY fecha_subida DESC
    ");
    $stmt->execute([$id_prospect]);
    $adjuntos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'adjuntos' => $adjuntos]);

} catch (PDOException $e) {
    error_log("Error al obtener adjuntos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno al obtener los adjuntos.']);
}
?>