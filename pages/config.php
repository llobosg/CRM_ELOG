<?php
$host = "127.0.0.1";
$dbname = "crm_aduanas";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

function limpiarRUT($rut) {
    return preg_replace('/[^0-9kK]/', '', strtolower($rut));
}
?>