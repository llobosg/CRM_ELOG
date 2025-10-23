<?php
// pages/incoterm_logic.php

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php?page=prospectos');
    exit;
}

// === Guardar nuevo incoterm ===
if ($_POST && isset($_POST['save'])) {
    try {
        $incoterm = strtoupper(trim($_POST['incoterm'] ?? ''));
        $detalle = trim($_POST['detalle'] ?? '');
        if (empty($incoterm)) {
            throw new Exception('El campo Incoterm es obligatorio');
        }
        $incoterm = substr($incoterm, 0, 10); // Máximo 10 caracteres

        $stmt = $pdo->prepare("INSERT INTO incoterm (incoterm, detalle) VALUES (?, ?)");
        $stmt->execute([$incoterm, $detalle]);
        
        header("Location: index.php?page=incoterm&exito=" . urlencode('✅ Incoterm guardado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=incoterm&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Actualizar incoterm ===
if ($_POST && isset($_POST['update'])) {
    try {
        $id = $_POST['incoterm_id'] ?? null;
        $incoterm = strtoupper(trim($_POST['incoterm'] ?? ''));
        $detalle = trim($_POST['detalle'] ?? '');
        if (!$id || empty($incoterm)) {
            throw new Exception('Datos inválidos para la actualización');
        }
        $incoterm = substr($incoterm, 0, 10);

        $stmt = $pdo->prepare("UPDATE incoterm SET incoterm = ?, detalle = ? WHERE id_incoterm = ?");
        $stmt->execute([$incoterm, $detalle, $id]);
        
        header("Location: index.php?page=incoterm&exito=" . urlencode('✅ Incoterm actualizado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=incoterm&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Eliminar incoterm ===
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM incoterm WHERE id_incoterm = ?");
        $stmt->execute([$_GET['delete']]);
        header('Location: index.php?page=incoterm&exito=' . urlencode('✅ Incoterm eliminado'));
        exit;
    } catch (Exception $e) {
        header('Location: index.php?page=incoterm&error=' . urlencode('❌ No se puede eliminar: registro en uso'));
        exit;
    }
}