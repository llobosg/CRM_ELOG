<?php
// Detectar Railway usando $_ENV o $_SERVER (no getenv)
$isRailway = isset($_ENV['MYSQLHOST']) || isset($_SERVER['MYSQLHOST']);

if ($isRailway) {
    // Railway: usa las variables inyectadas
    $db_host = $_ENV['MYSQLHOST']     ?? $_SERVER['MYSQLHOST']     ?? '127.0.0.1';
    $db_port = $_ENV['MYSQLPORT']     ?? $_SERVER['MYSQLPORT']     ?? 3306;
    $db_name = $_ENV['MYSQLDATABASE'] ?? $_SERVER['MYSQLDATABASE'] ?? 'railway';
    $db_user = $_ENV['MYSQLUSER']     ?? $_SERVER['MYSQLUSER']     ?? 'root';
    $db_password = $_ENV['MYSQLPASSWORD'] ?? $_SERVER['MYSQLPASSWORD'] ?? '';
} else {
    // Local (XAMPP)
    $db_host = '127.0.0.1';
    $db_port = 3306;
    $db_name = 'crm_aduanas';
    $db_user = 'root';
    $db_password = '';
}

try {
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    if ($isRailway) {
        die("Error en Railway: " . $e->getMessage());
    } else {
        die("Error local: " . $e->getMessage());
    }
}
?>