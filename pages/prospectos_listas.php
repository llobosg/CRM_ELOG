<?php
require_once '../session_check.php';
require_once __DIR__ . '/../config.php';

$rol = $_SESSION['rol'] ?? '';
if ($rol !== 'admin' && $rol !== 'comercial') {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso denegado.');
}

$prospectos = [];

if (php_sapi_name() !== 'cli') {
    try {
        $pdo = getDBConnection();
        if ($pdo) {
            // Consulta: últimos 10 prospectos con datos resumidos
            $stmt = $pdo->prepare("
                SELECT
                    p.id_ppl,
                    p.fecha_ppl,
                    p.cliente_ppl,
                    p.operacion,
                    p.tipo_oper,
                    p.concatenado,
                    p.servicio,
                    p.trafico,
                    c.nombre_clt AS cliente_nombre,
                    COALESCE(SUM(cost.costo * cost.qty), 0) AS total_costo,
                    COALESCE(SUM(cost.tarifa * cost.qty), 0) AS total_venta,
                    COALESCE(SUM(CASE WHEN g.tipo = 'COSTO' THEN g.monto ELSE 0 END), 0) AS gdc,
                    COALESCE(SUM(CASE WHEN g.tipo = 'VENTAS' THEN g.monto ELSE 0 END), 0) AS gdv
                FROM prospectos p
                LEFT JOIN clientes c ON p.cliente_ppl = c.id_clt
                LEFT JOIN costos cost ON p.id_ppl = cost.id_ppl
                LEFT JOIN gastos_locales g ON p.id_ppl = g.id_ppl
                GROUP BY p.id_ppl
                ORDER BY p.fecha_ppl DESC
                LIMIT 10
            ");
            $stmt->execute();
            $prospectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        error_log("Error al cargar lista de prospectos: " . $e->getMessage());
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
                window.location.href = `/pages/prospectos_logic.php?action=eliminar&id=${id}`;
            }
        }
    </script>
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem;">
        <h2 style="font-weight: bold; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-file-alt"></i> Lista de Prospectos
        </h2>
        <a href="/pages/prospectos.php" class="btn-primary" style="text-decoration: none; padding: 0.4rem 0.8rem; display: flex; align-items: center; gap: 0.4rem;">
            <i class="fas fa-plus"></i> Crear Prospecto
        </a>
    </div>

    <div class="card">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Operación</th>
                        <th>Tipo Oper</th>
                        <th>Concatenado</th>
                        <th>Servicio</th>
                        <th>Tráfico</th>
                        <th>Costo</th>
                        <th>Venta</th>
                        <th>GDC</th>
                        <th>GDV</th>
                        <th>Acción</th>
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
                        <td><?= htmlspecialchars(date('d-m-Y', strtotime($p['fecha_ppl']))) ?></td>
                        <td><?= htmlspecialchars($p['operacion'] ?? '–') ?></td>
                        <td><?= htmlspecialchars($p['tipo_oper'] ?? '–') ?></td>
                        <td><?= htmlspecialchars($p['concatenado'] ?? '–') ?></td>
                        <td><?= htmlspecialchars($p['servicio'] ?? '–') ?></td>
                        <td><?= htmlspecialchars($p['trafico'] ?? '–') ?></td>
                        <td><?= number_format((float)$p['total_costo'], 0, ',', '.') ?></td>
                        <td><?= number_format((float)$p['total_venta'], 0, ',', '.') ?></td>
                        <td><?= number_format((float)$p['gdc'], 0, ',', '.') ?></td>
                        <td><?= number_format((float)$p['gdv'], 0, ',', '.') ?></td>
                        <td>
                            <a href="/pages/prospectos.php?seleccionar=<?= $p['id_ppl'] ?>" class="btn-primary" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn-danger" title="Eliminar" 
                                onclick="confirmarEliminacion(<?= $p['id_ppl'] ?>, '<?= addslashes($p['concatenado']) ?>')">
                                <i class="fas fa-trash"></i>
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