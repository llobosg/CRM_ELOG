<?php
$host = "127.0.0.1";
$dbname = "crm_aduanas";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // En config.php, después de $pdo = new PDO( ... );
    $pdo->exec("SET NAMES utf8mb4");
    // Opcional pero recomendado:
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

function limpiarRUT($rut) {
    return preg_replace('/[^0-9kK]/', '', strtolower($rut));
}
?>