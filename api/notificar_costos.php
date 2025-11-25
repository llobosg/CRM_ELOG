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

    // === ENVIAR CORREOS SEGÚN EL ESTADO ===
    if ($estado === 'solicitado') {
        // === Enviar correo al equipo de Pricing ===
        $stmt = $pdo->prepare("
            SELECT 
                p.concatenado, 
                p.razon_social, 
                p.tipo_oper,
                s.id_prospect, 
                s.origen, 
                s.destino, 
                s.incoterm,
                u.nombre as comercial_nombre
            FROM servicios s
            JOIN prospectos p ON s.id_prospect = p.id_ppl
            LEFT JOIN usuarios u ON p.id_comercial = u.id_usr
            WHERE s.id_srvc = ?
        ");
        $stmt->execute([$idSrvc]);
        $prospecto = $stmt->fetch();

        if ($prospecto) {
            $datosServicio = [
                'origen' => $prospecto['origen'] ?? '—',
                'destino' => $prospecto['destino'] ?? '—',
                'tipo_oper' => $prospecto['tipo_oper'] ?? '—',
                'incoterm' => $prospecto['incoterm'] ?? '—'
            ];

            require_once __DIR__ . '/enviar_correo_pricing.php';
            $resultado = enviarCorreoPricing(
                $prospecto['id_prospect'],
                $prospecto['concatenado'],
                $prospecto['razon_social'],
                $prospecto['comercial_nombre'] ?? 'Comercial asignado',
                $datosServicio
            );
            if ($resultado['success']) {
                $mensaje .= " ✉️ " . $resultado['message'];
            } else {
                error_log("[NOTIFICAR_COSTOS] Error al enviar a Pricing: " . $resultado['message']);
            }
        } else {
            error_log("[NOTIFICAR_COSTOS] Prospecto/servicio no encontrado para id_srvc: " . $idSrvc);
        }
    } elseif ($estado === 'revisado') {
        // === Enviar correo al Comercial ===
        $stmt = $pdo->prepare("
            SELECT 
                p.concatenado, 
                p.razon_social, 
                s.id_prospect,
                u.email as comercial_email,
                u.nombre as comercial_nombre
            FROM servicios s
            JOIN prospectos p ON s.id_prospect = p.id_ppl
            LEFT JOIN usuarios u ON s.solicitado_por = u.id_usr
            WHERE s.id_srvc = ?
        ");
        $stmt->execute([$idSrvc]);
        $prospecto = $stmt->fetch();

        if ($prospecto && !empty($prospecto['comercial_email'])) {
            require_once __DIR__ . '/enviar_correo_pricing.php';
            $resultado = enviarCorreoPricing(
                $prospecto['id_prospect'],
                $prospecto['concatenado'],
                $prospecto['razon_social'],
                $prospecto['comercial_nombre'] ?? 'Comercial asignado',
                [],
                [$prospecto['comercial_email']]
            );
            if ($resultado['success']) {
                $mensaje .= " ✉️ Notificación enviada al Comercial.";
            } else {
                error_log("[NOTIFICAR_COSTOS] Error al enviar correo al Comercial: " . $resultado['message']);
            }
        } else {
            error_log("[NOTIFICAR_COSTOS] Comercial no encontrado o sin email para id_srvc: " . $idSrvc);
        }
    }

    echo json_encode(['success' => true, 'message' => $mensaje]);

} catch (Exception $e) {
    error_log("Error en notificar_costos.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>