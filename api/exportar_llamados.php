<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Evitar caché
header('Cache-Control: no-cache, must-revalidate');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="llamados_' . date('Ymd_His') . '.xlsx"');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Encabezados para Llamados
    $headers = [
        'Comercial',
        'Cliente',
        'Fecha/Hora',
        'Tipo Llamado',
        'Nota'
    ];
    $sheet->fromArray([$headers], null, 'A1');

    // Consulta
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
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Escribir datos
    $rowIndex = 2;
    foreach ($rows as $r) {
        $sheet->setCellValue("A{$rowIndex}", $r['comercial']);
        $sheet->setCellValue("B{$rowIndex}", $r['cliente']);
        $sheet->setCellValue("C{$rowIndex}", $r['fecha_hora']);
        $sheet->setCellValue("D{$rowIndex}", $r['tipo_llamado']);
        $sheet->setCellValue("E{$rowIndex}", $r['nota']);
        $rowIndex++;
    }

    // Ajustar ancho
    $sheet->getColumnDimension('A')->setWidth(16);
    $sheet->getColumnDimension('B')->setWidth(30);
    $sheet->getColumnDimension('C')->setWidth(20);
    $sheet->getColumnDimension('D')->setWidth(20);
    $sheet->getColumnDimension('E')->setWidth(40);

    // Guardar y enviar
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

} catch (Exception $e) {
    http_response_code(500);
    echo "Error al exportar: " . htmlspecialchars($e->getMessage());
}
?>