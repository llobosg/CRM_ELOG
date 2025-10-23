<?php
// pages/cartaservicios_logic.php

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php?page=prospectos');
    exit;
}

// === Guardar nuevo servicio ===
if ($_POST && isset($_POST['save'])) {
    try {
        $servicio_cs = trim($_POST['servicio_cs'] ?? '');
        $sigla_cs = trim($_POST['sigla_cs'] ?? '');
        if (empty($servicio_cs) || empty($sigla_cs)) {
            throw new Exception('Los campos Servicio y Sigla son obligatorios');
        }
        $sigla_cs = strtoupper(substr($sigla_cs, 0, 3)); // Máximo 3 caracteres, mayúsculas

        $trafico = trim($_POST['trafico'] ?? '');
        $subtrafico = trim($_POST['subtrafico'] ?? '');
        $tipo_cs = trim($_POST['tipo_cs'] ?? '');
        $detalle_cs = trim($_POST['detalle_cs'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO cartaservicios (servicio_cs, sigla_cs, trafico, subtrafico, tipo_cs, detalle_cs) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$servicio_cs, $sigla_cs, $trafico, $subtrafico, $tipo_cs, $detalle_cs]);
        
        header("Location: index.php?page=tservicios&exito=" . urlencode('✅ Servicio guardado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=tservicios&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Actualizar servicio ===
if ($_POST && isset($_POST['update'])) {
    try {
        $id = $_POST['servicios_id'] ?? null;
        $servicio_cs = trim($_POST['servicio_cs'] ?? '');
        $sigla_cs = trim($_POST['sigla_cs'] ?? '');
        if (!$id || empty($servicio_cs) || empty($sigla_cs)) {
            throw new Exception('Datos inválidos para la actualización');
        }
        $sigla_cs = strtoupper(substr($sigla_cs, 0, 3));

        $trafico = trim($_POST['trafico'] ?? '');
        $subtrafico = trim($_POST['subtrafico'] ?? '');
        $tipo_cs = trim($_POST['tipo_cs'] ?? '');
        $detalle_cs = trim($_POST['detalle_cs'] ?? '');

        $stmt = $pdo->prepare("UPDATE cartaservicios SET servicio_cs = ?, sigla_cs = ?, trafico = ?, subtrafico = ?, tipo_cs = ?, detalle_cs = ? WHERE id_cs = ?");
        $stmt->execute([$servicio_cs, $sigla_cs, $trafico, $subtrafico, $tipo_cs, $detalle_cs, $id]);
        
        header("Location: index.php?page=tservicios&exito=" . urlencode('✅ Servicio actualizado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=tservicios&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Eliminar servicio ===
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM cartaservicios WHERE id_cs = ?");
        $stmt->execute([$_GET['delete']]);
        header('Location: index.php?page=tservicios&exito=' . urlencode('✅ Servicio eliminado'));
        exit;
    } catch (Exception $e) {
        header('Location: index.php?page=tservicios&error=' . urlencode('❌ No se puede eliminar: registro en uso'));
        exit;
    }
}