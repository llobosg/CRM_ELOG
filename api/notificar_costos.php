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
        $campos[] = 'fecha_solicitado = NOW()';
        $campos[] = 'solicitado_por = ?';
        $valores[] = $usuarioId;

        // === Obtener TODOS los datos del prospecto y servicio ===
        $stmt = $pdo->prepare("
            SELECT 
                p.id_ppl,
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
                $datosServicio,
                null, // destinatarios por defecto (Pricing)
                null,
                null,
                null,
                $prospecto['id_ppl'] // ✅ PASAR id_ppl para el enlace
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
        $campos[] = 'fecha_revisado = NOW()';
        $campos[] = 'revisado_por = ?';
        $valores[] = $usuarioId;

        // === Obtener datos completos para el correo al Comercial ===
        $stmt = $pdo->prepare("
            SELECT 
                p.id_ppl,
                p.concatenado, 
                p.razon_social, 
                p.tipo_oper,
                s.id_prospect,
                s.origen,
                s.destino,
                s.incoterm,
                s.costo as costo_total,
                s.moneda as moneda_servicio,
                s.solicitado_por,
                u_solicitado.nombre as comercial_nombre,
                u_notifica.nombre as pricing_nombre
            FROM servicios s
            JOIN prospectos p ON s.id_prospect = p.id_ppl
            LEFT JOIN usuarios u_solicitado ON s.solicitado_por = u_solicitado.id_usr
            LEFT JOIN usuarios u_notifica ON s.completado_por = u_notifica.id_usr
            WHERE s.id_srvc = ?
        ");
        $stmt->execute([$idSrvc]);
        $prospecto = $stmt->fetch();

        if ($prospecto && !empty($prospecto['solicitado_por'])) {
            // === Obtener email del Comercial ===
            $stmtEmail = $pdo->prepare("SELECT email FROM usuarios WHERE id_usr = ?");
            $stmtEmail->execute([$prospecto['solicitado_por']]);
            $emailComercial = $stmtEmail->fetchColumn();

            if ($emailComercial) {
                $datosServicio = [
                    'origen' => $prospecto['origen'] ?? '—',
                    'destino' => $prospecto['destino'] ?? '—',
                    'tipo_oper' => $prospecto['tipo_oper'] ?? '—',
                    'incoterm' => $prospecto['incoterm'] ?? '—'
                ];

                $costoTotal = (float)($prospecto['costo_total'] ?? 0);
                $monedaServicio = $prospecto['moneda_servicio'] ?? 'USD';
                $pricingNombre = $prospecto['pricing_nombre'] ?? 'Pricing';

                require_once __DIR__ . '/enviar_correo_pricing.php';
                $resultado = enviarCorreoPricing(
                    $prospecto['id_prospect'],
                    $prospecto['concatenado'],
                    $prospecto['razon_social'],
                    $prospecto['comercial_nombre'] ?? 'Comercial',
                    $datosServicio,
                    [$emailComercial],
                    $costoTotal,
                    $pricingNombre,
                    $monedaServicio,
                    $prospecto['id_ppl'] // ✅ PASAR id_ppl para el enlace
                );
                if ($resultado['success']) {
                    $mensaje .= " ✉️ Notificación enviada al Comercial.";
                } else {
                    error_log("[NOTIFICAR_COSTOS] Error al enviar correo al Comercial: " . $resultado['message']);
                }
            } else {
                error_log("[NOTIFICAR_COSTOS] Comercial sin email para id_usr: " . $prospecto['solicitado_por']);
            }
        } else {
            error_log("[NOTIFICAR_COSTOS] Comercial no encontrado para id_srvc: " . $idSrvc);
        }
    }

    echo json_encode(['success' => true, 'message' => $mensaje]);

} catch (Exception $e) {
    error_log("Error en notificar_costos.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>