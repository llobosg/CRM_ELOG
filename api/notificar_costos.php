<?php
// api/notificar_costos.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $idSrvc = $data['id_srvc'] ?? '';
    $estado = $data['estado'] ?? '';
    $usuarioId = (int)($data['usuario_id'] ?? 0);

    if (empty($idSrvc) || !is_string($idSrvc)) throw new Exception('ID de servicio inválido');
    if (strlen($idSrvc) > 50 || !preg_match('/^[a-zA-Z0-9\-_]+$/', $idSrvc)) throw new Exception('Formato de ID no válido');
    if ($usuarioId <= 0) throw new Exception('Usuario no autenticado');
    if (!in_array($estado, ['solicitado', 'completado', 'revisado'])) throw new Exception('Estado no permitido');

    // === Actualizar en BD ===
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

    // === ENVIAR CORREO SEGÚN ESTADO ===
    if ($estado === 'solicitado') {
        $stmt = $pdo->prepare("
            SELECT p.id_ppl, p.concatenado, p.razon_social, s.id_prospect,
                   u_com.nombre as comercial_nombre
            FROM servicios s
            JOIN prospectos p ON s.id_prospect = p.id_ppl
            LEFT JOIN usuarios u_com ON p.id_comercial = u_com.id_usr
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
                $prospecto['comercial_nombre'] ?? 'Comercial',
                $datosServicio,
                null, null, null, null,
                $prospecto['id_ppl'] // ✅ id_ppl para el enlace
            );
            if ($resultado['success']) {
                $mensaje .= " ✉️ " . $resultado['message'];
            }
        }
    } elseif ($estado === 'revisado') {
        $stmt = $pdo->prepare("
            SELECT p.id_ppl, p.concatenado, p.razon_social, s.id_prospect,
                   s.origen, s.destino, s.incoterm, p.tipo_oper,
                   s.solicitado_por, s.costo as costo_total, s.moneda as moneda_servicio
            FROM servicios s
            JOIN prospectos p ON s.id_prospect = p.id_ppl
            WHERE s.id_srvc = ?
        ");
        $stmt->execute([$idSrvc]);
        $prospecto = $stmt->fetch();

        if ($prospecto && $prospecto['solicitado_por']) {
            // === Obtener email y nombre del Comercial ===
            $stmtCom = $pdo->prepare("SELECT email, nombre FROM usuarios WHERE id_usr = ?");
            $stmtCom->execute([$prospecto['solicitado_por']]);
            $comercial = $stmtCom->fetch();

            // === Obtener nombre del Pricing que notifica ===
            $stmtPricing = $pdo->prepare("SELECT nombre FROM usuarios WHERE id_usr = ?");
            $stmtPricing->execute([$usuarioId]);
            $pricingNombre = $stmtPricing->fetchColumn() ?: 'Pricing';

            if ($comercial && $comercial['email']) {
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
                    $comercial['nombre'] ?? 'Comercial',
                    $datosServicio,
                    [$comercial['email']],
                    (float)$prospecto['costo_total'],
                    $pricingNombre,
                    $prospecto['moneda_servicio'] ?? 'USD',
                    $prospecto['id_ppl'] // ✅ id_ppl para el enlace
                );
                if ($resultado['success']) {
                    $mensaje .= " ✉️ Notificación enviada al Comercial.";
                }
            }
        }
    }

    echo json_encode(['success' => true, 'message' => $mensaje]);

} catch (Exception $e) {
    error_log("Error en notificar_costos.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>