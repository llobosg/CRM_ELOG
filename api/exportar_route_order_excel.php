<?php
// api/exportar_route_order_excel.php
// Layout exacto del submodal Route Order

// --- Prevenir salida no deseada ---
if (ob_get_level()) ob_end_clean();
while (ob_get_level()) ob_end_clean();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('memory_limit', '256M');

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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

// Datos de SHIPPER/CONSIGNATARIO
$operacion = ($prospecto['operacion'] ?? '') ?: ($datosRO['servicio']['operacion'] ?? '');
$esImportacion = strtolower($operacion) === 'im';

$shipperRS = '';
$shipperDir = '';
$shipperCont = '';
$shipperRut = '';
$consignatarioRS = '';
$consignatarioDir = '';
$consignatarioCont = '';
$consignatarioRut = '';

if ($esImportacion) {
    $consignatarioRS = $servicio['razon_social'] ?? '';
    $consignatarioDir = $servicio['direccion'] ?? '';
    $consignatarioCont = $servicio['contacto_nombre'] ?? '';
    $consignatarioRut = $servicio['rut_empresa'] ?? '';
} else {
    $shipperRS = $servicio['razon_social'] ?? '';
    $shipperDir = $servicio['direccion'] ?? '';
    $shipperCont = $servicio['contacto_nombre'] ?? '';
    $shipperRut = $servicio['rut_empresa'] ?? '';
}

// --- Spreadsheet ---
$spreadsheet = new Spreadsheet();
$hoja = $spreadsheet->getActiveSheet();
$hoja->setTitle('Route Order');

// --- Estilos ---
$estiloTitulo = ['font' => ['bold' => true, 'size' => 12]];
$estiloCabecera = [
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
];
$estiloCelda = ['alignment' => ['vertical' => Alignment::VERTICAL_TOP]];
$estiloNumero = ['numberFormat' => ['formatCode' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1]];
$estiloTexto = ['alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_TOP]];

function nombreArchivoSeguro($str) {
    return substr(preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $str ?? ''), 0, 100);
}

$row = 1;

// === Nº Cotización ===
$hoja->setCellValue("A{$row}", "Nº Cotización:");
$hoja->setCellValue("B{$row}", $prospecto['concatenado'] ?? 'N_A');
$hoja->getStyle("A{$row}:B{$row}")->applyFromArray($estiloTitulo);
$row += 2;

// === SHIPPER (A3) ===
$hoja->setCellValue("A3", "SHIPPER:");
$hoja->getStyle("A3")->applyFromArray($estiloTitulo);
$hoja->setCellValue("A4", "Razón Social:");
$hoja->setCellValue("B4", $shipperRS);
$hoja->setCellValue("A5", "DIRECCIÓN:");
$hoja->setCellValue("B5", $shipperDir);
$hoja->setCellValue("A6", "CONTACTO:");
$hoja->setCellValue("B6", $shipperCont);
$hoja->setCellValue("A7", "R.U.T:");
$hoja->setCellValue("B7", $shipperRut);

// === CONSIGNATARIO (D3) ===
$hoja->setCellValue("D3", "CONSIGNATARIO:");
$hoja->getStyle("D3")->applyFromArray($estiloTitulo);
$hoja->setCellValue("D4", "Razón Social:");
$hoja->setCellValue("E4", $consignatarioRS);
$hoja->setCellValue("D5", "DIRECCIÓN:");
$hoja->setCellValue("E5", $consignatarioDir);
$hoja->setCellValue("D6", "CONTACTO:");
$hoja->setCellValue("E6", $consignatarioCont);
$hoja->setCellValue("D7", "R.U.T:");
$hoja->setCellValue("E7", $consignatarioRut);

