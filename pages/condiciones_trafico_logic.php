<?php
// pages/condiciones_trafico_logic.php

// Verificar si el usuario es admin (ajusta según sea necesario)
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php?page=dashboard');
    exit;
}

/**
 * Limpia y normaliza el texto de la condición para su almacenamiento seguro.
 * @param string $texto
 * @return string
 */
function limpiarTextoCondicion($texto) {
    if ($texto === null) {
        return '';
    }

    // 1. Asegurar codificación UTF-8
    $texto = mb_convert_encoding($texto, 'UTF-8', 'auto');

    // 2. Eliminar caracteres de control no deseados (excepto \n, \r, \t)
    $texto = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $texto);

    // 3. Normalizar viñetas comunes de Word u otros editores a un símbolo estándar (•)
    // Lista de posibles símbolos de viñeta (puedes ampliarla según sea necesario)
    $viñetasOriginales = [
        "\xE2\x80\xA2", // • Bullet
        "\xE2\x80\xA3", // ‣ Triangular bullet
        "\xE2\x80\xA4", // ․ One dot leader
        "\xE2\x80\xA5", // ‥ Two dot leader
        "\xE2\x80\xA6", // … Horizontal ellipsis (aunque no es viñeta, puede aparecer)
        "\xEF\x82\xA7", //  Black Small Square (copiado de Word)
        "\xEF\x82\xA8", //  White Small Square
        "\xEF\x82\xA9", //  Black Diamond on Black Square
        "\xEF\x82\xAA", //  Check Mark
        "\xEF\x82\xAB", //  Ballot X
        "\xEF\x82\xAC", //  Black Circle for Record
        "\xEF\x82\xAD", //  Black Rectangle
        "\xEF\x82\xAE", //  Black Right-Pointing Triangle
        "\xEF\x82\xAF", //  White Right-Pointing Triangle
        "\xEF\x82\xB0", //  Black Large Circle
        "\xEF\x82\xB1", //  White Large Circle
        "\xEF\x82\xB2", //  Black Medium Left-Pointing Triangle
        "\xEF\x82\xB3", //  Black Medium Right-Pointing Triangle
        "\xEF\x82\xB4", //  Multiplication X
        "\xEF\x82\xB5", //  Heavy Multiplication X
        "\xEF\x82\xB6", //  Saltire
        "\xEF\x82\xB7", //  Bullet (otra variante de )
        // Agregar más símbolos si es necesario
    ];

    $texto = str_replace($viñetasOriginales, '• ', $texto); // Reemplazar todos por "• "

    // 4. Opcional: Convertir otros caracteres problemáticos si aparecen
    // Por ejemplo, guiones largos (—) por guiones cortos (-) o espacios dobles por simples
    $texto = str_replace(["\xE2\x80\x93", "\xE2\x80\x94"], "-", $texto); // – — por -
    $texto = str_replace("\xE2\x80\x99", "'", $texto); // ’ por '

    // 5. Limpiar espacios al inicio y final de líneas y múltiples espacios
    $lineas = explode("\n", $texto);
    $lineasLimpia = array_map(function($linea) {
        return trim($linea); // Elimina espacios al inicio y final de cada línea
    }, $lineas);
    $texto = implode("\n", $lineasLimpia);
    $texto = preg_replace('/\s+/', ' ', $texto); // Reemplaza múltiples espacios/retornos por un espacio (dentro de una línea)

    // 6. Eliminar espacios en blanco al inicio y final del texto completo
    $texto = trim($texto);

    return $texto;
}

// === Guardar nueva condición ===
if ($_POST && isset($_POST['modo']) && $_POST['modo'] === 'crear') {
    try {
        $trafico = trim($_POST['trafico'] ?? '');
        $condicion = $_POST['condicion'] ?? ''; // No lo trims aún, la limpieza lo hará

        if (empty($trafico) || empty($condicion)) {
            throw new Exception('Los campos Tipo de Tráfico y Condición son obligatorios');
        }

        // --- LIMPIEZA DEL TEXTO DE CONDICIÓN ---
        $condicionLimpia = limpiarTextoCondicion($condicion);
        // --- FIN LIMPIEZA ---

        $stmt = $pdo->prepare("INSERT INTO condiciones_trafico (trafico, condicion) VALUES (?, ?)");
        $stmt->execute([$trafico, $condicionLimpia]); // Guardar el texto limpio

        $mensajeExito = '✅ Condición de tráfico guardada';
        header("Location: index.php?page=condiciones_trafico&exito=" . urlencode($mensajeExito));
        exit;
    } catch (Exception $e) {
        $mensajeError = '❌ Error: ' . $e->getMessage();
        header("Location: index.php?page=condiciones_trafico&error=" . urlencode($mensajeError));
        exit;
    }
}

// === Actualizar condición ===
if ($_POST && isset($_POST['modo']) && $_POST['modo'] === 'editar') {
    try {
        $id = (int)($_POST['id'] ?? 0);
        $trafico = trim($_POST['trafico'] ?? '');
        $condicion = $_POST['condicion'] ?? ''; // No lo trims aún, la limpieza lo hará

        if (!$id || empty($trafico) || empty($condicion)) {
            throw new Exception('Datos inválidos para la actualización');
        }

        // --- LIMPIEZA DEL TEXTO DE CONDICIÓN ---
        $condicionLimpia = limpiarTextoCondicion($condicion);
        // --- FIN LIMPIEZA ---

        $stmt = $pdo->prepare("UPDATE condiciones_trafico SET trafico = ?, condicion = ? WHERE id = ?");
        $stmt->execute([$trafico, $condicionLimpia, $id]); // Actualizar con el texto limpio

        $mensajeExito = '✅ Condición de tráfico actualizada';
        header("Location: index.php?page=condiciones_trafico&exito=" . urlencode($mensajeExito));
        exit;
    } catch (Exception $e) {
        $mensajeError = '❌ Error: ' . $e->getMessage();
        header("Location: index.php?page=condiciones_trafico&error=" . urlencode($mensajeError));
        exit;
    }
}

// === Eliminar condición ===
if (isset($_GET['delete'])) {
    try {
        $id = (int)$_GET['delete'];
        if ($id <= 0) {
            throw new Exception('ID inválido');
        }

        $stmt = $pdo->prepare("DELETE FROM condiciones_trafico WHERE id = ?");
        $stmt->execute([$id]);

        $mensajeExito = '✅ Condición de tráfico eliminada';
        header("Location: index.php?page=condiciones_trafico&exito=" . urlencode($mensajeExito));
        exit;
    } catch (Exception $e) {
        // Opcional: Puedes verificar si la FK impide la eliminación y mostrar un mensaje más específico
        $mensajeError = '❌ No se puede eliminar: registro en uso o error interno';
        header("Location: index.php?page=condiciones_trafico&error=" . urlencode($mensajeError));
        exit;
    }
}
?>