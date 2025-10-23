<?php
// pages/commoditys_logic.php

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php?page=prospectos');
    exit;
}

// === Guardar nuevo commodity ===
if ($_POST && isset($_POST['save'])) {
    try {
        $commodity = trim($_POST['commodity'] ?? '');
        if (empty($commodity)) {
            throw new Exception('El campo Commodity es obligatorio');
        }
        
        $cod_comm = trim($_POST['cod_comm'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO commodity (commodity, cod_comm) VALUES (?, ?)");
        $stmt->execute([$commodity, $cod_comm]);
        
        header("Location: index.php?page=commoditys&exito=" . urlencode('✅ Commodity guardado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=commoditys&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Actualizar commodity ===
if ($_POST && isset($_POST['update'])) {
    try {
        $id = $_POST['commoditys_id'] ?? null;
        $commodity = trim($_POST['commodity'] ?? '');
        if (!$id || empty($commodity)) {
            throw new Exception('Datos inválidos para la actualización');
        }
        
        $cod_comm = trim($_POST['cod_comm'] ?? '');

        $stmt = $pdo->prepare("UPDATE commodity SET commodity = ?, cod_comm = ? WHERE id_comm = ?");
        $stmt->execute([$commodity, $cod_comm, $id]);
        
        header("Location: index.php?page=commoditys&exito=" . urlencode('✅ Commodity actualizado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=commoditys&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Eliminar commodity ===
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM commodity WHERE id_comm = ?");
        $stmt->execute([$_GET['delete']]);
        header('Location: index.php?page=commoditys&exito=' . urlencode('✅ Commodity eliminado'));
        exit;
    } catch (Exception $e) {
        header('Location: index.php?page=commoditys&error=' . urlencode('❌ No se puede eliminar: registro en uso'));
        exit;
    }
}