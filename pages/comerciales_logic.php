<?php
// pages/comerciales_logic.php

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php?page=prospectos');
    exit;
}

// === Guardar nuevo comercial ===
if ($_POST && isset($_POST['save'])) {
    try {
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        if (empty($nombre) || empty($apellido)) {
            throw new Exception('Nombre y Apellido son obligatorios');
        }
        
        $cargo = trim($_POST['cargo'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO comerciales (nombre, apellido, cargo) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $apellido, $cargo]);
        
        header("Location: index.php?page=comerciales&exito=" . urlencode('✅ Comercial guardado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=comerciales&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Actualizar comercial ===
if ($_POST && isset($_POST['update'])) {
    try {
        $id = $_POST['comerciales_id'] ?? null;
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        if (!$id || empty($nombre) || empty($apellido)) {
            throw new Exception('Datos inválidos para la actualización');
        }
        
        $cargo = trim($_POST['cargo'] ?? '');

        $stmt = $pdo->prepare("UPDATE comerciales SET nombre = ?, apellido = ?, cargo = ? WHERE id_comercial = ?");
        $stmt->execute([$nombre, $apellido, $cargo, $id]);
        
        header("Location: index.php?page=comerciales&exito=" . urlencode('✅ Comercial actualizado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=comerciales&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Eliminar comercial ===
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM comerciales WHERE id_comercial = ?");
        $stmt->execute([$_GET['delete']]);
        header('Location: index.php?page=comerciales&exito=' . urlencode('✅ Comercial eliminado'));
        exit;
    } catch (Exception $e) {
        header('Location: index.php?page=comerciales&error=' . urlencode('❌ No se puede eliminar: registro en uso'));
        exit;
    }
}