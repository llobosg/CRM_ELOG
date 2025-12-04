<?php
// pages/dashboard.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php'; // Asegura que el usuario esté autenticado

$rol_usuario = $_SESSION['rol'] ?? 'comercial';
$nombre_usuario = $_SESSION['user'] ?? 'Usuario';
$id_usuario = (int)($_SESSION['user_id'] ?? 0);

// --- Lógica de Filtro por Rol ---
$where_clause = '';
$params_where = [];

if ($rol_usuario === 'comercial') {
    $where_clause = " WHERE p.id_comercial = ?";
    $params_where = [$id_usuario];
}
// Para admin, admin_finanzas, pricing, $where_clause y $params_where quedan vacíos.

// --- Estadísticas ---
$sql_total = "SELECT COUNT(*) as total FROM prospectos p " . $where_clause;
$stmt_total = $pdo->prepare($sql_total);
$stmt_total->execute($params_where);
$total = (int)$stmt_total->fetch()['total'];

// Asumiendo que 'Enviado', 'Pendiente', etc. son estados válidos en tu sistema
// Ajusta estos estados si es necesario
$sql_enviados = "SELECT COUNT(*) as total FROM prospectos p WHERE p.estado = 'Enviado' " . $where_clause;
$stmt_enviados = $pdo->prepare($sql_enviados);
$stmt_enviados->execute($params_where);
$enviados = (int)$stmt_enviados->fetch()['total'];

$sql_pendientes = "SELECT COUNT(*) as total FROM prospectos p WHERE p.estado = 'Pendiente' " . $where_clause;
$stmt_pendientes = $pdo->prepare($sql_pendientes);
$stmt_pendientes->execute($params_where);
$pendientes = (int)$stmt_pendientes->fetch()['total'];

$sql_devueltos = "SELECT COUNT(*) as total FROM prospectos p WHERE p.estado = 'Devuelto_pendiente' " . $where_clause;
$stmt_devueltos = $pdo->prepare($sql_devueltos);
$stmt_devueltos->execute($params_where);
$devueltos = (int)$stmt_devueltos->fetch()['total'];

$sql_cerrados = "SELECT COUNT(*) as total FROM prospectos p WHERE p.estado = 'CerradoOK' " . $where_clause;
$stmt_cerrados = $pdo->prepare($sql_cerrados);
$stmt_cerrados->execute($params_where);
$cerrados = (int)$stmt_cerrados->fetch()['total'];

$sql_rechazados = "SELECT COUNT(*) as total FROM prospectos p WHERE p.estado = 'Rechazado' " . $where_clause;
$stmt_rechazados = $pdo->prepare($sql_rechazados);
$stmt_rechazados->execute($params_where);
$rechazados = (int)$stmt_rechazados->fetch()['total'];

$porcentaje_cierre = $total > 0 ? round(($cerrados / $total) * 100, 1) : 0;

// --- Carga de datos para la tabla ---
$order = $_GET['order'] ?? 'id_ppl';
$dir = $_GET['dir'] ?? 'desc';
$allowed_order = ['concatenado', 'razon_social', 'rut_empresa', 'pais', 'estado', 'fecha_alta', 'id_ppl'];
$order = in_array($order, $allowed_order) ? $order : 'id_ppl';
$dir = $dir === 'asc' ? 'ASC' : 'DESC';
$order_clause = "ORDER BY p.$order $dir";

$sql_tabla = "SELECT p.concatenado, p.razon_social, p.rut_empresa, p.pais, p.estado, p.fecha_alta FROM prospectos p " . $where_clause . " " . $order_clause . " LIMIT 10";
$stmt_tabla = $pdo->prepare($sql_tabla);
$stmt_tabla->execute($params_where);

$prospectos_para_tabla = $stmt_tabla->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- Saludo personalizado -->
<div style="margin-bottom: 1.5rem; padding: 0.8rem; background-color: #e9ecef; border-radius: 6px;">
    <h2 style="margin: 0; font-size: 1.2rem; color: #3a4f63;">Bienvenido/a, <?= htmlspecialchars($nombre_usuario) ?> (Rol: <?= htmlspecialchars($rol_usuario) ?>)</h2>
</div>

