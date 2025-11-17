<?php
// config.php — Versión mínima y funcional para Railway

// Forzar la detección de Railway usando MYSQLHOST
if (!empty($_SERVER['MYSQLHOST'])) {
    $db_host = $_SERVER['MYSQLHOST'];
    $db_port = $_SERVER['MYSQLPORT'] ?? 3306;
    $db_name = $_SERVER['MYSQLDATABASE'] ?? 'railway';
    $db_user = $_SERVER['MYSQLUSER'] ?? 'root';
    $db_password = $_SERVER['MYSQL_ROOT_PASSWORD'] ?? $_SERVER['MYSQLPASSWORD'] ?? '';
} else {
    // Fallback local (solo para desarrollo)
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
    if (!empty($_SERVER['MYSQLHOST'])) {
        http_response_code(500);
        die("<h2>❌ Error en Railway</h2><p>No se pudo conectar a la base de datos.</p><pre>" . htmlspecialchars($e->getMessage()) . "</pre>");
    } else {
        die("<h2>❌ Error local</h2><pre>" . htmlspecialchars($e->getMessage()) . "</pre>");
    }
}
?>