<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// === Solo admins ===
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ?page=prospectos');
    exit;
}

// === Guardar nuevo registro ===
if ($_POST && isset($_POST['save'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO aplicacion_costos (aplica, medio_transporte) VALUES (?, ?)");
        $stmt->execute([$_POST['aplica'], $_POST['medio_transporte']]);
        echo "<script>alert('✅ Incoterm guardado'); location.href='?page=incoterms';</script>";
    } catch (Exception $e) {
        echo "<script>alert('❌ Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// === Eliminar registro ===
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM aplicaciones_costos WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        echo "<script>location.href='?page=aplicaciones_costos';</script>";
    } catch (Exception $e) {
        echo "<script>alert('❌ No se puede eliminar: registro en uso');</script>";
    }
}

// === Cargar registros ===
$registros = $pdo->query("SELECT * FROM aplicaciones_costos ORDER BY nombre")->fetchAll();
?>

<h2 class="section-title"><i class="fas fa-list"></i> Aplicación de Costos</h2>

<!-- Formulario de alta -->
<div class="card">
    <form method="POST">
        <div class="grid-form">
            <div class="form-group"><label>Aplica</label><input type="text" name="aplica" required /></div>
            <div class="form-group"><label>Medio Transporte</label><input type="text" name="medio_transporte" required /></div>
        </div>
        <button type="submit" name="save" class="btn-primary">Guardar</button>
        <a href="?page=prospectos" class="btn-secondary" style="margin-left: 0.8rem;">Volver</a>
    </form>
</div>

<!-- Listado de registros -->
<div class="card" style="margin-top: 1.5rem;">
    <h3 style="margin: 0 0 1rem 0; font-size: 1.05rem; color: #3a4f63;">Registros existentes</h3>
    <div class="table-container">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
            <thead>
                <tr style="background: #f0f0f0;">
                    <th style="padding: 0.6rem; text-align: left;">Aplica</th>
                    <th style="padding: 0.6rem; text-align: left;">Medio Transporte</th>
                    <th style="padding: 0.6rem; text-align: center; width: 120px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['aplica']) ?></td>
                    <td style="padding: 0.6rem; text-align: center;">
                        <!-- Editar: en una versión futura -->
                        <a href="?page=aplicaciones_costos&delete=<?= $r['id'] ?>" 
                           onclick="return confirm('¿Eliminar este registro?')" 
                           class="btn-delete" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">
                            🗑️ Eliminar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>