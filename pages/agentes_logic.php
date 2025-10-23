<?php
// pages/agentes_logic.php

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php?page=prospectos');
    exit;
}

// === Guardar nuevo agente ===
if ($_POST && isset($_POST['save'])) {
    try {
        $razon_social = trim($_POST['razon_social'] ?? '');
        if (empty($razon_social)) {
            throw new Exception('La Razón Social es obligatoria');
        }
        
        $ig_agente = trim($_POST['ig_agente'] ?? '');
        $rut_empresa = trim($_POST['rut_empresa'] ?? '');
        $fono_empresa = trim($_POST['fono_empresa'] ?? '');
        $pais = trim($_POST['pais'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $region = trim($_POST['region'] ?? '');
        $comuna = trim($_POST['comuna'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO agentes (razon_social, ig_agente, rut_empresa, fono_empresa, pais, direccion, region, comuna) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$razon_social, $ig_agente, $rut_empresa, $fono_empresa, $pais, $direccion, $region, $comuna]);
        
        header("Location: index.php?page=agentes&exito=" . urlencode('✅ Agente guardado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=agentes&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Actualizar agente ===
if ($_POST && isset($_POST['update'])) {
    try {
        $id = $_POST['agentes_id'] ?? null;
        $razon_social = trim($_POST['razon_social'] ?? '');
        if (!$id || empty($razon_social)) {
            throw new Exception('Datos inválidos para la actualización');
        }
        
        $ig_agente = trim($_POST['ig_agente'] ?? '');
        $rut_empresa = trim($_POST['rut_empresa'] ?? '');
        $fono_empresa = trim($_POST['fono_empresa'] ?? '');
        $pais = trim($_POST['pais'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $region = trim($_POST['region'] ?? '');
        $comuna = trim($_POST['comuna'] ?? '');

        $stmt = $pdo->prepare("UPDATE agentes SET razon_social = ?, ig_agente = ?, rut_empresa = ?, fono_empresa = ?, pais = ?, direccion = ?, region = ?, comuna = ? WHERE id_ppl = ?");
        $stmt->execute([$razon_social, $ig_agente, $rut_empresa, $fono_empresa, $pais, $direccion, $region, $comuna, $id]);
        
        header("Location: index.php?page=agentes&exito=" . urlencode('✅ Agente actualizado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=agentes&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Eliminar agente ===
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM agentes WHERE id_ppl = ?");
        $stmt->execute([$_GET['delete']]);
        header('Location: index.php?page=agentes&exito=' . urlencode('✅ Agente eliminado'));
        exit;
    } catch (Exception $e) {
        header('Location: index.php?page=agentes&error=' . urlencode('❌ No se puede eliminar: registro en uso'));
        exit;
    }
}