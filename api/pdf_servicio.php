<?php
// api/pdf_servicio.php
// Generación de PDF de cotización con layout ajustado

// Evitar cualquier salida previa
ob_start();

// Incluir autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Incluir configuración
require_once __DIR__ . '/../config.php';

// --- Función para sanitizar texto ---
function sanitizeText($text) {
    if ($text === null) return '';
    return htmlspecialchars_decode($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);
}

// Obtener id_srvc desde GET o POST
$id_srvc = null;
if (!empty($_GET['id_srvc'])) {
    $id_srvc = $_GET['id_srvc'];
} elseif (!empty($_POST['id_srvc'])) {
    $id_srvc = $_POST['id_srvc'];
}

if (!$id_srvc) {
    http_response_code(400);
    echo 'Error: ID de servicio no proporcionado.';
    exit;
}

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Cargar datos del servicio y prospecto
    $stmt_serv = $pdo->prepare("
        SELECT s.*, p.razon_social, p.direccion, p.notas_comerciales, p.notas_operaciones, p.concatenado, p.rut_empresa
        FROM servicios s
        JOIN prospectos p ON s.id_prospect = p.id_ppl
        WHERE s.id_srvc = ?
    ");
    $stmt_serv->execute([$id_srvc]);
    $servicio = $stmt_serv->fetch(PDO::FETCH_ASSOC);

    if (!$servicio) {
        http_response_code(404);
        echo 'Error: Servicio no encontrado.';
        exit;
    }

    // Cargar costos y gastos
    $stmt_costos = $pdo->prepare("SELECT * FROM costos_servicios WHERE id_servicio = ?");
    $stmt_costos->execute([$id_srvc]);
    $costos = $stmt_costos->fetchAll(PDO::FETCH_ASSOC);

    $stmt_gastos = $pdo->prepare("SELECT * FROM gastos_locales_detalle WHERE id_servicio = ?");
    $stmt_gastos->execute([$id_srvc]);
    $gastos_locales = $stmt_gastos->fetchAll(PDO::FETCH_ASSOC);

    // Datos del prospecto
    $razonSocialProspecto = $servicio['razon_social'] ?? '';
    $direccionProspecto = $servicio['direccion'] ?? '';
    $rutClienteParaContacto = $servicio['rut_empresa'] ?? null;

    // Cargar contacto primario
    $contacto_nombre = '';
    $contacto_email = '';
    if ($rutClienteParaContacto) {
        $stmt_contacto = $pdo->prepare("
            SELECT nom_contacto, email
            FROM contactos
            WHERE rut_cliente = ? AND primario = 'S'
            LIMIT 1
        ");
        $stmt_contacto->execute([$rutClienteParaContacto]);
        $contacto_primario = $stmt_contacto->fetch(PDO::FETCH_ASSOC);
        if ($contacto_primario) {
            $contacto_nombre = $contacto_primario['nom_contacto'] ?? '';
            $contacto_email = $contacto_primario['email'] ?? '';
        }
    }

} catch (PDOException $e) {
    error_log("Error obteniendo datos para PDF: " . $e->getMessage());
    http_response_code(500);
    echo 'Error interno al obtener los datos del servicio.';
    exit;
}

// Preparar datos
$servicio_datos = [
    'servicio' => sanitizeText($servicio['servicio'] ?? ''),
    'trafico' => sanitizeText($servicio['trafico'] ?? ''),
    'moneda' => sanitizeText($servicio['moneda'] ?? ''),
    'bultos' => (int)($servicio['bultos'] ?? 0),
    'peso' => (float)($servicio['peso'] ?? 0),
    'volumen' => (float)($servicio['volumen'] ?? 0),
    'costo' => (float)($servicio['costo'] ?? 0),
    'venta' => (float)($servicio['venta'] ?? 0),
    'costogastoslocalesdestino' => (float)($servicio['costogastoslocalesdestino'] ?? 0),
    'ventasgastoslocalesdestino' => (float)($servicio['ventasgastoslocalesdestino'] ?? 0),
    'agente' => sanitizeText($servicio['agente'] ?? ''),
    'ref_cliente' => sanitizeText($servicio['ref_cliente'] ?? ''),
    'proveedor_nac' => sanitizeText($servicio['proveedor_nac'] ?? ''),
    'desconsolidac' => sanitizeText($servicio['desconsolidac'] ?? ''),
    'incoterm' => sanitizeText($servicio['incoterm'] ?? ''),
    'commodity' => sanitizeText($servicio['commodity'] ?? ''),
    'origen' => sanitizeText($servicio['origen'] ?? ''),
    'destino' => sanitizeText($servicio['destino'] ?? ''),
    'concatenado' => $servicio['concatenado'] ?? 'N/A',
    'nota_srvc' => sanitizeText($servicio['nota_srvc'] ?? ''),
    'transportador' => sanitizeText($servicio['transportador'] ?? ''),
    'validez' => sanitizeText($servicio['validez'] ?? ''),
    'aol' => sanitizeText($servicio['aol'] ?? ''),
    'aod' => sanitizeText($servicio['aod'] ?? ''),
    'nombre_corto' => sanitizeText($servicio['nombre_corto'] ?? ''),
    'tipo' => sanitizeText($servicio['tipo'] ?? ''),
    'sub_trafico' => sanitizeText($servicio['sub_trafico'] ?? ''),
    'base_calculo' => sanitizeText($servicio['base_calculo'] ?? ''),
    'estado' => sanitizeText($servicio['estado'] ?? ''),
    'tipo_cambio' => (float)($servicio['tipo_cambio'] ?? 1),
    'ciudad' => sanitizeText($servicio['ciudad'] ?? ''),
    'pais' => sanitizeText($servicio['pais'] ?? ''),
    'direc_serv' => sanitizeText($servicio['direc_serv'] ?? ''),
    'estado_costos' => sanitizeText($servicio['estado_costos'] ?? ''),
    'solicitado_por' => (int)($servicio['solicitado_por'] ?? 0),
    'fecha_solicitado' => $servicio['fecha_solicitado'] ?? null,
    'completado_por' => (int)($servicio['completado_por'] ?? 0),
    'fecha_completado' => $servicio['fecha_completado'] ?? null,
    'revisado_por' => (int)($servicio['revisado_por'] ?? 0),
    'fecha_revisado' => $servicio['fecha_revisado'] ?? null,
];

$costos_datos = array_map(function($c) {
    return [
        'concepto' => sanitizeText($c['concepto'] ?? ''),
        'moneda' => sanitizeText($c['moneda'] ?? ''),
        'qty' => (float)($c['qty'] ?? 0),
        'costo' => (float)($c['costo'] ?? 0),
        'tarifa' => (float)($c['tarifa'] ?? 0),
        'total_costo' => (float)($c['total_costo'] ?? 0),
        'aplica' => sanitizeText($c['aplica'] ?? ''),
    ];
}, $costos);

$gastos_datos = array_map(function($g) {
    return [
        'tipo' => sanitizeText($g['tipo'] ?? ''),
        'gasto' => sanitizeText($g['gasto'] ?? ''),
        'moneda' => sanitizeText($g['moneda'] ?? ''),
        'monto' => (float)($g['monto'] ?? 0),
        'afecto' => sanitizeText($g['afecto'] ?? ''),
        'iva' => (float)($g['iva'] ?? 0),
    ];
}, $gastos_locales);

class PDFSinLineas extends TCPDF {
    public function Header() {}
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(0, 10, '          Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages() . ' - Cotización: ' . $GLOBALS['servicio_datos']['concatenado'], 0, false, 'C');
    }
}

// Limpiar cualquier salida previa
if (ob_get_level()) {
    ob_end_clean();
}

$pdf = new PDFSinLineas(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Cotización - ' . $servicio_datos['concatenado']);
$pdf->SetHeaderData('', 0, '', '');
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 9));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', 8));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(20, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);

