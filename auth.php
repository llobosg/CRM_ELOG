<?php
session_start();

if (empty($_POST['nombre']) || empty($_POST['password'])) {
    header('Location: login.php?error=1');
    exit;
}

$username = $_POST['nombre'];
$password = $_POST['password'];

// 🔍 LOG MÍNIMO (solo para diagnóstico)
error_log("🔍 Login intentado con: '$username'");

require_once __DIR__ . '/config.php';

error_log("🔍 Consulta: SELECT nombre, rol, password FROM usuarios WHERE nombre = '$username'");
$stmt = $pdo->prepare("SELECT nombre, rol, password FROM usuarios WHERE nombre = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();
error_log("🔍 Resultado SQL: " . json_encode($user));

// 🔍 LOG DEL RESULTADO
error_log("🔍 Usuario encontrado: " . ($user ? $user['nombre'] : 'NINGUNO'));

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user'] = $user['nombre'];
    $_SESSION['rol'] = $user['rol'];
    header('Location: index.php');
    exit;
} else {
    header('Location: login.php?error=1');
    exit;
}
?>