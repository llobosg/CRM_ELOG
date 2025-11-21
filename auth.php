<?php
// auth.php
// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    // Configurar cookies para Railway (proxy inverso)
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $_SERVER['HTTPS'] = 'on';
    }
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    session_start();
}

require_once __DIR__ . '/config.php';

// Solo permitir POST
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
    // Buscar por email o nombre de usuario
    $stmt = $pdo->prepare("
        SELECT id_usr, email, rol, password 
        FROM usuarios 
        WHERE email = ? OR nombre = ?
        LIMIT 1
    ");
    $stmt->execute([$usuario_input, $usuario_input]);
    $usuario = $stmt->fetch();

    // Comparación directa (texto plano)
    if ($usuario && $password_input === $usuario['password']) {
        // Guardar sesión
        $_SESSION['user'] = $usuario['email'];
        $_SESSION['user_id'] = (int)$usuario['id_usr'];
        $_SESSION['rol'] = $usuario['rol'];

        // Forzar escritura de sesión (crucial en Railway)
        session_write_close();

        // Redirigir
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