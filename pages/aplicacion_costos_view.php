<?php
$registros = $pdo->query("SELECT id, aplica, medio_transporte FROM aplicacion_costos ORDER BY aplica")->fetchAll();
?>

<h2 class="section-title"><i class="fas fa-calculator"></i> Aplicación de Costos</h2>

<div class="card">
    <form method="POST" id="form-aplicacion_costos">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.2rem;">
            <div class="form-group">
                <label>Aplica *</label>
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
                           onclick="editaraplicacion_costos(<?= $r['id'] ?>, '<?= addslashes($r['aplica']) ?>', '<?= addslashes($r['medio_transporte']) ?>')"
                           class="btn-edit" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none; margin-right: 0.5rem;">
                            ✏️
                        </a>
                        <a href="index.php?page=aplicacion_costos&delete=<?= $r['id'] ?>" 
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
let aplicacion_costosEdicionId = null;

function editaraplicacion_costos(id, aplica, medio_transporte) {
    document.getElementById('aplica').value = aplica;
    document.getElementById('medio_transporte').value = medio_transporte;
    
    aplicacion_costosEdicionId = id;
    document.getElementById('btn-guardar-aplicacion_costos').textContent = 'Actualizar';
    document.getElementById('btn-guardar-aplicacion_costos').name = 'update';
    document.getElementById('btn-cancelar-edicion').style.display = 'inline-block';
    
    let hidden = document.getElementById('aplicacion_costos_id_hidden');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.id = 'aplicacion_costos_id_hidden';
        hidden.name = 'aplicacion_costos_id';
        document.getElementById('form-aplicacion_costos').appendChild(hidden);
    }
    hidden.value = id;
}

function cancelarEdicion() {
    document.getElementById('aplica').value = '';
    document.getElementById('medio_transporte').value = '';
    
    document.getElementById('btn-guardar-aplicacion_costos').textContent = 'Guardar';
    document.getElementById('btn-guardar-aplicacion_costos').name = 'save';
    document.getElementById('btn-cancelar-edicion').style.display = 'none';
    const hidden = document.getElementById('aplicacion_costos_id_hidden');
    if (hidden) hidden.remove();
    aplicacion_costosEdicionId = null;
    warning('Edición cancelada');
}
</script>