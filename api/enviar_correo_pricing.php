<?php
// api/enviar_correo_pricing.php
// ✅ Versión usando API de Brevo con manejo de errores robusto y logging detallado

/**
 * Envía correo usando la API REST de Brevo.
 * 
 * @param int $prospectoId
 * @param string $concatenado
 * @param string $razonSocial
 * @param string $comercialNombre
 * @param array $datosServicio
 * @param array|null $destinatariosPersonalizados Si se proporciona, se asume que es notificación al Comercial
 * @param float|null $costoTotal (solo para notificación al Comercial)
 * @param string|null $pricingNombre (solo para notificación al Comercial)
 * @param string|null $monedaServicio (solo para notificación al Comercial)
 * @param int|null $idPpl (solo para notificación al Comercial)
 * @return array
 */
function enviarCorreoPricing(
    $prospectoId,
    $concatenado,
    $razonSocial,
    $comercialNombre = 'Comercial asignado',
    $datosServicio = [],
    $destinatariosPersonalizados = null,
    $costoTotal = null,
    $pricingNombre = null,
    $monedaServicio = 'USD',
    $idPpl = null
) {
    global $pdo;

    if ($destinatariosPersonalizados !== null) {
        // === NOTIFICACIÓN AL COMERCIAL ===
        $destinatarios = [];
        foreach ($destinatariosPersonalizados as $email) {
            $destinatarios[] = ['email' => $email, 'name' => 'Comercial']; // Brevo espera 'name'
        }
        $subject = "Solicitud de costos completada por Pricing";
        $htmlContent = generarHtmlCorreoComercial(
            $razonSocial,
            $concatenado,
            $comercialNombre,
            $datosServicio,
            $pricingNombre,
            $costoTotal,
            $monedaServicio,
            $idPpl ?? $prospectoId
        );
    } else {
        // === NOTIFICACIÓN AL EQUIPO DE PRICING ===
        $stmt = $pdo->prepare("
            SELECT email, nombre 
            FROM usuarios 
            WHERE rol = 'pricing' 
            AND email IS NOT NULL 
            AND email != '' 
            AND email != 'test@example.com'
        ");
        $stmt->execute();
        $destinatarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($destinatarios)) {
            return ['success' => false, 'message' => 'No hay destinatarios con rol Pricing'];
        }
        // Asegurar que 'name' esté presente para Brevo
        $destinatarios = array_map(function($d) {
            return ['email' => $d['email'], 'name' => $d['nombre'] ?? 'Pricing'];
        }, $destinatarios);

        $subject = "🔔 Nueva solicitud de costos: $concatenado";
        $htmlContent = generarHtmlCorreoPricing(
            $razonSocial,
            $concatenado,
            $comercialNombre,
            $datosServicio,
            $idPpl ?? $prospectoId
        );
    }

    // Datos para la API de Brevo
    $postData = [
        'sender' => [
            'email' => $_ENV['SMTP_FROM_EMAIL'] ?? 'notifica@elog.cl', // Usar la variable SMTP_FROM_EMAIL como remitente
            'name' => 'CRM ELOG - GLT Comex'
        ],
        'to' => $destinatarios,
        'subject' => $subject,
        'htmlContent' => $htmlContent
    ];

    // Enviar vía API de Brevo
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.brevo.com/v3/smtp/email');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'content-type: application/json',
        'api-key: ' . ($_ENV['BREVO_API_KEY'] ?? '') // Asegúrate que esta variable esté en Railway PROD
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData)); // Enviar el array PHP codificado
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20); // Aumentar timeout
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CRM_ELOG/1.0');

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    curl_close($ch);

    // Registrar detalles de la solicitud y respuesta para diagnóstico
    $logDetails = [
        'request_data' => $postData,
        'response_body' => $response,
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'curl_errno' => $curlErrno
    ];
    error_log("[EMAIL_BREVO_DEBUG] Detalles envío correo: " . json_encode($logDetails, JSON_PRETTY_PRINT));

    if ($httpCode >= 200 && $httpCode < 300) {
        // Log de éxito
        error_log("[EMAIL_LOG] Correo enviado exitosamente a " . count($destinatarios) . " destinatario(s) via Brevo API para prospecto $concatenado. HTTP: $httpCode");
        return ['success' => true, 'message' => "Correo enviado a " . count($destinatarios) . " destinatario(s)."];
    } else {
        // Log de error detallado
        $errorMessage = "Error al enviar correo via Brevo API (HTTP $httpCode)";
        if ($curlError) {
            $errorMessage .= " - cURL Error: $curlErrno - $curlError";
        }
        if ($response) {
            $errorMessage .= " - Response Body: $response";
        }
        error_log("[EMAIL_ERROR] $errorMessage");

        // Devolver un error que pueda ser manejado por el frontend
        return ['success' => false, 'message' => $errorMessage];
    }
}

