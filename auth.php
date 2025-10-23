<?php
// auth.php
session_start();

error_log("🔍 [LOGIN] Método: " . $_SERVER['REQUEST_METHOD']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = $_POST['nombre'] ?? '';
$password = $_POST['password'] ?? '';

error_log("👤 Usuario recibido: '$username'");
error_log("🔑 Contraseña recibida: " . ($password ? '***' : 'VACÍA'));

if (empty($username) || empty($password)) {
    error_log("❌ Campos vacíos");
    header('Location: login.php?error=1');
    exit;
}

require_once __DIR__ . '/config.php';

$stmt = $pdo->prepare("SELECT nombre, rol, password FROM usuarios WHERE nombre = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user) {
    error_log("✅ Usuario encontrado en BD: nombre='{$user['nombre']}', password='{$user['password']}'");
} else {
    error_log("❌ Usuario NO encontrado en BD para: '$username'");
}

// COMPARACIÓN DIRECTA (texto plano)
if ($user && $password === $user['password']) {
    error_log("🎉 ¡Credenciales correctas! Iniciando sesión para: {$user['nombre']}");
    $_SESSION['user'] = $user['nombre'];
    $_SESSION['rol'] = $user['rol'];
    header('Location: index.php');
    exit;
} else {
    error_log("❌ Credenciales incorrectas o usuario no existe");
    header('Location: login.php?error=1');
    exit;
}
?>