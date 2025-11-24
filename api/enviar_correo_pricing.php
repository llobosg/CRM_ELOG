<?php
// api/enviar_correo_pricing.php

/**
 * Envía correo usando la API REST de Brevo (no SMTP)
 */
function enviarCorreoPricing($prospectoId, $concatenado, $razonSocial, $comercialNombre = 'Comercial asignado', $datosServicio = []) {
    global $pdo;

    // Obtener destinatarios: usuarios con rol "pricing"
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

    // URL del CRM
    $appUrl = $_ENV['APP_URL'] ?? 'https://crmelog-qa.up.railway.app';
    $link = "$appUrl/?page=prospectos&id_ppl=" . urlencode($prospectoId);

    // Extraer datos del servicio (con valores por defecto)
    $origen      = $datosServicio['origen']      ?? '—';
    $destino     = $datosServicio['destino']     ?? '—';
    $tipoOper    = $datosServicio['tipo_oper']   ?? '—';
    $incoterm    = $datosServicio['incoterm']    ?? '—';

    // Cuerpo HTML del correo
    $body = "
        <div style='font-family: Arial, sans-serif; max-width: 650px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>
            <div style='background: linear-gradient(135deg, #0066cc, #004080); padding: 30px 20px; text-align: center;'>
                <h1 style='color: white; margin: 0; font-size: 24px; font-weight: 600;'>CRM ELOG</h1>
                <p style='color: rgba(255,255,255,0.9); margin: 8px 0 0; font-size: 16px;'>Sistema de Gestión Logística</p>
            </div>
            <div style='padding: 30px 25px;'>
                <h2 style='color: #333; font-size: 20px; margin-top: 0; margin-bottom: 20px;'>🔔 Nueva solicitud de costos</h2>
                <p style='color: #555; font-size: 15px; line-height: 1.5; margin-bottom: 25px;'>
                    Estimado equipo de <strong>Pricing</strong>,<br>
                    Se requiere la carga de costos para el siguiente servicio:
                </p>

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

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$link' 
                       style='background: linear-gradient(135deg, #0066cc, #004080); 
                              color: white; 
                              padding: 14px 32px; 
                              text-decoration: none; 
                              border-radius: 8px; 
                              font-weight: 600; 
                              font-size: 16px; 
                              display: inline-block; 
                              box-shadow: 0 4px 8px rgba(0,102,204,0.3);'>
                        Ir al Prospecto en CRM
                    </a>
                </div>

                <p style='color: #6c757d; font-size: 13px; line-height: 1.5; margin-top: 25px; border-top: 1px solid #eee; padding-top: 20px;'>
                    Este mensaje fue generado automáticamente por el sistema <strong>CRM ELOG</strong> de GLT Comex.<br>
                    Por favor, no responda a este correo.
                </p>
            </div>
        </div>
    ";

    // Preparar datos para la API de Brevo
    $to = array_map(fn($d) => ['email' => $d['email'], 'name' => $d['nombre']], $destinatarios);
    $data = [
        'sender' => ['email' => 'notifica@elog.cl', 'name' => 'CRM ELOG'],
        'to' => $to,
        'subject' => "🔔 Nueva solicitud de costos: $concatenado",
        'htmlContent' => $body
    ];

    // Enviar vía API de Brevo
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.brevo.com/v3/smtp/email');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'content-type: application/json',
        'api-key: ' . ($_ENV['BREVO_API_KEY'] ?? '')
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // timeout de 10 segundos

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 201) {
        return ['success' => true, 'message' => 'Correo enviado a ' . count($destinatarios) . ' usuario(s) de Pricing'];
    } else {
        error_log("Error Brevo API: HTTP $httpCode, Response: $response");
        return ['success' => false, 'message' => "Error al enviar correo (HTTP $httpCode)"];
    }
}
?>