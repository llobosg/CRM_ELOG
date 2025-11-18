<?php
// config.php - Compatible con XAMPP local y Railway.app

    // Entorno Railway
    $db_host     = 'mysql://root:oTLbkFsCazCViKmMFncPSBSHfoYjOnhA@shuttle.proxy.rlwy.net:48498/railway';
    $db_port     = 48498;
    $db_name     = 'railway';
    $db_user     = 'root';
    $db_password = 'oTLbkFsCazCViKmMFncPSBSHfoYjOnhA';

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