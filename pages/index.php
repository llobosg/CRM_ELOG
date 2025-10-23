<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CRM Forwarder ELOG</title>
    <!-- Estilos -->
    <link rel="stylesheet" href="styles.css" />
    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="assets/fontawesome7/css/all.min.css" />
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" /> -->
    <!-- Estilo adicional para el body -->
    <style>
        body {
            margin: 0;
            padding: 0;
            zoom: 0.9; /* Reducción del 10% general (opcional, puedes quitarlo si ya está en styles.css) */
        }
    </style>
</head>
<body>

    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    // ... resto del código ...
    require_once 'includes/auth_check.php';

    // === PASO 1: Validar la página solicitada ===
    $page = $_GET['page'] ?? 'dashboard'; // Página por defecto
    $validPages = [
        'agentes',
        'aplicacion_costos',
        'cartaservicios',
        'comerciales',
        'commoditys',
        'conceptos',
        'contactos',
        'incoterm',
        'lugares',
        'medios_transporte',
        'operacion',
        'cartaservicios',
        'trafico',
        'proveedor_pnac',
        'dashboard',
        'prospectos'];
    $safePage = in_array($page, $validPages) ? $page : 'dashboard';

    // === PASO 2: Construir la ruta del archivo ===
    $file = "pages/{$safePage}.php";

    // === PASO 3: Verificar que el archivo exista ===
    if (!file_exists($file)) {
        http_response_code(500);
        echo "<h2 style='color: #cc0000; text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>
                ❌ Error: No se encontró el archivo</h2>
              <p style='text-align: center; font-family: Arial, sans-serif; color: #555;'>
                El archivo <strong>$file</strong> no existe.<br>
                Asegúrate de que esté creado en la carpeta <strong>pages/</strong>
              </p>";
        exit;
    }
    ?>

    <!-- === MENÚ PRINCIPAL === -->
    <?php
    if (file_exists('includes/menu.php')) {
        include 'includes/menu.php';
    } else {
        echo '<div style="height: 70px; background: #3a4f63;"></div>'; // Espacio si no hay menú
    }
    ?>

    <!-- === CONTENIDO PRINCIPAL === -->
    <!-- Margen lateral del 10% en ambos lados -->
    <main style="
        padding: 0;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1%;
        min-height: calc(100vh - 150px);
    ">
        <?php include $file; ?>
    </main>

    <!-- === FOOTER === -->
    <?php
    if (file_exists('includes/footer.php')) {
        include 'includes/footer.php';
    }
    ?>

    <!-- === SCRIPTS === -->
    <script src="assets/js/script.js"></script>

    <script>
        function toggleDarkMode() {
            const body = document.body;
            body.classList.toggle('dark-mode');

            // Guardar preferencia
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
            } else {
                localStorage.setItem('darkMode', 'disabled');
            }
        }

        // Cargar preferencia al iniciar
        document.addEventListener('DOMContentLoaded', () => {
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.body.classList.add('dark-mode');
            }
        });
        </script>

        <style>
        /* === MODO OSCURO === */
        .dark-mode {
            background: #121212 !important;
            color: #e0e0e0 !important;
        }

        .dark-mode main {
            background: #1e1e1e;
            color: #e0e0e0;
        }

        .dark-mode .card,
        .dark-mode .login-container,
        .dark-mode .table-container table,
        .dark-mode .dashboard-widget {
            background: #2c2c2c !important;
            color: #e0e0e0 !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
        }

        .dark-mode h2, .dark-mode h3, .dark-mode label, .dark-mode th {
            color: #ffffff !important;
        }

        .dark-mode input, 
        .dark-mode select, 
        .dark-mode textarea,
        .dark-mode button,
        .dark-mode a {
            background: #333 !important;
            color: #fff !important;
            border-color: #555 !important;
        }

        .dark-mode .table-container th {
            background: #1a568c !important;
        }

        .dark-mode .close-modal {
            color: #ccc !important;
        }

        .dark-mode .menu {
            background: #1a1a1a !important;
        }

        .dark-mode .menu a {
            color: #bbb !important;
        }

        .dark-mode .menu a:hover {
            background: #333 !important;
        }
    </style>

</body>
</html>