$pdf->AddPage();

// Ruta al logo
$logoPath = __DIR__ . '/../assets/logoelog2.png';

// Calcular texto de tipo de transporte
$tipoTrafico = strtolower($servicio_datos['trafico']);
$tipoTransporteTexto = 'TRANSPORTE';
if (strpos($tipoTrafico, 'mar') !== false) {
    $tipoTransporteTexto = 'NAVIERA';
} elseif (strpos($tipoTrafico, 'aer') !== false) {
    $tipoTransporteTexto = 'AEROLÍNEA';
} elseif (strpos($tipoTrafico, 'ter') !== false || strpos($tipoTrafico, 'land') !== false) {
    $tipoTransporteTexto = 'TRANSPORTE';
}

$html = '';

// Tabla principal de 4 columnas
$html .= '<table cellpadding="2" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size: 9pt;">';

// Fila 1: Logo y Número de Cotización
$html .= '<tr>';
$html .= '<td style="width: 25%; vertical-align: top; border: none;">';
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, $pdf->GetX()+1, $pdf->GetY()+1, 24, 0, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    $pdf->Ln(18);
} else {
    $html .= '<div style="height: 16mm; margin-bottom: 1mm; background-color: #eee; display: flex; align-items: center; justify-content: center; color: #999;">[Logo]</div>';
}
$html .= '</td>';
$html .= '<td style="width: 25%; border: none;"></td>';
$html .= '<td style="width: 25%; border: none;"><strong>NÚMERO DE COTIZACIÓN:</strong></td>';
$html .= '<td style="width: 25%; border: none;">' . $servicio_datos['concatenado'] . '</td>';
$html .= '</tr>';

