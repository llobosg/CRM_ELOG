<?php
// auth.php — con sesiones persistentes en Redis (Railway)

// Soporte para HTTPS
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// === Configurar Redis (¡ANTES de session_start!) ===
if (isset($_ENV['REDIS_URL'])) {
    $redisUrl = parse_url($_ENV['REDIS_URL']);
    $redisHost = $redisUrl['host'];
    $redisPort = $redisUrl['port'];
    $redisPassword = $redisUrl['pass'] ?? null;

    ini_set('session.save_handler', 'redis');
    ini_set('session.save_path', "tcp://{$redisHost}:{$redisPort}");
    if ($redisPassword) {
        ini_set('redis.session.auth', $redisPassword);
    }
    ini_set('session.name', 'CRMSESSID');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
}

session_start();

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /login.php');
    exit;
}

$usuario_input = trim($_POST['nombre'] ?? '');
$password_input = $_POST['password'] ?? '';

if (empty($usuario_input) || empty($password_input)) {
    header('Location: /login.php?error=1');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id_usr, email, nombre, rol, password 
        FROM usuarios 
        WHERE email = ? OR nombre = ?
        LIMIT 1
    ");
    $stmt->execute([$usuario_input, $usuario_input]);
    $usuario = $stmt->fetch();

    if ($usuario && $password_input === $usuario['password']) {
        $_SESSION['user'] = $usuario['email'] ?: $usuario['nombre'] ?: 'Usuario';
        $_SESSION['user_id'] = (int)$usuario['id_usr'];
        $_SESSION['rol'] = $usuario['rol'];

        session_write_close();
        header('Location: /index.php?page=prospectos');
        exit;
    } else {
        header('Location: /login.php?error=1');
        exit;
    }
} catch (Exception $e) {
    error_log("Error en auth.php: " . $e->getMessage());
    header('Location: /login.php?error=1');
    exit;
}
?>