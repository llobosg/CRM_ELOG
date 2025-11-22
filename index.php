<?php
// index.php — Punto de entrada con sesiones persistentes en Redis

// Soporte para HTTPS en Railway (proxy inverso)
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// === Configurar Redis como manejador de sesiones (¡ANTES de session_start!) ===
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

// Validación global de sesión
if (empty($_SESSION['user_id']) || empty($_SESSION['user'])) {
    $currentFile = basename($_SERVER['SCRIPT_NAME']);
    if ($currentFile !== 'login.php') {
        header('Location: /login.php');
        exit;
    }
}

require_once __DIR__ . '/includes/security_headers.php';
require_once __DIR__ . '/config.php';

// Páginas válidas
$validPages = [
    'agentes', 'aplicacion_costos', 'comerciales', 'commoditys', 'conceptos',
    'contactos', 'incoterm', 'lugares', 'medios_transporte', 'operacion',
    'proveedor_pnac', 'tservicios', 'trafico', 'dashboard', 'prospectos',
    'ficha_cliente', 'facturacion'
];

$page = $_GET['page'] ?? 'dashboard';
$safePage = in_array($page, $validPages) ? $page : 'dashboard';

// Protección por rol
$paginas_admin_finanzas = ['ficha_cliente', 'facturacion'];
if (in_array($safePage, $paginas_admin_finanzas)) {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin_finanzas') {
        header('Location: ?page=dashboard');
        exit;
    }
}

// Lógica de mantenedores
$mantenedores_con_logica = [
    'agentes', 'aplicacion_costos', 'comerciales', 'commoditys', 'conceptos',
    'contactos', 'incoterm', 'lugares', 'medios_transporte', 'operacion',
    'proveedor_pnac', 'tservicios', 'trafico'
];

if (in_array($safePage, $mantenedores_con_logica)) {
    $nombre_archivo = ($safePage === 'tservicios') ? 'servicios' : $safePage;
    $logicFile = __DIR__ . "/pages/{$nombre_archivo}_logic.php";
    if (file_exists($logicFile)) {
        require_once $logicFile;
    }
}

// Lógica de prospectos
if ($safePage === 'prospectos' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/pages/prospectos_logic.php';
}

// Lógica de ficha cliente
if ($safePage === 'ficha_cliente' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/pages/ficha_cliente_logic.php';
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CRM Forwarder - ELOG v.1.0</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="assets/fontawesome7/css/all.min.css" />
    <style>
        body {
            margin: 0;
            padding: 0;
            zoom: 0.9;
        }
    </style>
</head>
<body>

<?php
if (file_exists(__DIR__ . '/includes/menu.php')) {
    include __DIR__ . '/includes/menu.php';
} else {
    echo '<div style="height: 70px; background: #3a4f63;"></div>';
}
?>

<main style="padding: 0; max-width: 1400px; margin: 0 auto; padding: 0 1%; min-height: calc(100vh - 150px);">
    <?php
    if (in_array($safePage, $mantenedores_con_logica)) {
        $nombre_archivo = ($safePage === 'tservicios') ? 'servicios' : $safePage;
        $viewFile = __DIR__ . "/pages/{$nombre_archivo}_view.php";
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "<p style='color: #cc0000; text-align: center; padding: 2rem;'>Vista no encontrada</p>";
        }
    } else {
        $file = __DIR__ . "/pages/{$safePage}.php";
        if (file_exists($file)) {
            include $file;
        } else {
            echo "<p style='color: #cc0000; text-align: center; padding: 2rem;'>Página no encontrada</p>";
        }
    }
    ?>
</main>

<?php
if (file_exists(__DIR__ . '/includes/footer.php')) {
    include __DIR__ . '/includes/footer.php';
}
?>

<!-- Toast notifications -->
<div id="toast" class="toast" style="display:none;">
    <i class="fas fa-info-circle"></i> 
    <span id="toast-message">Mensaje</span>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const toast = document.getElementById('toast');
    const msgElement = document.getElementById('toast-message');

    if (!toast || !msgElement) return;

    function mostrarNotificacion(mensaje, tipo = 'info') {
        msgElement.textContent = mensaje;
        toast.className = 'toast ' + (tipo === 'exito' ? 'success' : tipo === 'error' ? 'error' : 'info');
        toast.style.display = 'flex';
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => { toast.style.display = 'none'; }, 400);
        }, 5000);
    }

    if (urlParams.has('exito')) {
        mostrarNotificacion(decodeURIComponent(urlParams.get('exito')), 'exito');
        history.replaceState({}, document.title, window.location.pathname + '?page=' + (urlParams.get('page') || 'dashboard'));
    }
    if (urlParams.has('error')) {
        mostrarNotificacion(decodeURIComponent(urlParams.get('error')), 'error');
        history.replaceState({}, document.title, window.location.pathname + '?page=' + (urlParams.get('page') || 'dashboard'));
    }

    window.exito = (msg) => mostrarNotificacion(msg, 'exito');
    window.error = (msg) => mostrarNotificacion(msg, 'error');
});
</script>

<?php if ($safePage === 'prospectos'): ?>
<script>
const USER_ROLE = '<?php echo htmlspecialchars($_SESSION['rol'] ?? 'comercial'); ?>';
console.log('✅ Rol cargado:', USER_ROLE);
</script>
<?php endif; ?>

</body>
</html>