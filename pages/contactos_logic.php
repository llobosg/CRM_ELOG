<?php
// pages/contactos_logic.php

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php?page=prospectos');
    exit;
}

// === Guardar nuevo contacto ===
if ($_POST && isset($_POST['save'])) {
    try {
        $nom_contacto = trim($_POST['nom_contacto'] ?? '');
        if (empty($nom_contacto)) {
            throw new Exception('El campo Nombre de Contacto es obligatorio');
        }
        
        $fono_contacto = trim($_POST['fono_contacto'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $cargo = trim($_POST['cargo'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO contactos (nom_contacto, fono_contacto, email, cargo, tipo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nom_contacto, $fono_contacto, $email, $cargo, $tipo]);
        
        header("Location: index.php?page=contactos&exito=" . urlencode('✅ Contacto guardado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=contactos&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Actualizar contacto ===
if ($_POST && isset($_POST['update'])) {
    try {
        $id = $_POST['contactos_id'] ?? null;
        $nom_contacto = trim($_POST['nom_contacto'] ?? '');
        if (!$id || empty($nom_contacto)) {
            throw new Exception('Datos inválidos para la actualización');
        }
        
        $fono_contacto = trim($_POST['fono_contacto'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $cargo = trim($_POST['cargo'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');

        $stmt = $pdo->prepare("UPDATE contactos SET nom_contacto = ?, fono_contacto = ?, email = ?, cargo = ?, tipo = ? WHERE id_ctco = ?");
        $stmt->execute([$nom_contacto, $fono_contacto, $email, $cargo, $tipo, $id]);
        
        header("Location: index.php?page=contactos&exito=" . urlencode('✅ Contacto actualizado'));
        exit;
    } catch (Exception $e) {
        header("Location: index.php?page=contactos&error=" . urlencode('❌ Error: ' . $e->getMessage()));
        exit;
    }
}

// === Eliminar contacto ===
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM contactos WHERE id_ctco = ?");
        $stmt->execute([$_GET['delete']]);
        header('Location: index.php?page=contactos&exito=' . urlencode('✅ Contacto eliminado'));
        exit;
    } catch (Exception $e) {
        header('Location: index.php?page=contactos&error=' . urlencode('❌ No se puede eliminar: registro en uso'));
        exit;
    }
}