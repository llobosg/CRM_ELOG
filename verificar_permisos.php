<?php
$dir = __DIR__ . '/pages';

echo "<h2>Verificación de permisos para la carpeta 'pages'</h2>";

// 1. ¿Existe la carpeta?
if (!is_dir($dir)) {
    echo "❌ La carpeta <code>pages</code> NO existe.<br>";
    echo "👉 PHP intentará crearla...<br>";
    
    if (mkdir($dir, 0755, true)) {
        echo "✅ Carpeta creada con éxito.<br>";
    } else {
        echo "❌ ERROR: No se pudo crear la carpeta. Revisa permisos del directorio padre.<br>";
        exit;
    }
}

// 2. ¿Es escribible?
if (is_writable($dir)) {
    echo "✅ La carpeta <code>pages</code> es ESCRIBIBLE por PHP.<br>";
} else {
    echo "❌ La carpeta <code>pages</code> NO es escribible.<br>";
    
    // Mostrar permisos actuales
    $perms = fileperms($dir);
    echo "Permisos actuales: " . substr(sprintf('%o', $perms), -4) . "<br>";
    
    // Usuario que ejecuta PHP
    echo "Usuario de PHP: " . get_current_user() . "<br>";
    if (function_exists('posix_getuid')) {
        echo "UID de PHP: " . posix_getuid() . "<br>";
    }
}

// 3. Prueba de escritura real
$testFile = $dir . '/permiso_test.txt';
if (file_put_contents($testFile, "Prueba de escritura - " . date('Y-m-d H:i:s'))) {
    echo "✅ Prueba de escritura: EXITOSA.<br>";
    unlink($testFile); // eliminar archivo de prueba
} else {
    echo "❌ Prueba de escritura: FALLIDA.<br>";
}
?>