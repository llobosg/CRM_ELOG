<?php
// api/pdf_servicio.php

// Incluir autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php'; // Ajusta la ruta si es necesario

// Incluir archivos de configuración y utilidades
require_once __DIR__ . '/../config.php'; // Asegúrate de que config.php esté aquí y configure $pdo

use TCPDF;

// --- Función para sanitizar texto (opcional pero recomendable para PDF) ---
function sanitizeText($text) {
    return htmlspecialchars_decode($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);
}

// --- Obtener ID del servicio ---
$id_srvc = $_GET['id_srvc'] ?? $_POST['id_srvc'] ?? null;

if (!$id_srvc) {
    http_response_code(400);
    die('Error: ID de servicio no proporcionado.');
}

// --- Obtener datos del servicio y prospecto ---
try {
    // 1. Obtener datos del servicio
    $stmt_serv = $pdo->prepare("
        SELECT s.*, p.razon_social, p.direccion, p.notas_comerciales, p.notas_operaciones
        FROM servicios s
        JOIN prospectos p ON s.id_prospect = p.id_ppl
        WHERE s.id_srvc = ?
    ");
    $stmt_serv->execute([$id_srvc]);
    $servicio = $stmt_serv->fetch(PDO::FETCH_ASSOC);

    if (!$servicio) {
        http_response_code(404);
        die('Error: Servicio no encontrado.');
    }

    // 2. Obtener costos del servicio
    $stmt_costos = $pdo->prepare("SELECT * FROM costos_servicios WHERE id_servicio = ?");
    $stmt_costos->execute([$id_srvc]);
    $costos = $stmt_costos->fetchAll(PDO::FETCH_ASSOC);

    // 3. Obtener gastos locales del servicio
    $stmt_gastos = $pdo->prepare("SELECT * FROM gastos_locales_detalle WHERE id_servicio = ?");
    $stmt_gastos->execute([$id_srvc]);
    $gastos_locales = $stmt_gastos->fetchAll(PDO::FETCH_ASSOC);

    // 4. Obtener datos del prospecto principal (ya están en $servicio gracias al JOIN)
    $razonSocialProspecto = $servicio['razon_social'] ?? '';
    $direccionProspecto = $servicio['direccion'] ?? '';
    $notasComerciales = $servicio['notas_comerciales'] ?? '';
    $notasOperaciones = $servicio['notas_operaciones'] ?? '';

} catch (PDOException $e) {
    error_log("Error obteniendo datos para PDF: " . $e->getMessage());
    http_response_code(500);
    die('Error interno al obtener los datos del servicio.');
}

// --- Preparar datos ---
// Sanitizar datos de entrada
$servicio_datos = [
    'servicio' => sanitizeText($servicio['servicio']),
    'trafico' => sanitizeText($servicio['trafico']),
    'moneda' => sanitizeText($servicio['moneda']),
    'bultos' => (int)($servicio['bultos'] ?? 0),
    'peso' => (float)($servicio['peso'] ?? 0),
    'volumen' => (float)($servicio['volumen'] ?? 0),
    'costo' => (float)($servicio['costo'] ?? 0),
    'venta' => (float)($servicio['venta'] ?? 0),
    'costogastoslocalesdestino' => (float)($servicio['costogastoslocalesdestino'] ?? 0),
    'ventasgastoslocalesdestino' => (float)($servicio['ventasgastoslocalesdestino'] ?? 0),
    'agente' => sanitizeText($servicio['agente']),
    'ref_cliente' => sanitizeText($servicio['ref_cliente']),
    'proveedor_nac' => sanitizeText($servicio['proveedor_nac']),
    'desconsolidac' => sanitizeText($servicio['desconsolidac']),
    'incoterm' => sanitizeText($servicio['incoterm']),
    'commodity' => sanitizeText($servicio['commodity']),
    'origen' => sanitizeText($servicio['origen']),
    'destino' => sanitizeText($servicio['destino']),
    'concatenado' => sanitizeText($servicio['concatenado']), // Asumiendo que este campo existe en servicios o se puede obtener
    // Añadir otros campos necesarios
];

$costos_datos = array_map(function($c) {
    return [
        'concepto' => sanitizeText($c['concepto']),
        'moneda' => sanitizeText($c['moneda']),
        'qty' => (float)($c['qty'] ?? 0),
        'costo' => (float)($c['costo'] ?? 0),
        'tarifa' => (float)($c['tarifa'] ?? 0),
        'total_costo' => (float)($c['total_costo'] ?? 0),
        'aplica' => sanitizeText($c['aplica']),
    ];
}, $costos);

$gastos_datos = array_map(function($g) {
    return [
        'tipo' => sanitizeText($g['tipo']),
        'gasto' => sanitizeText($g['gasto']),
        'moneda' => sanitizeText($g['moneda']),
        'monto' => (float)($g['monto'] ?? 0),
        'afecto' => sanitizeText($g['afecto']),
        'iva' => (float)($g['iva'] ?? 0),
    ];
}, $gastos_locales);

// --- Crear PDF ---
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configuración del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Cotización - ' . $servicio_datos['concatenado']);
$pdf->SetHeaderData('', 0, '', '');
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Agregar una página
$pdf->AddPage();

// --- Ruta al logo (ajusta la ruta) ---
$logoPath = __DIR__ . '/../assets/logo.png'; // Ajusta la ruta relativa a este archivo

// --- Contenido del PDF ---
$html = '';

// Encabezado con logo y número de cotización
$html .= '<table cellpadding="0" cellspacing="0" style="width: 100%;">';
$html .= '<tr>';
$html .= '<td style="width: 50%; vertical-align: top;">';
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, $pdf->GetX(), $pdf->GetY(), 30, 0, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    $pdf->Ln(25); // Ajustar espacio después del logo
} else {
    $html .= '<div style="height: 20mm; margin-bottom: 2mm; background-color: #eee; display: flex; align-items: center; justify-content: center; color: #999;">[Logo]</div>';
}
$html .= '<h1 style="font-size: 14pt; font-weight: bold; margin-top: 2mm;">NÚMERO DE COTIZACIÓN: ' . $servicio_datos['concatenado'] . '</h1>';
$html .= '</td>';
$html .= '<td style="width: 50%; text-align: right; vertical-align: top;">';
$html .= '<strong>TIPO CAMBIO CLIENTE:</strong><br>';
$html .= '<strong>AGENTE / OFICINA:</strong> ' . $servicio_datos['agente'] . '<br>';
$html .= '<strong>PO # REFERENCIA CLIENTE:</strong> ' . $servicio_datos['ref_cliente'] . '<br>';
$html .= '<strong>PROVEEDOR NACIONAL:</strong> ' . $servicio_datos['proveedor_nac'] . '<br>';
$html .= '<strong>TERRESTRE:</strong><br>';
$html .= '<strong>DESCONSOLIDACIÓN:</strong> ' . $servicio_datos['desconsolidac'] . '<br>';
$html .= '</td>';
$html .= '</tr>';
$html .= '</table>';

