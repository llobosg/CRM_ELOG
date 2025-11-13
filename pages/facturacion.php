<?php
// ✅ NO hay validación de rol aquí → se hace en index.php
// ✅ Este archivo SOLO contiene HTML/JS, sin header() ni redirección
?>
<div class="card">
    <h3><i class="fas fa-file-invoice-dollar"></i> Módulo de Facturación</h3>
    <p style="margin-bottom: 1.5rem; color: #666;">
        Gestione las facturas de prospectos cerrados exitosamente.
    </p>

    <!-- Filtro -->
    <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; align-items: end;">
        <div style="flex: 1; min-width: 200px;">
            <label>Buscar por Concatenado, RUT o Razón Social</label>
            <input type="text" id="busqueda-facturacion" placeholder="Ej: IMEX250415-01, 12345678-9, Cliente SA" 
                   style="width: 100%; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px;" />
        </div>
        <button type="button" class="btn-primary" id="btn-buscar-facturacion">
            <i class="fas fa-search"></i> Buscar
        </button>
    </div>

    <!-- Tabla -->
    <div class="table-container">
        <table id="tabla-prospectos-cerrados">
            <thead>
                <tr>
                    <th>Concatenado</th>
                    <th>Razón Social</th>
                    <th>RUT</th>
                    <th>Total Venta</th>
                    <th>Fecha Alta</th>
                    <th>N° Factura</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="prospectos-cerrados-body"></tbody>
        </table>
    </div>
</div>

<!-- Modal: Factura -->
<div id="modal-factura" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 750px; width: 95%;">
        <h3><i class="fas fa-edit"></i> <span id="titulo-modal-factura">Registrar Factura</span></h3>
        <span class="close" onclick="cerrarModalFactura()" style="cursor:pointer; float:right; font-size:1.8rem;">&times;</span>
        
        <input type="hidden" id="id_ppl_factura" />
        <input type="hidden" id="rut_cliente_factura" />

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 1.2rem 0;">
            <div><strong>Concatenado:</strong> <span id="concatenado_factura">-</span></div>
            <div><strong>Razón Social:</strong> <span id="razon_social_factura">-</span></div>
            <div><strong>Total Venta:</strong> <span id="total_venta_factura">-</span></div>
            <div><strong>Estado Actual:</strong> <span id="estado_factura_actual">-</span></div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 1.2rem 0;">
            <div>
                <label>N° Factura *</label>
                <input type="text" id="numero_factura" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" />
            </div>
            <div>
                <label>Fecha Emisión *</label>
                <input type="date" id="fecha_emision" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" 
                       value="<?= date('Y-m-d') ?>" />
            </div>
            <div>
                <label>Fecha Vencimiento *</label>
                <input type="date" id="fecha_vencimiento" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" />
            </div>
            <div>
                <label>Estado</label>
                <select id="estado_factura" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="emitida">Emitida</option>
                    <option value="vencida">Vencida</option>
                    <option value="pagada">Pagada</option>
                </select>
            </div>
        </div>

        <div style="margin: 1rem 0;">
            <label>Notas (opcional)</label>
            <textarea id="notas_factura" rows="3" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;"></textarea>
        </div>

        <div style="text-align: right; margin-top: 1.5rem; display: flex; gap: 0.8rem; justify-content: flex-end;">
            <button type="button" class="btn-secondary" onclick="cerrarModalFactura()">Cancelar</button>
            <button type="button" class="btn-primary" id="btn-guardar-factura">Guardar Factura</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast" class="toast" style="display:none;">
    <i class="fas fa-info-circle"></i> 
    <span id="toast-message">Mensaje</span>
</div>

<script>
let prospectosCerrados = [];

function mostrarNotificacion(mensaje, tipo = 'info') {
    const toast = document.getElementById('toast');
    const msg = document.getElementById('toast-message');
    if (!toast || !msg) return;
    msg.textContent = mensaje;
    toast.className = 'toast ' + (tipo === 'exito' ? 'success' : tipo);
    toast.style.display = 'flex';
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.style.display = 'none', 400);
    }, 5000);
}
function exito(msg) { mostrarNotificacion(msg, 'exito'); }
function error(msg) { mostrarNotificacion(msg, 'error'); }

