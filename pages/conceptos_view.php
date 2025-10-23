<?php
$registros = $pdo->query("SELECT id, concepto FROM conceptos_costos ORDER BY concepto")->fetchAll();
?>

<h2 class="section-title"><i class="fas fa-tag"></i> Conceptos</h2>

<div class="card">
    <form method="POST" id="form-conceptos">
        <div style="display: grid; grid-template-columns: 100%; gap: 1rem; margin-bottom: 1.2rem;">
            <div class="form-group">
                <label>Concepto *</label>
                <input type="text" name="concepto" id="concepto" required style="width: 100%;" />
            </div>
        </div>
        <button type="submit" id="btn-guardar-conceptos" name="save" class="btn-primary">Guardar</button>
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
                    <th style="padding: 0.6rem; text-align: left;">Concepto</th>
                    <th style="padding: 0.6rem; text-align: center; width: 10%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.6rem; font-weight: bold;"><?= htmlspecialchars($r['concepto']) ?></td>
                    <td style="padding: 0.6rem; text-align: center;">
                        <a href="#" 
                           onclick="editarconceptos(<?= $r['id'] ?>, '<?= addslashes($r['concepto']) ?>')"
                           class="btn-edit" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none; margin-right: 0.5rem;">
                            ✏️
                        </a>
                        <a href="index.php?page=conceptos&delete=<?= $r['id'] ?>" 
                           class="btn-delete" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none;"
                           onclick="return confirm('¿Eliminar este concepto?')">
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
let conceptosEdicionId = null;

function editarconceptos(id, concepto) {
    document.getElementById('concepto').value = concepto;
    conceptosEdicionId = id;
    document.getElementById('btn-guardar-conceptos').textContent = 'Actualizar';
    document.getElementById('btn-guardar-conceptos').name = 'update';
    document.getElementById('btn-cancelar-edicion').style.display = 'inline-block';
    
    let hidden = document.getElementById('conceptos_id_hidden');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.id = 'conceptos_id_hidden';
        hidden.name = 'conceptos_id';
        document.getElementById('form-conceptos').appendChild(hidden);
    }
    hidden.value = id;
}

function cancelarEdicion() {
    document.getElementById('concepto').value = '';
    document.getElementById('btn-guardar-conceptos').textContent = 'Guardar';
    document.getElementById('btn-guardar-conceptos').name = 'save';
    document.getElementById('btn-cancelar-edicion').style.display = 'none';
    const hidden = document.getElementById('conceptos_id_hidden');
    if (hidden) hidden.remove();
    conceptosEdicionId = null;
    warning('Edición cancelada');
}
</script>