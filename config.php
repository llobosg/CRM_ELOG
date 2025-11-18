<?php
// config.php - Compatible con XAMPP local y Railway.app

if (getenv('MYSQLHOST')) {
    // Entorno Railway
    $db_host     = $_ENV['MYSQLHOST'];
    $db_port     = $_ENV['MYSQLPORT']     ?? 3306;
    $db_name     = $_ENV['MYSQLDATABASE'] ?? 'railway';
    $db_user     = $_ENV['MYSQLUSER']     ?? 'root';
    $db_password = $_ENV['MYSQLPASSWORD'] ?? '';
} else {
    // Entorno local (XAMPP)
    $db_host     = '127.0.0.1';
    $db_port     = 3306;
    $db_name     = 'crm_aduanas';
    $db_user     = 'root';
    $db_password = '';
}

try {
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);
} catch (PDOException $e) {
    if (getenv('MYSQLHOST')) {
        die("Error: No se pudo conectar a la base de datos.");
    } else {
        die("Error de conexión local: " . $e->getMessage());
    }
}
?>