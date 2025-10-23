<?php
$registros = $pdo->query("SELECT id_trafico, trafico FROM trafico ORDER BY trafico")->fetchAll();
?>

<h2 class="section-title"><i class="fas fa-truck"></i> Tráfico</h2>

<div class="card">
    <form method="POST" id="form-trafico">
        <div style="display: grid; grid-template-columns: 100%; gap: 1rem; margin-bottom: 1.2rem;">
            <div class="form-group">
                <label>Tráfico *</label>
                <input type="text" name="trafico" id="trafico" required style="width: 100%;" />
            </div>
        </div>
        <button type="submit" id="btn-guardar-trafico" name="save" class="btn-primary">Guardar</button>
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
                    <th style="padding: 0.6rem; text-align: left;">Tráfico</th>
                    <th style="padding: 0.6rem; text-align: center; width: 10%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.6rem; font-weight: bold;"><?= htmlspecialchars($r['trafico']) ?></td>
                    <td style="padding: 0.6rem; text-align: center;">
                        <a href="#" 
                           onclick="editartrafico(<?= $r['id_trafico'] ?>, '<?= addslashes($r['trafico']) ?>')"
                           class="btn-edit" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none; margin-right: 0.5rem;">
                            ✏️
                        </a>
                        <a href="index.php?page=trafico&delete=<?= $r['id_trafico'] ?>" 
                           class="btn-delete" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none;"
                           onclick="return confirm('¿Eliminar este registro de tráfico?')">
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
let traficoEdicionId = null;

function editartrafico(id, trafico) {
    document.getElementById('trafico').value = trafico;
    traficoEdicionId = id;
    document.getElementById('btn-guardar-trafico').textContent = 'Actualizar';
    document.getElementById('btn-guardar-trafico').name = 'update';
    document.getElementById('btn-cancelar-edicion').style.display = 'inline-block';
    
    let hidden = document.getElementById('trafico_id_hidden');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.id = 'trafico_id_hidden';
        hidden.name = 'trafico_id';
        document.getElementById('form-trafico').appendChild(hidden);
    }
    hidden.value = id;
}

function cancelarEdicion() {
    document.getElementById('trafico').value = '';
    document.getElementById('btn-guardar-trafico').textContent = 'Guardar';
    document.getElementById('btn-guardar-trafico').name = 'save';
    document.getElementById('btn-cancelar-edicion').style.display = 'none';
    const hidden = document.getElementById('trafico_id_hidden');
    if (hidden) hidden.remove();
    traficoEdicionId = null;
    warning('Edición cancelada');
}
</script>