<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth_check.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="prospectos_' . date('Ymd') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// Encabezados (solo campos que existen en tu tabla)
fputcsv($output, [
    'ID', 'Razón Social', 'RUT', 'Teléfono', 'País', 'Región', 'Ciudad', 'Comuna',
    'Dirección', 'Operación', 'Tipo Operación', 'Estado', 'Booking', 'Incoterm',
    'Comercial ID', 'Nombre Comercial', 'Apellido Comercial', 'Fecha Alta'
]);

// Consulta (solo columnas que existen en tu tabla)
$stmt = $pdo->query("
    SELECT 
        concatenado, razon_social, rut_empresa, fono_empresa,
        pais, region, ciudad, comuna, direccion,
        operacion, tipo_oper, estado, booking, incoterm,
        id_comercial, nombre, apellido, fecha_alta
    FROM prospectos
    ORDER BY id_ppl DESC
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, array_values($row));
}

fclose($output);
exit;
?>