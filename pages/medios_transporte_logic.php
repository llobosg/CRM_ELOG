<?php
// pages/medios_transporte_logic.php

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php?page=prospectos');
    exit;
}

// === Guardar nuevo medio de transporte ===
if ($_POST && isset($_POST['save'])) {
    try {
        $nombre = trim($_POST['nombre'] ?? '');
        if (empty($nombre)) {
            throw new Exception('El campo Nombre es obligatorio');
        }
        
        $pais = trim($_POST['pais'] ?? '');
        $ciudad = trim($_POST['ciudad'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $medio = trim($_POST['medio'] ?? '');
        $codigo_iata = trim($_POST['codigo_iata'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO medios_transporte (nombre, pais, ciudad, tipo, medio, codigo_iata) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $pais, $ciudad, $tipo, $medio, $codigo_iata]);
        
        header("Location: index.php?page=medios_transporte&exito=" . urlencode('✅ Medio de Transporte guardado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=medios_transporte&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Actualizar medio de transporte ===
if ($_POST && isset($_POST['update'])) {
    try {
        $id = $_POST['medios_transporte_id'] ?? null;
        $nombre = trim($_POST['nombre'] ?? '');
        if (!$id || empty($nombre)) {
            throw new Exception('Datos inválidos para la actualización');
        }
        
        $pais = trim($_POST['pais'] ?? '');
        $ciudad = trim($_POST['ciudad'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $medio = trim($_POST['medio'] ?? '');
        $codigo_iata = trim($_POST['codigo_iata'] ?? '');

        $stmt = $pdo->prepare("UPDATE medios_transporte SET nombre = ?, pais = ?, ciudad = ?, tipo = ?, medio = ?, codigo_iata = ? WHERE id = ?");
        $stmt->execute([$nombre, $pais, $ciudad, $tipo, $medio, $codigo_iata, $id]);
        
        header("Location: index.php?page=medios_transporte&exito=" . urlencode('✅ Medio de Transporte actualizado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=medios_transporte&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Eliminar medio de transporte ===
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM medios_transporte WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        header('Location: index.php?page=medios_transporte&exito=' . urlencode('✅ Medio de Transporte eliminado'));
        exit;
    } catch (Exception $e) {
        header('Location: index.php?page=medios_transporte&error=' . urlencode('❌ No se puede eliminar: registro en uso'));
        exit;
    }
}