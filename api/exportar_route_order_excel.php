<?php
// api/exportar_route_order_excel.php
// Genera un archivo Excel para el Route Order con formato idéntico al submodal

// --- Prevenir cualquier salida no deseada ---
if (ob_get_level()) {
    ob_end_clean();
}
while (ob_get_level()) {
    ob_end_clean();
}

// --- Manejo de errores seguro ---
error_reporting(E_ALL);
ini_set('display_errors', 0); // Nunca mostrar errores en la salida
ini_set('log_errors', 1);
ini_set('memory_limit', '256M');

// --- Cargar dependencias ---
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// --- Leer datos JSON ---
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true);

if (!$data || !isset($data['servicio'])) {
    http_response_code(400);
    exit;
}

$datosRO = $data;
$servicio = $datosRO['servicio'];
$prospecto = $datosRO['prospecto'] ?? [];
$costos = $datosRO['costos'] ?? [];
$gastos_locales = $datosRO['gastos_locales'] ?? [];
$transporte = $datosRO['transporte_nac'] ?? null;

// --- Crear spreadsheet ---
$spreadsheet = new Spreadsheet();
$hoja = $spreadsheet->getActiveSheet();
$hoja->setTitle('Route Order');

// --- Estilos ---
$estiloTitulo = [
    'font' => ['bold' => true, 'size' => 12],
];
$estiloCabecera = [
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
];
$estiloCelda = [
    'alignment' => ['vertical' => Alignment::VERTICAL_TOP],
];
$estiloNumero = [
    'numberFormat' => ['formatCode' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1],
];
$estiloTexto = [
    'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_TOP],
];

// --- Función auxiliar para nombre seguro ---
function nombreArchivoSeguro($str) {
    $str = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $str ?? '');
    return substr($str, 0, 100);
}

// --- Inicio del contenido ---
$row = 1;

// === Número Cotización (Columna A-B) ===
$hoja->setCellValue("A{$row}", "Nº Cotización:");
$hoja->setCellValue("B{$row}", $prospecto['concatenado'] ?? 'N_A');
$hoja->getStyle("A{$row}:B{$row}")->applyFromArray($estiloTitulo);
$row += 2;

// === Bloque Izquierdo: SHIPPER + Datos del Servicio ===
$hoja->setCellValue("A{$row}", "SHIPPER:");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->setCellValue("A{$row}", "DIRECCIÓN:");
$hoja->setCellValue("B{$row}", $servicio['direccion'] ?? '');
$row++;
$hoja->setCellValue("A{$row}", "CONTACTO:");
$hoja->setCellValue("B{$row}", $servicio['contacto_nombre'] ?? '');
$row++;
$hoja->setCellValue("A{$row}", "R.U.T:");
$hoja->setCellValue("B{$row}", $servicio['rut_empresa'] ?? '');
$row += 2;

