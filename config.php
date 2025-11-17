<?php
// config.php — Compatible con XAMPP local y Railway (QA/Producción)

// Detectar entorno Railway usando la variable oficial
$isRailway = !empty($_SERVER['RAILWAY_ENVIRONMENT_NAME']);

if ($isRailway) {
    // ✅ Entorno Railway (QA o producción)
    $db_host = $_SERVER['MYSQLHOST'] ?? '127.0.0.1';
    $db_port = $_SERVER['MYSQLPORT'] ?? 3306;
    $db_name = $_SERVER['MYSQLDATABASE'] ?? 'railway';
    $db_user = $_SERVER['MYSQLUSER'] ?? 'root';
    $db_password = $_SERVER['MYSQL_ROOT_PASSWORD'] ?? $_SERVER['MYSQLPASSWORD'] ?? '';
} else {
    // ✅ Entorno local (XAMPP/MAMP)
    $db_host = '127.0.0.1';
    $db_port = 3306;
    $db_name = 'crm_aduanas'; // ← ajusta si tu BD local tiene otro nombre
    $db_user = 'root';
    $db_password = '';
}

try {
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    if ($isRailway) {
        die("Error en Railway (" . ($_SERVER['RAILWAY_ENVIRONMENT_NAME'] ?? 'desconocido') . "): No se pudo conectar a la base de datos.<br>" .
            "Host: $db_host, DB: $db_name, Usuario: $db_user<br>" .
            "Detalles: " . htmlspecialchars($e->getMessage()));
    } else {
        die("Error local: " . htmlspecialchars($e->getMessage()));
    }
}
?>