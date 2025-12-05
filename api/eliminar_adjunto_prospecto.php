<?php
// api/eliminar_adjunto_prospecto.php
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php'; // Asegura que el usuario esté autenticado

$data = json_decode(file_get_contents('php://input'), true);
$id_adjunto = (int)($data['id_adjunto'] ?? 0);

if (!$id_adjunto) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de adjunto no proporcionado.']);
    exit;
}

try {
    // Obtener la ruta del archivo antes de eliminar el registro
    $stmt_path = $pdo->prepare("SELECT ruta_archivo FROM adjuntos_prospectos WHERE id_adjunto = ?");
    $stmt_path->execute([$id_adjunto]);
    $fila = $stmt_path->fetch(PDO::FETCH_ASSOC);

    if (!$fila) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Adjunto no encontrado.']);
        exit;
    }

    $rutaRelativa = $fila['ruta_archivo'];
    $rutaAbsoluta = __DIR__ . '/../' . ltrim($rutaRelativa, '/'); // Asegura ruta absoluta

    // Eliminar el archivo físico del servidor
    if (file_exists($rutaAbsoluta)) {
        if (!unlink($rutaAbsoluta)) {
            // Opcional: Loggear error al eliminar archivo físico, pero continuar con la eliminación del registro
            error_log("Advertencia: No se pudo eliminar el archivo físico: $rutaAbsoluta");
            // No se lanza una excepción aquí para no dejar registros huérfanos en la DB si falla la eliminación del archivo.
        }
    } else {
         // Opcional: Loggear que el archivo físico no existe, pero continuar con la eliminación del registro
         error_log("Advertencia: El archivo físico no existe en la ruta: $rutaAbsoluta");
         // No se lanza una excepción aquí.
    }

    // Eliminar el registro de la base de datos
    $stmt_delete = $pdo->prepare("DELETE FROM adjuntos_prospectos WHERE id_adjunto = ?");
    $stmt_delete->execute([$id_adjunto]);

    echo json_encode(['success' => true, 'message' => 'Archivo eliminado correctamente.']);

} catch (PDOException $e) {
    error_log("Error al eliminar adjunto: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno al eliminar el archivo.']);
}
?>