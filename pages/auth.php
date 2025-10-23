<?php
session_start();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

require_once __DIR__ . '/../config.php';

$stmt = $pdo->prepare("SELECT username, rol FROM usuarios WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user'] = $user['username'];
    $_SESSION['rol'] = $user['rol']; // ✅ ¡AHORA SÍ!
    header('Location: ../index.php');
    exit;
} else {
    header('Location: ../login.php?error=1');
    exit;
}
?>