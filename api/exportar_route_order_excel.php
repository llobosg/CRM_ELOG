<?php
// api/exportar_route_order_excel.php
// Genera un archivo Excel para el Route Order

// Importante: No enviar encabezados HTML ni texto antes de enviar el archivo Excel
// Por lo tanto, NO usar header('Content-Type: application/json') aquí.

// Incluir autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Incluir archivos de configuración si es necesario (para acceso a constantes o funciones auxiliares)
// require_once __DIR__ . '/../config.php';
// require_once __DIR__ . '/../includes/auth_check.php'; // Asegura que el usuario esté autenticado si es necesario

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Recibir los datos del Route Order
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true);

if (!$data || !isset($data['servicio'])) {
    http_response_code(400);
    echo "Error: Datos inválidos para generar Excel.";
    exit;
}

$datosRO = $data; // Alias para los datos recibidos
$servicio = $datosRO['servicio'];
$prospecto = $datosRO['prospecto'] ?? [];
$costos = $datosRO['costos'] ?? [];
$gastos_locales = $datosRO['gastos_locales'] ?? [];

// Crear una nueva hoja de cálculo
$spreadsheet = new Spreadsheet();
$hoja = $spreadsheet->getActiveSheet();
$hoja->setTitle('Route Order');

// --- Estilos comunes ---
$estiloTitulo = [
    'font' => ['bold' => true, 'size' => 12],
];
$estiloCabecera = [
    'font' => ['bold' => true],
    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']],
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
];
$estiloCelda = [
    'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP],
];
$estiloNumero = [
    'numberFormat' => ['formatCode' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1],
];

// --- Cabecera ---
$row = 1;
$hoja->setCellValue("A{$row}", "Nº Cotización:");
$hoja->setCellValue("B{$row}", $prospecto['concatenado'] ?? 'N/A');
$hoja->getStyle("A{$row}:B{$row}")->applyFromArray($estiloTitulo);
$row++;

// --- Datos del cliente ---
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
$row++;
$row++; // Espacio

// --- Datos del servicio ---
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
$row++;
$row++; // Espacio

// --- Notas adicionales ---
$hoja->setCellValue("A{$row}", "NOTAS ADICIONALES:");
$row++;
$hoja->setCellValue("A{$row}", $servicio['nota_srvc'] ?? '');
$hoja->getStyle("A{$row}")->getAlignment()->setWrapText(true);
$row++;
$row++; // Espacio

// --- Profit Share ---
$hoja->setCellValue("A{$row}", "PROFIT SHARE");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;

// --- Tabla de Costos ---
$hoja->setCellValue("A{$row}", "Costos");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;

$hoja->fromArray(
    ['Concepto', 'Moneda', 'Qty', 'Costo', 'Venta', 'Total', 'Aplica'],
    NULL,
    "A{$row}"
);
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
    ], NULL, "A{$row}");
    $hoja->getStyle("A{$row}:G{$row}")->applyFromArray($estiloCelda);
    $hoja->getStyle("C{$row}, D{$row}, E{$row}, F{$row}")->applyFromArray($estiloNumero);
    $row++;
}
$row++; // Espacio

// --- Tabla de Gastos Locales ---
$hoja->setCellValue("A{$row}", "Gastos Locales");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;

$hoja->fromArray(
    ['Tipo', 'Gasto', 'Moneda', 'Monto', 'Afecto', 'IVA%'],
    NULL,
    "A{$row}"
);
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
    ], NULL, "A{$row}");
    $hoja->getStyle("A{$row}:F{$row}")->applyFromArray($estiloCelda);
    $hoja->getStyle("D{$row}, F{$row}")->applyFromArray($estiloNumero);
    $row++;
}
$row++; // Espacio

