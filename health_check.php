<?php
try {
    $pdo = new PDO(
        "mysql:host={$_ENV['MYSQLHOST']};port={$_ENV['MYSQLPORT']};dbname={$_ENV['MYSQLDATABASE']}",
        $_ENV['MYSQLUSER'],
        $_ENV['MYSQLPASSWORD']
    );
    echo "✅ Conexión exitosa!";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>