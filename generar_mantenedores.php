<?php
// generar_mantenedores.php - Versión estable y compatible

// Configuración de los mantenedores
$mantenedores = [
    'agentes' => [
        'tabla' => 'agentes',
        'campo_principal' => 'razon_social',
        'id_campo' => 'id_ppl',
        'nombre_amigable' => 'Agente',
        'icono' => 'fa-building',
        'campos_extra' => ['ig_agente', 'rut_empresa', 'fono_empresa', 'pais', 'direccion', 'region', 'comuna']
    ],
    'aplicacion_costos' => [
        'tabla' => 'aplicacion_costos',
        'campo_principal' => 'aplica',
        'id_campo' => 'id',
        'nombre_amigable' => 'Aplicación Costos',
        'icono' => 'fa-calculator'
    ],
    'comerciales' => [
        'tabla' => 'comerciales',
        'campo_principal' => 'nombre',
        'campo_secundario' => 'apellido',
        'id_campo' => 'id_comercial',
        'nombre_amigable' => 'Comercial',
        'icono' => 'fa-user-tie'
    ],
    'trafico' => [
        'tabla' => 'trafico',
        'campo_principal' => 'trafico',
        'id_campo' => 'id_trafico',
        'nombre_amigable' => 'Tráfico',
        'icono' => 'fa-truck'
    ],
    'commoditys' => [
        'tabla' => 'commodity',
        'campo_principal' => 'commodity',
        'id_campo' => 'id_comm',
        'nombre_amigable' => 'Commodity',
        'icono' => 'fa-boxes'
    ],
    'conceptos' => [
        'tabla' => 'conceptos',
        'campo_principal' => 'concepto',
        'id_campo' => 'id',
        'nombre_amigable' => 'Concepto',
        'icono' => 'fa-tag'
    ],
    'lugares' => [
        'tabla' => 'lugares',
        'campo_principal' => 'detalle_lugar',
        'id_campo' => 'id_lugar',
        'nombre_amigable' => 'Lugar',
        'icono' => 'fa-map-marker-alt',
        'campos_extra' => ['medio_transporte', 'pais_lugar']
    ],
    'medios_transporte' => [
        'tabla' => 'medios_transporte',
        'campo_principal' => 'nombre',
        'id_campo' => 'id',
        'nombre_amigable' => 'Medio de Transporte',
        'icono' => 'fa-ship',
        'campos_extra' => ['pais', 'ciudad', 'tipo', 'medio', 'codigo_iata']
    ],
    'proveedores_nac' => [
        'tabla' => 'proveedores_nac',
        'campo_principal' => 'nombre_pnac',
        'id_campo' => 'id_pnac',
        'nombre_amigable' => 'Proveedor Nacional',
        'icono' => 'fa-handshake'
    ]
];

// Verificar que el directorio pages exista y sea escribible
$pagesDir = __DIR__ . '/pages';
if (!is_dir($pagesDir)) {
    if (!mkdir($pagesDir, 0755, true)) {
        die("❌ Error: No se pudo crear el directorio pages/");
    }
}

if (!is_writable($pagesDir)) {
    die("❌ Error: El directorio pages/ no es escribible.");
}

