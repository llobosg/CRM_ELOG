<?php
// api/pdf_servicio.php

// Incluir autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Incluir archivos de configuración y utilidades
require_once __DIR__ . '/../config.php';

use TCPDF;

// --- Función para sanitizar texto (opcional pero recomendable para PDF) ---
function sanitizeText($text) {
    if ($text === null) {
        return '';
    }
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
    $stmt_serv = $pdo->prepare("
        SELECT s.*, p.razon_social, p.direccion, p.notas_comerciales, p.notas_operaciones, p.concatenado
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

    $stmt_costos = $pdo->prepare("SELECT * FROM costos_servicios WHERE id_servicio = ?");
    $stmt_costos->execute([$id_srvc]);
    $costos = $stmt_costos->fetchAll(PDO::FETCH_ASSOC);

    $stmt_gastos = $pdo->prepare("SELECT * FROM gastos_locales_detalle WHERE id_servicio = ?");
    $stmt_gastos->execute([$id_srvc]);
    $gastos_locales = $stmt_gastos->fetchAll(PDO::FETCH_ASSOC);

    $razonSocialProspecto = $servicio['razon_social'] ?? '';
    $direccionProspecto = $servicio['direccion'] ?? '';
    $notasComerciales = $servicio['notas_comerciales'] ?? '';
    $notasOperaciones = $servicio['notas_operaciones'] ?? '';
    $concatenadoServicio = $servicio['concatenado'] ?? 'N/A';

} catch (PDOException $e) {
    error_log("Error obteniendo datos para PDF: " . $e->getMessage());
    http_response_code(500);
    die('Error interno al obtener los datos del servicio.');
}

// --- Preparar datos ---
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
    'concatenado' => $concatenadoServicio,
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

// Configuración del documento (tamaño de letra base reducido un 10%)
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Cotización - ' . $servicio_datos['concatenado']);
$pdf->SetHeaderData('', 0, '', '');
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 9)); // Reducido de 10 a 9 (10% menos)
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', 8)); // Reducido de 9 a 8 (aprox 10% menos)
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
// Margen izquierdo aumentado ligeramente para compensar el logo
$pdf->SetMargins(20, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT); // Ajuste de margen izquierdo
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Agregar una página
$pdf->AddPage();

// --- Ruta al logo (ajusta la ruta) ---
$logoPath = __DIR__ . '/../assets/logo.png';

// --- Contenido del PDF (estructurado en tabla) ---
$html = '';

// Tabla principal de 5 columnas
$html .= '<table cellpadding="2" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size: 9pt;">'; // Tamaño de fuente base un 10% menor

// Fila 1: Logo y Número de Cotización
$html .= '<tr>';
$html .= '<td style="width: 20%; vertical-align: top; border: none;">';
if (file_exists($logoPath)) {
    // Ajustar el ancho del logo para que sea un 20% más pequeño
    $pdf->Image($logoPath, $pdf->GetX()+1, $pdf->GetY()+1, 24, 0, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false); // Ancho original * 0.8 = 30 * 0.8 = 24
    $pdf->Ln(22); // Ajustar espacio después del logo reducido
} else {
    $html .= '<div style="height: 16mm; margin-bottom: 1mm; background-color: #eee; display: flex; align-items: center; justify-content: center; color: #999;">[Logo]</div>';
}
$html .= '</td>';
$html .= '<td style="width: 30%; border: none;"></td>'; // Columna vacía
$html .= '<td style="width: 20%; border: none;"></td>'; // Columna vacía
$html .= '<td style="width: 15%; border: none;"><strong>NÚMERO DE COTIZACIÓN:</strong></td>';
$html .= '<td style="width: 15%; border: none;">' . $servicio_datos['concatenado'] . '</td>';
$html .= '</tr>';

// Fila 2: Espacio
$html .= '<tr><td style="border: none; height: 3mm;" colspan="5"></td></tr>';

// Fila 3: Tipo de Cambio Cliente (vacío para dejar espacio)
$html .= '<tr><td style="border: none;"><strong>TIPO CAMBIO CLIENTE:</strong></td><td style="border: none;"></td><td style="border: none;"></td><td style="border: none;"><strong>AGENTE / OFICINA:</strong></td><td style="border: none;">' . $servicio_datos['agente'] . '</td></tr>';

// Fila 4: PO # Referencia Cliente
$html .= '<tr><td style="border: none;"></td><td style="border: none;"></td><td style="border: none;"></td><td style="border: none;"><strong>PO # REFERENCIA CLIENTE:</strong></td><td style="border: none;">' . $servicio_datos['ref_cliente'] . '</td></tr>';

// Fila 5: Proveedor Nacional
$html .= '<tr><td style="border: none;"></td><td style="border: none;"></td><td style="border: none;"></td><td style="border: none;"><strong>PROVEEDOR NACIONAL:</strong></td><td style="border: none;">' . $servicio_datos['proveedor_nac'] . '</td></tr>';

// Fila 6: Terrestre (vacío)
$html .= '<tr><td style="border: none;"></td><td style="border: none;"></td><td style="border: none;"></td><td style="border: none;"><strong>TERRESTRE:</strong></td><td style="border: none;"></td></tr>';

// Fila 7: Desconsolidación
$html .= '<tr><td style="border: none;"></td><td style="border: none;"></td><td style="border: none;"></td><td style="border: none;"><strong>DESCONSOLIDACIÓN:</strong></td><td style="border: none;">' . $servicio_datos['desconsolidac'] . '</td></tr>';

// Fila 8: Espacio
$html .= '<tr><td style="border: none; height: 3mm;" colspan="5"></td></tr>';

