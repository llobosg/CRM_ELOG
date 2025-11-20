<?php
// ==============================================
// index.php — PUNTO DE ENTRADA PRINCIPAL
// ==============================================

// Soporte para HTTPS en Railway
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// Configuración de sesión para Railway
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

session_start();

// ✅ LOG DE DIAGNÓSTICO
error_log("📥 [INDEX.PHP] Sesión recibida. user_id = " . ($_SESSION['user_id'] ?? 'NO DEFINIDO'));
error_log("🔑 [INDEX.PHP] PHPSESSID: " . session_id());

// 2. CABECERAS DE SEGURIDAD
require_once __DIR__ . '/includes/security_headers.php';

// 3. VALIDACIÓN GLOBAL DE SESIÓN
if (!isset($_SESSION['user']) || !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 4. CARGA DE CONFIGURACIÓN
require_once __DIR__ . '/config.php';

// 5. LISTA DE PÁGINAS VÁLIDAS
$validPages = [
    'agentes',
    'aplicacion_costos',
    'comerciales',
    'commoditys',
    'conceptos',
    'contactos',
    'incoterm',
    'lugares',
    'medios_transporte',
    'operacion',
    'proveedor_pnac',
    'tservicios',
    'trafico',
    'dashboard',
    'prospectos',
    'ficha_cliente',
    'facturacion'
];

// 6. OBTENER PÁGINA SOLICITADA
$page = $_GET['page'] ?? 'dashboard';
$safePage = in_array($page, $validPages) ? $page : 'dashboard';

// 7. PROTECCIÓN POR ROL: solo admin_finanzas puede acceder a estas páginas
$paginas_admin_finanzas = ['ficha_cliente', 'facturacion'];
if (in_array($safePage, $paginas_admin_finanzas)) {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin_finanzas') {
        header('Location: ?page=dashboard');
        exit;
    }
}

// 8. LISTA DE MANTENEDORES CON LÓGICA
$mantenedores_con_logica = [
    'agentes',
    'aplicacion_costos',
    'comerciales',
    'commoditys',
    'conceptos',
    'contactos',
    'incoterm',
    'lugares',
    'medios_transporte',
    'operacion',
    'proveedor_pnac',
    'tservicios',
    'trafico'
];

// 9. PROCESAR LÓGICA DE MANTENEDORES (ANTES DE CUALQUIER SALIDA HTML)
if (in_array($safePage, $mantenedores_con_logica)) {
    $nombre_archivo = ($safePage === 'tservicios') ? 'servicios' : $safePage;
    $logicFile = __DIR__ . "/pages/{$nombre_archivo}_logic.php";
    if (file_exists($logicFile)) {
        require_once $logicFile;
        // Si hay redirección (header + exit), el script ya terminó aquí
    }
}

// 10. PROCESAR LÓGICA DE PROSPECTOS
if ($safePage === 'prospectos' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/pages/prospectos_logic.php';
    // prospectos_logic.php hace header() + exit → no sigue
}

// 11. PROCESAR LÓGICA DE FICHA CLIENTE
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

<!-- === MENÚ PRINCIPAL === -->
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
            echo "<p style='color: #cc0000; text-align: center; padding: 2rem;'>Vista no encontrada para {$safePage}</p>";
        }
    } else {
        // Páginas normales (dashboard, prospectos, etc.)
        $file = __DIR__ . "/pages/{$safePage}.php";
        if (file_exists($file)) {
            include $file;
        } else {
            echo "<p style='color: #cc0000; text-align: center; padding: 2rem;'>Página no encontrada: {$safePage}</p>";
        }
    }
    ?>
</main>

<?php
if (file_exists(__DIR__ . '/includes/footer.php')) {
    include __DIR__ . '/includes/footer.php';
}
?>

<!-- === TOAST NOTIFICATIONS === -->
<div id="toast" class="toast">
    <i class="fas fa-info-circle"></i> 
    <span id="toast-message">Mensaje</span>
</div>

<!-- === SCRIPT GLOBAL DE NOTIFICACIONES === -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const toast = document.getElementById('toast');
    const msgElement = document.getElementById('toast-message');

    if (!toast || !msgElement) return;

    function mostrarNotificacion(mensaje, tipo = 'info') {
        msgElement.textContent = mensaje;
        toast.className = 'toast';
        let icono = 'fa-info-circle';
        switch (tipo) {
            case 'exito': 
                toast.classList.add('success'); 
                icono = 'fa-check-circle'; 
                break;
            case 'error': 
                toast.classList.add('error'); 
                icono = 'fa-times-circle'; 
                break;
            case 'warning': 
                toast.classList.add('warning'); 
                icono = 'fa-exclamation-triangle'; 
                break;
            default: 
                toast.classList.add('info');
        }
        const iconElement = toast.querySelector('i');
        if (iconElement) iconElement.className = `fas ${icono}`;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 5000);
    }

    if (urlParams.has('exito')) {
        mostrarNotificacion(urlParams.get('exito'), 'exito');
        const cleanUrl = window.location.pathname + '?page=' + (urlParams.get('page') || 'dashboard');
        history.replaceState({}, document.title, cleanUrl);
    }
    if (urlParams.has('error')) {
        mostrarNotificacion(urlParams.get('error'), 'error');
        const cleanUrl = window.location.pathname + '?page=' + (urlParams.get('page') || 'dashboard');
        history.replaceState({}, document.title, cleanUrl);
    }

    window.mostrarNotificacion = mostrarNotificacion;
    window.exito = (msg) => mostrarNotificacion(msg, 'exito');
    window.error = (msg) => mostrarNotificacion(msg, 'error');
    window.warning = (msg) => mostrarNotificacion(msg, 'warning');
    window.info = (msg) => mostrarNotificacion(msg, 'info');
});
</script>

<!-- === SCRIPT ESPECÍFICO PARA PROSPECTOS (solo si es la página prospectos) === -->
<?php if ($safePage === 'prospectos'): ?>
<script>
    const USER_ROLE = '<?php echo htmlspecialchars($_SESSION['rol'] ?? 'comercial'); ?>';
    console.log('✅ Rol cargado:', USER_ROLE);
</script>
<?php endif; ?>

</body>
</html>