// INCOTERM, COMMODITY, etc.
$hoja->setCellValue("A{$row}", "INCOTERM:");
$hoja->setCellValue("B{$row}", $servicio['incoterm'] ?? '');
$row++;
$hoja->setCellValue("A{$row}", "COMMODITY:");
$hoja->setCellValue("B{$row}", $servicio['commodity'] ?? '');
$row++;
$hoja->setCellValue("A{$row}", "VOLÚMEN:");
$hoja->setCellValue("B{$row}", $servicio['volumen'] ?? '');
$hoja->getStyle("B{$row}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
$row++;
$hoja->setCellValue("A{$row}", "PESO BRUTO:");
$hoja->setCellValue("B{$row}", $servicio['peso'] ?? '');
$hoja->getStyle("B{$row}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
$row++;
$hoja->setCellValue("A{$row}", "DIMENSIONES:");
$hoja->setCellValue("B{$row}", $servicio['dimensiones'] ?? '');
$row++;
$hoja->setCellValue("A{$row}", "UNIDADES:");
$hoja->setCellValue("B{$row}", $servicio['bultos'] ?? '');
$row++;
$hoja->setCellValue("A{$row}", "POL:");
$hoja->setCellValue("B{$row}", $servicio['origen'] ?? '');
$row++;
$hoja->setCellValue("A{$row}", "POD:");
$hoja->setCellValue("B{$row}", $servicio['destino'] ?? '');
$row += 2;

// NOTAS ADICIONALES
$hoja->setCellValue("A{$row}", "NOTAS ADICIONALES:");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->setCellValue("A{$row}", $servicio['nota_srvc'] ?? '');
$hoja->getStyle("A{$row}")->applyFromArray($estiloTexto);
$row += 2;

// === Bloque Derecho: PROFIT SHARE + Tablas ===
$hoja->setCellValue("D{$row}", "PROFIT SHARE");
$hoja->getStyle("D{$row}")->applyFromArray($estiloTitulo);
$row++;

// --- Costos (Columna D-G) ---
$hoja->setCellValue("D{$row}", "Costos");
$hoja->getStyle("D{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->fromArray(['Concepto', 'Moneda', 'Qty', 'Costo', 'Venta', 'Total', 'Aplica'], null, "D{$row}");
$hoja->getStyle("D{$row}:J{$row}")->applyFromArray($estiloCabecera);
$row++;

foreach ($costos as $c) {
    $hoja->fromArray([
        $c['concepto'] ?? '',
        $c['moneda'] ?? '',
        $c['qty'] ?? 0,
        $c['costo'] ?? 0,
        $c['tarifa'] ?? 0,
        $c['total_costo'] ?? 0,
        $c['aplica'] ?? ''
    ], null, "D{$row}");
    $hoja->getStyle("D{$row}:J{$row}")->applyFromArray($estiloCelda);
    $hoja->getStyle("F{$row}:I{$row}")->applyFromArray($estiloNumero);
    $row++;
}
$row++;

// --- Gastos Locales (Columna D-G) ---
$hoja->setCellValue("D{$row}", "Gastos Locales");
$hoja->getStyle("D{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->fromArray(['Tipo', 'Gasto', 'Moneda', 'Monto', 'Afecto', 'IVA%'], null, "D{$row}");
$hoja->getStyle("D{$row}:I{$row}")->applyFromArray($estiloCabecera);
$row++;

foreach ($gastos_locales as $g) {
    $hoja->fromArray([
        $g['tipo'] ?? '',
        $g['gasto'] ?? '',
        $g['moneda'] ?? '',
        $g['monto'] ?? 0,
        $g['afecto'] ?? '',
        $g['iva'] ?? 0
    ], null, "D{$row}");
    $hoja->getStyle("D{$row}:I{$row}")->applyFromArray($estiloCelda);
    $hoja->getStyle("G{$row}")->applyFromArray($estiloNumero);
    $hoja->getStyle("I{$row}")->applyFromArray($estiloNumero);
    $row++;
}
$row++;

// --- Totales Profit (Columna D-E) ---
$venta = $servicio['venta'] ?? 0;
$costo = $servicio['costo'] ?? 0;
$profitLocal = $venta - $costo;
$profitPorc = $venta > 0 ? (($venta - $costo) / $venta * 100) : 0;

$hoja->setCellValue("D{$row}", "TOTAL VENTA:");
$hoja->setCellValue("E{$row}", $venta);
$hoja->getStyle("E{$row}")->applyFromArray($estiloNumero);
$row++;
$hoja->setCellValue("D{$row}", "TOTAL COSTO:");
$hoja->setCellValue("E{$row}", $costo);
$hoja->getStyle("E{$row}")->applyFromArray($estiloNumero);
$row++;
$hoja->setCellValue("D{$row}", "PROFIT LOCAL:");
$hoja->setCellValue("E{$row}", $profitLocal);
$hoja->getStyle("E{$row}")->applyFromArray($estiloNumero);
$row++;
$hoja->setCellValue("D{$row}", "PROFIT %:");
$hoja->setCellValue("E{$row}", $profitPorc);
$hoja->getStyle("E{$row}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
$row += 2;

// === Condiciones Comerciales (Columna D) ===
$hoja->setCellValue("D{$row}", "CONDICIONES COMERCIALES");
$hoja->getStyle("D{$row}")->applyFromArray($estiloTitulo);
$row += 2;

// === Transporte Nacional (Columna D-G) ===
$hoja->setCellValue("D{$row}", "TRANSPORTE NACIONAL");
$hoja->getStyle("D{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->fromArray(['Concepto', 'Moneda', 'Costo', 'Venta', 'Profit', 'Acepta', 'Afecto'], null, "D{$row}");
$hoja->getStyle("D{$row}:J{$row}")->applyFromArray($estiloCabecera);
$row++;

if ($transporte) {
    $profit_transp = ($transporte['venta'] ?? 0) - ($transporte['costo'] ?? 0);
    $hoja->fromArray([
        $transporte['concepto'] ?? 'NACIONAL',
        $transporte['moneda'] ?? 'CLP',
        $transporte['costo'] ?? 0,
        $transporte['venta'] ?? 0,
        $profit_transp,
        $transporte['acepta'] ?? 'No',
        $transporte['afecto'] ?? 'No'
    ], null, "D{$row}");
    $hoja->getStyle("D{$row}:J{$row}")->applyFromArray($estiloCelda);
    $hoja->getStyle("F{$row}:H{$row}")->applyFromArray($estiloNumero);
} else {
    $hoja->fromArray(['', '', '', '', '', '', ''], null, "D{$row}");
}
$row += 2;

// === Seguro (Columna D-G) ===
$hoja->setCellValue("D{$row}", "SEGURO");
$hoja->getStyle("D{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->fromArray(['Concepto', 'Moneda', 'Costo', 'Venta', 'Min.', 'V.Venta', 'Aplica'], null, "D{$row}");
$hoja->getStyle("D{$row}:J{$row}")->applyFromArray($estiloCabecera);
$row++;
$hoja->fromArray(['', '', '', '', '', '', ''], null, "D{$row}");
$row += 2;

// === Notas A Operaciones (Columna A) ===
$hoja->setCellValue("A{$row}", "NOTAS A OPERACIONES");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->setCellValue("A{$row}", $servicio['notas_operaciones'] ?? '');
$hoja->getStyle("A{$row}")->applyFromArray($estiloTexto);
$row += 2;

// === Notas Comerciales (Columna A) ===
$hoja->setCellValue("A{$row}", "NOTAS COMERCIALES");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->setCellValue("A{$row}", $servicio['notas_comerciales'] ?? '');
$hoja->getStyle("A{$row}")->applyFromArray($estiloTexto);

// --- Autoajustar columnas ---
foreach (range('A', 'J') as $col) {
    $hoja->getColumnDimension($col)->setAutoSize(true);
}

// --- Salida final ---
$writer = new Xlsx($spreadsheet);

// Headers seguros
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Route_Order_' . nombreArchivoSeguro($prospecto['concatenado'] ?? 'N_A') . '.xlsx"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

// Salida limpia
$writer->save('php://output');
exit;