<?php
$registros = $pdo->query("SELECT id_ppl, razon_social, ig_agente, rut_empresa, fono_empresa, pais, direccion, region, comuna FROM agentes ORDER BY razon_social")->fetchAll();
?>

<h2 class="section-title"><i class="fas fa-building"></i> Agentes</h2>

<div class="card">
    <form method="POST" id="form-agentes">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.2rem;">
            <div class="form-group">
                <label>Razón Social *</label>
                <input type="text" name="razon_social" id="razon_social" required style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>IG Agente</label>
                <input type="text" name="ig_agente" id="ig_agente" style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>RUT Empresa</label>
                <input type="text" name="rut_empresa" id="rut_empresa" style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Fono Empresa</label>
                <input type="text" name="fono_empresa" id="fono_empresa" style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>País</label>
                <input type="text" name="pais" id="pais" style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="direccion" id="direccion" style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Región</label>
                <input type="text" name="region" id="region" style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Comuna</label>
                <input type="text" name="comuna" id="comuna" style="width: 100%;" />
            </div>
        </div>
        <button type="submit" id="btn-guardar-agentes" name="save" class="btn-primary">Guardar</button>
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
                    <th style="padding: 0.6rem; text-align: left;">Razón Social</th>
                    <th style="padding: 0.6rem; text-align: left;">IG Agente</th>
                    <th style="padding: 0.6rem; text-align: left;">RUT</th>
                    <th style="padding: 0.6rem; text-align: center; width: 10%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.6rem; font-weight: bold;"><?= htmlspecialchars($r['razon_social']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['ig_agente']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['rut_empresa']) ?></td>
                    <td style="padding: 0.6rem; text-align: center;">
                        <a href="#" 
                           onclick="editaragentes(<?= $r['id_ppl'] ?>, '<?= addslashes($r['razon_social']) ?>', '<?= addslashes($r['ig_agente']) ?>', '<?= addslashes($r['rut_empresa']) ?>', '<?= addslashes($r['fono_empresa']) ?>', '<?= addslashes($r['pais']) ?>', '<?= addslashes($r['direccion']) ?>', '<?= addslashes($r['region']) ?>', '<?= addslashes($r['comuna']) ?>')"
                           class="btn-edit" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none; margin-right: 0.5rem;">
                            ✏️
                        </a>
                        <a href="index.php?page=agentes&delete=<?= $r['id_ppl'] ?>" 
                           class="btn-delete" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none;"
                           onclick="return confirm('¿Eliminar este agente?')">
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
let agentesEdicionId = null;

function editaragentes(id, razon_social, ig_agente, rut_empresa, fono_empresa, pais, direccion, region, comuna) {
    document.getElementById('razon_social').value = razon_social;
    document.getElementById('ig_agente').value = ig_agente;
    document.getElementById('rut_empresa').value = rut_empresa;
    document.getElementById('fono_empresa').value = fono_empresa;
    document.getElementById('pais').value = pais;
    document.getElementById('direccion').value = direccion;
    document.getElementById('region').value = region;
    document.getElementById('comuna').value = comuna;
    
    agentesEdicionId = id;
    document.getElementById('btn-guardar-agentes').textContent = 'Actualizar';
    document.getElementById('btn-guardar-agentes').name = 'update';
    document.getElementById('btn-cancelar-edicion').style.display = 'inline-block';
    
    let hidden = document.getElementById('agentes_id_hidden');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.id = 'agentes_id_hidden';
        hidden.name = 'agentes_id';
        document.getElementById('form-agentes').appendChild(hidden);
    }
    hidden.value = id;
}

function cancelarEdicion() {
    document.getElementById('razon_social').value = '';
    document.getElementById('ig_agente').value = '';
    document.getElementById('rut_empresa').value = '';
    document.getElementById('fono_empresa').value = '';
    document.getElementById('pais').value = '';
    document.getElementById('direccion').value = '';
    document.getElementById('region').value = '';
    document.getElementById('comuna').value = '';
    
    document.getElementById('btn-guardar-agentes').textContent = 'Guardar';
    document.getElementById('btn-guardar-agentes').name = 'save';
    document.getElementById('btn-cancelar-edicion').style.display = 'none';
    const hidden = document.getElementById('agentes_id_hidden');
    if (hidden) hidden.remove();
    agentesEdicionId = null;
    warning('Edición cancelada');
}
</script>