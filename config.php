<?php
// config.php - Compatible con XAMPP local y Railway.app (MySQL/PostgreSQL)

$isRailway = !empty($_ENV['RAILWAY_ENVIRONMENT']) || !empty($_ENV['DATABASE_URL']) || !empty($_ENV['MYSQLHOST']);

if ($isRailway) {
    // === Opción 1: Usar DATABASE_URL (formato estándar en Railway) ===
    if (!empty($_ENV['DATABASE_URL'])) {
        $db_host = 'shuttle.proxy.rlwy.net';
        $db_port = 48498; // ← ¡Puerto real!
        $db_name = 'railway';
        $db_user = 'root';
        $db_password = 'oTLbkFsCazCViKmMFncPSBSHfoYjOnhA';
    }
    // === Opción 2: Fallback a variables de MySQL clásicas ===
    elseif (!empty($_ENV['MYSQLHOST'])) {
        $db_host = $_ENV['MYSQLHOST'];
        $db_port = $_ENV['MYSQLPORT'] ?? 3306;
        $db_name = $_ENV['MYSQLDATABASE'] ?? 'railway';
        $db_user = $_ENV['MYSQLUSER'] ?? 'root';
        $db_password = $_ENV['MYSQLPASSWORD'] ?? '';
    }
    // === No se encontraron variables válidas ===
    else {
        die("Error crítico: Railway no inyectó DATABASE_URL ni MYSQLHOST. Verifica que el servicio de base de datos esté vinculado al proyecto.");
    }
} else {
    // === Entorno local (XAMPP/MAMP) ===
    $db_host = '127.0.0.1';
    $db_port = 3306;
    $db_name = 'crm_aduanas'; // ← ajusta si tu BD local se llama distinto
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
        die("Error: No se pudo conectar a la base de datos en Railway.<br>Detalles: " . htmlspecialchars($e->getMessage()));
    } else {
        die("Error de conexión local: " . htmlspecialchars($e->getMessage()));
    }
}
?>