// === DATOS ADICIONALES DEL SERVICIO (F3) ===
$row = 3;
$hoja->setCellValue("F{$row}", "TIPO CAMBIO CLIENTE:");
$hoja->setCellValue("G{$row}", number_format($servicio['tipo_cambio'] ?? 1, 4, ',', '.'));
$row++;
$hoja->setCellValue("F{$row}", "AGENTE / OFICINA:");
$hoja->setCellValue("G{$row}", $servicio['agente'] ?? '');
$row++;
$hoja->setCellValue("F{$row}", "REF. CLIENTE:");
$hoja->setCellValue("G{$row}", $servicio['ref_cliente'] ?? '');
$row++;
$hoja->setCellValue("F{$row}", "PROV. NACIONAL:");
$hoja->setCellValue("G{$row}", $servicio['proveedor_nac'] ?? '');
$row++;
$hoja->setCellValue("F{$row}", "TERRESTRE:");
$hoja->setCellValue("G{$row}", '');
$row++;
$hoja->setCellValue("F{$row}", "DESCONSOLIDACIÓN:");
$hoja->setCellValue("G{$row}", $servicio['desconsolidac'] ?? '');
$row++;
$hoja->setCellValue("F{$row}", "GRÚAS:");
$hoja->setCellValue("G{$row}", '');
$row++;
$hoja->setCellValue("F{$row}", "EMBALAJE:");
$hoja->setCellValue("G{$row}", '');

// === Datos del Servicio (A9) ===
$row = 9;
$hoja->setCellValue("A{$row}", "INCOTERM:");
$hoja->setCellValue("B{$row}", $servicio['incoterm'] ?? '');
$row++;
$hoja->setCellValue("A{$row}", "COMMODITY:");
$hoja->setCellValue("B{$row}", $servicio['commodity'] ?? '');
$row++;
$hoja->setCellValue("A{$row}", "VOLUMEN:");
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
$hoja->setCellValue("A{$row}", "COLOADER:");
$hoja->setCellValue("B{$row}", $servicio['coloader'] ?? '');
$row += 2; // A18

// === NOTAS ADICIONALES (A18:A22) ===
$hoja->setCellValue("A{$row}", "NOTAS ADICIONALES:");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$hoja->mergeCells("A" . ($row + 1) . ":A" . ($row + 4));
$hoja->setCellValue("A" . ($row + 1), $servicio['nota_srvc'] ?? '');
$hoja->getStyle("A" . ($row + 1))->applyFromArray($estiloTexto);
$row = 23;

// === COSTOS (A23) ===
$hoja->setCellValue("A{$row}", "PROFIT SHARE");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;
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
    $hoja->getStyle("D{$row}:G{$row}")->applyFromArray($estiloNumero);
    $row++;
}
$row++;

// === CONDICIONES COMERCIALES (A debajo de Costos) ===
$hoja->setCellValue("A{$row}", "CONDICIONES COMERCIALES");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;

// Obtener estado de crédito
$estadoCredito = $datosRO['estado_credito'] ?? null;
$simboloCredito = ' &nbsp;';
$simboloContado = ' &nbsp;';

if ($estadoCredito) {
    $simboloCredito = $estadoCredito['credito'] ?? ' &nbsp;';
    $simboloContado = $estadoCredito['contado'] ?? ' &nbsp;';
} else {
    // Si no se envió, asumir contado (puedes cambiar la lógica)
    $simboloContado = ' ✅';
}

$hoja->setCellValue("A{$row}", "CRÉDITO:" . $simboloCredito);
$row++;
$hoja->setCellValue("A{$row}", "CONTADO:" . $simboloContado);
$row += 2;

// === TRANSPORTE NACIONAL (A debajo) ===
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
    $hoja->getStyle("C{$row}:E{$row}")->applyFromArray($estiloNumero);
} else {
    $hoja->fromArray(['', '', '', '', '', '', ''], null, "A{$row}");
}
$row += 2;

// === Campos de Transporte (A debajo) ===
$hoja->setCellValue("A{$row}", "TRANSPORTISTA:");
$hoja->setCellValue("B{$row}", $transporte['transportista'] ?? '');
$row++;
$hoja->setCellValue("A{$row}", "DIREC. RETIRO:");
$hoja->setCellValue("B{$row}", $transporte['direc_retiro'] ?? '');
$row++;
$hoja->setCellValue("A{$row}", "CONTACTO:");
$hoja->setCellValue("B{$row}", $transporte['contacto_retiro'] ?? '');
$row++;
$hoja->setCellValue("A{$row}", "FONO:");
$hoja->setCellValue("B{$row}", $transporte['fono_retiro'] ?? '');
$row++;
$hoja->setCellValue("A{$row}", "DIREC. ENTREGA:");
$hoja->setCellValue("B{$row}", $transporte['direc_entrega'] ?? '');
$row++;
$hoja->setCellValue("A{$row}", "FONO:");
$hoja->setCellValue("B{$row}", $transporte['fono_entrega'] ?? '');
$row++;
$hoja->setCellValue("A{$row}", "EMPRESA:");
$hoja->setCellValue("B{$row}", $transporte['empresa_entrega'] ?? '');
$row++;
$hoja->setCellValue("A{$row}", "CONTACTO:");
$hoja->setCellValue("B{$row}", $transporte['contacto_entrega'] ?? '');
$row += 2;

