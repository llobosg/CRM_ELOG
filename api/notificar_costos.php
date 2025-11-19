<?php
// api/notificar_costos.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $idSrvc = $data['id_srvc'] ?? '';
    $estado = $data['estado'] ?? '';
    $usuarioId = (int)($data['usuario_id'] ?? 0);

    if (!$idSrvc || !in_array($estado, ['solicitado', 'completado', 'revisado'])) {
        throw new Exception('Datos inválidos para notificación de costos');
    }

    // === Preparar campos a actualizar según el estado ===
    $campos = ['estado_costos = ?'];
    $valores = [$estado];

    if ($estado === 'solicitado') {
        $campos[] = 'fecha_solicitado = NOW()';
        $campos[] = 'solicitado_por = ?';
        $valores[] = $usuarioId;
    } elseif ($estado === 'completado') {
        $campos[] = 'fecha_completado = NOW()';
        $campos[] = 'completado_por = ?';
        $valores[] = $usuarioId;
    } elseif ($estado === 'revisado') {
        $campos[] = 'fecha_revisado = NOW()';
        $campos[] = 'revisado_por = ?';
        $valores[] = $usuarioId;
    }

    // === Actualiza BD ===
    $valores[] = $idSrvc;
    $sql = "UPDATE servicios SET " . implode(', ', $campos) . " WHERE id_srvc = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    // === Envío de correo ===
    if ($estado === 'solicitado') {
        // Obtener datos para el correo
        $stmt = $pdo->prepare("
            SELECT p.concatenado, p.razon_social, s.id_prospect, u.nombre as comercial_nombre
            FROM servicios s
            JOIN prospectos p ON s.id_prospect = p.id_ppl
            LEFT JOIN users u ON p.id_comercial = u.id
            WHERE s.id_srvc = ?
        ");
        $stmt->execute([$idSrvc]);
        $prospecto = $stmt->fetch();

        if ($prospecto) {
            // Incluir la función de envío
            require_once __DIR__ . '/enviar_correo_pricing.php';
            $resultado = enviarCorreoPricing(
                $prospecto['id_prospect'],
                $prospecto['concatenado'],
                $prospecto['razon_social'],
                $prospecto['comercial_nombre'] ?? 'Comercial asignado'
            );
            $mensaje .= " ✉️ " . $resultado['message'];
        }
    }

    // === Mensaje claro para el frontend ===
    $mensaje = match($estado) {
        'solicitado' => 'Solicitud de costos enviada al equipo de Pricing.',
        'completado' => 'Costos marcados como completados.',
        'revisado' => 'Costos aprobados por el Comercial.',
        default => 'Estado de costos actualizado.'
    };

    echo json_encode(['success' => true, 'message' => $mensaje]);

} catch (Exception $e) {
    error_log("Error en notificar_costos.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado de costos.']);
}
?>