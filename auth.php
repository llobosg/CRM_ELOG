<?php
// auth.php — compatible con contraseñas en TEXTO PLANO
session_start();
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$usuario_input = trim($_POST['nombre'] ?? '');
$password_input = $_POST['password'] ?? '';

if (empty($usuario_input) || empty($password_input)) {
    header('Location: login.php?error=1');
    exit;
}

try {
    // Buscar por email o por nombre de usuario
    $stmt = $pdo->prepare("
        SELECT id_usr, email, rol, password 
        FROM usuarios 
        WHERE email = ? OR nombre = ?
        LIMIT 1
    ");
    $stmt->execute([$usuario_input, $usuario_input]);
    $usuario = $stmt->fetch();

    // ✅ Comparación directa (texto plano)
    if ($usuario && $password_input === $usuario['password']) {
        // ✅ Guardar datos en sesión
        $_SESSION['user'] = $usuario['email'];
        $_SESSION['user_id'] = (int)$usuario['id_usr'];
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