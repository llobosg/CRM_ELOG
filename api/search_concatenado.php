<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

$term = $_GET['term'] ?? '';
$term = trim($term);

if (strlen($term) < 3) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_ppl, concatenado, razon_social, rut_empresa, estado FROM prospectos WHERE UPPER(concatenado) LIKE ? ORDER BY concatenado LIMIT 10");
    $stmt->execute(["%" . strtoupper($term) . "%"]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Asegurar vigente y nuevo como 'S'/'N' si se usan
    foreach ($resultados as &$p) {
        $p['vigente'] = isset($p['vigente']) && in_array($p['vigente'], ['S', 'N']) ? $p['vigente'] : 'S';
        $p['nuevo'] = isset($p['nuevo']) && in_array($p['nuevo'], ['S', 'N']) ? $p['nuevo'] : 'S';
    }

    echo json_encode($resultados);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([]);
}