<?php
// api/pdf_servicio.php
// Versión mejorada por ChatGPT - Diseño profesional para TCPDF

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

/**
 * Sanitizar texto para evitar problemas en PDF
 */
function sanitizeText($text) {
    if ($text === null) return '';
    return htmlspecialchars_decode($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);
}

/** Obtener ID del servicio */
$id_srvc = $_GET['id_srvc'] ?? $_POST['id_srvc'] ?? null;
if (!$id_srvc) {
    http_response_code(400);
    die('Error: ID de servicio no proporcionado.');
}

/** Cargar datos desde DB */
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

/** Preparar arreglos sanitizados */
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

/* -------------------- Clase TCPDF personalizada (header/footer) -------------------- */
class ServicePDF extends \TCPDF {
    public $logoPath = '';
    public $companyName = '';

    public function Header() {
        // Logo en la esquina superior izquierda
        if ($this->logoPath && file_exists($this->logoPath)) {
            $this->Image($this->logoPath, 15, 8, 26, 0, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        // Título en la cabecera (alineado a la derecha)
        $this->SetFont('helvetica', 'B', 12);
        $this->SetY(10);
        $this->Cell(0, 8, $this->companyName, 0, 1, 'R', 0, '', 0, false, 'T', 'M');

        // Línea separadora
        $this->SetDrawColor(200,200,200);
        $this->SetLineWidth(0.3);
        $this->Line(15, 30, $this->getPageWidth() - 15, 30);
        $this->Ln(5);
    }

    public function Footer() {
        $this->SetY(-18);
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(100,100,100);
        // Texto de pie - izquierda
        $this->Cell(0, 6, 'Documento generado por CRM-ELOG by GLTComex- ' . date('Y-m-d'), 0, 0, 'L');
        // Número de página - derecha
        $this->Cell(0, 6, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'R');
    }
}

/* -------------------- Instanciar PDF -------------------- */
$pdf = new ServicePDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('CRM-ELOG by GLTComex');
$pdf->SetTitle('Cotización - ' . $servicio_datos['concatenado']);
$pdf->SetMargins(18, 36, 18); // left, top, right (top accommodate header)
$pdf->SetAutoPageBreak(TRUE, 22);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('helvetica', '', 9);

$logoPath = __DIR__ . '/../assets/logoElog2.png';
$pdf->logoPath = $logoPath;
$pdf->companyName = 'ELOG'; // Cambia por nombre real si quieres

$pdf->AddPage();

/* -------------------- Estilos compatibles TCPDF -------------------- */
/* Nota: TCPDF soporta un subconjunto de CSS. Mantuvimos propiedades simples. */
$style = '
<style>
    body { font-family: helvetica, sans-serif; font-size: 9pt; color: #222; }
    .title { font-size: 14pt; font-weight: bold; margin-bottom: 2mm; }
    .subtitle { font-size: 10pt; color:#555; margin-bottom: 3mm; }
    .muted { color: #666; font-size: 8.5pt; }
    .section { margin-top:4mm; }
    table.grid { width:100%; border-collapse:collapse; font-size:9pt; }
    table.grid td { vertical-align: top; padding: 2px 4px; }
    .label { font-weight: bold; color:#111; }
    .value { color:#222; }
    .hr { background-color:#e9e9e9; height:1px; }
    .box { border:1px solid #e6e6e6; padding:6px; border-radius:2px; }
    .small { font-size:8.5pt; color:#444; }
    .table-stripe thead tr { background-color: #f7f7f7; }
    .table-stripe td, .table-stripe th { padding:6px; }
    .right { text-align: right; }
</style>
';
// --- NUEVAS VARIABLES NECESARIAS PARA EL NUEVO BLOQUE ---
$contacto = sanitizeText($servicio['nombre'] ?? ''); // Asumiendo que 'nombre' es el contacto en la tabla servicios o prospectos
// Si el contacto proviene de otra tabla (como contactos), necesitas hacer una consulta aquí.
// Ejemplo (si ya tienes el RUT del cliente en $servicio['rut_empresa']):
// $stmt_contacto = $pdo->prepare("SELECT nom_contacto FROM contactos WHERE rut_cliente = ? AND primario = 'S' LIMIT 1");
// $stmt_contacto->execute([$servicio['rut_empresa']]);
// $contacto_row = $stmt_contacto->fetch();
// $contacto = sanitizeText($contacto_row['nom_contacto'] ?? '');

$tipoTrafico = strtolower($servicio_datos['trafico'] ?? ''); // Asumiendo que 'trafico' define el tipo
if (strpos($tipoTrafico, 'mar') !== false) {
    $tipoTransporteTexto = 'NAVIERA';
} elseif (strpos($tipoTrafico, 'aer') !== false) {
    $tipoTransporteTexto = 'AEROLÍNEA';
} elseif (strpos($tipoTrafico, 'ter') !== false || strpos($tipoTrafico, 'land') !== false) {
    $tipoTransporteTexto = 'TRANSPORTE';
} else {
    $tipoTransporteTexto = 'TRANSPORTE'; // Valor por defecto
}

$html = $style;

/* -------------------- HEADER DEL DOCUMENTO (Título y subtítulo) -------------------- */
$html .= '<div class="title">COTIZACIÓN: <strong>' . $servicio_datos['concatenado'] . '</strong> &nbsp;&nbsp;|&nbsp;&nbsp; Fecha: ' . date('Y-m-d') . ' &nbsp;&nbsp;|&nbsp;&nbsp; Tráfico: ' . $servicio_datos['trafico'] . '</div>';

/* -------------------- GRID PRINCIPAL 5 COLUMNAS -------------------- */
$html .= '
<table cellpadding="3" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size: 9.5pt;">
    <colgroup>
        <col style="width:25%;">
        <col style="width:25%;">
        <col style="width:25%;">
        <col style="width:25%;">
    </colgroup>

    <!-- Spacer -->
    <tr><td colspan="5" style="height:4mm;"></td></tr>

    <!-- NUEVO BLOQUE: Información del Cliente y Servicio -->
    <tr>
        <td colspan="5" style="padding-bottom: 4mm;">
            <div style="font-size: 10pt; line-height: 1.4;">
                <strong>Atención:</strong> <span style="font-style: italic;">' . sanitizeText($contacto) . '</span> <!-- Campo contacto -->
                <br>
                <strong>Empresa:</strong> ' . sanitizeText($razonSocialProspecto) . '
                <br><br>
                Informamos a ustedes la cotización solicitada según los datos a continuación:
            </div>
        </td>
    </tr>

    <tr>
        <td style="vertical-align: top;">
            <table style="width: 100%; border-collapse: collapse; font-size: 9pt;">
                <tr>
                    <td style="padding-right: 2mm; white-space: nowrap;"><strong>INCOTERM:</strong></td>
                    <td style="text-align: right;">' . $servicio_datos['incoterm'] . '</td>
                </tr>
                <tr>
                    <td style="padding-right: 2mm;"><strong>COMMODITY:</strong></td>
                    <td style="text-align: right;">' . $servicio_datos['commodity'] . '</td>
                </tr>
                <tr>
                    <td style="padding-right: 2mm;"><strong>UNIDADES FCL:</strong></td>
                    <td style="text-align: right;">' . $servicio_datos['bultos'] . '</td> <!-- Asumiendo bultos como unidades FCL -->
                </tr>
                <tr>
                    <td style="padding-right: 2mm;"><strong>VOLUMEN:</strong></td>
                    <td style="text-align: right;">' . number_format($servicio['volumen'] ?? 0, 2) . '</td>
                </tr>
                <tr>
                    <td style="padding-right: 2mm;"><strong>POL:</strong></td>
                    <td style="text-align: right;">' . $servicio_datos['origen'] . '</td>
                </tr>
                <tr>
                    <td style="padding-right: 2mm;"><strong>POD:</strong></td>
                    <td style="text-align: right;">' . $servicio_datos['destino'] . '</td>
                </tr>
                <tr>
                    <td style="padding-right: 2mm;"><strong>TIPO:</strong></td> <!-- Etiqueta calculada -->
                    <td style="text-align: right;">' . $tipoTransporteTexto . '</td> <!-- Valor calculado -->
                </tr>
            </table>
        </td>
        <td style="vertical-align: top;">
            <table style="width: 100%; border-collapse: collapse; font-size: 9pt;">
                <tr>
                    <td style="padding-right: 2mm; white-space: nowrap;"><strong>Nº COTIZACIÓN:</strong></td>
                    <td style="text-align: right;">' . $servicio_datos['concatenado'] . '</td>
                </tr>
                <tr>
                    <td style="padding-right: 2mm;"><strong>REF. CLIENTE:</strong></td>
                    <td style="text-align: right;">' . $servicio_datos['ref_cliente'] . '</td>
                </tr>
                <tr>
                    <td style="padding-right: 2mm;"><strong>PESO BRUTO:</strong></td>
                    <td style="text-align: right;">' . number_format($servicio_datos['peso'], 2) . ' kg</td>
                </tr>
                <tr>
                    <td style="padding-right: 2mm;"><strong>CANTIDAD/BULTOS:</strong></td>
                    <td style="text-align: right;">' . $servicio_datos['bultos'] . '</td>
                </tr>
                <tr>
                    <td style="padding-right: 2mm;"><strong>AGENTE:</strong></td>
                    <td style="text-align: right;">' . $servicio_datos['agente'] . '</td>
                </tr>
                <tr>
                    <td style="padding-right: 2mm;"></td> <!-- Celda vacía para alinear -->
                    <td style="text-align: right;"></td> <!-- Celda vacía para alinear -->
                </tr>
                <tr>
                    <td style="padding-right: 2mm;"></td> <!-- Celda vacía para alinear -->
                    <td style="text-align: right;"></td> <!-- Celda vacía para alinear -->
                </tr>
            </table>
        </td>
        <td></td> <!-- Columna vacía central -->
        <td colspan="2" style="vertical-align: top;">
            <div style="margin-bottom: 2mm;"><strong>NOTAS SERVICIO</strong></div>
            <div style="padding: 4px; border: 1px solid #ccc; border-radius: 4px; min-height: 30px; background-color: #fafafa; font-size: 8.5pt; line-height: 1.3;">
                ' . nl2br(sanitizeText($servicio_datos['nota_srvc'])) . '
            </div>
        </td>
    </tr>

    <!-- Spacer antes de la siguiente sección -->
    <tr><td colspan="5" style="height:4mm;"></td></tr>

    <!-- Row: Tipo cambio / Agente (viejo código continúa aquí) -->
    <tr>
        <td><span class="label">TIPO CAMBIO:</span><div class="muted small">&nbsp;</div></td>
        <td><span class="label">AGENTE:</span><div class="value">' . $servicio_datos['agente'] . '</div></td>
        <td></td>
        <td><span class="label">PO # REF. CLIENTE:</span><div class="value">' . $servicio_datos['ref_cliente'] . '</div></td>
        <td><span class="label">PROVEEDOR NAC:</span><div class="value">' . $servicio_datos['proveedor_nac'] . '</div></td>
    </tr>

    <!-- Row: Tipo cambio / Agente -->
    <tr>
        <td><span class="label">TIPO CAMBIO:</span><div class="muted small">&nbsp;</div></td>
        <td><span class="label">AGENTE:</span><div class="value">' . $servicio_datos['agente'] . '</div></td>
        <td></td>
        <td><span class="label">PO # REF. CLIENTE:</span><div class="value">' . $servicio_datos['ref_cliente'] . '</div></td>
        <td><span class="label">PROVEEDOR NAC:</span><div class="value">' . $servicio_datos['proveedor_nac'] . '</div></td>
    </tr>

    <!-- Spacer -->
    <tr><td colspan="5" style="height:4mm;"></td></tr>

    <!-- Row: Shipper / Consignatario -->
    <tr>
        <td><span class="label">SHIPPER:</span><div class="value">' . $razonSocialProspecto . '</div></td>
        <td colspan="2"><span class="label">DIRECCIÓN:</span><div class="value">' . $direccionProspecto . '</div></td>
        <td><span class="label">CONSIGNATARIO:</span><div class="value">&nbsp;</div></td>
        <td><span class="label">DIRECCIÓN:</span><div class="value">&nbsp;</div></td>
    </tr>

    <tr><td colspan="5" style="height:3mm;"></td></tr>

    <!-- Row: Incoterm / Commodity -->
    <tr>
        <td><span class="label">INCOTERM:</span><div class="value">' . $servicio_datos['incoterm'] . '</div></td>
        <td><span class="label">PESO BRUTO:</span><div class="value">' . number_format($servicio_datos['peso'], 2) . ' kg</div></td>
        <td></td>
        <td><span class="label">COMMODITY:</span><div class="value">' . $servicio_datos['commodity'] . '</div></td>
        <td><span class="label">UNIDADES FCL:</span><div class="value">&nbsp;</div></td>
    </tr>

    <!-- Row: Cantidad / POL / POD -->
    <tr>
        <td><span class="label">CANTIDAD:</span><div class="value">' . $servicio_datos['bultos'] . '</div></td>
        <td><span class="label">VOLUMEN:</span><div class="value">' . number_format($servicio['volumen'] ?? 0, 2) . '</div></td>
        <td></td>
        <td><span class="label">POL:</span><div class="value">' . $servicio_datos['origen'] . '</div></td>
        <td><span class="label">POD:</span><div class="value">' . $servicio_datos['destino'] . '</div></td>
    </tr>

    <tr><td colspan="5" style="height:4mm;"></td></tr>

    <!-- Row: Naviera / Desconsolidación -->
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td><span class="label">NAVIERA:</span><div class="value">&nbsp;</div></td>
        <td><span class="label">DESCONSOLIDACIÓN:</span><div class="value">' . $servicio_datos['desconsolidac'] . '</div></td>
    </tr>

    <tr><td colspan="5" style="height:5mm;"></td></tr>

    <!-- Notas comerciales (usa columnas 4 y 5 para contenido amplio) -->
    <tr>
        <td colspan="2"><span class="label">NOTAS COMERCIALES:</span><div class="box small">' . nl2br(sanitizeText($notasComerciales)) . '</div></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

</table>
';

/* -------------------- Separador visual -------------------- */
$html .= '<div style="height:6mm;"></div>';
$html .= '<div class="hr"></div>';

/* -------------------- Profit Share (tabla detallada) -------------------- */
$html .= '
<div class="section">
    <div style="font-weight:bold; margin-bottom:3px;">PROFIT SHARE</div>
    <table class="table-stripe" border="0" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; font-size:9pt;">
        <thead>
            <tr>
                <th style="width:35%; text-align:left;">CONCEPTO</th>
                <th style="width:8%; text-align:center;">MONEDA</th>
                <th style="width:8%; text-align:center;">CANTIDAD</th>
                <th style="width:12%; text-align:center;">COSTO</th>
                <th style="width:12%; text-align:center;">VENTA</th>
                <th style="width:12%; text-align:right;">TOTAL</th>
                <th style="width:13%; text-align:center;">APLICA</th>
            </tr>
        </thead>
        <tbody>
';

/* fila principal - servicio */
$html .= '<tr>
    <td>' . $servicio_datos['servicio'] . '</td>
    <td style="text-align:center;">' . $servicio_datos['moneda'] . '</td>
    <td style="text-align:center;">1</td>
    <td style="text-align:right;">' . number_format($servicio_datos['costo'], 2) . '</td>
    <td style="text-align:right;">' . number_format($servicio_datos['venta'], 2) . '</td>
    <td style="text-align:right;">' . number_format($servicio_datos['costo'], 2) . '</td>
    <td style="text-align:center;">' . $servicio_datos['trafico'] . '</td>
</tr>';

/* filas de costos */
$total_costos = $servicio_datos['costo'];
$total_venta = $servicio_datos['venta'];
$total_total_costo = $servicio_datos['costo'];

foreach ($costos_datos as $c) {
    $html .= '<tr>
        <td>' . $c['concepto'] . '</td>
        <td style="text-align:center;">' . $c['moneda'] . '</td>
        <td style="text-align:center;">' . number_format($c['qty'], 2) . '</td>
        <td style="text-align:right;">' . number_format($c['costo'], 2) . '</td>
        <td style="text-align:right;">' . number_format($c['tarifa'], 2) . '</td>
        <td style="text-align:right;">' . number_format($c['total_costo'], 2) . '</td>
        <td style="text-align:center;">' . $c['aplica'] . '</td>
    </tr>';

    $total_costos += $c['costo'];
    $total_total_costo += $c['total_costo'];
    $total_venta += $c['tarifa'];
}

$html .= '
        </tbody>
        <tfoot>
            <tr style="font-weight:bold;">
                <td colspan="3" style="text-align:right;">TOTALES:</td>
                <td style="text-align:right;">' . number_format($total_costos, 2) . '</td>
                <td style="text-align:right;">' . number_format($total_venta, 2) . '</td>
                <td style="text-align:right;">' . number_format($total_total_costo, 2) . '</td>
                <td></td>
            </tr>
            <tr style="font-weight:bold;">
                <td colspan="5" style="text-align:right;">TOTAL PROFIT:</td>
                <td style="text-align:right;">' . number_format($total_venta - $total_costos, 2) . '</td>
                <td></td>
            </tr>
            <tr style="font-weight:bold;">
                <td colspan="5" style="text-align:right;">TOTAL PROFIT %:</td>
                <td style="text-align:right;">' . ($total_venta > 0 ? number_format((($total_venta - $total_costos) / $total_venta) * 100, 2) : '0.00') . '%</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
';

/* -------------------- Gastos ventas locales (si existen) -------------------- */
if (!empty($gastos_datos)) {
    $html .= '<div class="section"><div style="font-weight:bold; margin-bottom:3px;">GASTOS VENTAS LOCALES</div>';
    $html .= '<table border="0" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; font-size:9pt;">';
    $html .= '<thead><tr style="background-color:#f7f7f7;"><th>TIPO</th><th>GASTOS</th><th style="width:12%; text-align:center;">MON</th><th style="width:12%; text-align:right;">MONTO</th><th>AFECTO</th><th style="width:10%; text-align:right;">IVA%</th></tr></thead><tbody>';
    foreach ($gastos_datos as $g) {
        $html .= '<tr>
            <td>' . $g['tipo'] . '</td>
            <td>' . $g['gasto'] . '</td>
            <td style="text-align:center;">' . $g['moneda'] . '</td>
            <td style="text-align:right;">' . number_format($g['monto'], 2) . '</td>
            <td>' . $g['afecto'] . '</td>
            <td style="text-align:right;">' . number_format($g['iva'], 2) . '%</td>
        </tr>';
    }
    $html .= '</tbody></table></div>';
}

/* -------------------- Notas a Operaciones -------------------- */
$html .= '<div class="section"><div style="font-weight:bold; margin-bottom:3px;">NOTAS A OPERACIONES</div>';
$html .= '<div class="small box">' . nl2br(sanitizeText($notasOperaciones)) . '</div>';
$html .= '</div>';

/* -------------------- Escribir HTML al PDF -------------------- */
$pdf->writeHTML($html, true, false, true, false, '');

/* -------------------- Salida del PDF -------------------- */
$filename = 'Cotizacion_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $servicio_datos['concatenado']) . '.pdf';
$pdf->Output($filename, 'I'); // 'I' inline - abre en navegador
exit;
?>