<?php
// api/enviar_correo_pricing.php

/**
 * Envía correo usando la API REST de Brevo (no SMTP)
 */
function enviarCorreoPricing($prospectoId, $concatenado, $razonSocial, $comercialNombre = 'Comercial asignado') {
    // Obtener destinatarios Pricing
    global $pdo;
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

    // Construir enlace
    $appUrl = $_ENV['APP_URL'] ?? 'https://crmelog-qa.up.railway.app';
    $link = "$appUrl/?page=prospectos&id_ppl=" . urlencode($prospectoId);

    // Preparar correo
    $to = array_map(fn($d) => ['email' => $d['email'], 'name' => $d['nombre']], $destinatarios);
    $body = "
        <h2>🔔 Nueva solicitud de costos</h2>
        <p>Se ha solicitado la carga de costos para el prospecto:</p>
        <p><strong>Cliente:</strong> $razonSocial<br>
           <strong>Prospecto:</strong> <code>$concatenado</code><br>
           <strong>Solicitado por:</strong> $comercialNombre</p>
        <a href='$link' style='background:#0066cc;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Ir al Prospecto</a>
    ";

    $data = [
        'sender' => ['email' => 'llobos@gltcomex.com', 'name' => 'CRM ELOG'],
        'to' => $to,
        'subject' => "🔔 Nueva solicitud de costos: $concatenado",
        'htmlContent' => $body
    ];

    // Enviar vía API
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