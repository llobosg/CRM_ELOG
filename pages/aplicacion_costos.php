<?php
// pages/aplicacion_costos.php

require_once __DIR__ . '/../config.php';

// === Solo admins ===
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ?page=prospectos');
    exit;
}

// === Actualizar registro existente ===
if ($_POST && isset($_POST['update'])) {
    try {
        $id = (int)($_POST['aplicacion_costos_id'] ?? 0); // Asegurar que sea entero
        $aplica = trim($_POST['aplica'] ?? '');
        $medio_transporte = trim($_POST['medio_transporte'] ?? '');

        if (!$id || empty($aplica)) { // Asegurar que 'aplica' no esté vacío
            throw new Exception('ID o campo "Aplica" inválido para la actualización');
        }

        $stmt = $pdo->prepare("UPDATE aplicacion_costos SET aplica = ?, medio_transporte = ? WHERE id = ?");
        $stmt->execute([$aplica, $medio_transporte, $id]);

        // Redirigir con mensaje de éxito
        $mensajeExito = '✅ Aplicación de costos actualizada';
        header("Location: ?page=aplicacion_costos&exito=" . urlencode($mensajeExito));
        exit;
    } catch (Exception $e) {
        $mensajeError = '❌ Error: ' . $e->getMessage();
        header("Location: ?page=aplicacion_costos&error=" . urlencode($mensajeError));
        exit;
    }
}

// === Guardar nuevo registro ===
if ($_POST && isset($_POST['save'])) {
    try {
        $aplica = trim($_POST['aplica'] ?? '');
        $medio_transporte = trim($_POST['medio_transporte'] ?? '');

        if (empty($aplica)) { // Validar que 'aplica' no esté vacío
            throw new Exception('El campo "Aplica" es obligatorio');
        }

        $stmt = $pdo->prepare("INSERT INTO aplicacion_costos (aplica, medio_transporte) VALUES (?, ?)");
        $stmt->execute([$aplica, $medio_transporte]);

        // Redirigir con mensaje de éxito
        $mensajeExito = '✅ Aplicación de costos guardada';
        header("Location: ?page=aplicacion_costos&exito=" . urlencode($mensajeExito));
        exit;
    } catch (Exception $e) {
        $mensajeError = '❌ Error: ' . $e->getMessage();
        header("Location: ?page=aplicacion_costos&error=" . urlencode($mensajeError));
        exit;
    }
}

// === Eliminar registro ===
if (isset($_GET['delete'])) {
    try {
        // Usar la tabla correcta: aplicacion_costos
        $stmt = $pdo->prepare("DELETE FROM aplicacion_costos WHERE id = ?");
        $stmt->execute([$_GET['delete']]);

        // Redirigir con mensaje de éxito
        $mensajeExito = '✅ Aplicación de costos eliminada';
        header("Location: ?page=aplicacion_costos&exito=" . urlencode($mensajeExito));
        exit;
    } catch (Exception $e) {
        // Redirigir con mensaje de error (posible clave foránea)
        $mensajeError = '❌ No se puede eliminar: registro en uso o error interno';
        header("Location: ?page=aplicacion_costos&error=" . urlencode($mensajeError));
        exit;
    }
}

// === Cargar registros ===
// Usar la tabla correcta: aplicacion_costos
$registros = $pdo->query("SELECT id, aplica, medio_transporte FROM aplicacion_costos ORDER BY aplica")->fetchAll();
?>

<h2 class="section-title"><i class="fas fa-calculator"></i> Aplicación de Costos..</h2>

<div class="card">
    <form method="POST" id="form-aplicacion_costos">
        <input type="hidden" name="modo" value="crear"> <!-- Indicador de modo -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.2rem;">
            <div class="form-group">
                <label>Aplica **</label>
                <input type="text" name="aplica" id="aplica" required style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Medio de Transporte</label>
                <input type="text" name="medio_transporte" id="medio_transporte" style="width: 100%;" />
            </div>
        </div>
        <button type="submit" id="btn-guardar-aplicacion_costos" name="save" class="btn-primary">Guardar</button>
        <button type="button" id="btn-cancelar-edicion" class="btn-secondary" style="margin-left: 0.8rem; display: none;" onclick="cancelarEdicion()">
            <i class="fas fa-times"></i> Cancelar Edición
        </button>
        <button type="button" class="btn-secondary" style="margin-left: 0.8rem;" onclick="location.href='index.php?page=dashboard';">
            <i class="fas fa-arrow-left"></i> Volver
        </button>
    </form>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <h3 style="margin: 0 0 1rem 0; font-size: 1.05rem; color: #3a4f63;">Registros existentes</h3>
    <div class="table-container">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
            <thead>
                <tr style="background: #f0f0f0;">
                    <th style="padding: 0.6rem; text-align: left;">Aplica</th>
                    <th style="padding: 0.6rem; text-align: left;">Medio Transporte</th>
                    <th style="padding: 0.6rem; text-align: center; width: 10%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.6rem; font-weight: bold;"><?= htmlspecialchars($r['aplica']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['medio_transporte']) ?></td>
                    <td style="padding: 0.6rem; text-align: center;">
                        <a href="#" 
                           onclick="editarAplicacionCostos(<?= $r['id'] ?>, '<?= addslashes(htmlspecialchars($r['aplica'])) ?>', '<?= addslashes(htmlspecialchars($r['medio_transporte'])) ?>')"
                           class="btn-edit" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none; margin-right: 0.5rem;">
                            ✏️
                        </a>
                        <a href="?page=aplicacion_costos&delete=<?= $r['id'] ?>" 
                           class="btn-delete" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none;"
                           onclick="return confirm('¿Eliminar esta aplicación de costos?')">
                            🗑️
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
let aplicacionCostosEdicionId = null; // Variable global para el ID en edición

function editarAplicacionCostos(id, aplica, medioTransporte) {
    document.getElementById('aplica').value = aplica;
    document.getElementById('medio_transporte').value = medioTransporte;

    aplicacionCostosEdicionId = id;
    document.getElementById('btn-guardar-aplicacion_costos').textContent = 'Actualizar';
    document.getElementById('btn-guardar-aplicacion_costos').name = 'update'; // Cambiar nombre del botón
    document.getElementById('btn-cancelar-edicion').style.display = 'inline-block';

    // Agregar campo oculto con el ID para la actualización
    let hiddenId = document.getElementById('aplicacion_costos_id_hidden');
    if (!hiddenId) {
        hiddenId = document.createElement('input');
        hiddenId.type = 'hidden';
        hiddenId.id = 'aplicacion_costos_id_hidden';
        hiddenId.name = 'aplicacion_costos_id'; // Nombre del campo para el ID
        document.getElementById('form-aplicacion_costos').appendChild(hiddenId);
    }
    hiddenId.value = id;
}

function cancelarEdicion() {
    document.getElementById('aplica').value = '';
    document.getElementById('medio_transporte').value = '';

    document.getElementById('btn-guardar-aplicacion_costos').textContent = 'Guardar';
    document.getElementById('btn-guardar-aplicacion_costos').name = 'save'; // Volver nombre del botón a 'save'
    document.getElementById('btn-cancelar-edicion').style.display = 'none';
    const hiddenId = document.getElementById('aplicacion_costos_id_hidden');
    if (hiddenId) hiddenId.remove(); // Eliminar campo oculto si existe
    aplicacionCostosEdicionId = null;
    warning('Edición cancelada');
}
</script>