<?php
// pages/proveedor_pnac_logic.php

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php?page=prospectos');
    exit;
}

// === Guardar nuevo proveedor nacional ===
if ($_POST && isset($_POST['save'])) {
    try {
        $nombre_pnac = trim($_POST['nombre_pnac'] ?? '');
        if (empty($nombre_pnac)) {
            throw new Exception('El campo Nombre es obligatorio');
        }
        
        $cod_pnac = trim($_POST['cod_pnac'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO proveedor_pnac (nombre_pnac, cod_pnac) VALUES (?, ?)");
        $stmt->execute([$nombre_pnac, $cod_pnac]);
        
        header("Location: index.php?page=proveedor_pnac&exito=" . urlencode('✅ Proveedor Nacional guardado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=proveedor_pnac&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Actualizar proveedor nacional ===
if ($_POST && isset($_POST['update'])) {
    try {
        $id = $_POST['proveedor_pnac_id'] ?? null;
        $nombre_pnac = trim($_POST['nombre_pnac'] ?? '');
        if (!$id || empty($nombre_pnac)) {
            throw new Exception('Datos inválidos para la actualización');
        }
        
        $cod_pnac = trim($_POST['cod_pnac'] ?? '');

        $stmt = $pdo->prepare("UPDATE proveedor_pnac SET nombre_pnac = ?, cod_pnac = ? WHERE id_pnac = ?");
        $stmt->execute([$nombre_pnac, $cod_pnac, $id]);
        
        header("Location: index.php?page=proveedor_pnac&exito=" . urlencode('✅ Proveedor Nacional actualizado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=proveedor_pnac&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Eliminar proveedor nacional ===
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM proveedor_pnac WHERE id_pnac = ?");
        $stmt->execute([$_GET['delete']]);
        header('Location: index.php?page=proveedor_pnac&exito=' . urlencode('✅ Proveedor Nacional eliminado'));
        exit;
    } catch (Exception $e) {
        header('Location: index.php?page=proveedor_pnac&error=' . urlencode('❌ No se puede eliminar: registro en uso'));
        exit;
    }
}