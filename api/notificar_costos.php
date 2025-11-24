<?php
// api/notificar_costos.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

// ✅ Eliminamos auth_check.php porque ya no se incluye (como indicaste)

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

    // Actualizar servicios
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

    // === ENVIAR CORREO SOLO SI ES "solicitado" ===
    if ($estado === 'solicitado') {
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
            // ✅ Intentar cargar PHPMailer, pero sin romper si falla
            $correo_ok = false;
            $error_correo = '';

            if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
                try {
                    require_once __DIR__ . '/../vendor/autoload.php';
                    require_once __DIR__ . '/enviar_correo_pricing.php';
                    $resultado = enviarCorreoPricing(
                        $prospecto['id_prospect'],
                        $prospecto['concatenado'],
                        $prospecto['razon_social'],
                        $prospecto['comercial_nombre'] ?? 'Comercial asignado'
                    );
                    if ($resultado['success']) {
                        $mensaje .= " ✉️ " . $resultado['message'];
                        $correo_ok = true;
                    } else {
                        $error_correo = $resultado['message'];
                    }
                } catch (Exception $e) {
                    $error_correo = "Error al enviar correo: " . $e->getMessage();
                }
            } else {
                $error_correo = "PHPMailer no instalado (vendor/ faltante)";
            }

            // Registrar en log si falla el correo
            if (!$correo_ok) {
                error_log("[NOTIFICAR_COSTOS] Advertencia: " . $error_correo);
                $mensaje .= " ⚠️ Notificación por correo no enviada.";
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