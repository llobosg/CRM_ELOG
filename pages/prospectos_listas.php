<?php
$rol = $_SESSION['rol'] ?? '';
if ($rol !== 'admin' && $rol !== 'comercial' && $rol !== 'pricing') {
    http_response_code(403);
    exit('Acceso denegado.');
}

$prospectos = [];

if (php_sapi_name() !== 'cli') {
    try {
        require_once __DIR__ . '/../config.php';
        // 🔍 Log 1: Verificar conexión
        error_log("✅ [DEBUG] Conexión a DB establecida.");

        // Contar total de prospectos
        $countStmt = $pdo->query("SELECT COUNT(*) as total FROM prospectos");
        $totalProspectos = $countStmt->fetch()['total'];
        error_log("📊 [DEBUG] Total de prospectos en tabla: " . $totalProspectos);

        $stmt = $pdo->prepare("
            SELECT
                p.id_ppl,
                p.fecha_alta AS fecha,
                p.razon_social AS cliente_nombre,
                p.operacion,
                p.tipo_oper,
                p.concatenado,
                COALESCE(cost_data.total_costo, 0) AS total_costo,
                COALESCE(cost_data.total_venta, 0) AS total_venta,
                COALESCE(gasto_data.gdc, 0) AS gdc,
                COALESCE(gasto_data.gdv, 0) AS gdv,
                -- NUEVO: Traer el primer servicio asociado al prospecto
                COALESCE(serv_data.primer_servicio, '-') AS servicio
            FROM prospectos p
            LEFT JOIN (
                SELECT 
                    s.id_ppl,
                    SUM(cs.costo * cs.qty) AS total_costo,
                    SUM(cs.tarifa * cs.qty) AS total_venta
                FROM servicios s
                LEFT JOIN costos_servicios cs ON s.id_srvc = cs.id_servicio
                GROUP BY s.id_ppl
            ) cost_data ON p.id_ppl = cost_data.id_ppl
            LEFT JOIN (
                SELECT 
                    s.id_ppl,
                    SUM(CASE WHEN gld.tipo = 'Costo' THEN gld.monto ELSE 0 END) AS gdc,
                    SUM(CASE WHEN gld.tipo = 'Ventas' THEN gld.monto ELSE 0 END) AS gdv
                FROM servicios s
                LEFT JOIN gastos_locales_detalle gld ON s.id_srvc = gld.id_servicio
                GROUP BY s.id_ppl
            ) gasto_data ON p.id_ppl = gasto_data.id_ppl
            -- NUEVO JOIN: Para obtener el primer servicio
            LEFT JOIN (
                SELECT 
                    s.id_prospect,
                    MIN(s.servicio) as primer_servicio,  -- ✅ Corrección clave
                    MIN(s.id_srvc) as min_id
                FROM servicios s
                GROUP BY s.id_prospect
            ) serv_data ON p.id_ppl = serv_data.id_prospect
            ORDER BY p.fecha_alta DESC
            LIMIT 10
        ");
        $stmt->execute();
        $prospectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 🔍 Log 2: Resultados obtenidos
        error_log("🔍 [DEBUG] Número de prospectos devueltos por la consulta: " . count($prospectos));
        if (!empty($prospectos)) {
            error_log("📋 [DEBUG] Primer prospecto: " . json_encode($prospectos[0]));
        }
    } catch (Exception $e) {
        error_log("❌ [ERROR] Exception al cargar prospectos: " . $e->getMessage());
        $prospectos = [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Lista de Prospectos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="/styles.css">
    <script>
        function confirmarEliminacion(id, concatenado) {
            const mensaje = `¿Está seguro de eliminar el prospecto ${concatenado}?\n\n⚠️ Esta acción eliminará todos los Gastos, Ventas, Costos y Servicios asociados.`;
            if (confirm(mensaje)) {
                window.location.href = '/pages/prospectos_logic.php?action=eliminar&id=' + id;
            }
        }
    </script>
</head>
<body>
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem;">
        <h2 style="font-weight: bold; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-file-alt"></i> Lista de Prospectos
        </h2>
        <!-- ✅ Redirección correcta al sistema de rutas -->
        <a href="/?page=prospectos" class="btn-primary" style="text-decoration: none; padding: 0.4rem 0.8rem; display: flex; align-items: center; gap: 0.4rem;">
            <i class="fas fa-plus"></i> Crear Prospecto
        </a>
    </div>

    <div class="card">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 30%;">Cliente</th>      <!-- +10% -->
                        <th style="width: 9%;">Fecha</th>        <!-- +10% -->
                        <th style="width: 11%;">Concatenado</th>
                        <th style="width: 16%;">Servicio</th>
                        <th style="width: 7%;">Costo</th>
                        <th style="width: 7%;">Venta</th>
                        <th style="width: 7%;">GDC</th>
                        <th style="width: 7%;">GDV</th>
                        <th style="width: 7%;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($prospectos)): ?>
                    <tr>
                        <td colspan="12" style="text-align: center;">No hay prospectos registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($prospectos as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['cliente_nombre'] ?? '–') ?></td>
                        <td><?= htmlspecialchars($p['fecha'] ? date('d-m-Y', strtotime($p['fecha'])) : '–') ?></td>
                        <td><?= htmlspecialchars($p['concatenado'] ?? '–') ?></td>
                        <td><?= htmlspecialchars($p['servicio'] ?? '–') ?></td>
                        <td><?= number_format((float)$p['total_costo'], 0, ',', '.') ?></td>
                        <td><?= number_format((float)$p['total_venta'], 0, ',', '.') ?></td>
                        <td><?= number_format((float)$p['gdc'], 0, ',', '.') ?></td>
                        <td><?= number_format((float)$p['gdv'], 0, ',', '.') ?></td>
                        <td style="text-align: center; padding: 0.4rem;">
                            <!-- ✏️ Editar -->
                            <!-- En prospectos_listas.php -->
                            <button type="button" 
                                    onclick="window.location.href='/?page=prospectos&buscar_concatenado=<?= urlencode($p['concatenado']) ?>'"
                                    title="Editar"
                                    style="background: none; border: none; font-size: 1.2rem; cursor: pointer; padding: 0;">
                                ✏️
                            </button>
                            <!-- 🗑️ Eliminar -->
                            <button type="button"
                                    onclick="confirmarEliminacion(<?= $p['id_ppl'] ?>, '<?= addslashes($p['concatenado']) ?>')"
                                    title="Eliminar"
                                    style="background: none; border: none; font-size: 1.2rem; cursor: pointer; padding: 0;">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>