<?php
$registros = $pdo->query("SELECT id_oper, operacion, tipo_oper, detalle_tipo_oper FROM operacion ORDER BY operacion")->fetchAll();
?>

<h2 class="section-title"><i class="fas fa-exchange-alt"></i> Tipos de Operación</h2>

<div class="card">
    <form method="POST" id="form-operacion">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.2rem;">
            <div class="form-group">
                <label>Operación *</label>
                <input type="text" name="operacion" id="operacion" required style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Tipo de Operación</label>
                <input type="text" name="tipo_oper" id="tipo_oper" style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Detalle del Tipo</label>
                <input type="text" name="detalle_tipo_oper" id="detalle_tipo_oper" style="width: 100%;" />
            </div>
        </div>
        <button type="submit" id="btn-guardar-operacion" name="save" class="btn-primary">Guardar</button>
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
                    <th style="padding: 0.6rem; text-align: left;">Operación</th>
                    <th style="padding: 0.6rem; text-align: left;">Tipo</th>
                    <th style="padding: 0.6rem; text-align: left;">Detalle</th>
                    <th style="padding: 0.6rem; text-align: center; width: 10%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.6rem; font-weight: bold;"><?= htmlspecialchars($r['operacion']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['tipo_oper']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['detalle_tipo_oper']) ?></td>
                    <td style="padding: 0.6rem; text-align: center;">
                        <a href="#" 
                           onclick="editaroperacion(<?= $r['id_oper'] ?>, '<?= addslashes($r['operacion']) ?>', '<?= addslashes($r['tipo_oper']) ?>', '<?= addslashes($r['detalle_tipo_oper']) ?>')"
                           class="btn-edit" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none; margin-right: 0.5rem;">
                            ✏️
                        </a>
                        <a href="index.php?page=operacion&delete=<?= $r['id_oper'] ?>" 
                           class="btn-delete" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none;"
                           onclick="return confirm('¿Eliminar este tipo de operación?')">
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
let operacionEdicionId = null;

function editaroperacion(id, operacion, tipo_oper, detalle_tipo_oper) {
    document.getElementById('operacion').value = operacion;
    document.getElementById('tipo_oper').value = tipo_oper;
    document.getElementById('detalle_tipo_oper').value = detalle_tipo_oper;
    
    operacionEdicionId = id;
    document.getElementById('btn-guardar-operacion').textContent = 'Actualizar';
    document.getElementById('btn-guardar-operacion').name = 'update';
    document.getElementById('btn-cancelar-edicion').style.display = 'inline-block';
    
    let hidden = document.getElementById('operacion_id_hidden');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.id = 'operacion_id_hidden';
        hidden.name = 'operacion_id';
        document.getElementById('form-operacion').appendChild(hidden);
    }
    hidden.value = id;
}

function cancelarEdicion() {
    document.getElementById('operacion').value = '';
    document.getElementById('tipo_oper').value = '';
    document.getElementById('detalle_tipo_oper').value = '';
    
    document.getElementById('btn-guardar-operacion').textContent = 'Guardar';
    document.getElementById('btn-guardar-operacion').name = 'save';
    document.getElementById('btn-cancelar-edicion').style.display = 'none';
    const hidden = document.getElementById('operacion_id_hidden');
    if (hidden) hidden.remove();
    operacionEdicionId = null;
    warning('Edición cancelada');
}
</script>