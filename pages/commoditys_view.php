<?php
$registros = $pdo->query("SELECT id_comm, commodity, cod_comm FROM commodity ORDER BY commodity")->fetchAll();
?>

<h2 class="section-title"><i class="fas fa-boxes"></i> Commoditys</h2>

<div class="card">
    <form method="POST" id="form-commoditys">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.2rem;">
            <div class="form-group">
                <label>Commodity *</label>
                <input type="text" name="commodity" id="commodity" required style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Código</label>
                <input type="text" name="cod_comm" id="cod_comm" style="width: 100%;" />
            </div>
        </div>
        <button type="submit" id="btn-guardar-commoditys" name="save" class="btn-primary">Guardar</button>
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
                    <th style="padding: 0.6rem; text-align: left;">Commodity</th>
                    <th style="padding: 0.6rem; text-align: left;">Código</th>
                    <th style="padding: 0.6rem; text-align: center; width: 10%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.6rem; font-weight: bold;"><?= htmlspecialchars($r['commodity']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['cod_comm']) ?></td>
                    <td style="padding: 0.6rem; text-align: center;">
                        <a href="#" 
                           onclick="editarcommoditys(<?= $r['id_comm'] ?>, '<?= addslashes($r['commodity']) ?>', '<?= addslashes($r['cod_comm']) ?>')"
                           class="btn-edit" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none; margin-right: 0.5rem;">
                            ✏️
                        </a>
                        <a href="index.php?page=commoditys&delete=<?= $r['id_comm'] ?>" 
                           class="btn-delete" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none;"
                           onclick="return confirm('¿Eliminar este commodity?')">
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
let commoditysEdicionId = null;

function editarcommoditys(id, commodity, cod_comm) {
    document.getElementById('commodity').value = commodity;
    document.getElementById('cod_comm').value = cod_comm;
    
    commoditysEdicionId = id;
    document.getElementById('btn-guardar-commoditys').textContent = 'Actualizar';
    document.getElementById('btn-guardar-commoditys').name = 'update';
    document.getElementById('btn-cancelar-edicion').style.display = 'inline-block';
    
    let hidden = document.getElementById('commoditys_id_hidden');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.id = 'commoditys_id_hidden';
        hidden.name = 'commoditys_id';
        document.getElementById('form-commoditys').appendChild(hidden);
    }
    hidden.value = id;
}

function cancelarEdicion() {
    document.getElementById('commodity').value = '';
    document.getElementById('cod_comm').value = '';
    
    document.getElementById('btn-guardar-commoditys').textContent = 'Guardar';
    document.getElementById('btn-guardar-commoditys').name = 'save';
    document.getElementById('btn-cancelar-edicion').style.display = 'none';
    const hidden = document.getElementById('commoditys_id_hidden');
    if (hidden) hidden.remove();
    commoditysEdicionId = null;
    warning('Edición cancelada');
}
</script>