// Fila 2: Fecha
$html .= '<tr><td style="border: none;" colspan="2"></td><td style="border: none;"><strong>FECHA:</strong></td><td style="border: none;">' . date('d-m-Y') . '</td></tr>';

// Fila 3: Fecha vigencia cotización
$html .= '<tr><td style="border: none;" colspan="2"></td><td style="border: none;"><strong>VALIDEZ COTIZACIÓN:</strong></td><td style="border: none;"><strong>' . $servicio_datos['validez'] . '</strong></td></tr>';

// Fila 4: Tráfico
$html .= '<tr><td style="border: none;" colspan="2"></td><td style="border: none;"><strong>TRÁFICO:</strong></td><td style="border: none;"><strong>' . $servicio_datos['trafico'] . '</strong></td></tr>';

// Fila 5: Espacio
$html .= '<tr><td style="border: none; height: 3mm;" colspan="4"></td></tr>';

// Fila 6: Atención, Empresa y Mensaje
$html .= '<tr><td style="border: none;" colspan="4">';
$html .= '<div style="font-size: 10pt; line-height: 1.4; margin-bottom: 1mm;">';
$html .= '<strong>Atención:</strong> ' . sanitizeText($contacto_nombre) . '<br>';
$html .= '<strong>Empresa:</strong> ' . sanitizeText($razonSocialProspecto) . '<br>';
$html .= '<br>Informamos a ustedes la cotización solicitada según los datos a continuación:<br>';
$html .= '</div>';
$html .= '</td></tr>';

// Fila 7: Espacio
$html .= '<tr><td style="border: none; height: 3mm;" colspan="4"></td></tr>';

// Fila 8: Tabla de Datos del Servicio
$html .= '<tr><td style="border: none; vertical-align: top;" colspan="4">';
$html .= '<table style="width: 100%; border-collapse: collapse; font-size: 9pt;">';