// Shipper y Consignatario
$html .= '<table cellpadding="2" cellspacing="0" style="width: 100%; margin-bottom: 5mm;">';
$html .= '<tr>';
$html .= '<td style="width: 50%; padding-right: 5mm;"><strong>SHIPPER:</strong> ' . $razonSocialProspecto . '<br><strong>DIRECCIÓN:</strong> ' . $direccionProspecto . '</td>';
$html .= '<td style="width: 50%; padding-left: 5mm;"><strong>CONSIGNATARIO:</strong><br><strong>DIRECCIÓN:</strong></td>';
$html .= '</tr>';
$html .= '</table>';

// Incoterm, Commodity, etc.
$html .= '<div style="margin-bottom: 5mm;">';
$html .= '<strong>INCOTERM:</strong> ' . $servicio_datos['incoterm'] . '<br>';
$html .= '<strong>COMMODITY:</strong> ' . $servicio_datos['commodity'] . '<br>';
$html .= '<div style="display: flex; justify-content: space-between;">';
$html .= '<div><strong>PESO BRUTO:</strong> ' . number_format($servicio_datos['peso'], 2) . ' kg</div>';
$html .= '<div><strong>UNIDADES FCL:</strong></div>';
$html .= '</div>';
$html .= '<div style="display: flex; justify-content: space-between;">';
$html .= '<div><strong>CANTIDAD UNIDADES:</strong> ' . $servicio_datos['bultos'] . '</div>';
$html .= '<div><strong>POL:</strong> ' . $servicio_datos['origen'] . '</div>';
$html .= '<div><strong>POD:</strong> ' . $servicio_datos['destino'] . '</div>';
$html .= '</div>';
$html .= '<div style="display: flex; justify-content: space-between;">';
$html .= '<div><strong>NAVIERA:</strong></div>';
$html .= '</div>';
$html .= '</div>';

// Notas Comerciales
$html .= '<div style="margin-bottom: 5mm;">';
$html .= '<h3 style="text-decoration: underline;">NOTAS COMERCIALES</h3>';
$html .= nl2br(sanitizeText($notasComerciales));
$html .= '</div>';