// === SEGURO (A debajo) ===
$hoja->setCellValue("A{$row}", "SEGURO");
$hoja->getStyle("A{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->fromArray(['Concepto', 'Moneda', 'Costo', 'Venta', 'Min.', 'V.Venta', 'Aplica'], null, "A{$row}");
$hoja->getStyle("A{$row}:G{$row}")->applyFromArray($estiloCabecera);
$row++;
$hoja->fromArray(['', '', '', '', '', '', ''], null, "A{$row}");
$row += 2;

// === VENTAS (I23) ===
$ventasStartRow = 25; // 23 + "PROFIT SHARE" + "Costos"
$hoja->setCellValue("I{$ventasStartRow}", "Ventas");
$hoja->getStyle("I{$ventasStartRow}")->applyFromArray($estiloTitulo);
$ventasStartRow++;
$hoja->fromArray(['Concepto', 'Moneda', 'Qty', 'Venta', 'Total', 'Aplica'], null, "I{$ventasStartRow}");
$hoja->getStyle("I{$ventasStartRow}:N{$ventasStartRow}")->applyFromArray($estiloCabecera);
$ventasStartRow++;

foreach ($costos as $c) {
    $qty = $c['qty'] ?? 0;
    $tarifa = $c['tarifa'] ?? 0;
    $total = $qty * $tarifa;
    $hoja->fromArray([
        $c['concepto'] ?? '',
        $c['moneda'] ?? '',
        $qty,
        $tarifa,
        $total,
        $c['aplica'] ?? ''
    ], null, "I{$ventasStartRow}");
    $hoja->getStyle("I{$ventasStartRow}:N{$ventasStartRow}")->applyFromArray($estiloCelda);
    $hoja->getStyle("K{$ventasStartRow}:L{$ventasStartRow}")->applyFromArray($estiloNumero);
    $ventasStartRow++;
}
$ventasStartRow++;

// === Gastos Locales en Destino (Ventas) ===
$gastosVentas = array_filter($gastos_locales, fn($g) => strtoupper($g['tipo'] ?? '') === 'VENTAS');
$hoja->setCellValue("I{$ventasStartRow}", "Gastos Locales en Destino");
$hoja->getStyle("I{$ventasStartRow}")->applyFromArray($estiloTitulo);
$ventasStartRow++;
$hoja->fromArray(['Concepto', 'Moneda', 'Monto', 'Afecto'], null, "I{$ventasStartRow}");
$hoja->getStyle("I{$ventasStartRow}:L{$ventasStartRow}")->applyFromArray($estiloCabecera);
$ventasStartRow++;

$totalGastosVentas = 0;
foreach ($gastosVentas as $g) {
    $monto = $g['monto'] ?? 0;
    $totalGastosVentas += $monto;
    $hoja->fromArray([
        $g['gasto'] ?? '',
        $g['moneda'] ?? '',
        $monto,
        $g['afecto'] ?? ''
    ], null, "I{$ventasStartRow}");
    $hoja->getStyle("I{$ventasStartRow}:L{$ventasStartRow}")->applyFromArray($estiloCelda);
    $hoja->getStyle("K{$ventasStartRow}")->applyFromArray($estiloNumero);
    $ventasStartRow++;
}

$hoja->setCellValue("I{$ventasStartRow}", "TOTAL MONTO:");
$hoja->setCellValue("K{$ventasStartRow}", $totalGastosVentas);
$hoja->getStyle("K{$ventasStartRow}")->applyFromArray($estiloNumero);
$ventasStartRow += 2;

// === Gastos Locales en Destino Costo ===
$gastosCostos = array_filter($gastos_locales, fn($g) => strtoupper($g['tipo'] ?? '') === 'COSTO');
$hoja->setCellValue("I{$ventasStartRow}", "Gastos Locales en Destino Costo");
$hoja->getStyle("I{$ventasStartRow}")->applyFromArray($estiloTitulo);
$ventasStartRow++;
$hoja->fromArray(['Concepto', 'Moneda', 'Monto', 'Afecto'], null, "I{$ventasStartRow}");
$hoja->getStyle("I{$ventasStartRow}:L{$ventasStartRow}")->applyFromArray($estiloCabecera);
$ventasStartRow++;

$totalGastosCostos = 0;
foreach ($gastosCostos as $g) {
    $monto = $g['monto'] ?? 0;
    $totalGastosCostos += $monto;
    $hoja->fromArray([
        $g['gasto'] ?? '',
        $g['moneda'] ?? '',
        $monto,
        $g['afecto'] ?? ''
    ], null, "I{$ventasStartRow}");
    $hoja->getStyle("I{$ventasStartRow}:L{$ventasStartRow}")->applyFromArray($estiloCelda);
    $hoja->getStyle("K{$ventasStartRow}")->applyFromArray($estiloNumero);
    $ventasStartRow++;
}

$hoja->setCellValue("I{$ventasStartRow}", "TOTAL MONTO:");
$hoja->setCellValue("K{$ventasStartRow}", $totalGastosCostos);
$hoja->getStyle("K{$ventasStartRow}")->applyFromArray($estiloNumero);
$ventasStartRow += 2;

// === Total Gastos Locales + Profit ===
$profitLocal = $totalGastosVentas - $totalGastosCostos;
$profitPct = $totalGastosVentas > 0 ? ($profitLocal / $totalGastosVentas * 100) : 0;

$hoja->setCellValue("I{$ventasStartRow}", "Total Gastos Locales más Profit Local");
$hoja->getStyle("I{$ventasStartRow}")->applyFromArray($estiloTitulo);
$ventasStartRow++;
$hoja->setCellValue("I{$ventasStartRow}", "Moneda"); $hoja->setCellValue("J{$ventasStartRow}", "Monto");
$hoja->getStyle("I{$ventasStartRow}:J{$ventasStartRow}")->applyFromArray($estiloCabecera);
$ventasStartRow++;
$hoja->setCellValue("I{$ventasStartRow}", "CLP"); $hoja->setCellValue("J{$ventasStartRow}", $totalGastosVentas);
$hoja->getStyle("J{$ventasStartRow}")->applyFromArray($estiloNumero);
$ventasStartRow++;
$hoja->setCellValue("I{$ventasStartRow}", "CLP"); $hoja->setCellValue("J{$ventasStartRow}", $totalGastosCostos);
$hoja->getStyle("J{$ventasStartRow}")->applyFromArray($estiloNumero);
$ventasStartRow++;
$hoja->setCellValue("I{$ventasStartRow}", "CLP"); $hoja->setCellValue("J{$ventasStartRow}", $profitLocal);
$hoja->getStyle("J{$ventasStartRow}")->applyFromArray($estiloNumero);
$ventasStartRow++;
$hoja->setCellValue("I{$ventasStartRow}", ""); $hoja->setCellValue("J{$ventasStartRow}", $profitPct . '%');
$ventasStartRow++;

// === NOTAS finales (abajo de todo) ===
$lastRow = max($row, $ventasStartRow) + 2;
$hoja->setCellValue("A{$lastRow}", "NOTAS A OPERACIONES");
$hoja->getStyle("A{$lastRow}")->applyFromArray($estiloTitulo);
$hoja->setCellValue("A" . ($lastRow + 1), $servicio['notas_operaciones'] ?? '');
$lastRow += 3;
$hoja->setCellValue("A{$lastRow}", "NOTAS COMERCIALES");
$hoja->getStyle("A{$lastRow}")->applyFromArray($estiloTitulo);
$hoja->setCellValue("A" . ($lastRow + 1), $servicio['notas_comerciales'] ?? '');

// --- Autoajustar ---
foreach (range('A', 'N') as $col) {
    $hoja->getColumnDimension($col)->setAutoSize(true);
}

// --- Salida ---
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Route_Order_' . nombreArchivoSeguro($prospecto['concatenado'] ?? 'N_A') . '.xlsx"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
$writer->save('php://output');
exit;