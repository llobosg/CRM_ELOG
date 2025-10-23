<?php
$registros = $pdo->query("SELECT id_ctco, nom_contacto, fono_contacto, email, cargo, tipo FROM contactos ORDER BY nom_contacto")->fetchAll();
?>

<h2 class="section-title"><i class="fas fa-address-book"></i> Contactos</h2>

<div class="card">
    <form method="POST" id="form-contactos">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.2rem;">
            <div class="form-group">
                <label>Nombre de Contacto *</label>
                <input type="text" name="nom_contacto" id="nom_contacto" required style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="fono_contacto" id="fono_contacto" style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="email" style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Cargo</label>
                <input type="text" name="cargo" id="cargo" style="width: 100%;" />
            </div>
            <div class="form-group">
                <label>Tipo</label>
                <input type="text" name="tipo" id="tipo" style="width: 100%;" />
            </div>
        </div>
        <button type="submit" id="btn-guardar-contactos" name="save" class="btn-primary">Guardar</button>
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
                    <th style="padding: 0.6rem; text-align: left;">Teléfono</th>
                    <th style="padding: 0.6rem; text-align: left;">Email</th>
                    <th style="padding: 0.6rem; text-align: left;">Cargo</th>
                    <th style="padding: 0.6rem; text-align: center; width: 10%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.6rem; font-weight: bold;"><?= htmlspecialchars($r['nom_contacto']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['fono_contacto']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['email']) ?></td>
                    <td style="padding: 0.6rem;"><?= htmlspecialchars($r['cargo']) ?></td>
                    <td style="padding: 0.6rem; text-align: center;">
                        <a href="#" 
                           onclick="editarcontactos(<?= $r['id_ctco'] ?>, '<?= addslashes($r['nom_contacto']) ?>', '<?= addslashes($r['fono_contacto']) ?>', '<?= addslashes($r['email']) ?>', '<?= addslashes($r['cargo']) ?>', '<?= addslashes($r['tipo']) ?>')"
                           class="btn-edit" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none; margin-right: 0.5rem;">
                            ✏️
                        </a>
                        <a href="index.php?page=contactos&delete=<?= $r['id_ctco'] ?>" 
                           class="btn-delete" 
                           style="padding: 0.3rem 0.6rem; font-size: 0.85rem; text-decoration: none;"
                           onclick="return confirm('¿Eliminar este contacto?')">
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
let contactosEdicionId = null;

function editarcontactos(id, nom_contacto, fono_contacto, email, cargo, tipo) {
    document.getElementById('nom_contacto').value = nom_contacto;
    document.getElementById('fono_contacto').value = fono_contacto;
    document.getElementById('email').value = email;
    document.getElementById('cargo').value = cargo;
    document.getElementById('tipo').value = tipo;
    
    contactosEdicionId = id;
    document.getElementById('btn-guardar-contactos').textContent = 'Actualizar';
    document.getElementById('btn-guardar-contactos').name = 'update';
    document.getElementById('btn-cancelar-edicion').style.display = 'inline-block';
    
    let hidden = document.getElementById('contactos_id_hidden');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.id = 'contactos_id_hidden';
        hidden.name = 'contactos_id';
        document.getElementById('form-contactos').appendChild(hidden);
    }
    hidden.value = id;
}

function cancelarEdicion() {
    document.getElementById('nom_contacto').value = '';
    document.getElementById('fono_contacto').value = '';
    document.getElementById('email').value = '';
    document.getElementById('cargo').value = '';
    document.getElementById('tipo').value = '';
    
    document.getElementById('btn-guardar-contactos').textContent = 'Guardar';
    document.getElementById('btn-guardar-contactos').name = 'save';
    document.getElementById('btn-cancelar-edicion').style.display = 'none';
    const hidden = document.getElementById('contactos_id_hidden');
    if (hidden) hidden.remove();
    contactosEdicionId = null;
    warning('Edición cancelada');
}
</script>