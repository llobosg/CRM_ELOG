<?php
// debug_db.php - Versión segura

// Verificar variables de entorno
$required_vars = ['MYSQLHOST', 'MYSQLPORT', 'MYSQLDATABASE', 'MYSQLUSER', 'MYSQLPASSWORD'];
$missing_vars = [];

foreach ($required_vars as $var) {
    if (empty($_ENV[$var])) {
        $missing_vars[] = $var;
    }
}

if (!empty($missing_vars)) {
    echo "❌ Variables de entorno faltantes: " . implode(', ', $missing_vars) . "\n";
    echo "Variables disponibles:\n";
    print_r(array_keys($_ENV));
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['MYSQLHOST']};port={$_ENV['MYSQLPORT']};dbname={$_ENV['MYSQLDATABASE']};charset=utf8mb4",
        $_ENV['MYSQLUSER'],
        $_ENV['MYSQLPASSWORD'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ Conexión exitosa!\n\n";
    
    // Verificar tabla prospectos
    $stmt = $pdo->query("SHOW TABLES LIKE 'prospectos'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Tabla 'prospectos' no existe\n";
        echo "Tablas disponibles:\n";
        $tables = $pdo->query("SHOW TABLES");
        while ($table = $tables->fetch()) {
            echo "- " . $table[0] . "\n";
        }
        exit;
    }
    
    // Describir tabla
    echo "📋 Estructura de tabla 'prospectos':\n";
    $stmt = $pdo->query("DESCRIBE prospectos");
    while ($row = $stmt->fetch()) {
        echo "- {$row['Field']} ({$row['Type']})" . ($row['Null'] === 'NO' ? ' NOT NULL' : '') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
}
?>