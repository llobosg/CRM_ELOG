<?php
// pages/conceptos_logic.php

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php?page=prospectos');
    exit;
}

// === Guardar nuevo concepto ===
if ($_POST && isset($_POST['save'])) {
    try {
        $concepto = trim($_POST['concepto'] ?? '');
        if (empty($concepto)) {
            throw new Exception('El campo Concepto es obligatorio');
        }

        $stmt = $pdo->prepare("INSERT INTO conceptos_costo (concepto) VALUES (?)");
        $stmt->execute([$concepto]);
        
        header("Location: index.php?page=conceptos&exito=" . urlencode('✅ Concepto guardado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=conceptos&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Actualizar concepto ===
if ($_POST && isset($_POST['update'])) {
    try {
        $id = $_POST['conceptos_id'] ?? null;
        $concepto = trim($_POST['concepto'] ?? '');
        if (!$id || empty($concepto)) {
            throw new Exception('Datos inválidos para la actualización');
        }

        $stmt = $pdo->prepare("UPDATE conceptos_costos SET concepto = ? WHERE id = ?");
        $stmt->execute([$concepto, $id]);
        
        header("Location: index.php?page=conceptos&exito=" . urlencode('✅ Concepto actualizado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=conceptos&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Eliminar concepto ===
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM conceptos_costos WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        header('Location: index.php?page=conceptos&exito=' . urlencode('✅ Concepto eliminado'));
        exit;
    } catch (Exception $e) {
        header('Location: index.php?page=conceptos&error=' . urlencode('❌ No se puede eliminar: registro en uso'));
        exit;
    }
}