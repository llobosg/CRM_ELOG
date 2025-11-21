<?php
// auth.php — versión segura para Railway

// 1. Forzar HTTPS en Railway
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// 2. Configurar sesión SOLO si no está activa
if (session_status() === PHP_SESSION_NONE) {
    // Estas directivas DEBEN ir antes de session_start()
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    session_start();
}

require_once __DIR__ . '/config.php';

// Resto del código igual...
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
    $stmt = $pdo->prepare("
        SELECT id_usr, email, rol, password 
        FROM usuarios 
        WHERE email = ? OR nombre = ?
        LIMIT 1
    ");
    $stmt->execute([$usuario_input, $usuario_input]);
    $usuario = $stmt->fetch();

    if ($usuario && $password_input === $usuario['password']) {
        $_SESSION['user'] = $usuario['email'];
        $_SESSION['user_id'] = (int)$usuario['id_usr'];
        $_SESSION['rol'] = $usuario['rol'];

        error_log("✅ [AUTH.PHP] Login exitoso. user_id = " . $_SESSION['user_id']);
        error_log("🔑 [AUTH.PHP] PHPSESSID = " . session_id());

        header('Location: index.php?page=prospectos');
        exit;
    } else {
        error_log("❌ [AUTH.PHP] Credenciales inválidas para: " . $usuario_input);
        header('Location: login.php?error=1');
        exit;
    }
} catch (Exception $e) {
    error_log("💥 [AUTH.PHP] Error: " . $e->getMessage());
    header('Location: login.php?error=1');
    exit;
}
?>