<?php
// pages/operacion_logic.php

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php?page=prospectos');
    exit;
}

// === Guardar nuevo tipo de operación ===
if ($_POST && isset($_POST['save'])) {
    try {
        $operacion = trim($_POST['operacion'] ?? '');
        if (empty($operacion)) {
            throw new Exception('El campo Operación es obligatorio');
        }
        
        $tipo_oper = trim($_POST['tipo_oper'] ?? '');
        $detalle_tipo_oper = trim($_POST['detalle_tipo_oper'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO operacion (operacion, tipo_oper, detalle_tipo_oper) VALUES (?, ?, ?)");
        $stmt->execute([$operacion, $tipo_oper, $detalle_tipo_oper]);
        
        header("Location: index.php?page=operacion&exito=" . urlencode('✅ Tipo de Operación guardado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=operacion&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Actualizar tipo de operación ===
if ($_POST && isset($_POST['update'])) {
    try {
        $id = $_POST['operacion_id'] ?? null;
        $operacion = trim($_POST['operacion'] ?? '');
        if (!$id || empty($operacion)) {
            throw new Exception('Datos inválidos para la actualización');
        }
        
        $tipo_oper = trim($_POST['tipo_oper'] ?? '');
        $detalle_tipo_oper = trim($_POST['detalle_tipo_oper'] ?? '');

        $stmt = $pdo->prepare("UPDATE operacion SET operacion = ?, tipo_oper = ?, detalle_tipo_oper = ? WHERE id_oper = ?");
        $stmt->execute([$operacion, $tipo_oper, $detalle_tipo_oper, $id]);
        
        header("Location: index.php?page=operacion&exito=" . urlencode('✅ Tipo de Operación actualizado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=operacion&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Eliminar tipo de operación ===
if (isset($_GET['delete'])) {
    try {
        // Verificar si está en uso por algún prospecto
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM prospectos WHERE id_oper = ?");
        $checkStmt->execute([$_GET['delete']]);
        if ($checkStmt->fetchColumn() > 0) {
            throw new Exception('No se puede eliminar: tipo de operación en uso por prospectos');
        }
        
        $stmt = $pdo->prepare("DELETE FROM operacion WHERE id_oper = ?");
        $stmt->execute([$_GET['delete']]);
        header('Location: index.php?page=operacion&exito=' . urlencode('✅ Tipo de Operación eliminado'));
        exit;
    } catch (Exception $e) {
        header('Location: index.php?page=operacion&error=' . urlencode('❌ ' . $e->getMessage()));
        exit;
    }
}