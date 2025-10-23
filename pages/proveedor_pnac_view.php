<?php
$registros = $pdo->query("SELECT id_pnac, nombre_pnac, cod_pnac FROM proveedor_pnac ORDER BY nombre_pnac")->fetchAll();
?>

<h2 class="section-title"><i class="fas fa-handshake"></i> Proveedores Nacionales</h2>

<div class="card">
    <form method="POST" id="form-proveedor_pnac">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.2rem;">
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="nombre_pnac" id="nombre_pnac" required style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Código</label>
                <input type="text" name="cod_pnac" id="cod_pnac" style="width: 100%;" />
            </div>
        </div>
        <button type="submit" id="btn-guardar-proveedor_pnac" name="save" class="btn-primary">Guardar</button>
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
                    <th style="padding: 0.6rem; text-align: left;">Nombre</th>
                    <th style="padding: 0.6rem; text-align: left;">Código</th>
                    <th style="padding: 0.6rem; text-align: center; width: 10%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.6rem; font-weight: bold;"><?= htmlspecialchars($r['nombre_pnac']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['cod_pnac']) ?></td>
                    <td style="padding: 0.6rem; text-align: center;">
                        <a href="#" 
                           onclick="editarproveedor_pnac(<?= $r['id_pnac'] ?>, '<?= addslashes($r['nombre_pnac']) ?>', '<?= addslashes($r['cod_pnac']) ?>')"
                           class="btn-edit" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none; margin-right: 0.5rem;">
                            ✏️
                        </a>
                        <a href="index.php?page=proveedor_pnac&delete=<?= $r['id_pnac'] ?>" 
                           class="btn-delete" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none;"
                           onclick="return confirm('¿Eliminar este proveedor nacional?')">
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
let proveedor_pnacEdicionId = null;

function editarproveedor_pnac(id, nombre_pnac, cod_pnac) {
    document.getElementById('nombre_pnac').value = nombre_pnac;
    document.getElementById('cod_pnac').value = cod_pnac;
    
    proveedor_pnacEdicionId = id;
    document.getElementById('btn-guardar-proveedor_pnac').textContent = 'Actualizar';
    document.getElementById('btn-guardar-proveedor_pnac').name = 'update';
    document.getElementById('btn-cancelar-edicion').style.display = 'inline-block';
    
    let hidden = document.getElementById('proveedor_pnac_id_hidden');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.id = 'proveedor_pnac_id_hidden';
        hidden.name = 'proveedor_pnac_id';
        document.getElementById('form-proveedor_pnac').appendChild(hidden);
    }
    hidden.value = id;
}

function cancelarEdicion() {
    document.getElementById('nombre_pnac').value = '';
    document.getElementById('cod_pnac').value = '';
    
    document.getElementById('btn-guardar-proveedor_pnac').textContent = 'Guardar';
    document.getElementById('btn-guardar-proveedor_pnac').name = 'save';
    document.getElementById('btn-cancelar-edicion').style.display = 'none';
    const hidden = document.getElementById('proveedor_pnac_id_hidden');
    if (hidden) hidden.remove();
    proveedor_pnacEdicionId = null;
    warning('Edición cancelada');
}
</script>