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

// === Insertar logo en B2 ===
$logoPath = getLogoPath();
if ($logoPath) {
    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $drawing->setName('Logo Empresa');
    $drawing->setDescription('Logo');
    $drawing->setPath($logoPath);
    $drawing->setHeight(160); // Ajusta según necesidad
    $drawing->setCoordinates('B2');
    $drawing->setOffsetX(0);
    $drawing->setOffsetY(0);
    $drawing->setWorksheet($hoja);
}

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

function getLogoPath() {
    // Ajusta esta ruta según tu estructura de proyecto
    $possiblePaths = [
        __DIR__ . '/../assets/logoelog2.png',
    ];

    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    return null;
}

$row = 11;

// === Nº Cotización ===
$hoja->setCellValue("B{$row}", "Nº Cotización:");
$hoja->setCellValue("C{$row}", $prospecto['concatenado'] ?? 'N_A');
$hoja->getStyle("B{$row}:C{$row}")->applyFromArray($estiloTitulo);
$row += 2;

// === SHIPPER (A13) ===
$hoja->setCellValue("B{$row}", "SHIPPER:");
$hoja->getStyle("B{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->setCellValue("B{$row}", "Razón Social:");
$hoja->setCellValue("C{$row}", $shipperRS);
$row++;
$hoja->setCellValue("B{$row}", "DIRECCIÓN:");
$hoja->setCellValue("C{$row}", $shipperDir);
$row++;
$hoja->setCellValue("B{$row}", "CONTACTO:");
$hoja->setCellValue("C{$row}", $shipperCont);
$row++;
$hoja->setCellValue("B{$row}", "R.U.T:");
$hoja->setCellValue("C{$row}", $shipperRut);

// === CONSIGNATARIO (A19) ===
$row += 2;
$hoja->setCellValue("B{$row}", "CONSIGNATARIO:");
$hoja->getStyle("C{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->setCellValue("B{$row}", "Razón Social:");
$hoja->setCellValue("C{$row}", $consignatarioRS);
$row++;
$hoja->setCellValue("B{$row}", "DIRECCIÓN:");
$hoja->setCellValue("C{$row}", $consignatarioDir);
$row++;
$hoja->setCellValue("B{$row}", "CONTACTO:");
$hoja->setCellValue("C{$row}", $consignatarioCont);
$row++;
$hoja->setCellValue("B{$row}", "R.U.T:");
$hoja->setCellValue("C{$row}", $consignatarioRut);

// === DATOS ADICIONALES DEL SERVICIO (F3) ===
$row = 13;
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

// === Datos del Servicio (A22) ===
$row = 22;
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
$row = 22;

// === NOTAS ADICIONALES (A22:A30) ===
$hoja->setCellValue("G{$row}", "NOTAS ADICIONALES:");
$hoja->getStyle("G{$row}")->applyFromArray($estiloTitulo);
$hoja->mergeCells("G" . ($row + 1) . ":N" . ($row + 4));
$hoja->setCellValue("G" . ($row + 1), $servicio['nota_srvc'] ?? '');
$hoja->getStyle("G" . ($row + 1))->applyFromArray($estiloTexto);

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
$hoja->getStyle("J{$ventasStartRow}:M{$ventasStartRow}")->applyFromArray($estiloCabecera);
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
$hoja->getStyle("J{$ventasStartRow}:M{$ventasStartRow}")->applyFromArray($estiloCabecera);
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

// === Total Gastos Locales + Profit Local ===
$profitLocal = $totalGastosVentas - $totalGastosCostos;
$profitPct = $totalGastosVentas > 0 ? ($profitLocal / $totalGastosVentas * 100) : 0;

$hoja->setCellValue("J{$ventasStartRow}", "Total Gastos Locales más Profit Local");
$hoja->getStyle("J{$ventasStartRow}")->applyFromArray($estiloTitulo);
$ventasStartRow++;

// Encabezados: dejamos J vacío, K = Moneda, L = Monto
$hoja->setCellValue("K{$ventasStartRow}", "Moneda");
$hoja->setCellValue("L{$ventasStartRow}", "Monto");
$hoja->getStyle("K{$ventasStartRow}:L{$ventasStartRow}")->applyFromArray($estiloCabecera);
$ventasStartRow++;

// TOTAL VENTA
$hoja->setCellValue("J{$ventasStartRow}", "TOTAL VENTA:");
$hoja->setCellValue("K{$ventasStartRow}", "CLP");
$hoja->setCellValue("L{$ventasStartRow}", $totalGastosVentas);
$hoja->getStyle("L{$ventasStartRow}")->applyFromArray($estiloNumero);
$ventasStartRow++;

// TOTAL COSTO
$hoja->setCellValue("J{$ventasStartRow}", "TOTAL COSTO:");
$hoja->setCellValue("K{$ventasStartRow}", "CLP");
$hoja->setCellValue("L{$ventasStartRow}", $totalGastosCostos);
$hoja->getStyle("L{$ventasStartRow}")->applyFromArray($estiloNumero);
$ventasStartRow++;

// PROFIT LOCAL
$hoja->setCellValue("J{$ventasStartRow}", "PROFIT LOCAL:");
$hoja->setCellValue("K{$ventasStartRow}", "CLP");
$hoja->setCellValue("L{$ventasStartRow}", $profitLocal);
$hoja->getStyle("L{$ventasStartRow}")->applyFromArray($estiloNumero);
$ventasStartRow++;

// PROFIT %
$hoja->setCellValue("J{$ventasStartRow}", "PROFIT %:");
$hoja->setCellValue("K{$ventasStartRow}", "");
$hoja->setCellValue("L{$ventasStartRow}", $profitPct . '%');
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
//foreach (range('B', 'O') as $col) {
//    $hoja->getColumnDimension($col)->setAutoSize(true);
//}

// === ANCHOS FIJOS DE COLUMNAS - Layout del Route Order ===
// Columnas izquierda: datos generales (SHIPPER, Servicio, Notas)
$hoja->getColumnDimension('A')->setWidth(18); // Labels (ej: "Razón Social:")
$hoja->getColumnDimension('B')->setWidth(25); // Valores (ej: "Empresa XYZ")

// Columnas centro-izquierda: CONSIGNATARIO y datos adicionales
$hoja->getColumnDimension('D')->setWidth(18); // Labels CONSIGNATARIO
$hoja->getColumnDimension('E')->setWidth(25); // Valores CONSIGNATARIO

// Columnas centro-derecha: Datos adicionales del servicio (F-G)
$hoja->getColumnDimension('F')->setWidth(20); // Labels (ej: "TIPO CAMBIO CLIENTE:")
$hoja->getColumnDimension('G')->setWidth(22); // Valores (ej: "1,2345")

// Columnas derecha: Tablas (Profit Share, Gastos, etc.)
$hoja->getColumnDimension('I')->setWidth(15); // Concepto / Label
$hoja->getColumnDimension('J')->setWidth(10); // Moneda / Qty
$hoja->getColumnDimension('K')->setWidth(12); // Costo / Venta / Monto
$hoja->getColumnDimension('L')->setWidth(12); // Total / Afecto
$hoja->getColumnDimension('M')->setWidth(10); // Aplica (si se usa)
$hoja->getColumnDimension('N')->setWidth(10); // Extra (por margen)

// === Desactivar AutoSize en todas las columnas usadas ===
$columnas = ['A','B','D','E','F','G','I','J','K','L','M','N'];
foreach ($columnas as $col) {
    $hoja->getColumnDimension($col)->setAutoSize(false);
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