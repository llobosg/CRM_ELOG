<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

try {
    // ✅ Seleccionamos ID y nombre
    $stmt = $pdo->query("SELECT id_comm, commodity FROM commodity WHERE commodity IS NOT NULL AND commodity != '' ORDER BY commodity");
    $commodities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['commoditys' => $commodities]);
} catch (Exception $e) {
    error_log("Error en get_commoditys.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error al cargar commodities']);
}
?>