// Fila interna 1: Incoterm y Ref. Cliente
$html .= '<tr>';
$html .= '<td style="width: 25%; padding-right: 2mm; white-space: nowrap;"><strong>INCOTERM:</strong></td>';
$html .= '<td style="width: 25%; text-align: left;">' . $servicio_datos['incoterm'] . '</td>';
$html .= '<td style="width: 25%; padding-right: 2mm; white-space: nowrap;"><strong>REF. CLIENTE:</strong></td>';
$html .= '<td style="width: 25%; text-align: left;">' . $servicio_datos['ref_cliente'] . '</td>';
$html .= '</tr>';

// Fila interna 2: Commodity
$html .= '<tr><td style="border: none; height: 3mm;" colspan="4"></td></tr>';
$html .= '<tr>';
$html .= '<td style="width: 25%; padding-right: 2mm;"><strong>COMMODITY:</strong></td>';
$html .= '<td style="width: 50%; text-align: left;">' . $servicio_datos['commodity'] . '</td>';
$html .= '</tr>';

// Fila interna 3: Unidades FCL y Cantidad/Bultos
$html .= '<tr>';
$html .= '<td style="width: 25%; padding-right: 2mm;"><strong>UNIDADES FCL:</strong></td>';
$html .= '<td style="width: 25%; text-align: left;">' . $servicio_datos['bultos'] . '</td>';
$html .= '<td style="width: 25%; padding-right: 2mm;"><strong>CANTIDAD/BULTOS:</strong></td>';
$html .= '<td style="width: 25%; text-align: left;">' . $servicio_datos['bultos'] . '</td>';
$html .= '</tr>';

// Fila interna 4: Volumen y Peso Bruto
$html .= '<tr>';
$html .= '<td style="width: 25%; padding-right: 2mm;"><strong>VOLUMEN:</strong></td>';
$html .= '<td style="width: 25%; text-align: left;">' . number_format($servicio_datos['volumen'], 2) . '</td>';
$html .= '<td style="width: 25%; padding-right: 2mm;"><strong>PESO BRUTO:</strong></td>';
$html .= '<td style="width: 25%; text-align: left;">' . number_format($servicio_datos['peso'], 2) . '</td>';
$html .= '</tr>';

// Fila interna 5: POL y POD
$html .= '<tr>';
$html .= '<td style="width: 25%; padding-right: 2mm;"><strong>POL:</strong></td>';
$html .= '<td style="width: 25%; text-align: left;">' . $servicio_datos['origen'] . '</td>';
$html .= '<td style="width: 25%; padding-right: 2mm;"><strong>POD:</strong></td>';
$html .= '<td style="width: 25%; text-align: left;">' . $servicio_datos['destino'] . '</td>';
$html .= '</tr>';



$html .= '</table>';
$html .= '</td></tr>';

// Fila 9: Espacio
$html .= '<tr><td style="border: none; height: 3mm;" colspan="4"></td></tr>';

// Fila 10: Notas del Servicio
$html .= '<tr>';
$html .= '<td style="border: none;" colspan="4">';
$html .= '<div style="margin-bottom: 2px;"><strong>NOTAS SERVICIO</strong></div>';
$html .= '<div style="padding: 4px; border: 1px solid #ccc; border-radius: 4px; min-height: 30px; background-color: #fafafa; font-size: 8.5pt; line-height: 1.3;">';
$html .= nl2br(sanitizeText($servicio_datos['nota_srvc']));
$html .= '</div>';
$html .= '</td>';
$html .= '</tr>';

$html .= '</table>';

// Separador visual
$html .= '<div style="height:6mm;"></div>';
$html .= '<div style="background-color: #e9ecef; height: 1px;"></div>';

