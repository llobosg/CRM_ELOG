<?php
// pages/ficha_cliente_logic.php
// Solo accesible por admin_finanzas (debe validarse en el frontend o en auth_check)

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Validar rol (opcional, pero recomendado)
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin_finanzas') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

function validarRutPHP($rut) {
    if (!preg_match('/^(\d{7,8})([0-9K])$/', $rut, $matches)) return false;
    list(, $cuerpo, $dv) = $matches;
    $suma = 0; $multiplo = 2;
    for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
        $suma += $multiplo * intval($cuerpo[$i]);
        $multiplo = $multiplo < 7 ? $multiplo + 1 : 2;
    }
    $dvEsperado = 11 - ($suma % 11);
    $dvEsperado = $dvEsperado == 11 ? 0 : ($dvEsperado == 10 ? 'K' : strval($dvEsperado));
    return strtoupper($dv) === $dvEsperado;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['rut'])) {
        throw new Exception('Datos inválidos');
    }

    // Validar RUT (simple, puedes reutilizar tu función si la tienes en un helper)
    // Al recibir el RUT desde el frontend
    //$rut = $_POST['rut'] ?? '';
    error_log("RUT recibido: " . print_r($input['rut'] ?? 'NO RECIBIDO', true));
    $rut = $input['rut'] ?? '';
    $rutLimpio = preg_replace('/[^0-9Kk]/', '', strtoupper($rut));
    error_log("RUT limpio: '$rutLimpio'");
    if (!validarRutPHP($rutLimpio)) {
        throw new Exception('RUT inválido');
    }
    // Guardar $rutLimpio en la BD

    $pdo->beginTransaction();

    // === Preparar datos del cliente ===
    // === Preparar datos del cliente ===
    $data_cliente = [
        'rut' => $input['rut'],
        'razon_social' => $input['razon_social'] ?? '',
        'nacional_extranjero' => $input['nacional_extranjero'] ?? 'Nacional',
        'pais' => $input['pais'] ?? '',
        'direccion' => $input['direccion'] ?? '',
        'comuna' => $input['comuna'] ?? '',
        'ciudad' => $input['ciudad'] ?? '',
        'giro' => $input['giro'] ?? '',
        'fecha_creacion' => $input['fecha_creacion'] ?? null,
        'nombre_comercial' => $input['nombre_comercial'] ?? '', // ← Solo el nombre
        'tipo_vida' => $input['tipo_vida'] ?? 'lead',
        'fecha_vida' => $input['fecha_vida'] ?? null,
        'rubro' => $input['rubro'] ?? '',
        'potencial_usd' => isset($input['potencial_usd']) ? (float)$input['potencial_usd'] : 0.00,
        'fecha_alta_credito' => $input['fecha_alta_credito'] ?? null,
        'plazo_dias' => $input['plazo_dias'] ?? '30',
        'estado_credito' => $input['estado_credito'] ?? 'vigente',
        'monto_credito' => isset($input['monto_credito']) ? (float)$input['monto_credito'] : 0.00,
    ];

    // === Verificar si existe ===
    $stmt_check = $pdo->prepare("SELECT id_cliente FROM clientes WHERE rut = ?");
    $stmt_check->execute([$input['rut']]);
    $cliente_existe = $stmt_check->fetch();

    if ($cliente_existe) {
        // Actualizar
        $set = [];
        $values = [];
        foreach ($data_cliente as $key => $value) {
            $set[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $input['rut'];
        $sql = "UPDATE clientes SET " . implode(', ', $set) . " WHERE rut = ?";
        $pdo->prepare($sql)->execute($values);
    } else {
        // Insertar
        $campos = implode(', ', array_keys($data_cliente));
        $placeholders = str_repeat('?,', count($data_cliente) - 1) . '?';
        $sql = "INSERT INTO clientes ($campos) VALUES ($placeholders)";
        $pdo->prepare($sql)->execute(array_values($data_cliente));
    }

    // === Sincronizar contactos ===
    $pdo->prepare("DELETE FROM contactos WHERE rut = ?")->execute([$input['rut']]);

    if (!empty($input['contactos'])) {
        $stmt_contacto = $pdo->prepare("
            INSERT INTO contactos (rut, nombre, rol, primario, fono, email)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        foreach ($input['contactos'] as $c) {
            $stmt_contacto->execute([
                $input['rut'],
                $c['nombre'] ?? '',
                $c['rol'] ?? 'comercial',
                $c['primario'] ?? 'N',
                $c['fono'] ?? '',
                $c['email'] ?? ''
            ]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Ficha cliente guardada correctamente']);

} catch (Exception $e) {
    try { $pdo->rollback(); } catch (Exception $ex) {}
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>