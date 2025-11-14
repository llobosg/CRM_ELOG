<?php
// config.php - Compatible con XAMPP local y Railway.app

// Detectar Railway por variable específica
$isRailway = !empty($_ENV['RAILWAY_ENVIRONMENT']) || !empty($_ENV['MYSQLHOST']);

if ($isRailway) {
    // ✅ Railway: usa las variables inyectadas
    $db_host     = $_ENV['MYSQLHOST']     ?? '127.0.0.1';
    $db_port     = $_ENV['MYSQLPORT']     ?? '3306';
    $db_name     = $_ENV['MYSQLDATABASE'] ?? 'railway';
    $db_user     = $_ENV['MYSQLUSER']     ?? 'root';
    $db_password = $_ENV['MYSQLPASSWORD'] ?? '';
} else {
    // ✅ Local: XAMPP/MAMP
    $db_host     = '127.0.0.1';
    $db_port     = 3306;
    $db_name     = 'crm_aduanas'; // ← ajusta si tu BD local se llama distinto
    $db_user     = 'root';
    $db_password = '';
}

try {
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    $msg = $isRailway 
        ? "Error: No se pudo conectar a la base de datos en Railway." 
        : "Error de conexión local: " . $e->getMessage();
    die($msg);
}
?>