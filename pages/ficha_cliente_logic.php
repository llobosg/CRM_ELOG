<?php
// pages/ficha_cliente_logic.php

// Capturar cualquier salida previa
ob_start();

// Establecer encabezado JSON desde el inicio
header('Content-Type: application/json; charset=utf-8');

// Desactivar errores visibles (solo logs)
ini_set('display_errors', 0);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../includes/auth_check.php';

    // === Validar rol (solo admin y admin_finanzas) ===
    $rolesPermitidos = ['admin', 'admin_finanzas'];
    if (!in_array($_SESSION['rol'] ?? '', $rolesPermitidos)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // === Logging de depuración ===
        error_log("DEBUG POST: " . json_encode($_POST));

        $input = $_POST;

        // === Validar RUT ===
        $rut = $input['rut'] ?? '';
        if (!$rut) {
            throw new Exception('RUT es obligatorio');
        }
        $rut_limpio = preg_replace('/[^0-9kK]/', '', strtolower($rut));
        if (!validarRutChileno($rut_limpio)) {
            throw new Exception('RUT inválido');
        }

        // === Manejar id_comercial correctamente ===
        $id_comercial = null;
        
        // Caso 1: viene como id_comercial (correcto)
        if (!empty($input['id_comercial'])) {
            $id_comercial = (int)$input['id_comercial'];
        }
        // Caso 2: viene como nombre_comercial pero es numérico (retrocompatibilidad)
        elseif (!empty($input['nombre_comercial']) && is_numeric($input['nombre_comercial'])) {
            $id_comercial = (int)$input['nombre_comercial'];
        }

        // === Obtener nombre_comercial real desde la tabla comerciales ===
        $nombre_comercial = '';
        if ($id_comercial) {
            $stmt_nombre = $pdo->prepare("SELECT nombre FROM comerciales WHERE id_comercial = ?");
            $stmt_nombre->execute([$id_comercial]);
            $nombre_comercial = $stmt_nombre->fetchColumn() ?: '';
        }

        // === Sanitizar campos de texto (eliminar tabs y espacios extraños) ===
        function sanitizarTexto($texto) {
            return trim(preg_replace('/[\t\n\r\x0B\x0C]/', ' ', $texto));
        }

        // === Preparar datos del cliente ===
        $data_cliente = [
            'rut' => $rut_limpio,
            'razon_social' => sanitizarTexto($input['razon_social'] ?? ''),
            'nacional_extranjero' => sanitizarTexto($input['nacional_extranjero'] ?? 'Nacional'),
            'pais' => sanitizarTexto($input['pais'] ?? ''),
            'direccion' => sanitizarTexto($input['direccion'] ?? ''),
            'comuna' => sanitizarTexto($input['comuna'] ?? ''),
            'ciudad' => sanitizarTexto($input['ciudad'] ?? ''),
            'giro' => sanitizarTexto($input['giro'] ?? ''),
            'fecha_creacion' => !empty($input['fecha_creacion']) ? $input['fecha_creacion'] : null,
            'id_comercial' => $id_comercial,
            'nombre_comercial' => $nombre_comercial,
            'tipo_vida' => sanitizarTexto($input['tipo_vida'] ?? 'lead'),
            'fecha_vida' => !empty($input['fecha_vida']) ? $input['fecha_vida'] : null,
            'rubro' => sanitizarTexto($input['rubro'] ?? ''),
            'potencial_usd' => isset($input['potencial_usd']) ? (float)$input['potencial_usd'] : 0.00,
            'fecha_alta_credito' => !empty($input['fecha_alta_credito']) ? $input['fecha_alta_credito'] : null,
            'plazo_dias' => sanitizarTexto($input['plazo_dias'] ?? '30'),
            'estado_credito' => sanitizarTexto($input['estado_credito'] ?? 'vigente'),
            'monto_credito' => isset($input['monto_credito']) ? (float)$input['monto_credito'] : 0.00,
        ];

        // === Verificar si el cliente existe ===
        $stmt_check = $pdo->prepare("SELECT rut FROM clientes WHERE rut = ?");
        $stmt_check->execute([$rut_limpio]);
        $cliente_existe = $stmt_check->fetch();

        if ($cliente_existe) {
            // === ACTUALIZAR cliente existente ===
            $setParts = [];
            $values = [];
            foreach ($data_cliente as $key => $value) {
                $setParts[] = "$key = ?";
                $values[] = $value;
            }
            $values[] = $rut_limpio;
            $sql = "UPDATE clientes SET " . implode(', ', $setParts) . " WHERE rut = ?";
            $pdo->prepare($sql)->execute($values);

            // === Actualizar contactos ===
            $contactos = json_decode($input['contactos'] ?? '[]', true);
            if (!empty($contactos)) {
                // Eliminar contactos existentes
                $pdo->prepare("DELETE FROM contactos WHERE rut_cliente = ?")->execute([$rut_limpio]);
                
                // Insertar nuevos contactos
                foreach ($contactos as $c) {
                    $stmt_c = $pdo->prepare("
                        INSERT INTO contactos (rut_cliente, nom_contacto, fono_contacto, email, rol, primario)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt_c->execute([
                        $rut_limpio,
                        sanitizarTexto($c['nom_contacto'] ?? ''),
                        sanitizarTexto($c['fono_contacto'] ?? ''),
                        sanitizarTexto($c['email'] ?? ''),
                        sanitizarTexto($c['rol'] ?? ''),
                        sanitizarTexto($c['primario'] ?? 'N')
                    ]);
                    error_log("CLIENTE UPDATE - RUT: {$rut_limpio}, ID_COMERCIAL: {$id_comercial}, NOMBRE: {$nombre_comercial}");
                }
            }

            $mensaje = 'Cliente actualizado correctamente';
        } else {
            // === INSERTAR nuevo cliente ===
            $fields = implode(', ', array_keys($data_cliente));
            $placeholders = str_repeat('?,', count($data_cliente) - 1) . '?';
            $sql = "INSERT INTO clientes ($fields) VALUES ($placeholders)";
            $pdo->prepare($sql)->execute(array_values($data_cliente));

            // === Insertar contactos ===
            $contactos = json_decode($input['contactos'] ?? '[]', true);
            foreach ($contactos as $c) {
                $stmt_c = $pdo->prepare("
                    INSERT INTO contactos (rut_cliente, nom_contacto, fono_contacto, email, rol, primario)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt_c->execute([
                    $rut_limpio,
                    sanitizarTexto($c['nom_contacto'] ?? ''),
                    sanitizarTexto($c['fono_contacto'] ?? ''),
                    sanitizarTexto($c['email'] ?? ''),
                    sanitizarTexto($c['rol'] ?? ''),
                    sanitizarTexto($c['primario'] ?? 'N')
                ]);
            }
            error_log("CLIENTE GUARDADO - RUT: {$rut_limpio}, ID_COMERCIAL: {$id_comercial}, NOMBRE: {$nombre_comercial}");
            $mensaje = 'Cliente creado correctamente';
        }

        $pdo->commit();

        // === Limpiar cualquier salida previa antes de enviar JSON ===
        if (ob_get_level()) {
            ob_end_clean();
        }

        echo json_encode(['success' => true, 'message' => $mensaje]);
        exit;

    } catch (Exception $e) {
        $pdo->rollback();
        error_log("ERROR FICHA CLIENTE: " . $e->getMessage());
        
        // === Limpiar cualquier salida previa ===
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        echo json_encode(['success' => false, 'message' => "Error al guardar cliente: " . $e->getMessage()]);
        exit;
    }
}

// === Función de validación de RUT chileno ===
function validarRutChileno($rut) {
    if (!preg_match('/^[0-9]+[kK]?$/', $rut)) {
        return false;
    }
    
    $dv = substr($rut, -1);
    $numero = substr($rut, 0, -1);
    
    if ($numero == 0) {
        return false;
    }
    
    $factor = 2;
    $suma = 0;
    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $suma += $factor * (int)$numero[$i];
        $factor = $factor % 7 == 0 ? 2 : $factor + 1;
    }
    
    $dv_calculado = 11 - ($suma % 11);
    if ($dv_calculado == 11) {
        $dv_calculado = '0';
    } elseif ($dv_calculado == 10) {
        $dv_calculado = 'k';
    } else {
        $dv_calculado = (string)$dv_calculado;
    }
    
    return strtolower($dv) == $dv_calculado;
}
?>