<?php
// pages/condiciones_trafico_view.php
// Verificar si el usuario es admin (ajusta según sea necesario)
if ($_SESSION['rol'] !== 'admin') {
    header('Location: index.php?page=dashboard');
    exit;
}

// Cargar registros
$registros = $pdo->query("SELECT id, trafico, condicion FROM condiciones_trafico ORDER BY trafico")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="section-title"><i class="fas fa-list"></i> Condiciones de Tráfico</h2>

<div class="card">
    <form method="POST" id="form-condiciones-trafico">
        <input type="hidden" name="modo" value="crear">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.2rem;">
            <div class="form-group">
                <label>Tipo de Tráfico *</label>
                <select name="trafico" id="trafico" required style="width: 100%;">
                    <option value="">Seleccionar tipo</option>
                    <option value="Aéreo">Aéreo</option>
                    <option value="Marítimo FCL">Marítimo FCL</option>
                    <option value="Marítimo LCL">Marítimo LCL</option>
                    <option value="Terrestre">Terrestre</option>
                    <!-- Puedes agregar más tipos si es necesario -->
                </select>
            </div>
            <div class="form-group">
                <label>Condición *</label>
                <textarea name="condicion" id="condicion" required rows="3" style="width: 100%; resize: vertical;"></textarea>
            </div>
        </div>
        <button type="submit" id="btn-guardar-condicion" class="btn-primary">Guardar</button>
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
                    <th style="padding: 0.6rem; text-align: left; width: 25%;">Tipo de Tráfico</th>
                    <th style="padding: 0.6rem; text-align: left; width: 65%;">Condición</th>
                    <th style="padding: 0.6rem; text-align: center; width: 10%;">Acciones</th>
                </tr>
            </thead>
                <tbody>
                    <?php foreach ($registros as $r): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 0.6rem; font-weight: bold;"><?= htmlspecialchars($r['trafico']) ?></td>
                            <td style="padding: 0.6rem;"><?= nl2br(htmlspecialchars($r['condicion'])) ?></td>
                            <td style="padding: 0.6rem; text-align: center;">
                                <a href="#" 
                                onclick="editarCondicion(<?= (int)$r['id'] ?>, '<?= addslashes(htmlspecialchars($r['trafico'], ENT_QUOTES)) ?>', '<?= addslashes(htmlspecialchars(str_replace(["\r\n", "\r", "\n"], "\\n", $r['condicion']), ENT_QUOTES)) ?>')"
                                class="btn-edit" 
                                style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none; margin-right: 0.5rem;">
                                    ✏️
                                </a>
                                <a href="index.php?page=condiciones_trafico&amp;delete=<?= (int)$r['id'] ?>" 
                                class="btn-delete" 
                                style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none;"
                                onclick="return confirm('¿Eliminar esta condición de tráfico?')">
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
let condicionEdicionId = null;

function editarCondicion(id, trafico, condicion) {
    document.getElementById('trafico').value = trafico;
    // Reemplazar \n por saltos de línea reales en el textarea
    document.getElementById('condicion').value = condicion.replace(/\\n/g, '\n');
    condicionEdicionId = id;

    // Cambiar el formulario a modo edición
    document.getElementById('btn-guardar-condicion').textContent = 'Actualizar';
    document.getElementById('form-condiciones-trafico').querySelector('input[name="modo"]').value = 'editar';
    document.getElementById('btn-cancelar-edicion').style.display = 'inline-block';
    
    // Agregar campo oculto con el ID
    let hiddenId = document.getElementById('condicion_id_hidden');
    if (!hiddenId) {
        hiddenId = document.createElement('input');
        hiddenId.type = 'hidden';
        hiddenId.id = 'condicion_id_hidden';
        hiddenId.name = 'id';
        document.getElementById('form-condiciones-trafico').appendChild(hiddenId);
    }
    hiddenId.value = id;
}

function cancelarEdicion() {
    document.getElementById('trafico').value = '';
    document.getElementById('condicion').value = '';
    document.getElementById('btn-guardar-condicion').textContent = 'Guardar';
    document.getElementById('form-condiciones-trafico').querySelector('input[name="modo"]').value = 'crear';
    document.getElementById('btn-cancelar-edicion').style.display = 'none';
    const hiddenId = document.getElementById('condicion_id_hidden');
    if (hiddenId) hiddenId.remove();
    condicionEdicionId = null;
    // Opcional: Mostrar un mensaje
    // warning('Edición cancelada');
}
</script>