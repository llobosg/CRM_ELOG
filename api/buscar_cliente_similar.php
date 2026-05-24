<?php
ob_start();
header('Content-Type: application/json');

require_once '../config.php';

try {

    $term = trim($_GET['term'] ?? '');

    if (!$term) {
        echo json_encode([]);
        exit;
    }

    // 🔥 búsqueda simple (puedes mejorar a fuzzy luego)
    $stmt = $pdo->prepare("
        SELECT razon_social, rut
        FROM clientes
        WHERE razon_social LIKE ?
        LIMIT 5
    ");

    $stmt->execute(["%$term%"]);

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_clean();
    echo json_encode($result);
    exit;

} catch (Throwable $e) {

    ob_clean();
    echo json_encode([
        'error' => $e->getMessage()
    ]);
    exit;
}