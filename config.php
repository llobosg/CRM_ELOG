<?php
// config.php — Detección robusta de Railway

// === Detección de entorno Railway ===
$isRailway = (
    echo "<p style='color: green;'>¡pasa por $isRailway!</p>";
    !empty($_SERVER['RAILWAY']) ||
    !empty($_SERVER['RAILWAY_ENVIRONMENT']) ||
    !empty($_SERVER['RAILWAY_ENVIRONMENT_NAME']) ||
    !empty($_SERVER['MYSQLHOST']) ||
    !empty($_ENV['RAILWAY']) ||
    !empty($_ENV['MYSQLHOST']) ||
    (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], '.railway.app') !== false)
);

if ($isRailway) {
    // ✅ Entorno Railway (QA o producción)
    error_log("✅ [CONFIG] Detectado entorno Railway");
    echo "<p style='color: green;'>¡detecta entorno $isRailway!</p>";
    $db_host = $_SERVER['MYSQLHOST'] ?? $_ENV['MYSQLHOST'] ?? '127.0.0.1';
    $db_port = $_SERVER['MYSQLPORT'] ?? $_ENV['MYSQLPORT'] ?? 3306;
    $db_name = $_SERVER['MYSQLDATABASE'] ?? $_ENV['MYSQLDATABASE'] ?? 'railway';
    $db_user = $_SERVER['MYSQLUSER'] ?? $_ENV['MYSQLUSER'] ?? 'root';
    $db_password = $_SERVER['MYSQL_ROOT_PASSWORD'] ?? $_ENV['MYSQL_ROOT_PASSWORD'] ?? $_SERVER['MYSQLPASSWORD'] ?? $_ENV['MYSQLPASSWORD'] ?? '';
} else {
    // ✅ Entorno local (XAMPP/MAMP)
    error_log("🖥️ [CONFIG] Detectado entorno local");
    echo "<p style='color: green;'>¡no detectó entorno $isRailway, pasa por else...!</p>";
    $db_host = '127.0.0.1';
    $db_port = 3306;
    $db_name = 'crm_aduanas';
    $db_user = 'root';
    $db_password = '';
}

// === Conexión ===
try {
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    if ($isRailway) {
        die("<h2>❌ Error en Railway</h2><p>No se pudo conectar a la base de datos.</p><pre>" . htmlspecialchars($e->getMessage()) . "</pre>");
    } else {
        die("<h2>❌ Error local</h2><p>MySQL no está activo o la base de datos 'crm_aduanas' no existe.</p><pre>" . htmlspecialchars($e->getMessage()) . "</pre>");
    }
}
?>