<?php
// pages/aplicacion_costos_logic.php

require_once __DIR__ . '/../config.php';

// Verificar rol de administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php?page=dashboard');
    exit;
}

// === Guardar nuevo registro ===
if ($_POST && isset($_POST['save'])) {
    try {
        $aplica = trim($_POST['aplica'] ?? '');
        $medio_transporte = trim($_POST['medio_transporte'] ?? '');

        if (empty($aplica)) {
            throw new Exception('El campo "Aplica" es obligatorio');
        }

        $stmt = $pdo->prepare("INSERT INTO aplicacion_costos (aplica, medio_transporte) VALUES (?, ?)");
        $stmt->execute([$aplica, $medio_transporte]);

        $mensajeExito = '✅ Aplicación de costos guardada';
        header("Location: index.php?page=aplicacion_costos&exito=" . urlencode($mensajeExito));
        exit; // Importante terminar la ejecución después de redirigir
    } catch (Exception $e) {
        $mensajeError = '❌ Error: ' . $e->getMessage();
        header("Location: index.php?page=aplicacion_costos&error=" . urlencode($mensajeError));
        exit; // Importante terminar la ejecución después de redirigir
    }
}

// === Actualizar registro existente ===
if ($_POST && isset($_POST['update'])) {
    try {
        $id = (int)($_POST['aplicacion_costos_id'] ?? 0);
        $aplica = trim($_POST['aplica'] ?? '');
        $medio_transporte = trim($_POST['medio_transporte'] ?? '');

        if (!$id || empty($aplica)) {
            throw new Exception('ID o campo "Aplica" inválido para la actualización');
        }

        $stmt = $pdo->prepare("UPDATE aplicacion_costos SET aplica = ?, medio_transporte = ? WHERE id = ?");
        $stmt->execute([$aplica, $medio_transporte, $id]);

        $mensajeExito = '✅ Aplicación de costos actualizada';
        header("Location: index.php?page=aplicacion_costos&exito=" . urlencode($mensajeExito));
        exit; // Importante terminar la ejecución después de redirigir
    } catch (Exception $e) {
        $mensajeError = '❌ Error: ' . $e->getMessage();
        header("Location: index.php?page=aplicacion_costos&error=" . urlencode($mensajeError));
        exit; // Importante terminar la ejecución después de redirigir
    }
}

// === Eliminar registro ===
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM aplicacion_costos WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $mensajeExito = '✅ Aplicación de costos eliminada';
        header("Location: index.php?page=aplicacion_costos&exito=" . urlencode($mensajeExito));
        exit; // Importante terminar la ejecución después de redirigir
    } catch (Exception $e) {
        $mensajeError = '❌ No se puede eliminar: registro en uso o error interno';
        header("Location: index.php?page=aplicacion_costos&error=" . urlencode($mensajeError));
        exit; // Importante terminar la ejecución después de redirigir
    }
}

// Si no se procesó ninguna acción POST o GET, simplemente continua (index.php se encargará de incluir la vista)
// No se debe incluir la vista aquí, solo la lógica de guardado/eliminación.
?>