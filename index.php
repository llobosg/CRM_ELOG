<?php
    // === 1. INICIO ABSOLUTO: sin salida, sin HTML ===
    require_once __DIR__ . '/includes/security_headers.php';
    require_once __DIR__ . '/includes/auth_check.php';
    require_once __DIR__ . '/config.php';

    // === 2. LISTA DE PÁGINAS VÁLIDAS ===
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
        'tservicios',        // ← Mantenedor de tipos de servicios (cartaservicios)
        'trafico',
        'dashboard',
        'prospectos'
    ];

    $page = $_GET['page'] ?? 'dashboard';
    $safePage = in_array($page, $validPages) ? $page : 'dashboard';

    // === 3. LISTA DE MANTENEDORES CON LÓGICA (CRUD) ===
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
        'tservicios',        // ← Debe coincidir con el nombre en el menú
        'trafico'
    ];

    // === 4. PROCESAR LÓGICA DE MANTENEDORES (ANTES DE CUALQUIER SALIDA HTML) ===
    if (in_array($safePage, $mantenedores_con_logica)) {
        // Mapeo especial: tservicios → servicios (archivos reales)
        $nombre_archivo = ($safePage === 'tservicios') ? 'servicios' : $safePage;
        $logicFile = __DIR__ . "/pages/{$nombre_archivo}_logic.php";
        if (file_exists($logicFile)) {
            require_once $logicFile;
            // Si hay redirección (header + exit), el script ya terminó aquí
        }
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

<main style="
    padding: 0;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1%;
    min-height: calc(100vh - 150px);
">
    <?php
    // === RENDERIZAR VISTA ===
    if (in_array($safePage, $mantenedores_con_logica)) {
        // Mapeo especial: tservicios → servicios (archivos reales)
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

    // Mostrar notificación desde URL
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

    // Exponer globalmente
    window.mostrarNotificacion = mostrarNotificacion;
    window.exito = (msg) => mostrarNotificacion(msg, 'exito');
    window.error = (msg) => mostrarNotificacion(msg, 'error');
    window.warning = (msg) => mostrarNotificacion(msg, 'warning');
    window.info = (msg) => mostrarNotificacion(msg, 'info');
});
</script>

</body>
</html>