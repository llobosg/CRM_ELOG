<?php
// auth.php — con LOGS EXHAUSTIVOS
error_log("🚀 [AUTH.PHP] === INICIO DE AUTH.PHP ===");

// Ver si la sesión ya está activa
if (session_status() === PHP_SESSION_ACTIVE) {
    error_log("⚠️ [AUTH.PHP] Sesión YA ACTIVA antes de session_start()");
} else {
    error_log("ℹ️ [AUTH.PHP] Sesión NO activa. Iniciando...");
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $_SERVER['HTTPS'] = 'on';
    }
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    session_start();
    error_log("✅ [AUTH.PHP] Sesión iniciada. ID: " . session_id());
}

// Ver contenido de $_SESSION antes de login
error_log("🔍 [AUTH.PHP] Contenido de \$_SESSION ANTES del login: " . print_r($_SESSION, true));

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("❌ [AUTH.PHP] Método no POST. Redirigiendo a login.php");
    header('Location: login.php');
    exit;
}

$usuario_input = trim($_POST['nombre'] ?? '');
$password_input = $_POST['password'] ?? '';

if (empty($usuario_input) || empty($password_input)) {
    error_log("❌ [AUTH.PHP] Campos vacíos. Redirigiendo con error.");
    header('Location: login.php?error=1');
    exit;
}

require_once __DIR__ . '/config.php';

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
        // Guardar en sesión
        $_SESSION['user'] = $usuario['email'] ?: $usuario['nombre'] ?: 'Usuario Sin Nombre';
        $_SESSION['user_id'] = (int)$usuario['id_usr'];
        $_SESSION['rol'] = $usuario['rol'];
        error_log("✅ [AUTH.PHP] Login exitoso.");
        error_log("   → user: " . $_SESSION['user']);
        error_log("   → user_id: " . $_SESSION['user_id']);
        error_log("   → rol: " . $_SESSION['rol']);
        error_log("   → PHPSESSID: " . session_id());

        // Forzar escritura
        session_write_close();
        error_log("💾 [AUTH.PHP] Sesión escrita y cerrada.");

        // Redirigir
        error_log("➡️ [AUTH.PHP] Redirigiendo a index.php?page=prospectos");
        header('Location: index.php?page=prospectos');
        exit;
    } else {
        error_log("❌ [AUTH.PHP] Credenciales inválidas para: " . $usuario_input);
        header('Location: login.php?error=1');
        exit;
    }
} catch (Exception $e) {
    error_log("💥 [AUTH.PHP] Excepción: " . $e->getMessage());
    header('Location: login.php?error=1');
    exit;
}
?>