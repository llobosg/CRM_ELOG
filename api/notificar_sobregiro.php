<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['rut_cliente']) || !isset($data['total_venta']) || !isset($data['prospecto_id'])) {
        throw new Exception('Datos incompletos para notificación de sobregiro');
    }

    // Obtener datos del prospecto
    $stmt = $pdo->prepare("SELECT concatenado, razon_social FROM prospectos WHERE id_ppl = ?");
    $stmt->execute([$data['prospecto_id']]);
    $prospecto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$prospecto) {
        throw new Exception('Prospecto no encontrado');
    }

    // Obtener email de admin_finanzas
    $stmt = $pdo->prepare("SELECT email FROM usuarios WHERE rol = 'admin_finanzas'");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($admins)) {
        error_log("[NOTIFICAR_SOBREGIRO] No se encontraron usuarios admin_finanzas activos");
        echo json_encode(['success' => true, 'message' => 'Sobregiro detectado, pero no hay destinatarios']);
        exit;
    }

    // Datos para el correo
    $subject = "⚠️ ALERTA DE SOBREGIRO - Prospecto {$prospecto['concatenado']}";
    $body = "
    <h3>Alerta de Sobregiro en CRM</h3>
    <p><strong>Prospecto:</strong> {$prospecto['razon_social']} ({$prospecto['concatenado']})</p>
    <p><strong>RUT Cliente:</strong> {$data['rut_cliente']}</p>
    <p><strong>Venta Total:</strong> $" . number_format($data['total_venta'], 2) . "</p>
    <p><strong>Crédito Disponible:</strong> $" . number_format($data['saldo_credito'], 2) . "</p>
    <p><strong>Sobregiro:</strong> $" . number_format($data['total_venta'] - $data['saldo_credito'], 2) . "</p>
    <p><em>Este mensaje fue generado automáticamente al intentar cerrar un prospecto.</em></p>
    ";

    // Enviar correo a todos los admin_finanzas
    foreach ($admins as $email) {
        $mail = [
            'to' => $email,
            'subject' => $subject,
            'body' => $body
        ];
        
        // Aquí va tu lógica de envío de correo
        // Ejemplo si usas PHPMailer:
        /*
        require_once __DIR__ . '/../vendor/autoload.php';
        $mailer = new PHPMailer\PHPMailer\PHPMailer();
        $mailer->isSMTP();
        $mailer->Host = $_ENV['SMTP_HOST'];
        $mailer->Port = $_ENV['SMTP_PORT'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $_ENV['SMTP_USER'];
        $mailer->Password = $_ENV['SMTP_PASS'];
        $mailer->setFrom($_ENV['MAIL_FROM'], 'CRM ELOG');
        $mailer->addAddress($email);
        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $body;
        $mailer->send();
        */
        
        // Para Railway, puedes usar mail() o una API externa
        // mail($email, $subject, strip_tags($body), "From: noreply@crmelog.com\r\nContent-Type: text/html; charset=UTF-8");
        
        error_log("[NOTIFICAR_SOBREGIRO] Correo enviado a: $email");
    }

    echo json_encode(['success' => true, 'message' => 'Notificación de sobregiro enviada a finanzas']);

} catch (Exception $e) {
    error_log("[NOTIFICAR_SOBREGIRO] Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al enviar notificación']);
}
?>