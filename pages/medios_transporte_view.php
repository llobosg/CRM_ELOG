<?php
$registros = $pdo->query("SELECT id, nombre, pais, ciudad, tipo, medio, codigo_iata FROM medios_transporte ORDER BY nombre")->fetchAll();
?>

<h2 class="section-title"><i class="fas fa-ship"></i> Medios de Transporte</h2>

<div class="card">
    <form method="POST" id="form-medios_transporte">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.2rem;">
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="nombre" id="nombre" required style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>País</label>
                <input type="text" name="pais" id="pais" style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Ciudad</label>
                <input type="text" name="ciudad" id="ciudad" style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Tipo</label>
                <input type="text" name="tipo" id="tipo" style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Medio</label>
                <input type="text" name="medio" id="medio" style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Código IATA</label>
                <input type="text" name="codigo_iata" id="codigo_iata" style="width: 100%;" />
            </div>
        </div>
        <button type="submit" id="btn-guardar-medios_transporte" name="save" class="btn-primary">Guardar</button>
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
                    <th style="padding: 0.6rem; text-align: left;">País</th>
                    <th style="padding: 0.6rem; text-align: left;">Ciudad</th>
                    <th style="padding: 0.6rem; text-align: left;">Tipo</th>
                    <th style="padding: 0.6rem; text-align: center; width: 10%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.6rem; font-weight: bold;"><?= htmlspecialchars($r['nombre']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['pais']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['ciudad']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['tipo']) ?></td>
                    <td style="padding: 0.6rem; text-align: center;">
                        <a href="#" 
                           onclick="editarmedios_transporte(<?= $r['id'] ?>, '<?= addslashes($r['nombre']) ?>', '<?= addslashes($r['pais']) ?>', '<?= addslashes($r['ciudad']) ?>', '<?= addslashes($r['tipo']) ?>', '<?= addslashes($r['medio']) ?>', '<?= addslashes($r['codigo_iata']) ?>')"
                           class="btn-edit" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none; margin-right: 0.5rem;">
                            ✏️
                        </a>
                        <a href="index.php?page=medios_transporte&delete=<?= $r['id'] ?>" 
                           class="btn-delete" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none;"
                           onclick="return confirm('¿Eliminar este medio de transporte?')">
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
let medios_transporteEdicionId = null;

function editarmedios_transporte(id, nombre, pais, ciudad, tipo, medio, codigo_iata) {
    document.getElementById('nombre').value = nombre;
    document.getElementById('pais').value = pais;
    document.getElementById('ciudad').value = ciudad;
    document.getElementById('tipo').value = tipo;
    document.getElementById('medio').value = medio;
    document.getElementById('codigo_iata').value = codigo_iata;
    
    medios_transporteEdicionId = id;
    document.getElementById('btn-guardar-medios_transporte').textContent = 'Actualizar';
    document.getElementById('btn-guardar-medios_transporte').name = 'update';
    document.getElementById('btn-cancelar-edicion').style.display = 'inline-block';
    
    let hidden = document.getElementById('medios_transporte_id_hidden');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.id = 'medios_transporte_id_hidden';
        hidden.name = 'medios_transporte_id';
        document.getElementById('form-medios_transporte').appendChild(hidden);
    }
    hidden.value = id;
}

function cancelarEdicion() {
    document.getElementById('nombre').value = '';
    document.getElementById('pais').value = '';
    document.getElementById('ciudad').value = '';
    document.getElementById('tipo').value = '';
    document.getElementById('medio').value = '';
    document.getElementById('codigo_iata').value = '';
    
    document.getElementById('btn-guardar-medios_transporte').textContent = 'Guardar';
    document.getElementById('btn-guardar-medios_transporte').name = 'save';
    document.getElementById('btn-cancelar-edicion').style.display = 'none';
    const hidden = document.getElementById('medios_transporte_id_hidden');
    if (hidden) hidden.remove();
    medios_transporteEdicionId = null;
    warning('Edición cancelada');
}
</script>