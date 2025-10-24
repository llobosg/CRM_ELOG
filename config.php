<?php
// config.php - Compatible con XAMPP local y Railway.app

// Detectar entorno Railway: Railway inyecta MYSQLHOST automáticamente
if (getenv('MYSQLHOST')) {
    // Entorno Railway
    $host     = $_ENV['MYSQLHOST'];
    $port     = $_ENV['MYSQLPORT']     ?? 3306;
    $dbname   = $_ENV['MYSQLDATABASE'] ?? 'railway';
    $username = $_ENV['MYSQLUSER']     ?? 'root';
    $password = $_ENV['MYSQLPASSWORD'] ?? '';
} else {
    // Entorno local (XAMPP)
    $host     = '127.0.0.1';
    $port     = 3306;
    $dbname   = 'crm_aduanas';
    $username = 'root';
    $password = ''; // XAMPP no tiene contraseña por defecto
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // En producción, evita mostrar detalles sensibles
    if (getenv('MYSQLHOST')) {
        die("Error: No se pudo conectar a la base de datos.");
    } else {
        die("Error de conexión local: " . $e->getMessage());
    }
}
?>