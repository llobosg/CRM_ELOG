<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="prospectos_' . date('Ymd_His') . '.xlsx"');

try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    $headers = ['Razón Social', 'RUT', 'País', 'Estado'];
    $sheet->fromArray([$headers], null, 'A1');

    $sql = "SELECT razon_social, rut_empresa, pais, estado FROM prospectos WHERE 1=1";
    $params = [];
    
    if ($_SESSION['rol'] === 'comercial') {
        $sql .= " AND id_comercial = ?";
        $params[] = $_SESSION['user_id'];
    }
    
    $sql .= " ORDER BY fecha_alta DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $rowIndex = 2;
    foreach ($rows as $r) {
        $sheet->setCellValue("A{$rowIndex}", $r['razon_social'] ?? '');
        $sheet->setCellValue("B{$rowIndex}", $r['rut_empresa'] ?? '');
        $sheet->setCellValue("C{$rowIndex}", $r['pais'] ?? '');
        $sheet->setCellValue("D{$rowIndex}", $r['estado'] ?? '');
        $rowIndex++;
    }

    $sheet->getColumnDimension('A')->setWidth(35);
    $sheet->getColumnDimension('B')->setWidth(15);
    $sheet->getColumnDimension('C')->setWidth(20);
    $sheet->getColumnDimension('D')->setWidth(15);

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    error_log("Error exportar prospectos: " . $e->getMessage());
    echo "Error al generar el archivo Excel.";
    exit;
}
?>