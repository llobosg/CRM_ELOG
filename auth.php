<?php
session_start();
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$password = $_POST['password'] ?? '';

if (!$nombre || !$password) {
    header('Location: login.php?error=1');
    exit;
}

try {
    // Ajusta el nombre de la tabla y columnas según tu esquema
    $stmt = $pdo->prepare("SELECT id_usr, email, rol, password FROM usuarios WHERE email = ? OR nombre = ?");
    $stmt->execute([$nombre, $nombre]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password'])) {
        // ✅ Guarda TODOS los datos necesarios en la sesión
        $_SESSION['user'] = $usuario['email'];
        $_SESSION['user_id'] = (int)$usuario['id_usr']; // ← ¡ESTO ES CLAVE!
        $_SESSION['rol'] = $usuario['rol'];

        header('Location: index.php?page=prospectos');
        exit;
    } else {
        header('Location: login.php?error=1');
        exit;
    }
} catch (Exception $e) {
    error_log("Error en auth.php: " . $e->getMessage());
    header('Location: login.php?error=1');
    exit;
}
?>