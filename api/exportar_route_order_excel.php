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
$hoja->setCellValue("B{$row}", "Nº Cotización:");
$hoja->setCellValue("C{$row}", $prospecto['concatenado'] ?? 'N_A');
$hoja->getStyle("B{$row}:C{$row}")->applyFromArray($estiloTitulo);
$row += 2;

// === SHIPPER (A3) ===
$hoja->setCellValue("B3", "SHIPPER:");
$hoja->getStyle("B3")->applyFromArray($estiloTitulo);
$hoja->setCellValue("B4", "Razón Social:");
$hoja->setCellValue("C4", $shipperRS);
$hoja->setCellValue("B5", "DIRECCIÓN:");
$hoja->setCellValue("C5", $shipperDir);
$hoja->setCellValue("B6", "CONTACTO:");
$hoja->setCellValue("C6", $shipperCont);
$hoja->setCellValue("B7", "R.U.T:");
$hoja->setCellValue("C7", $shipperRut);

// === CONSIGNATARIO (A9) ===
$row = 9;
$hoja->setCellValue("B9", "CONSIGNATARIO:");
$hoja->getStyle("C9")->applyFromArray($estiloTitulo);
$hoja->setCellValue("B10", "Razón Social:");
$hoja->setCellValue("C10", $consignatarioRS);
$hoja->setCellValue("B11", "DIRECCIÓN:");
$hoja->setCellValue("C11", $consignatarioDir);
$hoja->setCellValue("B12", "CONTACTO:");
$hoja->setCellValue("C12", $consignatarioCont);
$hoja->setCellValue("B13", "R.U.T:");
$hoja->setCellValue("C13", $consignatarioRut);

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
$row = 15;
$hoja->setCellValue("B{$row}", "INCOTERM:");
$hoja->setCellValue("C{$row}", $servicio['incoterm'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "COMMODITY:");
$hoja->setCellValue("C{$row}", $servicio['commodity'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "VOLUMEN:");
$hoja->setCellValue("C{$row}", $servicio['volumen'] ?? '');
$hoja->getStyle("C{$row}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
$row++;
$hoja->setCellValue("B{$row}", "PESO BRUTO:");
$hoja->setCellValue("C{$row}", $servicio['peso'] ?? '');
$hoja->getStyle("C{$row}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
$row++;
$hoja->setCellValue("B{$row}", "DIMENSIONES:");
$hoja->setCellValue("C{$row}", $servicio['dimensiones'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "UNIDADES:");
$hoja->setCellValue("C{$row}", $servicio['bultos'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "POL:");
$hoja->setCellValue("C{$row}", $servicio['origen'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "POD:");
$hoja->setCellValue("C{$row}", $servicio['destino'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "COLOADER:");
$hoja->setCellValue("C{$row}", $servicio['coloader'] ?? '');
$row += 2; // A25

// === NOTAS ADICIONALES (A25:A29) ===
$hoja->setCellValue("B{$row}", "NOTAS ADICIONALES:");
$hoja->getStyle("B{$row}")->applyFromArray($estiloTitulo);
$hoja->mergeCells("B" . ($row + 1) . ":B" . ($row + 4));
$hoja->setCellValue("B" . ($row + 1), $servicio['nota_srvc'] ?? '');
$hoja->getStyle("B" . ($row + 1))->applyFromArray($estiloTexto);

// === COSTOS (A30) ===
$row = 30;
$hoja->setCellValue("B{$row}", "PROFIT SHARE");
$hoja->getStyle("B{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->setCellValue("B{$row}", "Costos");
$hoja->getStyle("B{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->fromArray(['Concepto', 'Moneda', 'Qty', 'Costo', 'Venta', 'Total', 'Aplica'], null, "B{$row}");
$hoja->getStyle("B{$row}:H{$row}")->applyFromArray($estiloCabecera);
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
    ], null, "B{$row}");
    $hoja->getStyle("B{$row}:H{$row}")->applyFromArray($estiloCelda);
    $hoja->getStyle("E{$row}:H{$row}")->applyFromArray($estiloNumero);
    $row++;
}
$row++;

// === CONDICIONES COMERCIALES (B debajo de Costos) ===
$hoja->setCellValue("B{$row}", "CONDICIONES COMERCIALES");
$hoja->getStyle("B{$row}")->applyFromArray($estiloTitulo);
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

$hoja->setCellValue("B{$row}", "CRÉDITO:" . $simboloCredito);
$row++;
$hoja->setCellValue("B{$row}", "CONTADO:" . $simboloContado);
$row += 2;

// === TRANSPORTE NACIONAL (B debajo) ===
$hoja->setCellValue("B{$row}", "TRANSPORTE NACIONAL");
$hoja->getStyle("B{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->fromArray(['Concepto', 'Moneda', 'Costo', 'Venta', 'Profit', 'Acepta', 'Afecto'], null, "B{$row}");
$hoja->getStyle("B{$row}:H{$row}")->applyFromArray($estiloCabecera);
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
    ], null, "B{$row}");
    $hoja->getStyle("B{$row}:H{$row}")->applyFromArray($estiloCelda);
    $hoja->getStyle("E{$row}:G{$row}")->applyFromArray($estiloNumero);
} else {
    $hoja->fromArray(['', '', '', '', '', '', ''], null, "B{$row}");
}
$row += 2;

// === Campos de Transporte (B debajo) ===
$hoja->setCellValue("B{$row}", "TRANSPORTISTA:");
$hoja->setCellValue("C{$row}", $transporte['transportista'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "DIREC. RETIRO:");
$hoja->setCellValue("C{$row}", $transporte['direc_retiro'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "CONTACTO:");
$hoja->setCellValue("C{$row}", $transporte['contacto_retiro'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "FONO:");
$hoja->setCellValue("C{$row}", $transporte['fono_retiro'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "DIREC. ENTREGA:");
$hoja->setCellValue("C{$row}", $transporte['direc_entrega'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "FONO:");
$hoja->setCellValue("C{$row}", $transporte['fono_entrega'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "EMPRESA:");
$hoja->setCellValue("C{$row}", $transporte['empresa_entrega'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "CONTACTO:");
$hoja->setCellValue("C{$row}", $transporte['contacto_entrega'] ?? '');
$row += 2;

// === SEGURO (B debajo) ===
$hoja->setCellValue("B{$row}", "SEGURO");
$hoja->getStyle("B{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->fromArray(['Concepto', 'Moneda', 'Costo', 'Venta', 'Min.', 'V.Venta', 'Aplica'], null, "B{$row}");
$hoja->getStyle("B{$row}:H{$row}")->applyFromArray($estiloCabecera);
$row++;
$hoja->fromArray(['', '', '', '', '', '', ''], null, "B{$row}");
$row += 2;

// === VENTAS (J23) ===
$ventasStartRow = 25; // 23 + "PROFIT SHARE" + "Costos"
$hoja->setCellValue("J{$ventasStartRow}", "Ventas");
$hoja->getStyle("J{$ventasStartRow}")->applyFromArray($estiloTitulo);
$ventasStartRow++;
$hoja->fromArray(['Concepto', 'Moneda', 'Qty', 'Venta', 'Total', 'Aplica'], null, "J{$ventasStartRow}");
$hoja->getStyle("J{$ventasStartRow}:O{$ventasStartRow}")->applyFromArray($estiloCabecera);
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
    ], null, "J{$ventasStartRow}");
    $hoja->getStyle("J{$ventasStartRow}:O{$ventasStartRow}")->applyFromArray($estiloCelda);
    $hoja->getStyle("L{$ventasStartRow}:M{$ventasStartRow}")->applyFromArray($estiloNumero);
    $ventasStartRow++;
}
$ventasStartRow++;

// === Gastos Locales en Destino (Ventas) ===
$gastosVentas = array_filter($gastos_locales, fn($g) => strtoupper($g['tipo'] ?? '') === 'VENTAS');
$hoja->setCellValue("J{$ventasStartRow}", "Gastos Locales en Destino");
$hoja->getStyle("J{$ventasStartRow}")->applyFromArray($estiloTitulo);
$ventasStartRow++;
$hoja->fromArray(['Concepto', 'Moneda', 'Monto', 'Afecto'], null, "J{$ventasStartRow}");
$hoja->getStyle("J{$ventasStartRow}:O{$ventasStartRow}")->applyFromArray($estiloCabecera);
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
    ], null, "J{$ventasStartRow}");
    $hoja->getStyle("J{$ventasStartRow}:O{$ventasStartRow}")->applyFromArray($estiloCelda);
    $hoja->getStyle("K{$ventasStartRow}")->applyFromArray($estiloNumero);
    $ventasStartRow++;
}

$hoja->setCellValue("J{$ventasStartRow}", "TOTAL MONTO:");
$hoja->setCellValue("L{$ventasStartRow}", $totalGastosVentas);
$hoja->getStyle("L{$ventasStartRow}")->applyFromArray($estiloNumero);
$ventasStartRow += 2;

// === Gastos Locales en Destino Costo ===
$gastosCostos = array_filter($gastos_locales, fn($g) => strtoupper($g['tipo'] ?? '') === 'COSTO');
$hoja->setCellValue("J{$ventasStartRow}", "Gastos Locales en Destino Costo");
$hoja->getStyle("J{$ventasStartRow}")->applyFromArray($estiloTitulo);
$ventasStartRow++;
$hoja->fromArray(['Concepto', 'Moneda', 'Monto', 'Afecto'], null, "J{$ventasStartRow}");
$hoja->getStyle("J{$ventasStartRow}:O{$ventasStartRow}")->applyFromArray($estiloCabecera);
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
    ], null, "J{$ventasStartRow}");
    $hoja->getStyle("J{$ventasStartRow}:O{$ventasStartRow}")->applyFromArray($estiloCelda);
    $hoja->getStyle("L{$ventasStartRow}")->applyFromArray($estiloNumero);
    $ventasStartRow++;
}

$hoja->setCellValue("J{$ventasStartRow}", "TOTAL MONTO:");
$hoja->setCellValue("L{$ventasStartRow}", $totalGastosCostos);
$hoja->getStyle("L{$ventasStartRow}")->applyFromArray($estiloNumero);
$ventasStartRow += 2;

// === Total Gastos Locales + Profit ===
$profitLocal = $totalGastosVentas - $totalGastosCostos;
$profitPct = $totalGastosVentas > 0 ? ($profitLocal / $totalGastosVentas * 100) : 0;

$hoja->setCellValue("J{$ventasStartRow}", "Total Gastos Locales más Profit Local");
$hoja->getStyle("J{$ventasStartRow}")->applyFromArray($estiloTitulo);
$ventasStartRow++;
$hoja->setCellValue("J{$ventasStartRow}", "Moneda"); $hoja->setCellValue("K{$ventasStartRow}", "Monto");
$hoja->getStyle("J{$ventasStartRow}:K{$ventasStartRow}")->applyFromArray($estiloCabecera);
$ventasStartRow++;
$hoja->setCellValue("J{$ventasStartRow}", "CLP"); $hoja->setCellValue("K{$ventasStartRow}", $totalGastosVentas);
$hoja->getStyle("K{$ventasStartRow}")->applyFromArray($estiloNumero);
$ventasStartRow++;
$hoja->setCellValue("J{$ventasStartRow}", "CLP"); $hoja->setCellValue("K{$ventasStartRow}", $totalGastosCostos);
$hoja->getStyle("K{$ventasStartRow}")->applyFromArray($estiloNumero);
$ventasStartRow++;
$hoja->setCellValue("J{$ventasStartRow}", "CLP"); $hoja->setCellValue("K{$ventasStartRow}", $profitLocal);
$hoja->getStyle("K{$ventasStartRow}")->applyFromArray($estiloNumero);
$ventasStartRow++;
$hoja->setCellValue("J{$ventasStartRow}", ""); $hoja->setCellValue("J{$ventasStartRow}", $profitPct . '%');
$ventasStartRow++;

// === NOTAS finales (abajo de todo) ===
$lastRow = max($row, $ventasStartRow) + 2;
$hoja->setCellValue("B{$lastRow}", "NOTAS A OPERACIONES");
$hoja->getStyle("B{$lastRow}")->applyFromArray($estiloTitulo);
$hoja->setCellValue("B" . ($lastRow + 1), $servicio['notas_operaciones'] ?? '');
$lastRow += 3;
$hoja->setCellValue("B{$lastRow}", "NOTAS COMERCIALES");
$hoja->getStyle("B{$lastRow}")->applyFromArray($estiloTitulo);
$hoja->setCellValue("B" . ($lastRow + 1), $servicio['notas_comerciales'] ?? '');

// --- Autoajustar ---
foreach (range('B', 'O') as $col) {
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