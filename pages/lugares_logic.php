<?php
// pages/lugares_logic.php

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php?page=prospectos');
    exit;
}

// === Guardar nuevo lugar ===
if ($_POST && isset($_POST['save'])) {
    try {
        $detalle_lugar = trim($_POST['detalle_lugar'] ?? '');
        if (empty($detalle_lugar)) {
            throw new Exception('El campo Detalle del Lugar es obligatorio');
        }
        
        $medio_transporte = trim($_POST['medio_transporte'] ?? '');
        $pais_lugar = trim($_POST['pais_lugar'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO lugares (detalle_lugar, medio_transporte, pais_lugar) VALUES (?, ?, ?)");
        $stmt->execute([$detalle_lugar, $medio_transporte, $pais_lugar]);
        
        header("Location: index.php?page=lugares&exito=" . urlencode('✅ Lugar guardado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=lugares&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Actualizar lugar ===
if ($_POST && isset($_POST['update'])) {
    try {
        $id = $_POST['lugares_id'] ?? null;
        $detalle_lugar = trim($_POST['detalle_lugar'] ?? '');
        if (!$id || empty($detalle_lugar)) {
            throw new Exception('Datos inválidos para la actualización');
        }
        
        $medio_transporte = trim($_POST['medio_transporte'] ?? '');
        $pais_lugar = trim($_POST['pais_lugar'] ?? '');

        $stmt = $pdo->prepare("UPDATE lugares SET detalle_lugar = ?, medio_transporte = ?, pais_lugar = ? WHERE id_lugar = ?");
        $stmt->execute([$detalle_lugar, $medio_transporte, $pais_lugar, $id]);
        
        header("Location: index.php?page=lugares&exito=" . urlencode('✅ Lugar actualizado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=lugares&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Eliminar lugar ===
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM lugares WHERE id_lugar = ?");
        $stmt->execute([$_GET['delete']]);
        header('Location: index.php?page=lugares&exito=' . urlencode('✅ Lugar eliminado'));
        exit;
    } catch (Exception $e) {
        header('Location: index.php?page=lugares&error=' . urlencode('❌ No se puede eliminar: registro en uso'));
        exit;
    }
}