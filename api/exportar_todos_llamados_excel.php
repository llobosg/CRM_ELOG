<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Encabezados para CSV en UTF-8 (compatible con Excel)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="llamados_' . date('Ymd_His') . '.csv"');

// Salida UTF-8 BOM para Excel (opcional pero recomendado)
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

// Escribir encabezados
fputcsv($output, [
    'Comercial',
    'Cliente',
    'Fecha/Hora',
    'Tipo Llamado',
    'Nota'
], ';'); // Usamos ';' como delimitador (Excel en español lo espera así)

// Consulta segura
$sql = "
    SELECT 
        l.nombre_comercial AS comercial,
        l.razon_social AS cliente,
        CONCAT(l.fecha, ' ', l.hora) AS fecha_hora,
        l.tipo_gestion AS tipo_llamado,
        l.nota
    FROM llamados l
    WHERE 1=1
";
$params = [];

if ($_SESSION['rol'] === 'comercial') {
    $sql .= " AND l.id_comercial = ?";
    $params[] = $_SESSION['user_id'];
}

$sql .= " ORDER BY l.fecha DESC, l.hora DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

// Escribir filas
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['comercial'] ?? '',
        $row['cliente'] ?? '',
        $row['fecha_hora'] ?? '',
        $row['tipo_llamado'] ?? '',
        $row['nota'] ?? ''
    ], ';');
}

fclose($output);
exit;
?>