// Sección: GASTOS-VENTAS INTERNACIONALES (antes PROFIT SHARE)
$html .= '<div style="margin-top: 4mm; font-size: 9pt;">';
$html .= '<h3 style="font-size: 10pt; margin-bottom: 2mm;">GASTOS-VENTAS INTERNACIONALES</h3>';
$html .= '<table border="0" cellpadding="2" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size: 9pt;">';
$html .= '<thead><tr style="background-color: #f2f2f2;"><th style="border: 1px solid #ddd; text-align: left;">CONCEPTO</th><th style="border: 1px solid #ddd; text-align: center;">MONEDA</th><th style="border: 1px solid #ddd; text-align: center;">QTY</th><th style="border: 1px solid #ddd; text-align: right;">VENTA</th><th style="border: 1px solid #ddd; text-align: center;">APLICA</th></tr></thead>';
$html .= '<tbody>';

$total_costos = 0;
$total_venta = 0;
$total_venta_todo = 0;

foreach ($costos_datos as $c) {
    $qty = $c['qty'] ?? 0;
    $costo = $c['costo'] ?? 0;
    $tarifa = $c['tarifa'] ?? 0;
    $total_costos += $costo * $qty;
    $total_venta += $tarifa * $qty;
    $total_venta_todo += $total_venta_todo + $total_venta;
    $html .= '<tr>';
    $html .= '<td style="border: 1px solid #ddd;">' . $c['concepto'] . '</td>';
    $html .= '<td style="border: 1px solid #ddd; text-align: center;">' . $c['moneda'] . '</td>';
    $html .= '<td style="border: 1px solid #ddd; text-align: center;">' . number_format($qty, 2) . '</td>';
    $html .= '<td style="border: 1px solid #ddd; text-align: right;">' . number_format($total_venta, 2) . '</td>';
    $html .= '<td style="border: 1px solid #ddd; text-align: center;">' . $c['aplica'] . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody>';
$html .= '<tfoot>';
$html .= '<tr style="font-weight: bold;"><td style="border: 1px solid #ddd; text-align: right;" colspan="3">TOTAL:</td><td style="border: 1px solid #ddd; text-align: right;">' . number_format($total_venta_todo, 2) . '</td><td style="border: 1px solid #ddd;"></td></tr>';
// FILAS ELIMINADAS SEGÚN REQUERIMIENTO
/*
<tr style="font-weight: bold;"><td style="border: 1px solid #ddd; text-align: right;" colspan="3">TOTAL PROFIT:</td><td style="border: 1px solid #ddd; text-align: right;">' . number_format($total_venta - $total_costos, 2) . '</td><td style="border: 1px solid #ddd;"></td></tr>
<tr style="font-weight: bold;"><td style="border: 1px solid #ddd; text-align: right;" colspan="3">TOTAL PROFIT %:</td><td style="border: 1px solid #ddd; text-align: right;">' . ($total_venta > 0 ? number_format((($total_venta - $total_costos) / $total_venta) * 100, 2) : 0) . '%</td><td style="border: 1px solid #ddd;"></td></tr>
*/
$html .= '</tfoot>';
$html .= '</table>';
$html .= '</div>';

// Gastos Locales en Destino (Ventas y Costo)
if (!empty($gastos_datos)) {
    // Ventas
    $html .= '<div style="margin-top: 4mm; font-size: 9pt;">';
    $html .= '<h3 style="font-size: 10pt; margin-bottom: 2mm;">GASTOS LOCALES EN DESTINO</h3>';
    $html .= '<table border="0" cellpadding="2" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size: 9pt;">';
    $html .= '<thead><tr style="background-color: #f2f2f2;"><th style="border: 1px solid #ddd; text-align: left;">GASTO</th><th style="border: 1px solid #ddd; text-align: center;">MONEDA</th><th style="border: 1px solid #ddd; text-align: center;">AFECTO</th><th style="border: 1px solid #ddd; text-align: right;">MONTO</th></tr></thead>';
    $html .= '<tbody>';
    foreach ($gastos_datos as $g) {
        if (strtoupper($g['tipo']) === 'VENTAS') {
            $monto = $g['monto'] ?? 0;
            $html .= '<tr>';
            $html .= '<td style="border: 1px solid #ddd;">' . $g['gasto'] . '</td>';
            $html .= '<td style="border: 1px solid #ddd; text-align: center;">' . $g['moneda'] . '</td>';
            $html .= '<td style="border: 1px solid #ddd; text-align: center;">' . $g['afecto'] . '</td>';
            $html .= '<td style="border: 1px solid #ddd; text-align: right;">' . number_format($monto, 2) . '</td>';
            $html .= '</tr>';
        }
    }
    $html .= '</tbody></table></div>'; 
}

// Cargar y agregar condiciones de tráfico si existen
$condicionTraficoLimpia = '';
$tipoTraficoDelServicio = $servicio_datos['trafico'];

if ($tipoTraficoDelServicio) {
    try {
        $stmt_cond_interna = $pdo->prepare("
            SELECT condicion
            FROM condiciones_trafico
            WHERE trafico = ?
            LIMIT 1
        ");
        $stmt_cond_interna->execute([$tipoTraficoDelServicio]);
        $fila_cond_interna = $stmt_cond_interna->fetch(PDO::FETCH_ASSOC);

        if ($fila_cond_interna) {
            $condicion_cruda_interna = $fila_cond_interna['condicion'];
            $condicionTraficoLimpia = mb_convert_encoding($condicion_cruda_interna, 'UTF-8', 'auto');
            $condicionTraficoLimpia = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $condicionTraficoLimpia);
            $condicionTraficoLimpia = str_replace(["\xE2\x80\xA2", "\xE2\x80\xA3", "\xE2\x80\xA4", "\xE2\x80\xA5", "\xE2\x80\xA6", "\xEF\x82\xA7", "\xEF\x82\xA8", "\xEF\x82\xA9", "\xEF\x82\xAA", "\xEF\x82\xAB", "\xEF\x82\xAC", "\xEF\x82\xAD", "\xEF\x82\xAE", "\xEF\x82\xAF", "\xEF\x82\xB0", "\xEF\x82\xB1", "\xEF\x82\xB2", "\xEF\x82\xB3", "\xEF\x82\xB4", "\xEF\x82\xB5", "\xEF\x82\xB6", "\xEF\x82\xB7", "\xEF\x82\xB8", "\xEF\x82\xB9", "\xEF\x82\xBA", "\xEF\x82\xBB", "\xEF\x82\xBC", "\xEF\x82\xBD", "\xEF\x82\xBE", "\xEF\x82\xBF"], "• ", $condicionTraficoLimpia);
            $condicionTraficoLimpia = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $condicionTraficoLimpia);
            $condicionTraficoLimpia = trim($condicionTraficoLimpia);
            $condicionTraficoLimpia = preg_replace('/\s*•\s*/', "\n• ", $condicionTraficoLimpia);
        }
    } catch (PDOException $e) {
        error_log("[PDF_SERVICIO] Error al buscar condición de tráfico '$tipoTraficoDelServicio' para PDF: " . $e->getMessage());
    }
}

if ($condicionTraficoLimpia) {
    $html .= '<div style="margin-top: 4mm; font-size: 9pt; page-break-before: auto;">';
    $html .= '<h3 style="font-size: 10pt; margin-bottom: 2mm; text-decoration: underline;">CONDICIONES ESPECÍFICAS</h3>';
    $lineas = preg_split('/\r\n|\r|\n/', $condicionTraficoLimpia);
    $html .= '<table border="0" cellpadding="2" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size: 9pt;">';
    foreach ($lineas as $linea) {
        $linea = trim($linea);
        if ($linea !== '') {
            $html .= '<tr><td style="padding: 1px 0; vertical-align: top;">' . htmlspecialchars($linea, ENT_NOQUOTES | ENT_SUBSTITUTE | ENT_HTML401) . '</td></tr>';
        }
    }
    $html .= '</table>';
    $html .= '</div>';
}

// Salida del PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Configurar encabezados para descarga
$pdf->Output('Cotizacion_' . $servicio_datos['concatenado'] . '.pdf', 'I');

exit;
?>