// Profit Share
$html .= '<h3>PROFIT SHARE</h3>';
$html .= '<table border="1" cellpadding="2" cellspacing="0" style="width: 100%; margin-bottom: 2mm;">';
$html .= '<thead><tr style="background-color: #f2f2f2;"><th style="text-align: center;">CONCEPTO</th><th style="text-align: center;">MONEDA</th><th style="text-align: center;">QTY</th><th style="text-align: center;">COSTO</th><th style="text-align: center;">VENTA</th><th style="text-align: center;">TOTAL</th><th style="text-align: center;">APLICA</th></tr></thead>';
$html .= '<tbody>';

// Fila del servicio principal
$html .= '<tr>';
$html .= '<td>' . $servicio_datos['servicio'] . '</td>';
$html .= '<td>' . $servicio_datos['moneda'] . '</td>';
$html .= '<td>1</td>';
$html .= '<td style="text-align: right;">' . number_format($servicio_datos['costo'], 2) . '</td>';
$html .= '<td style="text-align: right;">' . number_format($servicio_datos['venta'], 2) . '</td>';
$html .= '<td style="text-align: right;">' . number_format($servicio_datos['costo'], 2) . '</td>';
$html .= '<td>' . $servicio_datos['trafico'] . '</td>'; // Usando 'trafico' como ejemplo de 'aplica'
$html .= '</tr>';

// Filas de costos
foreach ($costos_datos as $c) {
    $html .= '<tr>';
    $html .= '<td>' . $c['concepto'] . '</td>';
    $html .= '<td>' . $c['moneda'] . '</td>';
    $html .= '<td style="text-align: center;">' . number_format($c['qty'], 2) . '</td>';
    $html .= '<td style="text-align: right;">' . number_format($c['costo'], 2) . '</td>';
    $html .= '<td style="text-align: right;">' . number_format($c['tarifa'], 2) . '</td>';
    $html .= '<td style="text-align: right;">' . number_format($c['total_costo'], 2) . '</td>';
    $html .= '<td>' . $c['aplica'] . '</td>';
    $html .= '</tr>';
}
$html .= '</tbody>';
$html .= '<tfoot>';
$html .= '<tr style="font-weight: bold;"><td colspan="3" style="text-align: right;">TOTALES:</td><td style="text-align: right;">' . number_format($servicio_datos['costo'], 2) . '</td><td style="text-align: right;">' . number_format($servicio_datos['venta'], 2) . '</td><td style="text-align: right;">' . number_format($servicio_datos['costo'], 2) . '</td><td></td></tr>';
$html .= '<tr style="font-weight: bold;"><td colspan="5" style="text-align: right;">TOTAL PROFIT:</td><td style="text-align: right;">' . number_format($servicio_datos['venta'] - $servicio_datos['costo'], 2) . '</td><td></td></tr>';
$html .= '<tr style="font-weight: bold;"><td colspan="5" style="text-align: right;">TOTAL PROFIT %:</td><td style="text-align: right;">' . ($servicio_datos['venta'] > 0 ? number_format((($servicio_datos['venta'] - $servicio_datos['costo']) / $servicio_datos['venta']) * 100, 2) : 0) . '%</td><td></td></tr>';
$html .= '</tfoot>';
$html .= '</table>';

// Gastos Ventas Locales
if (!empty($gastos_datos)) {
    $html .= '<h3>GASTOS VENTAS LOCALES</h3>';
    $html .= '<table border="1" cellpadding="2" cellspacing="0" style="width: 100%; margin-bottom: 2mm;">';
    $html .= '<thead><tr style="background-color: #f2f2f2;"><th style="text-align: center;">TIPO</th><th style="text-align: center;">GASTOS</th><th style="text-align: center;">MONEDA</th><th style="text-align: center;">MONTO</th><th style="text-align: center;">AFECTO</th><th style="text-align: center;">IVA%</th></tr></thead>';
    $html .= '<tbody>';
    foreach ($gastos_datos as $g) {
        $html .= '<tr>';
        $html .= '<td>' . $g['tipo'] . '</td>';
        $html .= '<td>' . $g['gasto'] . '</td>';
        $html .= '<td>' . $g['moneda'] . '</td>';
        $html .= '<td style="text-align: right;">' . number_format($g['monto'], 2) . '</td>';
        $html .= '<td>' . $g['afecto'] . '</td>';
        $html .= '<td style="text-align: right;">' . number_format($g['iva'], 2) . '%</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody>';
    $html .= '</table>';
}

// Notas Operaciones
$html .= '<div style="margin-top: 5mm;">';
$html .= '<h3 style="text-decoration: underline;">NOTAS A OPERACIONES</h3>';
$html .= nl2br(sanitizeText($notasOperaciones));
$html .= '</div>';

// Salida del PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Configurar encabezados para descarga
$pdf->Output('Cotizacion_' . $servicio_datos['concatenado'] . '.pdf', 'I'); // 'I' para inline (abrir en navegador), 'D' para descargar

?>