// Función para generar el archivo de lógica
function generarLogicFile($config, $nombre_archivo, $ruta) {
    $tabla = $config['tabla'];
    $campo_principal = $config['campo_principal'];
    $id_campo = $config['id_campo'];
    $nombre_amigable = $config['nombre_amigable'];
    $campo_secundario = $config['campo_secundario'] ?? null;
    $campos_extra = $config['campos_extra'] ?? [];

    // Construir lista de campos
    $campos = [$campo_principal];
    if ($campo_secundario) $campos[] = $campo_secundario;
    $campos = array_merge($campos, $campos_extra);
    
    $placeholders = str_repeat('?, ', count($campos) - 1) . '?';
    $set_clause = implode(' = ?, ', $campos) . ' = ?';

    $content = "<?php\n";
    $content .= "// pages/{$nombre_archivo}_logic.php\n\n";
    $content .= "if (!isset(\$_SESSION['rol']) || \$_SESSION['rol'] !== 'admin') {\n";
    $content .= "    header('Location: index.php?page=prospectos');\n";
    $content .= "    exit;\n";
    $content .= "}\n\n";

    // Guardar
    $content .= "// === Guardar nuevo registro ===\n";
    $content .= "if (\$_POST && isset(\$_POST['save'])) {\n";
    $content .= "    try {\n";
    $content .= "        \$campo_principal = trim(\$_POST['{$campo_principal}'] ?? '');\n";
    $content .= "        if (empty(\$campo_principal)) {\n";
    $content .= "            throw new Exception('El campo principal es obligatorio');\n";
    $content .= "        }\n\n";
    $content .= "        \$valores = [];\n";
    $content .= "        \$valores[] = \$campo_principal;\n";
    
    if ($campo_secundario) {
        $content .= "        \$valores[] = trim(\$_POST['{$campo_secundario}'] ?? '');\n";
    }
    foreach ($campos_extra as $campo) {
        $content .= "        \$valores[] = trim(\$_POST['{$campo}'] ?? '');\n";
    }
    
    $content .= "\n        \$stmt = \$pdo->prepare(\"INSERT INTO {$tabla} (" . implode(', ', $campos) . ") VALUES ({$placeholders})\");\n";
    $content .= "        \$stmt->execute(\$valores);\n";
    $content .= "        header(\"Location: index.php?page={$nombre_archivo}&exito=\" . urlencode('✅ {$nombre_amigable} guardado'));\n";
    $content .= "        exit;\n";
    $content .= "    } catch (Exception \$e) {\n";
    $content .= "        header(\"Location: index.php?page={$nombre_archivo}&error=\" . urlencode('❌ Error: ' . \$e->getMessage()));\n";
    $content .= "        exit;\n";
    $content .= "    }\n";
    $content .= "}\n\n";

    // Actualizar
    $content .= "// === Actualizar registro ===\n";
    $content .= "if (\$_POST && isset(\$_POST['update'])) {\n";
    $content .= "    try {\n";
    $content .= "        \$id = \$_POST['{$nombre_archivo}_id'] ?? null;\n";
    $content .= "        \$campo_principal = trim(\$_POST['{$campo_principal}'] ?? '');\n";
    $content .= "        if (!\$id || empty(\$campo_principal)) {\n";
    $content .= "            throw new Exception('Datos inválidos para la actualización');\n";
    $content .= "        }\n\n";
    $content .= "        \$valores = [];\n";
    $content .= "        \$valores[] = \$campo_principal;\n";
    
    if ($campo_secundario) {
        $content .= "        \$valores[] = trim(\$_POST['{$campo_secundario}'] ?? '');\n";
    }
    foreach ($campos_extra as $campo) {
        $content .= "        \$valores[] = trim(\$_POST['{$campo}'] ?? '');\n";
    }
    $content .= "        \$valores[] = \$id;\n\n";
    
    $content .= "        \$stmt = \$pdo->prepare(\"UPDATE {$tabla} SET {$set_clause} WHERE {$id_campo} = ?\");\n";
    $content .= "        \$stmt->execute(\$valores);\n";
    $content .= "        header(\"Location: index.php?page={$nombre_archivo}&exito=\" . urlencode('✅ {$nombre_amigable} actualizado'));\n";
    $content .= "        exit;\n";
    $content .= "    } catch (Exception \$e) {\n";
    $content .= "        header(\"Location: index.php?page={$nombre_archivo}&error=\" . urlencode('❌ Error: ' . \$e->getMessage()));\n";
    $content .= "        exit;\n";
    $content .= "    }\n";
    $content .= "}\n\n";

    // Eliminar
    $content .= "// === Eliminar registro ===\n";
    $content .= "if (isset(\$_GET['delete'])) {\n";
    $content .= "    try {\n";
    $content .= "        \$stmt = \$pdo->prepare(\"DELETE FROM {$tabla} WHERE {$id_campo} = ?\");\n";
    $content .= "        \$stmt->execute([\$_GET['delete']]);\n";
    $content .= "        header('Location: index.php?page={$nombre_archivo}&exito=' . urlencode('✅ {$nombre_amigable} eliminado'));\n";
    $content .= "        exit;\n";
    $content .= "    } catch (Exception \$e) {\n";
    $content .= "        header('Location: index.php?page={$nombre_archivo}&error=' . urlencode('❌ No se puede eliminar: registro en uso'));\n";
    $content .= "        exit;\n";
    $content .= "    }\n";
    $content .= "}\n";

    file_put_contents($ruta, $content);
}

