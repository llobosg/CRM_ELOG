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
?><?php
// config.php — Modo diagnóstico con logs detallados

// === 1. Detectar entorno ===
$isRailway = !empty($_SERVER['RAILWAY_ENVIRONMENT_NAME']) 
              || !empty($_SERVER['MYSQLHOST']) 
              || !empty($_SERVER['DATABASE_URL']);

// === 2. Registrar todas las variables relevantes en error_log ===
error_log("🔍 [DIAGNÓSTICO CONFIG.PHP] Iniciando configuración de BD");
error_log("💻 Entorno Railway detectado: " . ($isRailway ? 'SÍ' : 'NO'));
if ($isRailway) {
    error_log("📦 RAILWAY_ENVIRONMENT_NAME: " . ($_SERVER['RAILWAY_ENVIRONMENT_NAME'] ?? 'NO DEFINIDO'));
    error_log("🔗 MYSQLHOST: " . ($_SERVER['MYSQLHOST'] ?? 'NO DEFINIDO'));
    error_log("🔏 MYSQLUSER: " . ($_SERVER['MYSQLUSER'] ?? 'NO DEFINIDO'));
    error_log("📁 MYSQLDATABASE: " . ($_SERVER['MYSQLDATABASE'] ?? 'NO DEFINIDO'));
    error_log("🔢 MYSQLPORT: " . ($_SERVER['MYSQLPORT'] ?? 'NO DEFINIDO'));
    error_log("🌐 DATABASE_URL: " . ($_SERVER['DATABASE_URL'] ?? 'NO DEFINIDO'));
}

// === 3. Configurar conexión ===
if ($isRailway) {
    $db_host = $_SERVER['MYSQLHOST'] ?? '127.0.0.1';
    $db_port = $_SERVER['MYSQLPORT'] ?? 3306;
    $db_name = $_SERVER['MYSQLDATABASE'] ?? 'railway';
    $db_user = $_SERVER['MYSQLUSER'] ?? 'root';
    $db_password = $_SERVER['MYSQL_ROOT_PASSWORD'] ?? $_SERVER['MYSQLPASSWORD'] ?? '';
} else {
    // Local
    $db_host = '127.0.0.1';
    $db_port = 3306;
    $db_name = 'crm_aduanas';
    $db_user = 'root';
    $db_password = '';
    error_log("💻 Entorno LOCAL detectado");
    error_log("📁 BD local: $db_name en $db_host:$db_port");
}

// === 4. Intentar conexión ===
try {
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
    error_log("📡 Intentando conexión con DSN: $dsn");
    $pdo = new PDO($dsn, $db_user, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    error_log("✅ Conexión a base de datos exitosa");
} catch (PDOException $e) {
    error_log("❌ Error de conexión: " . $e->getMessage());
    if ($isRailway) {
        die("<h2>Error en Railway QA</h2><pre>" . htmlspecialchars($e->getMessage()) . "</pre><p>Consulta los logs en Railway → Logs</p>");
    } else {
        die("<h2>Error local</h2><pre>" . htmlspecialchars($e->getMessage()) . "</pre>");
    }
}
?>