<!-- Tarjetas de estadísticas -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div style="background: #f8f9fa; padding: 1.2rem; border-radius: 10px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.08); border: 1px solid #e9ecef;">
        <h3 style="margin: 0 0 0.8rem 0; color: #0b29beff; font-size: 1rem; font-weight: 600;">Total Prospectos</h3>
        <p style="font-size: 2rem; font-weight: bold; color: #0b29beff; margin: 0;"><?= $total ?></p>
    </div>
    <div style="background: #f0f9ff; padding: 1.2rem; border-radius: 10px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.08); border: 1px solid #e9ecef;">
        <h3 style="margin: 0 0 0.8rem 0; color: #0284c7; font-size: 1rem; font-weight: 600;">Enviado</h3>
        <p style="font-size: 2rem; font-weight: bold; color: #0284c7; margin: 0;"><?= $enviados ?></p>
    </div>
    <div style="background: #fff8f0; padding: 1.2rem; border-radius: 10px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.08); border: 1px solid #e9ecef;">
        <h3 style="margin: 0 0 0.8rem 0; color: #d97706; font-size: 1rem; font-weight: 600;">Pendiente</h3>
        <p style="font-size: 2rem; font-weight: bold; color: #d97706; margin: 0;"><?= $pendientes ?></p>
    </div>
    <div style="background: #fdecee; padding: 1.2rem; border-radius: 10px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.08); border: 1px solid #e9ecef;">
        <h3 style="margin: 0 0 0.8rem 0; color: #7c2d12; font-size: 1rem; font-weight: 600;">Devuelto</h3>
        <p style="font-size: 2rem; font-weight: bold; color: #7c2d12; margin: 0;"><?= $devueltos ?></p>
    </div>
    <div style="background: #f3f4f6; padding: 1.2rem; border-radius: 10px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.08); border: 1px solid #e9ecef;">
        <h3 style="margin: 0 0 0.8rem 0; color: #dc2626; font-size: 1rem; font-weight: 600;">Rechazado</h3>
        <p style="font-size: 2rem; font-weight: bold; color: dc2626; margin: 0;"><?= $rechazados ?></p>
    </div>
    <div style="background: #f0fdf4; padding: 1.2rem; border-radius: 10px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.08); border: 1px solid #e9ecef;">
        <h3 style="margin: 0 0 0.8rem 0; color: #059669; font-size: 1rem; font-weight: 600;">Cerrado OK</h3>
        <p style="font-size: 2rem; font-weight: bold; color: #059669; margin: 0;"><?= $cerrados ?></p>
    </div>
    <div style="background: #f9fafb; padding: 1.2rem; border-radius: 10px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.08); border: 1px solid #e9ecef;">
        <h3 style="margin: 0 0 0.8rem 0; color: #4b5563; font-size: 1rem; font-weight: 600;">Tasa de Cierre</h3>
        <p style="font-size: 2rem; font-weight: bold; color: #4b5563; margin: 0;"><?= $porcentaje_cierre ?>%</p>
    </div>
</div>

<!-- Búsqueda en tiempo real -->
<div style="margin-bottom: 1.5rem;">
    <input
        type="text"
        id="search-dashboard"
        placeholder="Buscar en prospectos..."
        style="
            width: 100%;
            max-width: 400px;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 0.95rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        "
        onkeyup="filtrarTabla()"
    />
</div>

<!-- Tabla con ordenamiento -->
<h3 style="margin: 1.5rem 0 1rem 0; color: #3a4f63; font-size: 1.1rem;"><i class="fas fa-list"></i> Últimos Prospectos</h3>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <?php
                $order = $_GET['order'] ?? 'id_ppl';
                $dir = $_GET['dir'] ?? 'desc';

                function sortLink($field, $label, $currentOrder, $currentDir) {
                    $newDir = ($currentOrder === $field && $currentDir === 'asc') ? 'desc' : 'asc';
                    $icon = '';
                    if ($currentOrder === $field) {
                        $icon = $currentDir === 'asc'
                            ? ' <i class="fas fa-sort-up"></i>'
                            : ' <i class="fas fa-sort-down"></i>';
                    }
                    return "<a href='?page=dashboard&order=$field&dir=$newDir' style='color: white; text-decoration: none; display: flex; align-items: center; gap: 0.3rem;'>$label$icon</a>";
                }

                $allowedOrder = ['concatenado', 'razon_social', 'rut_empresa', 'pais', 'estado', 'fecha_alta', 'id_ppl'];
                $order = in_array($order, $allowedOrder) ? $order : 'id_ppl';
                $dir = $dir === 'asc' ? 'ASC' : 'DESC';
                ?>
                <th><?= sortLink('concatenado', 'Concatenado', $order, $dir) ?></th>
                <th><?= sortLink('razon_social', 'Razón Social', $order, $dir) ?></th>
                <th><?= sortLink('rut_empresa', 'RUT', $order, $dir) ?></th>
                <th><?= sortLink('pais', 'País', $order, $dir) ?></th>
                <th><?= sortLink('estado', 'Estado', $order, $dir) ?></th>
                <th><?= sortLink('fecha_alta', 'Fecha Alta', $order, $dir) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($prospectos_para_tabla as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['concatenado']) ?></td>
                <td><?= htmlspecialchars($row['razon_social']) ?></td>
                <td><?= htmlspecialchars($row['rut_empresa']) ?></td>
                <td><?= htmlspecialchars($row['pais']) ?></td>
                <td><?= htmlspecialchars($row['estado']) ?></td>
                <td><?= date('d-m-Y', strtotime($row['fecha_alta'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Botón Exportar Excel -->
<div style="text-align: right; margin-top: 2rem;">
    <a href="exportar_excel.php" class="btn-primary">
        <i class="fas fa-file-excel"></i> Exportar a Excel
    </a>
</div>

<script>
function filtrarTabla() {
    const input = document.getElementById('search-dashboard');
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('.table-container tbody tr');

    rows.forEach(row => {
        const match = Array.from(row.cells).some(cell =>
            cell.textContent.toLowerCase().includes(filter)
        );
        row.style.display = match ? '' : 'none';
    });
}
</script>

</main>

</body>
</html>