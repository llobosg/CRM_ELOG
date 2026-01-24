<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/enviar_correo_pricing.php'; // Reutilizamos las funciones existentes

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['rut_cliente']) || !isset($data['total_venta']) || !isset($data['prospecto_id'])) {
        throw new Exception('Datos incompletos para notificación de sobregiro');
    }

    // Obtener datos del prospecto
    $stmt = $pdo->prepare("SELECT 
        p.concatenado, 
        p.razon_social,
        u.nombre as comercial_nombre
    FROM prospectos p
    LEFT JOIN usuarios u ON p.id_comercial = u.id_usr
    WHERE p.id_ppl = ?");
    
    $stmt->execute([$data['prospecto_id']]);
    $prospecto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$prospecto) {
        throw new Exception('Prospecto no encontrado');
    }

    // Obtener emails de admin_finanzas
    $stmt = $pdo->prepare("
        SELECT email, nombre 
        FROM usuarios 
        WHERE rol = 'admin_finanzas' 
        AND email IS NOT NULL 
        AND email != '' 
        AND email != 'test@example.com'
    ");
    $stmt->execute();
    $destinatariosFinanzas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($destinatariosFinanzas)) {
        error_log("[NOTIFICAR_SOBREGIRO] No se encontraron usuarios con rol admin_finanzas");
        echo json_encode(['success' => true, 'message' => 'Sobregiro detectado, pero no hay destinatarios']);
        exit;
    }

    // Preparar datos del servicio para el correo
    $datosServicio = [
        'origen' => '—',
        'destino' => '—',
        'tipo_oper' => '—',
        'incoterm' => '—'
    ];

    // Calcular sobregiro
    $sobregiro = $data['total_venta'] - $data['saldo_credito'];
    $moneda = 'USD';

    // Construir cuerpo del correo personalizado para sobregiro
    $razonSocial = $prospecto['razon_social'];
    $concatenado = $prospecto['concatenado'];
    $comercialNombre = $prospecto['comercial_nombre'] ?? 'Comercial asignado';
    $idPpl = $data['prospecto_id'];

    // URL del CRM
    $appUrl = $_ENV['APP_URL'] ?? 'https://crmelog-production.up.railway.app';
    $link = "$appUrl/?page=prospectos&id_ppl=" . urlencode($idPpl);

    // Generar HTML personalizado para sobregiro
    $htmlContent = "
        <div style='font-family: Arial, sans-serif; max-width: 650px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>
            <div style='background: linear-gradient(135deg, #d97706, #b45309); padding: 30px 20px; text-align: center;'>
                <h1 style='color: white; margin: 0; font-size: 24px; font-weight: 600;'>🚨 ALERTA DE SOBREGIRO</h1>
                <p style='color: rgba(255,255,255,0.9); margin: 8px 0 0; font-size: 16px;'>CRM ELOG - Sistema de Gestión Logística</p>
            </div>
            <div style='padding: 30px 25px;'>
                <h2 style='color: #333; font-size: 20px; margin-top: 0; margin-bottom: 20px;'>⚠️ Se ha detectado un sobregiro en crédito</h2>
                <p style='color: #555; font-size: 15px; line-height: 1.5; margin-bottom: 25px;'>
                    Estimado equipo de Finanzas,<br>
                    Se ha intentado cerrar un prospecto que excede el límite de crédito disponible.
                </p>
                
                <table style='width: 100%; background-color: #fff3cd; border-radius: 8px; padding: 18px; margin-bottom: 25px; border: 1px solid #ffeaa7;'>
                    <tr>
                        <td style='padding: 6px 0; font-weight: 600; color: #856404; width: 35%;'>Cliente:</td>
                        <td style='padding: 6px 0; color: #856404;'>$razonSocial</td>
                    </tr>
                    <tr>
                        <td style='padding: 6px 0; font-weight: 600; color: #856404;'>Prospecto:</td>
                        <td style='padding: 6px 0; color: #856404;'><code style='background: #ffeaa7; padding: 2px 6px; border-radius: 4px; font-family: monospace;'>$concatenado</code></td>
                    </tr>
                    <tr>
                        <td style='padding: 6px 0; font-weight: 600; color: #856404;'>Comercial:</td>
                        <td style='padding: 6px 0; color: #856404;'>$comercialNombre</td>
                    </tr>
                    <tr>
                        <td style='padding: 6px 0; font-weight: 600; color: #856404;'>RUT Cliente:</td>
                        <td style='padding: 6px 0; color: #856404;'>{$data['rut_cliente']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 6px 0; font-weight: 600; color: #856404;'>Venta Total:</td>
                        <td style='padding: 6px 0; color: #856404;'>$" . number_format($data['total_venta'], 2) . " $moneda</td>
                    </tr>
                    <tr>
                        <td style='padding: 6px 0; font-weight: 600; color: #856404;'>Crédito Disponible:</td>
                        <td style='padding: 6px 0; color: #856404;'>$" . number_format($data['saldo_credito'], 2) . " $moneda</td>
                    </tr>
                    <tr>
                        <td style='padding: 6px 0; font-weight: 600; color: #856404;'>Sobregiro:</td>
                        <td style='padding: 6px 0; color: #d97706; font-weight: bold;'>$" . number_format($sobregiro, 2) . " $moneda</td>
                    </tr>
                </table>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$link' 
                       style='background: linear-gradient(135deg, #d97706, #b45309); color: white; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block; box-shadow: 0 4px 8px rgba(217, 119, 6, 0.3);'>
                        Revisar Prospecto en CRM
                    </a>
                </div>
                
                <p style='color: #6c757d; font-size: 13px; line-height: 1.5; margin-top: 25px; border-top: 1px solid #eee; padding-top: 20px;'>
                    Este mensaje fue generado automáticamente por el sistema <strong>CRM ELOG</strong> de GLT Comex.<br>
                    Por favor, no responda a este correo.
                </p>
            </div>
        </div>
    ";

    // Enviar vía Brevo (copiando la lógica de enviarCorreoPricing)
    $postData = [
        'sender' => [
            'email' => 'llobos@gltcomex.com',
            'name' => 'CRM ELOG - GLT Comex'
        ],
        'to' => array_map(function($d) {
            return ['email' => $d['email'], 'name' => $d['nombre'] ?? 'Finanzas'];
        }, $destinatariosFinanzas),
        'subject' => "🚨 ALERTA DE SOBREGIRO - Prospecto $concatenado",
        'htmlContent' => $htmlContent
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
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CRM_ELOG/1.0');

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        error_log("[NOTIFICAR_SOBREGIRO] ✅ Correo de sobregiro enviado exitosamente a " . count($destinatariosFinanzas) . " destinatario(s)");
        echo json_encode(['success' => true, 'message' => "Notificación de sobregiro enviada a " . count($destinatariosFinanzas) . " destinatario(s)"]);
    } else {
        $errorMessage = "Error al enviar correo de sobregiro via Brevo API (HTTP $httpCode)";
        if ($response) $errorMessage .= " - Response: $response";
        if ($curlError) $errorMessage .= " - cURL Error: $curlError";
        error_log("[NOTIFICAR_SOBREGIRO] ❌ $errorMessage");
        echo json_encode(['success' => false, 'message' => $errorMessage]);
    }

} catch (Exception $e) {
    error_log("[NOTIFICAR_SOBREGIRO] Error general: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al enviar notificación de sobregiro']);
}
?>