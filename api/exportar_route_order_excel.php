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
$hoja->setCellValue("D{$row}", $prospecto['concatenado'] ?? 'N_A');
$hoja->getStyle("B{$row}:D{$row}")->applyFromArray($estiloTitulo);
$row += 2;

// === SHIPPER (A13) ===
$hoja->setCellValue("B{$row}", "SHIPPER:");
$hoja->getStyle("B{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->setCellValue("B{$row}", "Razón Social:");
$hoja->setCellValue("D{$row}", $shipperRS);
$row++;
$hoja->setCellValue("B{$row}", "DIRECCIÓN:");
$hoja->setCellValue("D{$row}", $shipperDir);
$row++;
$hoja->setCellValue("B{$row}", "CONTACTO:");
$hoja->setCellValue("D{$row}", $shipperCont);
$row++;
$hoja->setCellValue("B{$row}", "R.U.T:");
$hoja->setCellValue("D{$row}", $shipperRut);

// === DATOS ADICIONALES DEL SERVICIO (F3) ===
$row = 13;
$hoja->setCellValue("G{$row}", "TIPO CAMBIO CLIENTE:");
$hoja->setCellValue("I{$row}", number_format($servicio['tipo_cambio'] ?? 1, 4, ',', '.'));
$row++;
$hoja->setCellValue("G{$row}", "AGENTE / OFICINA:");
$hoja->setCellValue("I{$row}", $servicio['agente'] ?? '');
$row++;
$hoja->setCellValue("G{$row}", "REF. CLIENTE:");
$hoja->setCellValue("I{$row}", $servicio['ref_cliente'] ?? '');
$row++;
$hoja->setCellValue("G{$row}", "PROV. NACIONAL:");
$hoja->setCellValue("I{$row}", $servicio['proveedor_nac'] ?? '');
$row++;
$hoja->setCellValue("G{$row}", "TERRESTRE:");
$hoja->setCellValue("I{$row}", '');
$row++;
$hoja->setCellValue("G{$row}", "DESCONSOLIDACIÓN:");
$hoja->setCellValue("I{$row}", $servicio['desconsolidac'] ?? '');
$row++;
$hoja->setCellValue("G{$row}", "GRÚAS:");
$hoja->setCellValue("I{$row}", '');
$row++;
$hoja->setCellValue("G{$row}", "EMBALAJE:");
$hoja->setCellValue("I{$row}", '');

// === CONSIGNATARIO (A19) ===
$row = 19;
$hoja->setCellValue("B{$row}", "CONSIGNATARIO:");
$hoja->getStyle("B{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->setCellValue("B{$row}", "Razón Social:");
$hoja->setCellValue("D{$row}", $consignatarioRS);
$row++;
$hoja->setCellValue("B{$row}", "DIRECCIÓN:");
$hoja->setCellValue("D{$row}", $consignatarioDir);
$row++;
$hoja->setCellValue("B{$row}", "CONTACTO:");
$hoja->setCellValue("D{$row}", $consignatarioCont);
$row++;
$hoja->setCellValue("B{$row}", "R.U.T:");
$hoja->setCellValue("D{$row}", $consignatarioRut);

// === Datos del Servicio (A25) ===
$row += 2;
$hoja->setCellValue("B{$row}", "INCOTERM:");
$hoja->setCellValue("D{$row}", $servicio['incoterm'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "COMMODITY:");
$hoja->setCellValue("D{$row}", $servicio['commodity'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "VOLUMEN:");
$hoja->setCellValue("D{$row}", $servicio['volumen'] ?? '');
$hoja->getStyle("D{$row}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
$row++;
$hoja->setCellValue("B{$row}", "PESO BRUTO:");
$hoja->setCellValue("D{$row}", $servicio['peso'] ?? '');
$hoja->getStyle("D{$row}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
$row++;
$hoja->setCellValue("B{$row}", "DIMENSIONES:");
$hoja->setCellValue("D{$row}", $servicio['dimensiones'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "UNIDADES:");
$hoja->setCellValue("D{$row}", $servicio['bultos'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "POL:");
$hoja->setCellValue("D{$row}", $servicio['origen'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "POD:");
$hoja->setCellValue("D{$row}", $servicio['destino'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "COLOADER:");
$hoja->setCellValue("D{$row}", $servicio['coloader'] ?? '');

// === NOTAS ADICIONALES (A22:A30) ===
$row = 25;
$hoja->setCellValue("G{$row}", "NOTAS ADICIONALES:");
$hoja->getStyle("G{$row}")->applyFromArray($estiloTitulo);
$hoja->mergeCells("G" . ($row + 1) . ":O" . ($row + 7));
$hoja->setCellValue("G" . ($row + 1), $servicio['nota_srvc'] ?? '');
$hoja->getStyle("G" . ($row + 1))->applyFromArray($estiloTexto);

// === COSTOS (B) ===
$row = 35;
$hoja->setCellValue("B{$row}", "PROFIT SHARE");
$hoja->getStyle("B{$row}")->applyFromArray($estiloTitulo);
$row++;
$hoja->setCellValue("B{$row}", "Costos");
$hoja->getStyle("B{$row}")->applyFromArray($estiloTitulo);
$row++;

// Encabezados
$hoja->setCellValue("B{$row}", "Concepto");
$hoja->mergeCells("B{$row}:C{$row}");
$hoja->setCellValue("D{$row}", "Moneda");
$hoja->setCellValue("E{$row}", "Qty");
$hoja->setCellValue("F{$row}", "Costo");
$hoja->setCellValue("G{$row}", "Venta");
$hoja->setCellValue("H{$row}", "Total");
$hoja->setCellValue("I{$row}", "Aplica");
$hoja->getStyle("B{$row}:I{$row}")->applyFromArray($estiloCabecera);
$row++;

// Datos
foreach ($costos as $c) {
    $qty = $c['qty'] ?? 0;
    $costo = $c['costo'] ?? 0;
    $total = $qty * $costo;

    $hoja->setCellValue("B{$row}", $c['concepto'] ?? '');
    $hoja->setCellValue("D{$row}", $c['moneda'] ?? '');
    $hoja->setCellValue("E{$row}", $qty);
    $hoja->setCellValue("F{$row}", $costo);
    $hoja->setCellValue("G{$row}", $total);
    $hoja->setCellValue("H{$row}", $c['aplica'] ?? '');

    $hoja->getStyle("B{$row}:I{$row}")->applyFromArray($estiloCelda);
    $hoja->getStyle("E{$row}:G{$row}")->applyFromArray($estiloNumero);
    $row++;
}
$row += 7;

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
$hoja->setCellValue("D{$row}", $transporte['transportista'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "DIREC. RETIRO:");
$hoja->setCellValue("D{$row}", $transporte['direc_retiro'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "CONTACTO:");
$hoja->setCellValue("D{$row}", $transporte['contacto_retiro'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "FONO:");
$hoja->setCellValue("D{$row}", $transporte['fono_retiro'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "DIREC. ENTREGA:");
$hoja->setCellValue("D{$row}", $transporte['direc_entrega'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "FONO:");
$hoja->setCellValue("D{$row}", $transporte['fono_entrega'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "EMPRESA:");
$hoja->setCellValue("D{$row}", $transporte['empresa_entrega'] ?? '');
$row++;
$hoja->setCellValue("B{$row}", "CONTACTO:");
$hoja->setCellValue("D{$row}", $transporte['contacto_entrega'] ?? '');
$row += 2;

// === SEGURO (B debajo) ===
$hoja->setCellValue("B{$row}", "SEGURO");
$hoja->getStyle("B{$row}")->applyFromArray($estiloTitulo);
$row++;

// Encabezados
$hoja->setCellValue("B{$row}", "Concepto");
$hoja->mergeCells("B{$row}:C{$row}");
$hoja->setCellValue("D{$row}", "Moneda");
$hoja->setCellValue("E{$row}", "Costo");
$hoja->setCellValue("F{$row}", "Venta");
$hoja->setCellValue("G{$row}", "Min.");
$hoja->setCellValue("H{$row}", "V.Venta");
$hoja->setCellValue("I{$row}", "Aplica");
$hoja->getStyle("B{$row}:I{$row}")->applyFromArray($estiloCabecera);
$row++;

// Datos (fila vacía)
$hoja->setCellValue("B{$row}", "");
$hoja->setCellValue("C{$row}", "");
$hoja->setCellValue("D{$row}", "");
$hoja->setCellValue("E{$row}", "");
$hoja->setCellValue("F{$row}", "");
$hoja->setCellValue("G{$row}", "");
$hoja->setCellValue("H{$row}", "");
$hoja->setCellValue("I{$row}", "");
$hoja->getStyle("B{$row}:I{$row}")->applyFromArray($estiloCelda);
$row += 2;

// === VENTAS ===
$ventasStartRow = 36;
$hoja->setCellValue("K{$ventasStartRow}", "Ventas");
$hoja->getStyle("K{$ventasStartRow}")->applyFromArray($estiloTitulo);
$ventasStartRow++;

// --- Encabezados ---
$hoja->setCellValue("K{$ventasStartRow}", "Concepto");
$hoja->mergeCells("K{$ventasStartRow}:L{$ventasStartRow}");

$hoja->setCellValue("M{$ventasStartRow}", "Moneda");
$hoja->setCellValue("N{$ventasStartRow}", "Qty");
$hoja->setCellValue("O{$ventasStartRow}", "Venta");
$hoja->setCellValue("P{$ventasStartRow}", "Total");
$hoja->setCellValue("Q{$ventasStartRow}", "Aplica");

$hoja->getStyle("K{$ventasStartRow}:Q{$ventasStartRow}")->applyFromArray($estiloCabecera);
$ventasStartRow++;

// --- Datos ---
foreach ($costos as $c) {
    $qty = $c['qty'] ?? 0;
    $tarifa = $c['tarifa'] ?? 0;
    $total = $qty * $tarifa;

    // Concepto (fusionado en K-L)
    $hoja->setCellValue("K{$ventasStartRow}", $c['concepto'] ?? '');
    // Moneda en M
    $hoja->setCellValue("M{$ventasStartRow}", $c['moneda'] ?? '');
    // Qty en N
    $hoja->setCellValue("N{$ventasStartRow}", $qty);
    // Venta en O
    $hoja->setCellValue("O{$ventasStartRow}", $tarifa);
    // Total en P
    $hoja->setCellValue("P{$ventasStartRow}", $total);
    // Aplica en Q
    $hoja->setCellValue("Q{$ventasStartRow}", $c['aplica'] ?? '');

    // Estilos
    $hoja->getStyle("K{$ventasStartRow}:Q{$ventasStartRow}")->applyFromArray($estiloCelda);
    // Aplicar formato numérico a Qty, Venta, Total
    $hoja->getStyle("N{$ventasStartRow}:P{$ventasStartRow}")->applyFromArray($estiloNumero);

    $ventasStartRow++;
}
$ventasStartRow++;

// === Gastos Locales en Destino (Ventas) ===
$gastosVentas = array_filter($gastos_locales, fn($g) => strtoupper($g['tipo'] ?? '') === 'VENTAS');
$hoja->setCellValue("K{$ventasStartRow}", "Gastos Locales en Destino");
$hoja->getStyle("K{$ventasStartRow}")->applyFromArray($estiloTitulo);
$ventasStartRow++;

// --- Encabezados ---
// Fusionar J y K para "Concepto"
$hoja->setCellValue("K{$ventasStartRow}", "Concepto");
$hoja->mergeCells("K{$ventasStartRow}:L{$ventasStartRow}");

// Otros encabezados en L, M, N
$hoja->setCellValue("M{$ventasStartRow}", "Moneda");
$hoja->setCellValue("N{$ventasStartRow}", "Monto");
$hoja->setCellValue("O{$ventasStartRow}", "Afecto");

// Aplicar estilo a todo el rango
$hoja->getStyle("K{$ventasStartRow}:O{$ventasStartRow}")->applyFromArray($estiloCabecera);
$ventasStartRow++;

// --- Datos ---
$totalGastosVentas = 0;
foreach ($gastosVentas as $g) {
    $monto = $g['monto'] ?? 0;
    $totalGastosVentas += $monto;

    // Concepto ocupa K y L (solo se escribe en K, L queda vacío por el merge)
    $hoja->setCellValue("K{$ventasStartRow}", $g['gasto'] ?? '');
    // Moneda en M
    $hoja->setCellValue("M{$ventasStartRow}", $g['moneda'] ?? '');
    // Monto en N
    $hoja->setCellValue("N{$ventasStartRow}", $monto);
    // Afecto en O
    $hoja->setCellValue("O{$ventasStartRow}", $g['afecto'] ?? '');
    // Estilos
    $hoja->getStyle("K{$ventasStartRow}:O{$ventasStartRow}")->applyFromArray($estiloCelda);
    $hoja->getStyle("N{$ventasStartRow}")->applyFromArray($estiloNumero); // Solo Monto es número

    $ventasStartRow++;
}

// --- Total ---
$hoja->setCellValue("K{$ventasStartRow}", "TOTAL MONTO:");
$hoja->mergeCells("K{$ventasStartRow}:L{$ventasStartRow}"); // Total también ocupa 2 columnas
$hoja->setCellValue("N{$ventasStartRow}", $totalGastosVentas);
$hoja->getStyle("N{$ventasStartRow}")->applyFromArray($estiloNumero);
$ventasStartRow += 2;

// === Total Gastos Locales + Profit Local ===
$profitLocal = $totalGastosVentas - $totalGastosCostos;
$profitPct = $totalGastosVentas > 0 ? ($profitLocal / $totalGastosVentas * 100) : 0;

$hoja->setCellValue("K{$ventasStartRow}", "Total Gastos Locales más Profit Local");
$hoja->getStyle("K{$ventasStartRow}")->applyFromArray($estiloTitulo);
$ventasStartRow++;

// Encabezados (sin fusionar, ya que no hay "Concepto")
$hoja->setCellValue("M{$ventasStartRow}", "Moneda");
$hoja->setCellValue("N{$ventasStartRow}", "Monto");
$hoja->getStyle("K{$ventasStartRow}:N{$ventasStartRow}")->applyFromArray($estiloCabecera);
$ventasStartRow++;

// Filas de datos: fusionar J-K para el label
$labels = ['TOTAL VENTA:', 'TOTAL COSTO:', 'PROFIT LOCAL:', 'PROFIT %:'];
$values = [$totalGastosVentas, $totalGastosCostos, $profitLocal, $profitPct . '%'];
$monedas = ['CLP', 'CLP', 'CLP', ''];

for ($i = 0; $i < count($labels); $i++) {
    $hoja->setCellValue("K{$ventasStartRow}", $labels[$i]);
    $hoja->mergeCells("K{$ventasStartRow}:L{$ventasStartRow}");
    $hoja->setCellValue("M{$ventasStartRow}", $monedas[$i]);
    
    if ($i < 3) {
        // Números
        $hoja->setCellValue("N{$ventasStartRow}", $values[$i]);
        $hoja->getStyle("N{$ventasStartRow}")->applyFromArray($estiloNumero);
    } else {
        // Porcentaje (texto)
        $hoja->setCellValue("N{$ventasStartRow}", $values[$i]);
    }
    $ventasStartRow++;
}
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
$hoja->getColumnDimension('A')->setWidth(10); // Columna vacía
$hoja->getColumnDimension('B')->setWidth(10); // Valores (ej: "Empresa XYZ")
$hoja->getColumnDimension('C')->setWidth(10); // Valores (ej: "Empresa XYZ")

// Columnas centro-izquierda: CONSIGNATARIO y datos adicionales
$hoja->getColumnDimension('D')->setWidth(10); // Labels CONSIGNATARIO
$hoja->getColumnDimension('E')->setWidth(10); // Valores CONSIGNATARIO

// Columnas centro-derecha: Datos adicionales del servicio (F-G)
$hoja->getColumnDimension('F')->setWidth(10); // Labels (ej: "TIPO CAMBIO CLIENTE:")
$hoja->getColumnDimension('G')->setWidth(10); // Valores (ej: "1,2345")
$hoja->getColumnDimension('H')->setWidth(10); // Valores (ej: "Empresa XYZ")

// Columnas derecha: Tablas (Profit Share, Gastos, etc.)
$hoja->getColumnDimension('I')->setWidth(10); // Concepto / Label
$hoja->getColumnDimension('J')->setWidth(10); // Moneda / Qty
$hoja->getColumnDimension('K')->setWidth(10); // Costo / Venta / Monto
$hoja->getColumnDimension('L')->setWidth(10); // Total / Afecto
$hoja->getColumnDimension('M')->setWidth(10); // Aplica (si se usa)
$hoja->getColumnDimension('N')->setWidth(10); // Extra (por margen)
$hoja->getColumnDimension('O')->setWidth(10); // Extra (por margen)

// === Desactivar AutoSize en todas las columnas usadas ===
$columnas = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O'];
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