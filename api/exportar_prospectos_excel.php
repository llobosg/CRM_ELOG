<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Evitar caché
header('Cache-Control: no-cache, must-revalidate');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="prospectos_' . date('Ymd_His') . '.xlsx"');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Encabezados (usando las columnas que SÍ existen en tu tabla `prospectos`)
    $headers = [
        'Comercial',
        'Cliente',
        'Fecha',
        'Concatenado',
        'Servicio',
        'Costo',
        'Venta',
        'GDC',
        'GDV'
    ];
    $sheet->fromArray([$headers], null, 'A1');

    // Consulta segura (sin columnas inexistentes como `region`)
    $sql = "
        SELECT 
            p.nombre AS comercial,
            p.razon_social AS cliente,
            p.fecha_alta AS fecha,
            p.concatenado,
            GROUP_CONCAT(s.servicio SEPARATOR '; ') AS servicio,
            SUM(s.costo) AS costo,
            SUM(s.venta) AS venta,
            SUM(s.costogastoslocalesdestino) AS gdc,
            SUM(s.ventasgastoslocalesdestino) AS gdv
        FROM prospectos p
        LEFT JOIN servicios s ON p.id_ppl = s.id_prospect
        WHERE 1=1
    ";

    // Filtro por rol (solo comerciales ven sus propios prospectos)
    $params = [];
    if ($_SESSION['rol'] === 'comercial') {
        $sql .= " AND p.id_comercial = ?";
        $params[] = $_SESSION['user_id'];
    }

    $sql .= " GROUP BY p.id_ppl ORDER BY p.fecha_alta DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Escribir datos
    $rowIndex = 2;
    foreach ($rows as $r) {
        $sheet->setCellValue("A{$rowIndex}", $r['comercial']);
        $sheet->setCellValue("B{$rowIndex}", $r['cliente']);
        $sheet->setCellValue("C{$rowIndex}", $r['fecha']);
        $sheet->setCellValue("D{$rowIndex}", $r['concatenado']);
        $sheet->setCellValue("E{$rowIndex}", $r['servicio'] ?: '');
        $sheet->setCellValue("F{$rowIndex}", $r['costo'] ?? 0);
        $sheet->setCellValue("G{$rowIndex}", $r['venta'] ?? 0);
        $sheet->setCellValue("H{$rowIndex}", $r['gdc'] ?? 0);
        $sheet->setCellValue("I{$rowIndex}", $r['gdv'] ?? 0);
        $rowIndex++;
    }

    // Ajustar ancho de columnas
    $sheet->getColumnDimension('A')->setWidth(16);
    $sheet->getColumnDimension('B')->setWidth(30);
    $sheet->getColumnDimension('C')->setWidth(12);
    $sheet->getColumnDimension('D')->setWidth(20);
    $sheet->getColumnDimension('E')->setWidth(25);
    $sheet->getColumnDimension('F')->setWidth(10);
    $sheet->getColumnDimension('G')->setWidth(10);
    $sheet->getColumnDimension('H')->setWidth(10);
    $sheet->getColumnDimension('I')->setWidth(10);

    // Guardar y enviar
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

} catch (Exception $e) {
    http_response_code(500);
    echo "Error al exportar: " . htmlspecialchars($e->getMessage());
}
?>