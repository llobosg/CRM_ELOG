<?php
    // api/pdf_servicio.php
    // Versión corregida para incluir Atención/Empresa, tabla de 4 columnas, y corregir warning de use TCPDF

    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../config.php';

    // No es necesario usar 'use TCPDF;' si se instancia con \TCPDF

    function sanitizeText($text) {
        if ($text === null) return '';
        return htmlspecialchars_decode($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);
    }

    $id_srvc = $_GET['id_srvc'] ?? $_POST['id_srvc'] ?? null;
    if (!$id_srvc) {
        http_response_code(400);
        die('Error: ID de servicio no proporcionado.');
    }

    try {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
        $rutClienteParaContacto = $servicio['rut_empresa'] ?? null;

        // --- Cargar Contacto Primario ---
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
        'nota_srvc' => sanitizeText($servicio['nota_srvc']),
        // --- AÑADIDO: Transportador ---
        'transportador' => sanitizeText($servicio['transportador']),
        // Asegurar otros campos necesarios
        'aol' => sanitizeText($servicio['aol']),
        'aod' => sanitizeText($servicio['aod']),
        'nombre_corto' => sanitizeText($servicio['nombre_corto']),
        'tipo' => sanitizeText($servicio['tipo']),
        'sub_trafico' => sanitizeText($servicio['sub_trafico']),
        'base_calculo' => sanitizeText($servicio['base_calculo']),
        'estado' => sanitizeText($servicio['estado']),
        'desconsolidac' => sanitizeText($servicio['desconsolidac']),
        'tipo_cambio' => (float)($servicio['tipo_cambio'] ?? 1),
        'ciudad' => sanitizeText($servicio['ciudad']),
        'pais' => sanitizeText($servicio['pais']),
        'direc_serv' => sanitizeText($servicio['direc_serv']),
        'estado_costos' => sanitizeText($servicio['estado_costos']),
        'solicitado_por' => (int)($servicio['solicitado_por'] ?? null),
        'fecha_solicitado' => $servicio['fecha_solicitado'],
        'completado_por' => (int)($servicio['completado_por'] ?? null),
        'fecha_completado' => $servicio['fecha_completado'],
        'revisado_por' => (int)($servicio['revisado_por'] ?? null),
        'fecha_revisado' => $servicio['fecha_revisado'],
        'validez' => sanitizeText($servicio['validez'] ?? ''),
    ];
    // === FORMATEAR FECHA DE VALIDEZ ===
    $validezRaw = $servicio['validez'] ?? null;
    $validez = $validezRaw 
        ? date('d-m-Y', strtotime($validezRaw))
        : '';  
    $servicio_datos['validez'] = sanitizeText($validez);

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

    class PDFSinLineas extends TCPDF {
        // Desactiva header completamente
        public function Header() {}

        // Pie de página SOLO con número de página
        public function Footer() {
            // Posicionar 15 mm desde el fondo
            $this->SetY(-15);

            $this->SetFont('helvetica', '', 8);

            // Número de página centrado
            $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages() . ' - Cotización: ' . $GLOBALS['servicio_datos']['concatenado'], 0, false, 'R');
        }
    }

    // --- Crear PDF ---
    $pdf = new PDFSinLineas(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    // 🔥 DESACTIVAR HEADER Y FOOTER (tu requerimiento)
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);

    // Configuración del documento (tamaño de letra base reducido un 10%)
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle('Cotización - ' . $servicio_datos['concatenado']);
    //$pdf->SetHeaderData('', 0, '', '');
    //$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 9)); // Reducido de 10 a 9 (10% menos)
    //$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', 8)); // Reducido de 9 a 8 (aprox 10% menos)
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    // Margen izquierdo aumentado ligeramente para compensar el logo
    //$pdf->SetMargins(20, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT); // Ajuste de margen izquierdo
    $pdf->SetMargins(20, 28, 20); // Ajuste de margen izquierdo
    //$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Agregar una página
    $pdf->AddPage();

    // --- Ruta al logo (ajusta la ruta) ---
    $logoPath = __DIR__ . '/../assets/logoelog2.png';

    // --- Calcular texto de tipo de transporte ---
    $tipoTrafico = strtolower($servicio_datos['trafico'] ?? '');
    $tipoTransporteTexto = 'TRANSPORTE';
    if (strpos($tipoTrafico, 'mar') !== false) {
        $tipoTransporteTexto = 'NAVIERA';
    } elseif (strpos($tipoTrafico, 'aer') !== false) {
        $tipoTransporteTexto = 'AEROLÍNEA';
    } elseif (strpos($tipoTrafico, 'ter') !== false || strpos($tipoTrafico, 'land') !== false) {
        $tipoTransporteTexto = 'TRANSPORTE';
    }

    // --- Contenido del PDF (estructurado en tabla de 4 columnas) ---
    $html = '';

    // Tabla principal de 4 columnas
    $html .= '<table cellpadding="2" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size: 9pt;">'; // Tamaño de fuente base un 10% menor

    // Fila 1: Logo y Número de Cotización
    $html .= '<tr>';
    $html .= '<td style="width: 25%; vertical-align: top; border: none;">';
    if (file_exists($logoPath)) {
        // Ajustar el ancho del logo para que sea un 20% más pequeño
        //$pdf->Image($logoPath, $pdf->GetX()+1, $pdf->GetY()+1, 24, 0, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false); // Ancho original * 0.8 = 30 * 0.8 = 24
        $pdf->Image($logoPath, 12, 8, 26, 0, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false); // Ancho original * 0.8 = 30 * 0.8 = 24
        $pdf->Ln(18); // Ajustar espacio después del logo reducido (subió 2mm)
    } else {
        $html .= '<div style="height: 16mm; margin-bottom: 1mm; background-color: #eee; display: flex; align-items: center; justify-content: center; color: #999;">[Logo]</div>';
    }
    $html .= '</td>';
        $html .= '<td style="width: 25%; border: none;"></td>'; // Columna vacía
        $html .= '<td style="width: 25%; border: none;"><strong>NÚMERO DE COTIZACIÓN:</strong></td>';
        $html .= '<td style="width: 25%; border: none;">' . $servicio_datos['concatenado'] . '</td>';
    $html .= '</tr>';

    // Fila 2: Fecha
    $html .= '<tr><td style="border: none;" colspan="2"></td><td style="border: none;"><strong>FECHA COTIZACIÓN:</strong></td><td style="border: none;">' . date('d-m-Y') . '</td></tr>';

    // Fila 4: Fecha vigencia cotización
    $html .= '<tr><td style="border: none;" colspan="2"></td><td style="border: none;"><strong>VALIDEZ COTIZACIÓN:</strong></td><td style="border: none;"><strong>' . $servicio_datos['validez'] . '</strong></td></tr>';

    // Fila 5: Espacio
    $html .= '<tr><td style="border: none; height: 3mm;" colspan="4"></td></tr>';

    // Fila 6: Nueva Sección - Atención, Empresa y Mensaje
    $html .= '<tr><td style="border: none;" colspan="4">';
        $html .= '<div style="font-size: 10pt; line-height: 1.4;">';
        $html .= '<strong>Atención:</strong> <span style="font-style: italic;">' . sanitizeText($contacto_nombre) . '</span> <!-- Campo contacto -->';
        $html .= '<br><strong>Empresa:</strong> ' . sanitizeText($razonSocialProspecto);
        $html .= '<br><br>Informamos a ustedes la cotización solicitada según los datos a continuación:';
        $html .= '</div>';
    $html .= '</td></tr>';

    // Fila 7: Espacio
    $html .= '<tr><td style="border: none; height: 3mm;" colspan="4"></td></tr>';

    // Fila 8: Tabla de Datos del Servicio (4 columnas)
    $html .= '<tr><td style="border: none; vertical-align: top;" colspan="4">'; // Celda que ocupa toda la fila
    $html .= '<table style="width: 100%; border-collapse: collapse; font-size: 9pt;">'; // Tabla anidada para los datos del servicio

    // Fila interna 1 de la tabla de datos
    $html .= '<tr>';
        $html .= '<td style="width: 25%; padding-right: 2mm; white-space: nowrap;"><strong>INCOTERM:</strong></td>';
        $html .= '<td style="width: 25%; text-align: left;">' . $servicio_datos['incoterm'] . '</td>'; // Alineado a la izquierda
        $html .= '<td style="width: 25%; padding-right: 2mm; white-space: nowrap;"><strong>PO # REF. CLIENTE:</strong></td>';
        $html .= '<td style="width: 25%; text-align: left;">' . $servicio_datos['ref_cliente'] . '</td>'; // Alineado a la izquierda
    $html .= '</tr>';

    // Fila interna 2
    $html .= '<tr>';
        $html .= '<td style="width: 25%; padding-right: 2mm; white-space: nowrap;"><strong>COMMODITY:</strong></td>';
        $html .= '<td style="width: 50%; text-align: left;">' . $servicio_datos['commodity'] . '</td>'; // Alineado a la izquierda
    $html .= '</tr>';

    // Fila interna 3
    $html .= '<tr>';
        $html .= '<td style="width: 25%; padding-right: 2mm;"><strong>UNIDADES FCL:</strong></td>';
        $html .= '<td style="width: 25%; text-align: left;">' . $servicio_datos['bultos'] . '</td>'; // Alineado a la izquierda
        $html .= '<td style="width: 25%; padding-right: 2mm;"><strong>VOLUMEN:</strong></td>';
        $html .= '<td style="width: 25%; text-align: left;">' . number_format($servicio_datos['volumen'], 2) . '</td>'; // Alineado a la izquierda
    $html .= '</tr>';

    // Fila interna 3
    $html .= '<tr>';
        $html .= '<td style="width: 25%; padding-right: 2mm;"><strong>PESO BRUTO:</strong></td>';
        $html .= '<td style="width: 25%; text-align: left;">' . number_format($servicio_datos['peso'], 2) . ' kg</td>'; // Alineado a la izquierda
        $html .= '<td style="width: 25%; padding-right: 2mm;"><strong>CANTIDAD/BULTOS:</strong></td>';
        $html .= '<td style="width: 25%; text-align: left;">' . $servicio_datos['bultos'] . '</td>'; // Alineado a la izquierda
    $html .= '</tr>';

    // Fila interna 4
    $html .= '<tr>';
        $html .= '<td style="width: 25%; padding-right: 2mm;"><strong>POL:</strong></td>';
        $html .= '<td style="width: 25%; text-align: left;">' . $servicio_datos['origen'] . '</td>'; // Alineado a la izquierda
        $html .= '<td style="width: 25%; padding-right: 2mm;"><strong>POD:</strong></td>';
        $html .= '<td style="width: 25%; text-align: left;">' . $servicio_datos['destino'] . '</td>'; // Alineado a la izquierda
    $html .= '</tr>';

    // Fila interna 5
    $html .= '<tr>';
        $html .= '<td style="width: 25%; padding-right: 2mm;"><strong>AGENTE/OFICINA:</strong></td>';
        $html .= '<td style="width: 25%; text-align: left;">' . $servicio_datos['agente'] . '</td>'; // Alineado a la izquierda
        $html .= '<td style="width: 25%; padding-right: 2mm;"><strong>' . $tipoTransporteTexto . ':</strong></td>';
        $html .= '<td style="width: 25%; text-align: left;">' . $servicio_datos['transportador'] . '</td>'; // Alineado a la izquierda
    $html .= '</tr>';

    // Fila interna 6
    $html .= '<tr>';
        $html .= '<td style="width: 25%; padding-right: 2mm;"><strong>PROVEEDOR NAC:</strong></td>';
        $html .= '<td style="width: 25%; text-align: left;">' . $servicio_datos['proveedor_nac'] . '</td>'; // Alineado a la izquierda
    $html .= '</tr>';

    $html .= '</table>';
    $html .= '</td></tr>';

    // Fila interna 7: Espacio
    $html .= '<tr><td style="border: none; height: 3mm;" colspan="4"></td></tr>';

    // Fila 8: Notas del Servicio (ocupando 2 columnas de la tabla principal de 4)
    $html .= '<tr>';
        $html .= '<td style="border: none;" colspan="4">'; // Ocupa toda la fila
        $html .= '<div style="margin-bottom: 2px;"><strong>NOTAS SERVICIO</strong></div>';
        $html .= '<div style="padding: 4px; border: 1px solid #ccc; border-radius: 4px; min-height: 30px; background-color: #fafafa; font-size: 8.5pt; line-height: 1.3;">';
        $html .= nl2br(sanitizeText($servicio_datos['nota_srvc'])); // Mostrar la nota del servicio
        $html .= '</div>';
        $html .= '</td>';
    $html .= '</tr>';

    $html .= '</table>';

    // --- Separador visual ---
    $html .= '<div style="height:6mm;"></div>';
    $html .= '<div style="background-color: #e9ecef; height: 1px;"></div>';

    // --- Continuar con Profit Share y demás ---
    $html .= '<div style="margin-top: 4mm; font-size: 9pt;">'; // Tamaño de fuente base un 10% menor para secciones posteriores
        $html .= '<h3 style="font-size: 10pt; margin-bottom: 2mm;">PROFIT SHARE</h3>'; // Tamaño de título ligeramente menor
        $html .= '<table border="0" cellpadding="2" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size: 9pt;">'; // Tamaño de fuente base un 10% menor
            $html .= '<thead><tr style="background-color: #f2f2f2;"><th style="border: 1px solid #ddd; text-align: left;">CONCEPTO</th><th style="border: 1px solid #ddd; text-align: center;">MONEDA</th><th style="border: 1px solid #ddd; text-align: center;">QTY</th><th style="border: 1px solid #ddd; text-align: right;">COSTO</th><th style="border: 1px solid #ddd; text-align: right;">VENTA</th><th style="border: 1px solid #ddd; text-align: right;">TOTAL</th><th style="border: 1px solid #ddd; text-align: center;">APLICA</th></tr></thead>';
            $html .= '<tbody>';
            $html .= '<tr>';
                $html .= '<td style="border: 1px solid #ddd;">' . $servicio_datos['servicio'] . '</td>';
                $html .= '<td style="border: 1px solid #ddd; text-align: center;">' . $servicio_datos['moneda'] . '</td>';
                $html .= '<td style="border: 1px solid #ddd; text-align: center;">1</td>';
                $html .= '<td style="border: 1px solid #ddd; text-align: right;">' . number_format($servicio_datos['costo'], 2) . '</td>';
                $html .= '<td style="border: 1px solid #ddd; text-align: right;">' . number_format($servicio_datos['venta'], 2) . '</td>';
                $html .= '<td style="border: 1px solid #ddd; text-align: right;">' . number_format($servicio_datos['costo'], 2) . '</td>';
                $html .= '<td style="border: 1px solid #ddd; text-align: center;">' . $servicio_datos['trafico'] . '</td>';
            $html .= '</tr>';
            $total_costos = $servicio_datos['costo'];
            $total_venta = $servicio_datos['venta'];
            $total_total_costo = $servicio_datos['costo'];

            foreach ($costos_datos as $c) {
                $html .= '<tr>';
                    $html .= '<td style="border: 1px solid #ddd;">' . $c['concepto'] . '</td>';
                    $html .= '<td style="border: 1px solid #ddd; text-align: center;">' . $c['moneda'] . '</td>';
                    $html .= '<td style="border: 1px solid #ddd; text-align: center;">' . number_format($c['qty'], 2) . '</td>';
                    $html .= '<td style="border: 1px solid #ddd; text-align: right;">' . number_format($c['costo'], 2) . '</td>';
                    $html .= '<td style="border: 1px solid #ddd; text-align: right;">' . number_format($c['tarifa'], 2) . '</td>';
                    $html .= '<td style="border: 1px solid #ddd; text-align: right;">' . number_format($c['total_costo'], 2) . '</td>';
                    $html .= '<td style="border: 1px solid #ddd; text-align: center;">' . $c['aplica'] . '</td>';
                $html .= '</tr>';
                $total_costos += $c['costo'];
                $total_total_costo += $c['total_costo'];
                $total_venta += $c['tarifa'];
            }
            $html .= '</tbody>';
            $html .= '<tfoot>';
                $html .= '<tr style="font-weight: bold;"><td style="border: 1px solid #ddd; text-align: right;" colspan="3">TOTALES:</td><td style="border: 1px solid #ddd; text-align: right;">' . number_format($total_costos, 2) . '</td><td style="border: 1px solid #ddd; text-align: right;">' . number_format($total_venta, 2) . '</td><td style="border: 1px solid #ddd; text-align: right;">' . number_format($total_total_costo, 2) . '</td><td style="border: 1px solid #ddd;"></td></tr>';
                $html .= '<tr style="font-weight: bold;"><td style="border: 1px solid #ddd; text-align: right;" colspan="5">TOTAL PROFIT:</td><td style="border: 1px solid #ddd; text-align: right;">' . number_format($total_venta - $total_costos, 2) . '</td><td style="border: 1px solid #ddd;"></td></tr>';
                $html .= '<tr style="font-weight: bold;"><td style="border: 1px solid #ddd; text-align: right;" colspan="5">TOTAL PROFIT %:</td><td style="border: 1px solid #ddd; text-align: right;">' . ($total_venta > 0 ? number_format((($total_venta - $total_costos) / $total_venta) * 100, 2) : 0) . '%</td><td style="border: 1px solid #ddd;"></td></tr>';
            $html .= '</tfoot>';
        $html .= '</table>';
    $html .= '</div>';

    if (!empty($gastos_datos)) {
        $html .= '<div style="margin-top: 4mm; font-size: 9pt;">'; // Tamaño de fuente base un 10% menor
        $html .= '<h3 style="font-size: 10pt; margin-bottom: 2mm;">GASTOS VENTAS LOCALES</h3>'; // Tamaño de título ligeramente menor
        $html .= '<table border="0" cellpadding="2" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size: 9pt;">'; // Tamaño de fuente base un 10% menor
        $html .= '<thead><tr style="background-color: #f2f2f2;"><th style="border: 1px solid #ddd; text-align: center;">TIPO</th><th style="border: 1px solid #ddd; text-align: center;">GASTOS</th><th style="border: 1px solid #ddd; text-align: center;">MONEDA</th><th style="border: 1px solid #ddd; text-align: right;">MONTO</th><th style="border: 1px solid #ddd; text-align: center;">AFECTO</th><th style="border: 1px solid #ddd; text-align: right;">IVA%</th></tr></thead>';
        $html .= '<tbody>';
        foreach ($gastos_datos as $g) {
            $html .= '<tr>';
            $html .= '<td style="border: 1px solid #ddd;">' . $g['tipo'] . '</td>';
            $html .= '<td style="border: 1px solid #ddd;">' . $g['gasto'] . '</td>';
            $html .= '<td style="border: 1px solid #ddd; text-align: center;">' . $g['moneda'] . '</td>';
            $html .= '<td style="border: 1px solid #ddd; text-align: right;">' . number_format($g['monto'], 2) . '</td>';
            $html .= '<td style="border: 1px solid #ddd; text-align: center;">' . $g['afecto'] . '</td>';
            $html .= '<td style="border: 1px solid #ddd; text-align: right;">' . number_format($g['iva'], 2) . '%</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
    }

    // --- Notas Comerciales ---
    $html .= '<div style="margin-top: 4mm; font-size: 9pt;">'; // Tamaño de fuente base un 10% menor
        $html .= '<h3 style="font-size: 10pt; text-decoration: underline; margin-bottom: 2mm;">NOTAS COMERCIALES</h3>'; // Tamaño de título ligeramente menor
        $html .= nl2br(sanitizeText($notasComerciales));
    $html .= '</div>';

    // --- Notas Comerciales ---
    $html .= '<div style="margin-top: 4mm; font-size: 9pt;">'; // Tamaño de fuente base un 10% menor
        $html .= '<h3 style="font-size: 10pt; text-decoration: underline;">NOTAS A OPERACIONES</h3>'; // Tamaño de título ligeramente menor
        $html .= nl2br(sanitizeText($notasOperaciones));
    $html .= '</div>';

    // --- Cargar y Agregar Notas Condicionales según Tráfico ---
    // 1. Obtener el tipo de tráfico del servicio actual
    $tipoTrafico = $servicio_datos['trafico'] ?? '';
    $notasCondicionales = ''; // Inicializar variable

    if ($tipoTrafico) {
        try {
            // 2. Consultar la tabla condiciones_trafico
            $stmt_condiciones = $pdo->prepare("SELECT condicion FROM condiciones_trafico WHERE trafico = ?");
            $stmt_condiciones->execute([trim($tipoTrafico)]);
            $fila_condicion = $stmt_condiciones->fetch(PDO::FETCH_ASSOC);

            if ($fila_condicion) {
                // 3. Si se encuentra, asignar el texto
                $notasCondicionales = sanitizeText($fila_condicion['condicion']);
            } else {
                // Opcional: Si no se encuentra una condición específica, dejar vacío o poner un mensaje
                $notasCondicionales = ''; // O un mensaje como "(No hay condiciones específicas para este tráfico)"
                error_log("[PDF_SERVICIO] Advertencia: No se encontraron condiciones para el tráfico '$tipoTrafico'");
            }
        } catch (PDOException $e) {
            // Manejar error de base de datos al buscar condiciones
            error_log("[PDF_SERVICIO] Error al buscar condiciones de tráfico: " . $e->getMessage());
            $notasCondicionales = ''; // Dejar vacío en caso de error
            // Opcional: Mostrar un mensaje de error genérico en el PDF si es crítico
            // $notasCondicionales = 'Error al cargar condiciones específicas.';
        }
    }

    // 4. Añadir las notas condicionales al HTML del PDF (si existen)
    if ($notasCondicionales) {
        $html .= '<div style="margin-top: 4mm; font-size: 9pt; page-break-before: always;">'; // Nueva página opcional, tamaño de fuente base
        $html .= '<h3 style="font-size: 10pt; margin-bottom: 2mm;">Notas adicionales - ' . strtoupper($tipoTrafico) . '</h3>'; // Título con tráfico
        $html .= '<div style="line-height: 1.4;">'; // Contenedor para mejor formato de párrafos
        $html .= nl2br($notasCondicionales); // Asegura saltos de línea
        $html .= '</div>';
        $html .= '</div>';
    }

    // Salida del PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Configurar encabezados para descarga
    $pdf->Output('Cotizacion_' . $servicio_datos['concatenado'] . '.pdf', 'I'); // 'I' para inline (abrir en navegador), 'D' para descargar

?>