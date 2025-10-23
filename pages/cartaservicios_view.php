<?php
$registros = $pdo->query("SELECT id_cs, servicio_cs, sigla_cs, trafico, subtrafico, tipo_cs, detalle_cs FROM cartaservicios ORDER BY servicio_cs")->fetchAll();
?>

<h2 class="section-title"><i class="fas fa-concierge-bell"></i> Servicios</h2>

<div class="card">
    <form method="POST" id="form-servicios">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.2rem;">
            <div class="form-group">
                <label>Servicio *</label>
                <input type="text" name="servicio_cs" id="servicio_cs" required style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Sigla * (máx. 3)</label>
                <input type="text" name="sigla_cs" id="sigla_cs" required 
                       style="width: 100%; text-transform: uppercase;" 
                       maxlength="3" 
                       placeholder="Ej: IMP" />
            </div>
            <div class="form-group">
                <label>Tráfico</label>
                <input type="text" name="trafico" id="trafico" style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Subtráfico</label>
                <input type="text" name="subtrafico" id="subtrafico" style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Tipo</label>
                <input type="text" name="tipo_cs" id="tipo_cs" style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Detalle</label>
                <input type="text" name="detalle_cs" id="detalle_cs" style="width: 100%;" />
            </div>
        </div>
        <button type="submit" id="btn-guardar-servicios" name="save" class="btn-primary">Guardar</button>
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
                    <th style="padding: 0.6rem; text-align: left;">Servicio</th>
                    <th style="padding: 0.6rem; text-align: left;">Sigla</th>
                    <th style="padding: 0.6rem; text-align: left;">Tráfico</th>
                    <th style="padding: 0.6rem; text-align: left;">Tipo</th>
                    <th style="padding: 0.6rem; text-align: center; width: 10%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.6rem; font-weight: bold;"><?= htmlspecialchars($r['servicio_cs']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['sigla_cs']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['trafico']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['tipo_cs']) ?></td>
                    <td style="padding: 0.6rem; text-align: center;">
                        <a href="#" 
                           onclick="editarservicios(<?= $r['id_cs'] ?>, '<?= addslashes($r['servicio_cs']) ?>', '<?= addslashes($r['sigla_cs']) ?>', '<?= addslashes($r['trafico']) ?>', '<?= addslashes($r['subtrafico']) ?>', '<?= addslashes($r['tipo_cs']) ?>', '<?= addslashes($r['detalle_cs']) ?>')"
                           class="btn-edit" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none; margin-right: 0.5rem;">
                            ✏️
                        </a>
                        <a href="index.php?page=servicios&delete=<?= $r['id_cs'] ?>" 
                           class="btn-delete" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none;"
                           onclick="return confirm('¿Eliminar este servicio?')">
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
let serviciosEdicionId = null;

function editarservicios(id, servicio_cs, sigla_cs, trafico, subtrafico, tipo_cs, detalle_cs) {
    document.getElementById('servicio_cs').value = servicio_cs;
    document.getElementById('sigla_cs').value = sigla_cs;
    document.getElementById('trafico').value = trafico;
    document.getElementById('subtrafico').value = subtrafico;
    document.getElementById('tipo_cs').value = tipo_cs;
    document.getElementById('detalle_cs').value = detalle_cs;
    
    serviciosEdicionId = id;
    document.getElementById('btn-guardar-servicios').textContent = 'Actualizar';
    document.getElementById('btn-guardar-servicios').name = 'update';
    document.getElementById('btn-cancelar-edicion').style.display = 'inline-block';
    
    let hidden = document.getElementById('servicios_id_hidden');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.id = 'servicios_id_hidden';
        hidden.name = 'servicios_id';
        document.getElementById('form-servicios').appendChild(hidden);
    }
    hidden.value = id;
}

function cancelarEdicion() {
    document.getElementById('servicio_cs').value = '';
    document.getElementById('sigla_cs').value = '';
    document.getElementById('trafico').value = '';
    document.getElementById('subtrafico').value = '';
    document.getElementById('tipo_cs').value = '';
    document.getElementById('detalle_cs').value = '';
    
    document.getElementById('btn-guardar-servicios').textContent = 'Guardar';
    document.getElementById('btn-guardar-servicios').name = 'save';
    document.getElementById('btn-cancelar-edicion').style.display = 'none';
    const hidden = document.getElementById('servicios_id_hidden');
    if (hidden) hidden.remove();
    serviciosEdicionId = null;
    warning('Edición cancelada');
}
</script>