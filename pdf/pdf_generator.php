<?php
require_once '../config.php';
require_once 'fpdf/fpdf.php';

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'CRM Aduanas - Prospecto', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Datos del prospecto
$id_ppl = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM prospectos WHERE id_ppl = ?");
$stmt->execute([$id_ppl]);
$prospecto = $stmt->fetch();

if (!$prospecto) {
    $pdf->Cell(0, 10, 'Prospecto no encontrado.', 0, 1);
    $pdf->Output();
    exit;
}

$pdf->Cell(0, 10, 'Razón Social: ' . $prospecto['razon_social'], 0, 1);
$pdf->Cell(0, 10, 'RUT: ' . $prospecto['rut_empresa'], 0, 1);
$pdf->Cell(0, 10, 'Teléfono: ' . $prospecto['fono_empresa'], 0, 1);
$pdf->Cell(0, 10, 'País: ' . $prospecto['pais'], 0, 1);
$pdf->Cell(0, 10, 'Ciudad: ' . $prospecto['ciudad'], 0, 1);
$pdf->Cell(0, 10, 'Estado: ' . $prospecto['estado'], 0, 1);
$pdf->Cell(0, 10, 'Fecha Alta: ' . $prospecto['fecha_alta'], 0, 1);
$pdf->Ln(10);

// Servicios
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Servicios Asociados', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(50, 10, 'Servicio', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Tipo', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Costo', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Venta', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Gastos Destino', 1, 1, 'C', true);

$stmt = $pdo->prepare("SELECT * FROM servicios WHERE id_prospect = ?");
$stmt->execute([$id_ppl]);
$servicios = $stmt->fetchAll();

foreach ($servicios as $s) {
    $pdf->Cell(50, 10, $s['servicio'], 1, 0);
    $pdf->Cell(50, 10, $s['tipo'], 1, 0);
    $pdf->Cell(40, 10, '$' . number_format($s['costo'], 2), 1, 0, 'R');
    $pdf->Cell(40, 10, '$' . number_format($s['venta'], 2), 1, 0, 'R');
    $pdf->Cell(50, 10, '$' . number_format($s['ventasgastoslocalesdestino'], 2), 1, 1, 'R');
}

$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Totales:', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Total Costo: $' . number_format($prospecto['total_costo'], 2), 0, 1);
$pdf->Cell(0, 10, 'Total Venta: $' . number_format($prospecto['total_venta'], 2), 0, 1);
$pdf->Cell(0, 10, 'Total Gastos Destino: $' . number_format($prospecto['total_ventasgastoslocalesdestino'], 2), 0, 1);

$pdf->Output();