<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) throw new Exception('Datos inválidos');
    
    // Validar campos obligatorios
    $required = ['id_prospecto', 'fecha', 'hora', 'rut_cliente', 'razon_social', 'tipo_gestion', 'nota'];
    foreach ($required as $field) {
        if (empty($data[$field])) throw new Exception("Campo '$field' es obligatorio");
    }
    
    // Validar fecha y hora
    if (!strtotime($data['fecha'])) throw new Exception('Fecha inválida');
    if (!strtotime("1970-01-01 {$data['hora']}")) throw new Exception('Hora inválida');
    
    // Insertar llamado
    $stmt = $pdo->prepare("
        INSERT INTO llamados (
            id_prospecto, fecha, hora, rut_cliente, razon_social, 
            id_comercial, nombre_comercial, tipo_gestion, nota
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $data['id_prospecto'],
        $data['fecha'],
        $data['hora'],
        $data['rut_cliente'],
        $data['razon_social'],
        $_SESSION['user_id'],
        $_SESSION['nombre'],
        $data['tipo_gestion'],
        $data['nota']
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Llamado registrado correctamente']);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>