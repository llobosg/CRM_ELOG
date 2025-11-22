<?php
// api/enviar_correo_pricing.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

/**
 * Envía un correo real a todos los usuarios con rol 'pricing'
 * 
 * @param int $prospectoId ID del prospecto
 * @param string $concatenado Código del prospecto (ej: IMEX251115-01)
 * @param string $razonSocial Nombre del cliente
 * @param string $comercialNombre Nombre del comercial que solicitó
 * @return array ['success' => bool, 'message' => string]
 */
function enviarCorreoPricing($prospectoId, $concatenado, $razonSocial, $comercialNombre = 'Comercial asignado') {
    global $pdo;
    
    // 1. Obtener correos de usuarios con rol 'pricing' → TABLA: usuarios (no "users")
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
        error_log("[CORREO] No se encontraron usuarios con rol 'pricing' para notificar.");
        return ['success' => false, 'message' => 'No hay destinatarios con rol Pricing'];
    }

    // 2. Construir enlace al prospecto
    $appUrl = $_ENV['APP_URL'] ?? 'https://crmelog-qa.up.railway.app';
    $link = "$appUrl/?page=prospectos&id_ppl=" . urlencode($prospectoId);

    // 3. Configurar PHPMailer
    $mail = new PHPMailer(true);
    try {
        // Configuración SMTP
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'] ?? 'email.elog.cl';
        $mail->Port       = (int)($_ENV['SMTP_PORT'] ?? 587);
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USERNAME'] ?? 'notifica@elog.cl';
        $mail->Password   = $_ENV['SMTP_PASSWORD'] ?? '';
        $mail->SMTPSecure = $mail->Port === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;

        // Remitente
        $fromEmail = $_ENV['SMTP_FROM_EMAIL'] ?? 'notifica@elog.cl';
        $mail->setFrom($fromEmail, 'CRM_ELOG - Powered by GLT Comex');

        // Destinatarios
        foreach ($destinatarios as $dest) {
            $mail->addAddress($dest['email'], $dest['nombre']);
        }

        // Contenido HTML
        $mail->isHTML(true);
        $mail->Subject = "🔔 Nueva solicitud de costos: $concatenado";
        $mail->Body = "
            <!DOCTYPE html>
            <html lang='es'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Solicitud de Costos - CRM ELOG</title>
            </head>
            <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f9f9f9;'>
                <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f9f9f9; padding: 20px 0;'>
                    <tr>
                        <td align='center'>
                            <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden;'>
                                <tr>
                                    <td style='background: linear-gradient(135deg, #0066cc, #004080); padding: 30px 20px; text-align: center;'>
                                        <h1 style='color: white; margin: 0; font-size: 24px; font-weight: 600;'>CRM ELOG</h1>
                                        <p style='color: rgba(255,255,255,0.9); margin: 8px 0 0; font-size: 16px;'>Sistema de Gestión Logística</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='padding: 30px 25px;'>
                                        <h2 style='color: #333; font-size: 20px; margin-top: 0; margin-bottom: 20px;'>🔔 Nueva solicitud de costos</h2>
                                        <p style='color: #555; font-size: 15px; line-height: 1.5; margin-bottom: 20px;'>
                                            Estimado equipo de <strong>Pricing</strong>,
                                        </p>
                                        <p style='color: #555; font-size: 15px; line-height: 1.5; margin-bottom: 25px;'>
                                            Se ha solicitado la carga de costos para el siguiente prospecto:
                                        </p>
                                        <table width='100%' style='background-color: #f8f9fa; border-radius: 8px; padding: 18px; margin-bottom: 25px; border: 1px solid #e9ecef;'>
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
                                            Por favor, no responda a este correo. Para consultas, contacte al área comercial correspondiente.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>
        ";

        // Alternativa en texto plano
        $mail->AltBody = "Nueva solicitud de costos para el prospecto $concatenado (Cliente: $razonSocial).\n\n" .
                         "Solicitado por: $comercialNombre\n\n" .
                         "Acceda al prospecto aquí: $link";

        if ($mail->send()) {
            $emails = implode(', ', array_column($destinatarios, 'email'));
            error_log("[CORREO] ✔️ Enviado a: $emails");
            return [
                'success' => true, 
                'message' => 'Correo enviado a ' . count($destinatarios) . ' usuario(s) de Pricing'
            ];
        }
    } catch (Exception $e) {
        $error = "Error al enviar correo: " . $e->getMessage();
        error_log($error);
        return ['success' => false, 'message' => $error];
    }
}
?>