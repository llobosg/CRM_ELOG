<?php
$registros = $pdo->query("SELECT id_incoterm, incoterm, detalle FROM incoterm ORDER BY incoterm")->fetchAll();
?>

<h2 class="section-title"><i class="fas fa-list"></i> Incoterms</h2>

<div class="card">
    <form method="POST" id="form-incoterm">
        <div style="display: grid; grid-template-columns: 30% 70%; gap: 1rem; margin-bottom: 1.2rem;">
            <div class="form-group">
                <label>Incoterm *</label>
                <input type="text" name="incoterm" id="incoterm" required 
                       style="width: 100%; text-transform: uppercase;" 
                       maxlength="10" 
                       placeholder="Máx. 10 caracteres" />
            </div>
            <div class="form-group">
                <label>Detalle</label>
                <input type="text" name="detalle" id="detalle" required style="width: 100%;" />
            </div>
        </div>
        <button type="submit" id="btn-guardar-incoterm" name="save" class="btn-primary">Guardar</button>
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
                    <th style="padding: 0.6rem; text-align: left; width: 30%;">Incoterm</th>
                    <th style="padding: 0.6rem; text-align: left; width: 60%;">Detalle</th>
                    <th style="padding: 0.6rem; text-align: center; width: 10%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.6rem; font-weight: bold;"><?= htmlspecialchars($r['incoterm']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['detalle']) ?></td>
                    <td style="padding: 0.6rem; text-align: center;">
                        <a href="#" 
                           onclick="editarincoterm(<?= $r['id_incoterm'] ?>, '<?= addslashes($r['incoterm']) ?>', '<?= addslashes($r['detalle']) ?>')"
                           class="btn-edit" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none; margin-right: 0.5rem;">
                            ✏️
                        </a>
                        <a href="index.php?page=incoterm&delete=<?= $r['id_incoterm'] ?>" 
                           class="btn-delete" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none;"
                           onclick="return confirm('¿Eliminar este Incoterm?')">
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
let incotermEdicionId = null;

function editarincoterm(id, incoterm, detalle) {
    document.getElementById('incoterm').value = incoterm;
    document.getElementById('detalle').value = detalle;
    incotermEdicionId = id;
    document.getElementById('btn-guardar-incoterm').textContent = 'Actualizar';
    document.getElementById('btn-guardar-incoterm').name = 'update';
    document.getElementById('btn-cancelar-edicion').style.display = 'inline-block';
    
    let hidden = document.getElementById('incoterm_id_hidden');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.id = 'incoterm_id_hidden';
        hidden.name = 'incoterm_id';
        document.getElementById('form-incoterm').appendChild(hidden);
    }
    hidden.value = id;
}

function cancelarEdicion() {
    document.getElementById('incoterm').value = '';
    document.getElementById('detalle').value = '';
    document.getElementById('btn-guardar-incoterm').textContent = 'Guardar';
    document.getElementById('btn-guardar-incoterm').name = 'save';
    document.getElementById('btn-cancelar-edicion').style.display = 'none';
    const hidden = document.getElementById('incoterm_id_hidden');
    if (hidden) hidden.remove();
    incotermEdicionId = null;
    warning('Edición cancelada');
}
</script>