// --- Totales Profit ---
$hoja->setCellValue("A{$row}", "TOTAL VENTA:");
$hoja->setCellValue("B{$row}", $servicio['venta'] ?? 0);
$hoja->getStyle("B{$row}")->applyFromArray($estiloNumero);
$row++;
$hoja->setCellValue("A{$row}", "TOTAL COSTO:");
$hoja->setCellValue("B{$row}", $servicio['costo'] ?? 0);
$hoja->getStyle("B{$row}")->applyFromArray($estiloNumero);
$row++;
$hoja->setCellValue("A{$row}", "PROFIT LOCAL:");
$hoja->setCellValue("B{$row}", ($servicio['venta'] ?? 0) - ($servicio['costo'] ?? 0));
$hoja->getStyle("B{$row}")->applyFromArray($estiloNumero);
$row++;
$hoja->setCellValue("A{$row}", "PROFIT %:");
$venta = $servicio['venta'] ?? 0;
$costo = $servicio['costo'] ?? 0;
$porcentaje = $venta > 0 ? (($venta - $costo) / $venta * 100) : 0;
$hoja->setCellValue("B{$row}", $porcentaje);
$hoja->getStyle("B{$row}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
$row++;
$row++; // Espacio

// --- Condiciones Comerciales ---
$hoja->setCellValue("A{$row}", "CONDICIONES COMERCIALES");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;
$row++; // Espacio

// --- Transporte Nacional ---
$hoja->setCellValue("A{$row}", "TRANSPORTE NACIONAL");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;

$hoja->fromArray(
    ['Concepto', 'Moneda', 'Costo', 'Venta', 'Profit', 'Acepta', 'Afecto'],
    NULL,
    "A{$row}"
);
$hoja->getStyle("A{$row}:G{$row}")->applyFromArray($estiloCabecera);
$row++;

// Verificar si se enviaron datos de transporte
$transporte = $datosRO['transporte_nac'] ?? null;
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
    ], NULL, "A{$row}");
    $hoja->getStyle("A{$row}:G{$row}")->applyFromArray($estiloCelda);
    $hoja->getStyle("C{$row}, D{$row}, E{$row}")->applyFromArray($estiloNumero);
} else {
    $hoja->fromArray(['', '', '', '', '', '', ''], NULL, "A{$row}");
}
$row++;
$row++; // Espacio

// --- Seguro ---
$hoja->setCellValue("A{$row}", "SEGURO");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;

$hoja->fromArray(
    ['Concepto', 'Moneda', 'Costo', 'Venta', 'Min.', 'V.Venta', 'Aplica'],
    NULL,
    "A{$row}"
);
$hoja->getStyle("A{$row}:G{$row}")->applyFromArray($estiloCabecera);
$row++;
// Añadir filas vacías o datos si existen
$hoja->fromArray(['', '', '', '', '', '', ''], NULL, "A{$row}");
$row++;
$row++; // Espacio

// --- Notas A Operaciones ---
$hoja->setCellValue("A{$row}", "NOTAS A OPERACIONES");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->setCellValue("A{$row}", $servicio['notas_operaciones'] ?? '');
$hoja->getStyle("A{$row}")->getAlignment()->setWrapText(true);
$row++;
$row++; // Espacio

// --- Notas Comerciales ---
$hoja->setCellValue("A{$row}", "NOTAS COMERCIALES");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->setCellValue("A{$row}", $servicio['notas_comerciales'] ?? '');
$hoja->getStyle("A{$row}")->getAlignment()->setWrapText(true);

// --- Autoajustar ancho de columnas ---
foreach (range('A', 'G') as $col) {
    $hoja->getColumnDimension($col)->setAutoSize(true);
}

// --- Generar y enviar archivo Excel ---
$writer = new Xlsx($spreadsheet);

// Configurar encabezados para descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Route_Order_' . ($prospecto['concatenado'] ?? 'N/A') . '.xlsx"');
header('Cache-Control: max-age=0');
// Si se usan buffers, limpiarlos
if (ob_get_level()) {
    ob_end_clean();
}

$writer->save('php://output');
exit; // Terminar la ejecución para asegurar que solo se envía el archivo
?>