// Función para generar el archivo de vista
function generarViewFile($config, $nombre_archivo, $ruta) {
    $tabla = $config['tabla'];
    $campo_principal = $config['campo_principal'];
    $id_campo = $config['id_campo'];
    $nombre_amigable = $config['nombre_amigable'];
    $icono = $config['icono'];
    $campo_secundario = $config['campo_secundario'] ?? null;
    $campos_extra = $config['campos_extra'] ?? [];

    // Campos para SELECT
    $select_campos = [$id_campo, $campo_principal];
    if ($campo_secundario) $select_campos[] = $campo_secundario;
    $select = implode(', ', $select_campos);

    // Inputs
    $inputs = "";
    $label = ucfirst(str_replace('_', ' ', $campo_principal));
    $inputs .= "            <div class=\"form-group\">\n                <label>{$label}</label>\n                <input type=\"text\" name=\"{$campo_principal}\" id=\"{$campo_principal}\" required style=\"width: 100%;\" />\n            </div>\n";

    if ($campo_secundario) {
        $label2 = ucfirst(str_replace('_', ' ', $campo_secundario));
        $inputs .= "            <div class=\"form-group\">\n                <label>{$label2}</label>\n                <input type=\"text\" name=\"{$campo_secundario}\" id=\"{$campo_secundario}\" required style=\"width: 100%;\" />\n            </div>\n";
    }

    foreach ($campos_extra as $campo) {
        $label_extra = ucfirst(str_replace('_', ' ', $campo));
        $inputs .= "            <div class=\"form-group\">\n                <label>{$label_extra}</label>\n                <input type=\"text\" name=\"{$campo}\" id=\"{$campo}\" style=\"width: 100%;\" />\n            </div>\n";
    }

    // Display en tabla
    if ($campo_secundario) {
        $display = "\$r['{$campo_principal}'] . ' ' . \$r['{$campo_secundario}']";
    } else {
        $display = "\$r['{$campo_principal}']";
    }

    // Parámetros para JS
    $js_params = "id";
    $js_vals = "campo1";
    if ($campo_secundario) {
        $js_params .= ", campo1, campo2";
        $js_vals = "campo1, campo2";
    }
    foreach ($campos_extra as $campo) {
        $js_params .= ", extra_{$campo}";
        $js_vals .= ", extra_{$campo}";
    }

    // Asignaciones JS
    $js_assign = "    document.getElementById('{$campo_principal}').value = campo1;\n";
    if ($campo_secundario) {
        $js_assign .= "    document.getElementById('{$campo_secundario}').value = campo2;\n";
    }
    foreach ($campos_extra as $campo) {
        $js_assign .= "    document.getElementById('{$campo}').value = extra_{$campo};\n";
    }

    // Reset JS
    $js_reset = "    document.getElementById('{$campo_principal}').value = '';\n";
    if ($campo_secundario) {
        $js_reset .= "    document.getElementById('{$campo_secundario}').value = '';\n";
    }
    foreach ($campos_extra as $campo) {
        $js_reset .= "    document.getElementById('{$campo}').value = '';\n";
    }

    $content = "<?php\n";
    $content .= "\$registros = \$pdo->query(\"SELECT {$select} FROM {$tabla} ORDER BY {$campo_principal}\")->fetchAll();\n";
    $content .= "?>\n\n";
    $content .= "<h2 class=\"section-title\"><i class=\"fas {$icono}\"></i> {$nombre_amigable}s</h2>\n\n";
    $content .= "<div class=\"card\">\n";
    $content .= "    <form method=\"POST\" id=\"form-{$nombre_archivo}\">\n";
    $content .= "        <div style=\"display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.2rem;\">\n";
    $content .= $inputs;
    $content .= "        </div>\n";
    $content .= "        <button type=\"submit\" id=\"btn-guardar-{$nombre_archivo}\" name=\"save\" class=\"btn-primary\">Guardar</button>\n";
    $content .= "        <button type=\"button\" id=\"btn-cancelar-edicion\" class=\"btn-secondary\" style=\"margin-left: 0.8rem; display: none;\" onclick=\"cancelarEdicion()\">\n";
    $content .= "            <i class=\"fas fa-times\"></i> Cancelar Edición\n";
    $content .= "        </button>\n";
    $content .= "        <button type=\"button\" class=\"btn-secondary\" style=\"margin-left: 0.8rem;\" onclick=\"location.href='index.php?page=dashboard';\">\n";
    $content .= "            <i class=\"fas fa-arrow-left\"></i> Volver\n";
    $content .= "        </button>\n";
    $content .= "    </form>\n";
    $content .= "</div>\n\n";
    $content .= "<div class=\"card\" style=\"margin-top: 1.5rem;\">\n";
    $content .= "    <h3 style=\"margin: 0 0 1rem 0; font-size: 1.05rem; color: #3a4f63;\">Registros existentes</h3>\n";
    $content .= "    <div class=\"table-container\">\n";
    $content .= "        <table style=\"width: 100%; border-collapse: collapse; font-size: 0.92rem;\">\n";
    $content .= "            <thead>\n";
    $content .= "                <tr style=\"background: #f0f0f0;\">\n";
    $content .= "                    <th style=\"padding: 0.6rem; text-align: left;\">{$nombre_amigable}</th>\n";
    $content .= "                    <th style=\"padding: 0.6rem; text-align: center; width: 10%;\">Acciones</th>\n";
    $content .= "                </tr>\n";
    $content .= "            </thead>\n";
    $content .= "            <tbody>\n";
    $content .= "                <?php foreach (\$registros as \$r): ?>\n";
    $content .= "                <tr style=\"border-bottom: 1px solid #eee;\">\n";
    $content .= "                    <td style=\"padding: 0.6rem;"><?= htmlspecialchars({$display}) ?></td>\n";
    $content .= "                    <td style=\"padding: 0.6rem; text-align: center;\">\n";

    // onclick para editar
    $onclick_params = "\$r['{$id_campo}']";
    if ($campo_secundario) {
        $onclick_params .= ", '" . addslashes("\$r['{$campo_principal}']") . "', '" . addslashes("\$r['{$campo_secundario}']") . "'";
    } else {
        $onclick_params .= ", '" . addslashes("\$r['{$campo_principal}']") . "'";
    }
    foreach ($campos_extra as $campo) {
        $onclick_params .= ", '" . addslashes("\$r['{$campo}']") . "'";
    }

    $content .= "                        <a href=\"#\" \n";
    $content .= "                           onclick=\"editar{$nombre_archivo}({$onclick_params})\"\n";
    $content .= "                           class=\"btn-edit\" \n";
    $content .= "                           style=\"padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none; margin-right: 0.5rem;\">\n";
    $content .= "                            ✏️\n";
    $content .= "                        </a>\n";
    $content .= "                        <a href=\"index.php?page={$nombre_archivo}&delete=<?= \$r['{$id_campo}'] ?>\" \n";
    $content .= "                           class=\"btn-delete\" \n";
    $content .= "                           style=\"padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none;\"\n";
    $content .= "                           onclick=\"return confirm('¿Eliminar este registro?')\">\n";
    $content .= "                            🗑️\n";
    $content .= "                        </a>\n";
    $content .= "                    </td>\n";
    $content .= "                </tr>\n";
    $content .= "                <?php endforeach; ?>\n";
    $content .= "            </tbody>\n";
    $content .= "        </table>\n";
    $content .= "    </div>\n";
    $content .= "</div>\n\n";
    $content .= "<script>\n";
    $content .= "let {$nombre_archivo}EdicionId = null;\n\n";
    $content .= "function editar{$nombre_archivo}({$js_params}) {\n";
    $content .= $js_assign;
    $content .= "    {$nombre_archivo}EdicionId = id;\n";
    $content .= "    document.getElementById('btn-guardar-{$nombre_archivo}').textContent = 'Actualizar';\n";
    $content .= "    document.getElementById('btn-guardar-{$nombre_archivo}').name = 'update';\n";
    $content .= "    document.getElementById('btn-cancelar-edicion').style.display = 'inline-block';\n";
    $content .= "    \n";
    $content .= "    let hidden = document.getElementById('{$nombre_archivo}_id_hidden');\n";
    $content .= "    if (!hidden) {\n";
    $content .= "        hidden = document.createElement('input');\n";
    $content .= "        hidden.type = 'hidden';\n";
    $content .= "        hidden.id = '{$nombre_archivo}_id_hidden';\n";
    $content .= "        hidden.name = '{$nombre_archivo}_id';\n";
    $content .= "        document.getElementById('form-{$nombre_archivo}').appendChild(hidden);\n";
    $content .= "    }\n";
    $content .= "    hidden.value = id;\n";
    $content .= "}\n\n";
    $content .= "function cancelarEdicion() {\n";
    $content .= $js_reset;
    $content .= "    document.getElementById('btn-guardar-{$nombre_archivo}').textContent = 'Guardar';\n";
    $content .= "    document.getElementById('btn-guardar-{$nombre_archivo}').name = 'save';\n";
    $content .= "    document.getElementById('btn-cancelar-edicion').style.display = 'none';\n";
    $content .= "    const hidden = document.getElementById('{$nombre_archivo}_id_hidden');\n";
    $content .= "    if (hidden) hidden.remove();\n";
    $content .= "    {$nombre_archivo}EdicionId = null;\n";
    $content .= "    warning('Edición cancelada');\n";
    $content .= "}\n";
    $content .= "</script>\n";

    file_put_contents($ruta, $content);
}

// Generar archivos
$errores = [];
foreach ($mantenedores as $nombre_archivo => $config) {
    try {
        $logicPath = "{$pagesDir}/{$nombre_archivo}_logic.php";
        $viewPath = "{$pagesDir}/{$nombre_archivo}_view.php";
        
        generarLogicFile($config, $nombre_archivo, $logicPath);
        generarViewFile($config, $nombre_archivo, $viewPath);
    } catch (Exception $e) {
        $errores[] = "Error en {$nombre_archivo}: " . $e->getMessage();
    }
}

if (!empty($errores)) {
    echo "<h3>⚠️ Errores:</h3><ul>";
    foreach ($errores as $error) {
        echo "<li>{$error}</li>";
    }
    echo "</ul>";
} else {
    echo "<h2>✅ ¡Mantenedores generados con éxito!</h2>";
    echo "<p>Archivos creados en la carpeta <code>pages/</code>.</p>";
    echo "<ul>";
    foreach (array_keys($mantenedores) as $m) {
        echo "<li>{$m}_logic.php y {$m}_view.php</li>";
    }
    echo "</ul>";
}
?>