<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

ob_clean();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="llamados_' . date('Ymd_His') . '.xlsx"');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Encabezados: Comercial, Cliente, Fecha/Hora, Tipo Llamado, Nota
    $headers = ['Comercial', 'Cliente', 'Fecha/Hora', 'Tipo Llamado', 'Nota'];
    $sheet->fromArray([$headers], null, 'A1');

    $sql = "
        SELECT 
            nombre_comercial AS comercial,
            razon_social AS cliente,
            CONCAT(fecha, ' ', hora) AS fecha_hora,
            tipo_gestion AS tipo_llamado,
            nota
        FROM llamados WHERE 1=1
    ";
    $params = [];
    
    if ($_SESSION['rol'] === 'comercial') {
        $sql .= " AND id_comercial = ?";
        $params[] = $_SESSION['user_id'];
    }
    
    $sql .= " ORDER BY fecha DESC, hora DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $rowIndex = 2;
    foreach ($rows as $r) {
        $sheet->setCellValue("A{$rowIndex}", $r['comercial'] ?? '');
        $sheet->setCellValue("B{$rowIndex}", $r['cliente'] ?? '');
        $sheet->setCellValue("C{$rowIndex}", $r['fecha_hora'] ?? '');
        $sheet->setCellValue("D{$rowIndex}", $r['tipo_llamado'] ?? '');
        $sheet->setCellValue("E{$rowIndex}", $r['nota'] ?? '');
        $rowIndex++;
    }

    $sheet->getColumnDimension('A')->setWidth(20);
    $sheet->getColumnDimension('B')->setWidth(30);
    $sheet->getColumnDimension('C')->setWidth(20);
    $sheet->getColumnDimension('D')->setWidth(20);
    $sheet->getColumnDimension('E')->setWidth(50);

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    error_log("Error exportar llamados: " . $e->getMessage());
    http_response_code(500);
    echo "Error al generar el archivo Excel.";
    exit;
}
?>