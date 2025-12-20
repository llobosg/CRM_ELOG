<?php
// api/exportar_route_order_excel.php
// Genera un archivo Excel para el Route Order

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
$estiloTitulo = ['font' => ['bold' => true, 'size' => 12]];
$estiloCabecera = [
    'font' => ['bold' => true],
    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']],
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
];
$estiloCelda = ['alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP]];
$estiloNumero = ['numberFormat' => ['formatCode' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1]];

// --- Función auxiliar para nombre seguro ---
function nombreArchivoSeguro($str) {
    $str = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $str ?? '');
    return substr($str, 0, 100);
}

// --- Inicio del contenido ---
$row = 1;

// Cabecera
$hoja->setCellValue("A{$row}", "Nº Cotización:");
$hoja->setCellValue("B{$row}", $prospecto['concatenado'] ?? 'N_A');
$hoja->getStyle("A{$row}:B{$row}")->applyFromArray($estiloTitulo);
$row += 2;

// SHIPPER
$hoja->setCellValue("A{$row}", "SHIPPER:");
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

// Servicio
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

// Notas
$hoja->setCellValue("A{$row}", "NOTAS ADICIONALES:");
$row++;
$hoja->setCellValue("A{$row}", $servicio['nota_srvc'] ?? '');
$hoja->getStyle("A{$row}")->getAlignment()->setWrapText(true);
$row += 2;

// Profit Share
$hoja->setCellValue("A{$row}", "PROFIT SHARE");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;

// Costos
$hoja->setCellValue("A{$row}", "Costos");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->fromArray(['Concepto', 'Moneda', 'Qty', 'Costo', 'Venta', 'Total', 'Aplica'], null, "A{$row}");
$hoja->getStyle("A{$row}:G{$row}")->applyFromArray($estiloCabecera);
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
    ], null, "A{$row}");
    $hoja->getStyle("A{$row}:G{$row}")->applyFromArray($estiloCelda);
    $hoja->getStyle("C{$row}:F{$row}")->applyFromArray($estiloNumero); // ✅ Corregido: rango continuo
    $row++;
}
$row++;

// Gastos Locales
$hoja->setCellValue("A{$row}", "Gastos Locales");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->fromArray(['Tipo', 'Gasto', 'Moneda', 'Monto', 'Afecto', 'IVA%'], null, "A{$row}");
$hoja->getStyle("A{$row}:F{$row}")->applyFromArray($estiloCabecera);
$row++;

foreach ($gastos_locales as $g) {
    $hoja->fromArray([
        $g['tipo'] ?? '',
        $g['gasto'] ?? '',
        $g['moneda'] ?? '',
        $g['monto'] ?? 0,
        $g['afecto'] ?? '',
        $g['iva'] ?? 0
    ], null, "A{$row}");
    $hoja->getStyle("A{$row}:F{$row}")->applyFromArray($estiloCelda);
    // ✅ Corregido: celdas no contiguas → aplicar por separado
    $hoja->getStyle("D{$row}")->applyFromArray($estiloNumero);
    $hoja->getStyle("F{$row}")->applyFromArray($estiloNumero);
    $row++;
}
$row++;

// Totales Profit
$venta = $servicio['venta'] ?? 0;
$costo = $servicio['costo'] ?? 0;
$profitLocal = $venta - $costo;
$profitPorc = $venta > 0 ? (($venta - $costo) / $venta * 100) : 0;

$hoja->setCellValue("A{$row}", "TOTAL VENTA:");
$hoja->setCellValue("B{$row}", $venta);
$hoja->getStyle("B{$row}")->applyFromArray($estiloNumero);
$row++;
$hoja->setCellValue("A{$row}", "TOTAL COSTO:");
$hoja->setCellValue("B{$row}", $costo);
$hoja->getStyle("B{$row}")->applyFromArray($estiloNumero);
$row++;
$hoja->setCellValue("A{$row}", "PROFIT LOCAL:");
$hoja->setCellValue("B{$row}", $profitLocal);
$hoja->getStyle("B{$row}")->applyFromArray($estiloNumero);
$row++;
$hoja->setCellValue("A{$row}", "PROFIT %:");
$hoja->setCellValue("B{$row}", $profitPorc);
$hoja->getStyle("B{$row}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
$row += 2;

// Condiciones Comerciales
$hoja->setCellValue("A{$row}", "CONDICIONES COMERCIALES");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row += 2;

// Transporte Nacional
$hoja->setCellValue("A{$row}", "TRANSPORTE NACIONAL");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->fromArray(['Concepto', 'Moneda', 'Costo', 'Venta', 'Profit', 'Acepta', 'Afecto'], null, "A{$row}");
$hoja->getStyle("A{$row}:G{$row}")->applyFromArray($estiloCabecera);
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
    ], null, "A{$row}");
    $hoja->getStyle("A{$row}:G{$row}")->applyFromArray($estiloCelda);
    $hoja->getStyle("C{$row}:E{$row}")->applyFromArray($estiloNumero); // ✅ Corregido: rango continuo
} else {
    $hoja->fromArray(['', '', '', '', '', '', ''], null, "A{$row}");
}
$row += 2;

// Seguro
$hoja->setCellValue("A{$row}", "SEGURO");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->fromArray(['Concepto', 'Moneda', 'Costo', 'Venta', 'Min.', 'V.Venta', 'Aplica'], null, "A{$row}");
$hoja->getStyle("A{$row}:G{$row}")->applyFromArray($estiloCabecera);
$row++;
$hoja->fromArray(['', '', '', '', '', '', ''], null, "A{$row}");
$row += 2;

// Notas
$hoja->setCellValue("A{$row}", "NOTAS A OPERACIONES");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->setCellValue("A{$row}", $servicio['notas_operaciones'] ?? '');
$hoja->getStyle("A{$row}")->getAlignment()->setWrapText(true);
$row += 2;

$hoja->setCellValue("A{$row}", "NOTAS COMERCIALES");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->setCellValue("A{$row}", $servicio['notas_comerciales'] ?? '');
$hoja->getStyle("A{$row}")->getAlignment()->setWrapText(true);

// Ajustar columnas
foreach (range('A', 'G') as $col) {
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