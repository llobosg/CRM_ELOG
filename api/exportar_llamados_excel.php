<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Para PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    $id_prospecto = $_GET['id_prospecto'] ?? null;
    if (!$id_prospecto) throw new Exception('ID de prospecto requerido');
    
    // Obtener datos
    $stmt = $pdo->prepare("
        SELECT l.*, p.concatenado 
        FROM llamados l
        JOIN prospectos p ON l.id_prospecto = p.id_ppl
        WHERE l.id_prospecto = ?
        ORDER BY l.fecha DESC, l.hora DESC
    ");
    $stmt->execute([$id_prospecto]);
    $llamados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($llamados)) throw new Exception('No hay llamados para exportar');
    
    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Encabezados
    $sheet->setCellValue('A1', 'Fecha/Hora');
    $sheet->setCellValue('B1', 'Tipo Gestión');
    $sheet->setCellValue('C1', 'Comercial');
    $sheet->setCellValue('D1', 'Cliente');
    $sheet->setCellValue('E1', 'RUT Cliente');
    $sheet->setCellValue('F1', 'Nota');
    $sheet->setCellValue('G1', 'Código Prospecto');
    
    // Datos
    $row = 2;
    foreach ($llamados as $l) {
        $sheet->setCellValue("A{$row}", "{$l['fecha']} {$l['hora']}");
        $sheet->setCellValue("B{$row}", $l['tipo_gestion']);
        $sheet->setCellValue("C{$row}", $l['nombre_comercial']);
        $sheet->setCellValue("D{$row}", $l['razon_social']);
        $sheet->setCellValue("E{$row}", $l['rut_cliente']);
        $sheet->setCellValue("F{$row}", $l['nota']);
        $sheet->setCellValue("G{$row}", $l['concatenado']);
        $row++;
    }
    
    // Configurar descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="llamados_prospecto.xlsx"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
    
} catch (Exception $e) {
    echo "Error al exportar: " . $e->getMessage();
}
?>