<?php
// api/get_condiciones_trafico.php

// Establecer encabezado de respuesta JSON
header('Content-Type: application/json');

// Incluir archivos necesarios
require_once __DIR__ . '/../config.php';
// require_once __DIR__ . '/../includes/auth_check.php'; // Descomentar si se requiere autenticación para este endpoint

// Obtener el parámetro 'tipo_trafico' del query string o del body POST
$tipo_trafico = $_GET['tipo_trafico'] ?? $_POST['tipo_trafico'] ?? null;

if (!$tipo_trafico) {
    // Si no se proporciona el tipo de tráfico, devolver un error
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Tipo de tráfico no proporcionado.']);
    exit;
}

try {
    // Preparar y ejecutar la consulta
    $stmt = $pdo->prepare("
        SELECT condicion
        FROM condiciones_trafico
        WHERE trafico = ?
        LIMIT 1
    ");
    $stmt->execute([$tipo_trafico]);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($fila) {
        // Si se encuentra una condición, limpiarla
        $condicion_cruda = $fila['condicion'];

        // --- LIMPIEZA DE TEXTO (opcional, según sea necesario) ---
        // Asegurar codificación UTF-8
        $condicion_limpia = mb_convert_encoding($condicion_cruda, 'UTF-8', 'auto');
        // Eliminar caracteres de control no deseados (excepto \n, \r, \t)
        $condicion_limpia = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $condicion_limpia);
        // Reemplazar viñetas comunes por •
        $condicion_limpia = str_replace(["\xE2\x80\xA2", "\xE2\x80\xA3", "\xE2\x80\xA4", "\xE2\x80\xA5", "\xE2\x80\xA6", "\xEF\x82\xA7", "\xEF\x82\xA8", "\xEF\x82\xA9", "\xEF\x82\xAA", "\xEF\x82\xAB", "\xEF\x82\xAC", "\xEF\x82\xAD", "\xEF\x82\xAE", "\xEF\x82\xAF", "\xEF\x82\xB0", "\xEF\x82\xB1", "\xEF\x82\xB2", "\xEF\x82\xB3", "\xEF\x82\xB4", "\xEF\x82\xB5", "\xEF\x82\xB6", "\xEF\x82\xB7", "\xEF\x82\xB8", "\xEF\x82\xB9", "\xEF\x82\xBA", "\xEF\x82\xBB", "\xEF\x82\xBC", "\xEF\x82\xBD", "\xEF\x82\xBE", "\xEF\x82\xBF"], "• ", $condicion_limpia);
        // Opcional: Reemplazar otros caracteres de control no deseados si aparecen
        $condicion_limpia = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $condicion_limpia);
        $condicion_limpia = trim($condicion_limpia); // Limpiar espacios al inicio y final
        // --- FIN LIMPIEZA ---

    } else {
        // Si no se encuentra, devolver una cadena vacía o un mensaje predeterminado
        $condicion_limpia = ''; // O '(No hay condiciones definidas para este tipo de tráfico)'
        error_log("[GET_CONDICIONES_TRAFICO] Advertencia: No se encontraron condiciones para el tráfico '$tipo_trafico'");
    }

    // Devolver la respuesta exitosa con la condición
    echo json_encode(['success' => true, 'condicion' => $condicion_limpia]);

} catch (PDOException $e) {
    // Manejar errores de la base de datos
    error_log("[GET_CONDICIONES_TRAFICO] Error al buscar condición de tráfico '$tipo_trafico': " . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Error interno al obtener la condición de tráfico.']);
}
?>