<?php
// pages/trafico_logic.php

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php?page=prospectos');
    exit;
}

// === Guardar nuevo tráfico ===
if ($_POST && isset($_POST['save'])) {
    try {
        $trafico = trim($_POST['trafico'] ?? '');
        if (empty($trafico)) {
            throw new Exception('El campo Tráfico es obligatorio');
        }

        $stmt = $pdo->prepare("INSERT INTO trafico (trafico) VALUES (?)");
        $stmt->execute([$trafico]);
        
        header("Location: index.php?page=trafico&exito=" . urlencode('✅ Tráfico guardado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=trafico&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Actualizar tráfico ===
if ($_POST && isset($_POST['update'])) {
    try {
        $id = $_POST['trafico_id'] ?? null;
        $trafico = trim($_POST['trafico'] ?? '');
        if (!$id || empty($trafico)) {
            throw new Exception('Datos inválidos para la actualización');
        }

        $stmt = $pdo->prepare("UPDATE trafico SET trafico = ? WHERE id_trafico = ?");
        $stmt->execute([$trafico, $id]);
        
        header("Location: index.php?page=trafico&exito=" . urlencode('✅ Tráfico actualizado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=trafico&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Eliminar tráfico ===
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM trafico WHERE id_trafico = ?");
        $stmt->execute([$_GET['delete']]);
        header('Location: index.php?page=trafico&exito=' . urlencode('✅ Tráfico eliminado'));
        exit;
    } catch (Exception $e) {
        header('Location: index.php?page=trafico&error=' . urlencode('❌ No se puede eliminar: registro en uso'));
        exit;
    }
}