// === PLANTILLA PARA PRICING ===
function generarHtmlCorreoPricing($razonSocial, $concatenado, $comercialNombre, $datosServicio, $idPpl) {
    $origen = $datosServicio['origen'] ?? '—';
    $destino = $datosServicio['destino'] ?? '—';
    $tipoOper = $datosServicio['tipo_oper'] ?? '—';
    $incoterm = $datosServicio['incoterm'] ?? '—';

    // URL del CRM (debería ser la de PROD si este archivo está en PROD)
    $appUrl = $_ENV['APP_URL'] ?? 'https://crmelog-production.up.railway.app'; // Asegúrate que sea la URL correcta de PROD
    $link = "$appUrl/?page=prospectos&id_ppl=" . urlencode($idPpl);

    return "
        <div style='font-family: Arial, sans-serif; max-width: 650px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>
            <div style='background: linear-gradient(135deg, #0066cc, #004080); padding: 30px 20px; text-align: center;'>
                <h1 style='color: white; margin: 0; font-size: 24px; font-weight: 600;'>CRM ELOG</h1>
                <p style='color: rgba(255,255,255,0.9); margin: 8px 0 0; font-size: 16px;'>Sistema de Gestión Logística</p>
            </div>
            <div style='padding: 30px 25px;'>
                <h2 style='color: #333; font-size: 20px; margin-top: 0; margin-bottom: 20px;'>🔔 Nueva solicitud de costos</h2>
                <p style='color: #555; font-size: 15px; line-height: 1.5; margin-bottom: 25px;'>
                    Estimado equipo,<br>
                    Se requiere la carga de costos para el siguiente servicio:
                </p>
                " . generarTablaServicio($razonSocial, $concatenado, $comercialNombre, $origen, $destino, $tipoOper, $incoterm) . "
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$link'
                       style='background: linear-gradient(135deg, #0066cc, #004080); color: white; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 8px rgba(0,102,204,0.3);'>
                        Ir al Prospecto en CRM
                    </a>
                </div>
                " . generarFooter() . "
            </div>
        </div>
    ";
}

