<?php
    // auth.php - Versión corregida para Railway + diagnóstico
    session_start();

    // 🔍 Diagnóstico inicial
    error_log("🚀 auth.php versión: OCT2025-FINAL");
    error_log("📡 \$_POST recibido: " . print_r($_POST, true));
    error_log("🌐 REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A'));

    // ✅ Redirigir si no hay datos POST (más confiable que verificar REQUEST_METHOD)
    if (empty($_POST['nombre']) || empty($_POST['password'])) {
        error_log("❌ Acceso directo o formulario vacío → redirigiendo a login.php");
        header('Location: login.php?error=1');
        exit;
    }

    $username = $_POST['nombre'];
    $password = $_POST['password'];

    error_log("👤 Usuario recibido: '$username'");
    error_log("🔑 Contraseña recibida: " . ($password ? '***' : 'VACÍA'));

    require_once __DIR__ . '/config.php';

    try {
        $stmt = $pdo->prepare("SELECT nombre, rol, password FROM usuarios WHERE nombre = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            error_log("✅ Usuario encontrado en BD: nombre='{$user['nombre']}', password='{$user['password']}'");
        } else {
            error_log("❌ Usuario NO encontrado en BD para: '$username'");
        }

        // 🔑 Comparación en texto plano (solo para QA)
        if ($user && $password === $user['password']) {
            error_log("🎉 ¡Credenciales correctas! Iniciando sesión para: {$user['nombre']}");
            $_SESSION['user'] = $user['nombre'];
            $_SESSION['rol'] = $user['rol'];
            header('Location: index.php');
            exit;
        } else {
            error_log("❌ Credenciales incorrectas");
            header('Location: login.php?error=1');
            exit;
        }
    } catch (Exception $e) {
        error_log("💥 Error en consulta SQL: " . $e->getMessage());
        header('Location: login.php?error=1');
        exit;
    }
?>