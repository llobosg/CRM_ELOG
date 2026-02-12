<?php
require_once __DIR__ . '/../config.php';

// Verificar conexión
try {
    $stmt = $pdo->query("DESCRIBE prospectos");
    echo "<pre>";
    while ($row = $stmt->fetch()) {
        print_r($row);
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>