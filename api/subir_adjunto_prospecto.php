<?php
// api/subir_adjunto_prospecto.php
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

// Directorio donde se guardarán los archivos
$uploadDir = __DIR__ . '/../adjuntos_prospectos/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true); // Crear directorio si no existe
}

$id_prospect = (int)($_POST['id_prospect'] ?? 0);
$archivo = $_FILES['archivo'] ?? null;

if (!$id_prospect) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de prospecto no proporcionado.']);
    exit;
}

if (!$archivo || $archivo['error'] !== UPLOAD_ERR_OK) {
    $errorMsg = match ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Archivo demasiado grande.',
        UPLOAD_ERR_PARTIAL => 'Archivo subido parcialmente.',
        UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo.',
        UPLOAD_ERR_NO_TMP_DIR => 'Directorio temporal faltante.',
        UPLOAD_ERR_CANT_WRITE => 'Error al escribir archivo en disco.',
        UPLOAD_ERR_EXTENSION => 'Subida detenida por extensión del archivo.',
        default => 'Error desconocido al subir el archivo.'
    };
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

$nombreOriginal = $archivo['name'];
$tamano = $archivo['size'];
$tipoMime = $archivo['type'];
$tempPath = $archivo['tmp_name'];

// Validar tipo de archivo (opcional pero recomendable)
$extensionesPermitidas = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'];
$ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

if (!in_array($ext, $extensionesPermitidas)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => "Extensión de archivo no permitida. Permitted: " . implode(', ', $extensionesPermitidas)]);
    exit;
}

// Validar tamaño (ejemplo: 5MB)
$maxSize = 5 * 1024 * 1024; // 5 MB en bytes
if ($tamano > $maxSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Archivo demasiado grande. Máximo 5MB.']);
    exit;
}

// Generar nombre único para evitar colisiones
$nombreUnico = uniqid() . '_' . basename($nombreOriginal);
$rutaFinal = $uploadDir . $nombreUnico;

if (!move_uploaded_file($tempPath, $rutaFinal)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo en el servidor.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO adjuntos_prospectos (id_prospect, nombre_archivo, ruta_archivo, tipo_mime, tamano_bytes)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$id_prospect, $nombreOriginal, '/adjuntos_prospectos/' . $nombreUnico, $tipoMime, $tamano]);

    echo json_encode(['success' => true, 'message' => 'Archivo subido correctamente.']);

} catch (PDOException $e) {
    // Si la inserción falla, intentar eliminar el archivo subido
    if (file_exists($rutaFinal)) {
        unlink($rutaFinal);
    }
    error_log("Error al guardar adjunto en DB: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno al guardar la referencia del archivo.']);
}
?>