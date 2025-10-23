<?php
$registros = $pdo->query("SELECT id_lugar, detalle_lugar, medio_transporte, pais_lugar FROM lugares ORDER BY detalle_lugar")->fetchAll();
?>

<h2 class="section-title"><i class="fas fa-map-marker-alt"></i> Lugares</h2>

<div class="card">
    <form method="POST" id="form-lugares">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.2rem;">
            <div class="form-group">
                <label>Detalle del Lugar *</label>
                <input type="text" name="detalle_lugar" id="detalle_lugar" required style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Medio de Transporte</label>
                <input type="text" name="medio_transporte" id="medio_transporte" style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>País</label>
                <input type="text" name="pais_lugar" id="pais_lugar" style="width: 100%;" />
            </div>
        </div>
        <button type="submit" id="btn-guardar-lugares" name="save" class="btn-primary">Guardar</button>
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
                    <th style="padding: 0.6rem; text-align: left;">Lugar</th>
                    <th style="padding: 0.6rem; text-align: left;">Medio Transporte</th>
                    <th style="padding: 0.6rem; text-align: left;">País</th>
                    <th style="padding: 0.6rem; text-align: center; width: 10%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.6rem; font-weight: bold;"><?= htmlspecialchars($r['detalle_lugar']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['medio_transporte']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['pais_lugar']) ?></td>
                    <td style="padding: 0.6rem; text-align: center;">
                        <a href="#" 
                           onclick="editarlugares(<?= $r['id_lugar'] ?>, '<?= addslashes($r['detalle_lugar']) ?>', '<?= addslashes($r['medio_transporte']) ?>', '<?= addslashes($r['pais_lugar']) ?>')"
                           class="btn-edit" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none; margin-right: 0.5rem;">
                            ✏️
                        </a>
                        <a href="index.php?page=lugares&delete=<?= $r['id_lugar'] ?>" 
                           class="btn-delete" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none;"
                           onclick="return confirm('¿Eliminar este lugar?')">
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
let lugaresEdicionId = null;

function editarlugares(id, detalle_lugar, medio_transporte, pais_lugar) {
    document.getElementById('detalle_lugar').value = detalle_lugar;
    document.getElementById('medio_transporte').value = medio_transporte;
    document.getElementById('pais_lugar').value = pais_lugar;
    
    lugaresEdicionId = id;
    document.getElementById('btn-guardar-lugares').textContent = 'Actualizar';
    document.getElementById('btn-guardar-lugares').name = 'update';
    document.getElementById('btn-cancelar-edicion').style.display = 'inline-block';
    
    let hidden = document.getElementById('lugares_id_hidden');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.id = 'lugares_id_hidden';
        hidden.name = 'lugares_id';
        document.getElementById('form-lugares').appendChild(hidden);
    }
    hidden.value = id;
}

function cancelarEdicion() {
    document.getElementById('detalle_lugar').value = '';
    document.getElementById('medio_transporte').value = '';
    document.getElementById('pais_lugar').value = '';
    
    document.getElementById('btn-guardar-lugares').textContent = 'Guardar';
    document.getElementById('btn-guardar-lugares').name = 'save';
    document.getElementById('btn-cancelar-edicion').style.display = 'none';
    const hidden = document.getElementById('lugares_id_hidden');
    if (hidden) hidden.remove();
    lugaresEdicionId = null;
    warning('Edición cancelada');
}
</script>