function cargarProspectosCerrados() {
    fetch('/api/get_prospectos_cerrados.php')
        .then(r => r.json())
        .then(data => {
            prospectosCerrados = data.prospectos || [];
            actualizarTablaProspectos();
        })
        .catch(() => error('Error al cargar prospectos cerrados'));
}

function actualizarTablaProspectos(filtro = '') {
    const tbody = document.getElementById('prospectos-cerrados-body');
    if (!tbody) return;
    
    const filtrados = prospectosCerrados.filter(p => 
        p.concatenado.toLowerCase().includes(filtro) ||
        p.rut_empresa.toLowerCase().includes(filtro) ||
        p.razon_social.toLowerCase().includes(filtro)
    );

    tbody.innerHTML = filtrados.map(p => `
        <tr>
            <td>${p.concatenado}</td>
            <td>${p.razon_social}</td>
            <td>${p.rut_empresa}</td>
            <td>$${parseFloat(p.total_venta).toLocaleString('es-CL', { minimumFractionDigits: 2 })}</td>
            <td>${p.fecha_alta}</td>
            <td>${p.factura_numero || '—'}</td>
            <td>
                <span style="color: ${
                    p.estado_factura === 'pagada' ? '#009966' : 
                    p.estado_factura === 'vencida' ? '#ff9900' : '#0066cc'
                };">
                    ${p.estado_factura || 'Sin factura'}
                </span>
            </td>
            <td>
                <button type="button" class="btn-comment" onclick="abrirModalFactura(
                    ${p.id_ppl}, 
                    '${p.concatenado}', 
                    '${p.rut_empresa.replace(/'/g, "\\'")}', 
                    '${p.razon_social.replace(/'/g, "\\'")}', 
                    ${p.total_venta}, 
                    '${p.estado_factura || ''}', 
                    '${(p.factura_numero || '').replace(/'/g, "\\'")}'
                )">
                    ${p.id_factura ? 'Editar' : 'Facturar'}
                </button>
            </td>
        </tr>
    `).join('');
}

function abrirModalFactura(id_ppl, concatenado, rut, razon, total, estadoActual, numFactura) {
    document.getElementById('id_ppl_factura').value = id_ppl;
    document.getElementById('rut_cliente_factura').value = rut;
    document.getElementById('concatenado_factura').textContent = concatenado;
    document.getElementById('razon_social_factura').textContent = razon;
    document.getElementById('total_venta_factura').textContent = '$' + parseFloat(total).toLocaleString('es-CL', { minimumFractionDigits: 2 });
    document.getElementById('estado_factura_actual').textContent = estadoActual || 'Sin factura';
    document.getElementById('numero_factura').value = numFactura || '';
    document.getElementById('estado_factura').value = estadoActual || 'emitida';
    document.getElementById('notas_factura').value = '';
    
    document.getElementById('titulo-modal-factura').textContent = numFactura ? 'Editar Factura' : 'Registrar Factura';
    document.getElementById('modal-factura').style.display = 'block';
}

function cerrarModalFactura() {
    document.getElementById('modal-factura').style.display = 'none';
}

document.getElementById('btn-guardar-factura').addEventListener('click', function() {
    const id_ppl = document.getElementById('id_ppl_factura').value;
    const numero = document.getElementById('numero_factura').value.trim();
    const emision = document.getElementById('fecha_emision').value;
    const vencimiento = document.getElementById('fecha_vencimiento').value;
    const estado = document.getElementById('estado_factura').value;
    const notas = document.getElementById('notas_factura').value.trim();
    const rut = document.getElementById('rut_cliente_factura').value;

    if (!numero || !emision || !vencimiento) {
        return error('N° Factura, Fecha Emisión y Fecha Vencimiento son obligatorios');
    }
    if (new Date(vencimiento) < new Date(emision)) {
        return error('La fecha de vencimiento no puede ser anterior a la de emisión');
    }

    fetch('/api/guardar_factura.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_ppl, numero, emision, vencimiento, estado, notas, rut })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            exito(data.message);
            cerrarModalFactura();
            cargarProspectosCerrados();
        } else {
            error(data.message || 'Error al guardar la factura');
        }
    })
    .catch(() => error('Error de conexión'));
});

document.getElementById('btn-buscar-facturacion')?.addEventListener('click', function() {
    const term = document.getElementById('busqueda-facturacion').value.toLowerCase().trim();
    actualizarTablaProspectos(term);
});

document.addEventListener('DOMContentLoaded', cargarProspectosCerrados);
</script>