// === PLANTILLA PARA COMERCIAL ===
function generarHtmlCorreoComercial($razonSocial, $concatenado, $comercialNombre, $datosServicio, $pricingNombre, $costoTotal, $monedaServicio, $idPpl) {
    $origen = $datosServicio['origen'] ?? '—';
    $destino = $datosServicio['destino'] ?? '—';
    $tipoOper = $datosServicio['tipo_oper'] ?? '—';
    $incoterm = $datosServicio['incoterm'] ?? '—';
    $pricing = $pricingNombre ?? 'Equipo de Pricing';
    $costo = $costoTotal !== null ? number_format($costoTotal, 2) . ' ' . htmlspecialchars($monedaServicio) : '—';

    // URL del CRM (debería ser la de PROD si este archivo está en PROD)
    $appUrl = $_ENV['APP_URL'] ?? 'https://crmelog-production.up.railway.app'; // Asegúrate que sea la URL correcta de PROD
    $link = "$appUrl/?page=prospectos&id_ppl=" . urlencode($idPpl);

    return "
        <div style='font-family: Arial, sans-serif; max-width: 650px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>
            <div style='background: linear-gradient(135deg, #0066cc, #004080); padding: 30px 20px; text-align: center;'>
                <h1 style='color: white; margin: 0; font-size: 24px; font-weight: 600;'>CRM ELOG</h1>
                <p style='color: rgba(255,255,255,0.9); margin: 8px 0 0; font-size: 16px;'>Sistema de Gestión Logística</p>
            </div>
            <div style='padding: 30px 25px;'>
                <h2 style='color: #333; font-size: 20px; margin-top: 0; margin-bottom: 20px;'>✅ Solicitud de costos completada</h2>
                <p style='color: #555; font-size: 15px; line-height: 1.5; margin-bottom: 25px;'>
                    Estimado/a <strong>$comercialNombre</strong>,<br>
                    El equipo de Pricing ha completado la solicitud de costos para el siguiente servicio:
                </p>
                " . generarTablaServicio($razonSocial, $concatenado, $comercialNombre, $origen, $destino, $tipoOper, $incoterm) . "
                <table style='width: 100%; background-color: #f8f9fa; border-radius: 8px; padding: 18px; margin-bottom: 25px; border: 1px solid #e9ecef;'>
                    <tr>
                        <td style='padding: 6px 0; font-weight: 600; color: #495057; width: 35%;'>Preparado por (Pricing):</td>
                        <td style='padding: 6px 0; color: #212529;'>$pricing</td>
                    </tr>
                    <tr>
                        <td style='padding: 6px 0; font-weight: 600; color: #495057;'>Costo Total:</td>
                        <td style='padding: 6px 0; color: #212529;'>$costo</td>
                    </tr>
                </table>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$link' 
                       style='background: linear-gradient(135deg, #0066cc, #004080); color: white; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 8px rgba(0,102,204,0.3);'>
                        Revisar en el CRM
                    </a>
                </div>
                " . generarFooter() . "
            </div>
        </div>
    ";
}

// === TABLA COMÚN ===
function generarTablaServicio($razonSocial, $concatenado, $comercialNombre, $origen, $destino, $tipoOper, $incoterm) {
    return "
        <table style='width: 100%; background-color: #f8f9fa; border-radius: 8px; padding: 18px; margin-bottom: 25px; border: 1px solid #e9ecef;'>
            <tr>
                <td style='padding: 6px 0; font-weight: 600; color: #495057; width: 35%;'>Cliente:</td>
                <td style='padding: 6px 0; color: #212529;'>$razonSocial</td>
            </tr>
            <tr>
                <td style='padding: 6px 0; font-weight: 600; color: #495057;'>Prospecto:</td>
                <td style='padding: 6px 0; color: #212529;'><code style='background: #e9ecef; padding: 2px 6px; border-radius: 4px; font-family: monospace;'>$concatenado</code></td>
            </tr>
            <tr>
                <td style='padding: 6px 0; font-weight: 600; color: #495057;'>Solicitado por:</td>
                <td style='padding: 6px 0; color: #212529;'>$comercialNombre</td>
            </tr>
            <tr>
                <td style='padding: 6px 0; font-weight: 600; color: #495057;'>Origen:</td>
                <td style='padding: 6px 0; color: #212529;'>$origen</td>
            </tr>
            <tr>
                <td style='padding: 6px 0; font-weight: 600; color: #495057;'>Destino:</td>
                <td style='padding: 6px 0; color: #212529;'>$destino</td>
            </tr>
            <tr>
                <td style='padding: 6px 0; font-weight: 600; color: #495057;'>Tipo de Operación:</td>
                <td style='padding: 6px 0; color: #212529;'>$tipoOper</td>
            </tr>
            <tr>
                <td style='padding: 6px 0; font-weight: 600; color: #495057;'>Incoterm:</td>
                <td style='padding: 6px 0; color: #212529;'>$incoterm</td>
            </tr>
        </table>
    ";
}

// === FOOTER COMÚN ===
function generarFooter() {
    return '
        <p style="color: #6c757d; font-size: 13px; line-height: 1.5; margin-top: 25px; border-top: 1px solid #eee; padding-top: 20px;">
            Este mensaje fue generado automáticamente por el sistema <strong>CRM ELOG</strong> de GLT Comex.<br>
            Por favor, no responda a este correo.
        </p>
    ';
}
?>