// Fila 9: Shipper
$html .= '<tr><td style="border: none;"><strong>SHIPPER:</strong></td><td style="border: none;" colspan="2">' . $razonSocialProspecto . '</td><td style="border: none;"><strong>CONSIGNATARIO:</strong></td><td style="border: none;"></td></tr>';

// Fila 10: Dirección
$html .= '<tr><td style="border: none;"><strong>DIRECCIÓN:</strong></td><td style="border: none;" colspan="2">' . $direccionProspecto . '</td><td style="border: none;"><strong>DIRECCIÓN:</strong></td><td style="border: none;"></td></tr>';

// Fila 11: Espacio
$html .= '<tr><td style="border: none; height: 3mm;" colspan="5"></td></tr>';

// Fila 12: Incoterm
$html .= '<tr><td style="border: none;"><strong>INCOTERM:</strong></td><td style="border: none;">' . $servicio_datos['incoterm'] . '</td><td style="border: none;"></td><td style="border: none;"><strong>COMMODITY:</strong></td><td style="border: none;">' . $servicio_datos['commodity'] . '</td></tr>';

// Fila 13: Peso Bruto
$html .= '<tr><td style="border: none;"><strong>PESO BRUTO:</strong></td><td style="border: none;">' . number_format($servicio_datos['peso'], 2) . ' kg</td><td style="border: none;"></td><td style="border: none;"><strong>UNIDADES FCL:</strong></td><td style="border: none;"></td></tr>';

// Fila 14: Cantidad Unidades
$html .= '<tr><td style="border: none;"><strong>CANTIDAD UNIDADES:</strong></td><td style="border: none;">' . $servicio_datos['bultos'] . '</td><td style="border: none;"></td><td style="border: none;"><strong>POL:</strong></td><td style="border: none;">' . $servicio_datos['origen'] . '</td></tr>';

// Fila 15: POD
$html .= '<tr><td style="border: none;"></td><td style="border: none;"></td><td style="border: none;"></td><td style="border: none;"><strong>POD:</strong></td><td style="border: none;">' . $servicio_datos['destino'] . '</td></tr>';

// Fila 16: Naviera (espacio reducido)
$html .= '<tr><td style="border: none; height: 1mm;" colspan="5"></td></tr>'; // Espacio reducido antes de Naviera

// Fila 17: Naviera
$html .= '<tr><td style="border: none;"></td><td style="border: none;"></td><td style="border: none;"></td><td style="border: none;"><strong>NAVIERA:</strong></td><td style="border: none;"></td></tr>';

// Fila 18: Espacio
$html .= '<tr><td style="border: none; height: 3mm;" colspan="5"></td></tr>';

// Fila 19: Notas Comerciales (Título en columna 4)
$html .= '<tr><td style="border: none;"></td><td style="border: none;"></td><td style="border: none;"></td><td style="border: none;"><strong>NOTAS COMERCIALES:</strong></td><td style="border: none;"></td></tr>';

// Fila 20: Notas Comerciales (Contenido en columnas 4 y 5)
$html .= '<tr><td style="border: none;"></td><td style="border: none;"></td><td style="border: none;"></td><td style="border: none;" colspan="2">' . nl2br(sanitizeText($notasComerciales)) . '</td></tr>';

$html .= '</table>';

// Tabla para Profit Share (fuera de la principal para mejor manejo de anchos)
$html .= '<h3 style="font-size: 9pt; margin-top: 3mm;">PROFIT SHARE</h3>'; // Tamaño de título reducido
$html .= '<table border="1" cellpadding="2" cellspacing="0" style="width: 100%; margin-bottom: 2mm; font-size: 9pt;">'; // Tamaño de fuente reducido
$html .= '<thead><tr style="background-color: #f2f2f2;"><th style="text-align: center;">CONCEPTO</th><th style="text-align: center;">MONEDA</th><th style="text-align: center;">QTY</th><th style="text-align: center;">COSTO</th><th style="text-align: center;">VENTA</th><th style="text-align: center;">TOTAL</th><th style="text-align: center;">APLICA</th></tr></thead>';
$html .= '<tbody>';
$html .= '<tr>';
$html .= '<td>' . $servicio_datos['servicio'] . '</td>';
$html .= '<td>' . $servicio_datos['moneda'] . '</td>';
$html .= '<td>1</td>';
$html .= '<td style="text-align: right;">' . number_format($servicio_datos['costo'], 2) . '</td>';
$html .= '<td style="text-align: right;">' . number_format($servicio_datos['venta'], 2) . '</td>';
$html .= '<td style="text-align: right;">' . number_format($servicio_datos['costo'], 2) . '</td>';
$html .= '<td>' . $servicio_datos['trafico'] . '</td>';
$html .= '</tr>';
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

if (!empty($gastos_datos)) {
    $html .= '<h3 style="font-size: 9pt; margin-top: 3mm;">GASTOS VENTAS LOCALES</h3>'; // Tamaño de título reducido
    $html .= '<table border="1" cellpadding="2" cellspacing="0" style="width: 100%; margin-bottom: 2mm; font-size: 9pt;">'; // Tamaño de fuente reducido
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

$html .= '<div style="margin-top: 3mm;">'; // Espacio reducido antes de Notas Operaciones
$html .= '<h3 style="font-size: 9pt; text-decoration: underline;">NOTAS A OPERACIONES</h3>'; // Tamaño de título reducido
$html .= nl2br(sanitizeText($notasOperaciones));
$html .= '</div>';

// Salida del PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Configurar encabezados para descarga
$pdf->Output('Cotizacion_' . $servicio_datos['concatenado'] . '.pdf', 'I'); // 'I' para inline (abrir en navegador), 'D' para descargar

?>