<?php
$registros = $pdo->query("SELECT id_comercial, nombre, apellido, cargo FROM comerciales ORDER BY nombre, apellido")->fetchAll();
?>

<h2 class="section-title"><i class="fas fa-user-tie"></i> Comerciales</h2>

<div class="card">
    <form method="POST" id="form-comerciales">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.2rem;">
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="nombre" id="nombre" required style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Apellido *</label>
                <input type="text" name="apellido" id="apellido" required style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Cargo</label>
                <input type="text" name="cargo" id="cargo" style="width: 100%;" />
            </div>
        </div>
        <button type="submit" id="btn-guardar-comerciales" name="save" class="btn-primary">Guardar</button>
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
                    <th style="padding: 0.6rem; text-align: left;">Nombre Completo</th>
                    <th style="padding: 0.6rem; text-align: left;">Cargo</th>
                    <th style="padding: 0.6rem; text-align: center; width: 10%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.6rem; font-weight: bold;"><?= htmlspecialchars($r['nombre'] . ' ' . $r['apellido']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['cargo']) ?></td>
                    <td style="padding: 0.6rem; text-align: center;">
                        <a href="#" 
                           onclick="editarcomerciales(<?= $r['id_comercial'] ?>, '<?= addslashes($r['nombre']) ?>', '<?= addslashes($r['apellido']) ?>', '<?= addslashes($r['cargo']) ?>')"
                           class="btn-edit" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none; margin-right: 0.5rem;">
                            ✏️
                        </a>
                        <a href="index.php?page=comerciales&delete=<?= $r['id_comercial'] ?>" 
                           class="btn-delete" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none;"
                           onclick="return confirm('¿Eliminar este comercial?')">
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
let comercialesEdicionId = null;

function editarcomerciales(id, nombre, apellido, cargo) {
    document.getElementById('nombre').value = nombre;
    document.getElementById('apellido').value = apellido;
    document.getElementById('cargo').value = cargo;
    
    comercialesEdicionId = id;
    document.getElementById('btn-guardar-comerciales').textContent = 'Actualizar';
    document.getElementById('btn-guardar-comerciales').name = 'update';
    document.getElementById('btn-cancelar-edicion').style.display = 'inline-block';
    
    let hidden = document.getElementById('comerciales_id_hidden');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.id = 'comerciales_id_hidden';
        hidden.name = 'comerciales_id';
        document.getElementById('form-comerciales').appendChild(hidden);
    }
    hidden.value = id;
}

function cancelarEdicion() {
    document.getElementById('nombre').value = '';
    document.getElementById('apellido').value = '';
    document.getElementById('cargo').value = '';
    
    document.getElementById('btn-guardar-comerciales').textContent = 'Guardar';
    document.getElementById('btn-guardar-comerciales').name = 'save';
    document.getElementById('btn-cancelar-edicion').style.display = 'none';
    const hidden = document.getElementById('comerciales_id_hidden');
    if (hidden) hidden.remove();
    comercialesEdicionId = null;
    warning('Edición cancelada');
}
</script>