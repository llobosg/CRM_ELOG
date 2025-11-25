<?php
// api/notificar_costos.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $idSrvc = $data['id_srvc'] ?? '';
    $estado = $data['estado'] ?? '';
    $usuarioId = (int)($data['usuario_id'] ?? 0);

    // Validaciones
    if (empty($idSrvc) || !is_string($idSrvc)) {
        throw new Exception('ID de servicio inválido');
    }
    if (strlen($idSrvc) > 50 || !preg_match('/^[a-zA-Z0-9\-_]+$/', $idSrvc)) {
        throw new Exception('Formato de ID de servicio no válido');
    }
    if ($usuarioId <= 0) {
        throw new Exception('Usuario no autenticado');
    }
    if (!in_array($estado, ['solicitado', 'completado', 'revisado'])) {
        throw new Exception('Estado no permitido');
    }

    // Actualizar en BD
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
    $valores[] = $idSrvc;
    $sql = "UPDATE servicios SET " . implode(', ', $campos) . " WHERE id_srvc = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    $mensaje = match($estado) {
        'solicitado' => 'Solicitud de costos enviada al equipo de Pricing.',
        'completado' => 'Costos marcados como completados.',
        'revisado' => 'Costos aprobados por el Comercial.',
        default => 'Estado de costos actualizado.'
    };

    // === ENVIAR CORREO AL PRICING (solo en 'solicitado') ===
    if ($estado === 'solicitado') {
        $campos[] = 'fecha_solicitado = NOW()';
        $campos[] = 'solicitado_por = ?';
        $valores[] = $usuarioId;

        // === Enviar correo al equipo de Pricing ===
        error_log("[NOTIFICAR_COSTOS] Iniciando envío a Pricing para id_srvc: " . $idSrvc);

        // Obtener datos del prospecto
        $stmt = $pdo->prepare("
            SELECT p.concatenado, p.razon_social, s.id_prospect, u.nombre as comercial_nombre
            FROM servicios s
            JOIN prospectos p ON s.id_prospect = p.id_ppl
            LEFT JOIN usuarios u ON p.id_comercial = u.id_usr
            WHERE s.id_srvc = ?
        ");
        $stmt->execute([$idSrvc]);
        $prospecto = $stmt->fetch();

        if ($prospecto) {
            require_once __DIR__ . '/enviar_correo_pricing.php';
            $resultado = enviarCorreoPricing(
                $prospecto['id_prospect'],
                $prospecto['concatenado'],
                $prospecto['razon_social'],
                $prospecto['comercial_nombre'] ?? 'Comercial asignado'
            );
            if ($resultado['success']) {
                $mensaje .= " ✉️ " . $resultado['message'];
            } else {
                error_log("[NOTIFICAR_COSTOS] Error al enviar a Pricing: " . $resultado['message']);
            }
        } else {
            error_log("[NOTIFICAR_COSTOS] Prospecto no encontrado para id_srvc: " . $idSrvc);
        }
    }

    // === ENVIAR CORREO AL COMERCIAL (solo en 'revisado') ===
    if ($estado === 'revisado') {
        error_log("[REVISADO] Iniciando envío de correo al Comercial para id_srvc: " . $idSrvc);

        $stmt = $pdo->prepare("
            SELECT p.concatenado, p.razon_social, s.id_prospect, 
                   u.email as comercial_email, u.nombre as comercial_nombre
            FROM servicios s
            JOIN prospectos p ON s.id_prospect = p.id_ppl
            LEFT JOIN usuarios u ON p.id_comercial = u.id_usr
            WHERE s.id_srvc = ?
        ");
        $stmt->execute([$idSrvc]);
        $prospecto = $stmt->fetch();

        if ($prospecto && !empty($prospecto['comercial_email'])) {
            error_log("[REVISADO] Comercial encontrado: " . $prospecto['comercial_email']);

            // ✅ Forzar la inclusión del archivo
            if (file_exists(__DIR__ . '/enviar_correo_pricing.php')) {
                require_once __DIR__ . '/enviar_correo_pricing.php';
                if (function_exists('enviarCorreoPricing')) {
                    $resultado = enviarCorreoPricing(
                        $prospecto['id_prospect'],
                        $prospecto['concatenado'],
                        $prospecto['razon_social'],
                        $prospecto['comercial_nombre'] ?? 'Comercial asignado',
                        [],
                        [$prospecto['comercial_email']]
                    );
                    error_log("[REVISADO] Resultado del correo: " . json_encode($resultado));
                    if ($resultado['success']) {
                        $mensaje .= " ✉️ Notificación enviada al Comercial.";
                    } else {
                        $mensaje .= " ⚠️ Error al enviar correo.";
                    }
                } else {
                    $mensaje .= " ⚠️ Función enviarCorreoPricing no encontrada.";
                    error_log("[REVISADO] Función enviarCorreoPricing no existe");
                }
            } else {
                $mensaje .= " ⚠️ Archivo enviar_correo_pricing.php no encontrado.";
                error_log("[REVISADO] Archivo enviar_correo_pricing.php no existe");
            }
        } else {
            $mensaje .= " ⚠️ Comercial no tiene correo.";
            error_log("[REVISADO] Comercial no encontrado o sin email");
        }
    }

    echo json_encode(['success' => true, 'message' => $mensaje]);

} catch (Exception $e) {
    error_log("Error en notificar_costos.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>