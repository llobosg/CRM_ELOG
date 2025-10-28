<!-- Mini consola de depuración -->
<div id="debug-trace" style="margin: 1rem; padding: 0.5rem; background: #f0f8ff; border: 1px solid #87ceeb; border-radius: 4px; font-size: 0.85rem; display: none;"></div>

<!-- ========   Para búsqueda inteligente  ==== -->
<div style="height: 4rem;"></div>
<div style="margin: 1rem 0;">
    <label><i class="fas fa-search"></i> Búsqueda Inteligente v2.1</label>
    <input 
        type="text" 
        id="busqueda-inteligente" 
        placeholder="Buscar por Concatenado, Razón Social, RUT o Comercial..."
        style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px;"
    />
    <div id="resultados-busqueda" style="position: absolute; background: white; border: 1px solid #ddd; border-radius: 8px; max-height: 300px; overflow-y: auto; width: 95%; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.1); display: none;"></div>
</div>

<form method="POST" id="form-prospecto" action="">
    <input type="hidden" name="id_ppl" id="id_ppl" />
    <input type="hidden" name="id_prospect" id="id_prospect" />

    <div class="card" style="margin-bottom: 2rem;">
        <h3 style="margin:0 0 1rem 0; color:#3a4f63; font-size:1.1rem;"><i class="fas fa-user"></i> Datos del Prospecto</h3>
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">RUT Empresa *</label>
            <input type="text" name="rut_empresa" required style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" />
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Razón Social *</label>
            <input type="text" name="razon_social" required style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" />
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Teléfono</label>
            <input type="tel" name="fono_empresa" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" />
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Fecha</label>
            <input type="date" name="fecha_alta" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" value="<?= date('Y-m-d') ?>" />
        </div>
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">País</label>
            <select name="pais" id="pais" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
                <option value="">Seleccionar país</option>
            </select>
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Dirección</label>
            <input type="text" name="direccion" id="direccion" style="grid-column: span 3; width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" />
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Estado</label>
            <select name="estado" id="estado" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
                <option value="Pendiente">Pendiente</option>
                <option value="Enviado">Enviado</option>
                <option value="Devuelto_pendiente">Devuelto_pendiente</option>
                <option value="CerradoOK">CerradoOK</option>
                <option value="Rechazado">Rechazado</option>
            </select>
        </div>
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Operación</label>
            <select name="operacion" id="operacion" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" required>
                <option value="">Seleccionar</option>
            </select>
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Tipo Operación</label>
            <select name="tipo_oper" id="tipo_oper" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" required>
                <option value="">Seleccionar</option>
            </select>
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Concatenado</label>
            <input type="text" name="concatenado" id="concatenado" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; font-weight: bold; box-sizing: border-box;" readonly />
        </div>
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Booking</label>
            <input type="text" name="booking" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" />
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Comercial ID</label>
            <input type="number" name="id_comercial" id="id_comercial" min="1" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" />
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Nombre</label>
            <input type="text" name="nombre" id="nombre" readonly style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; background: #f8f9fa; box-sizing: border-box;" />
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Incoterm</label>
            <input type="text" name="incoterm" style="flex: 1; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" />
        </div>
    </div>

    <div class="card">
        <h3><i class="fas fa-truck"></i> <i class="fas fa-plane"></i> <i class="fas fa-ship"></i> Servicios Asociados</h3>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem;">
            <div style="display: flex; gap: 1rem;">
                <button type="button" class="btn-add" style="font-size: 0.8rem;" onclick="abrirModalServicio()" id="btn-agregar-servicio" disabled>
                    <i class="fas fa-plus"></i> Agregar
                </button>
                <button type="button" class="btn-comment" onclick="abrirModalComercial()" style="font-size: 0.8rem;"><i class="fas fa-comments"></i> Comerciales</button>
                <button type="button" class="btn-comment" onclick="abrirModalOperaciones()" style="font-size: 0.8rem;"><i class="fas fa-clipboard-list"></i> Operaciones</button>
            </div>
            <button type="button" class="btn-primary" id="btn-save-all" style="min-width: 120px; padding: 0.6rem 1rem;">Grabar Todo</button>
        </div>

        <div class="table-container">
            <table id="tabla-servicios">
                <thead>
                    <tr>
                        <th>Servicio</th><th>Tráfico</th><th>Base Cálculo</th><th>Moneda</th><th>Tarifa</th>
                        <th>Costo</th><th>Venta</th><th>GDC</th><th>GDV</th><th>Acción</th>
                    </tr>
                </thead>
                <tbody id="servicios-body"></tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="5" style="text-align: right; font-weight: bold;">Totales:</td>
                        <td id="total-costo">0.00</td>
                        <td id="total-venta">0.00</td>
                        <td id="total-costogasto">0.00</td>
                        <td id="total-ventagasto">0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <input type="hidden" name="servicios_json" id="servicios_json" />
</form>

<!-- Modales -->
<div id="modal-comercial" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 500px;">
        <h3><i class="fas fa-comments"></i> Notas Comerciales</h3>
        <span class="close" onclick="cerrarModalComercial()">&times;</span>
        <textarea id="notas_comerciales_input" rows="6" placeholder="Escribe tus comentarios comerciales..."></textarea>
        <div class="modal-footer">
            <button type="button" onclick="cerrarModalComercial()">Cerrar</button>
            <button type="button" onclick="guardarNotasComerciales()">Guardar</button>
        </div>
    </div>
</div>

<div id="modal-operaciones" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 500px;">
        <h3><i class="fas fa-clipboard-list"></i> Notas Operaciones</h3>
        <span class="close" onclick="cerrarModalOperaciones()">&times;</span>
        <textarea id="notas_operaciones_input" rows="6" placeholder="Escribe tus comentarios de operaciones..."></textarea>
        <div class="modal-footer">
            <button type="button" onclick="cerrarModalOperaciones()">Cerrar</button>
            <button type="button" onclick="guardarNotasOperaciones()">Guardar</button>
        </div>
    </div>
</div>

<!-- Toast de notificaciones -->
<div id="toast" class="toast" style="display:none;">
    <i class="fas fa-info-circle"></i> 
    <span id="toast-message">Mensaje</span>
</div>

<script>
// === VARIABLES GLOBALES ===
let servicios = [];
let estadoProspecto = 'Pendiente';
let tieneServiciosIniciales = false;

// === NOTIFICACIONES ===
function mostrarNotificacion(mensaje, tipo = 'info') {
    const toast = document.getElementById('toast');
    const msg = document.getElementById('toast-message');
    if (!toast || !msg) return;
    msg.textContent = mensaje;
    toast.className = 'toast ' + tipo;
    toast.style.display = 'block';
    setTimeout(() => toast.style.display = 'none', 5000);
}
const exito = (msg) => mostrarNotificacion(msg, 'exito');
const error = (msg) => mostrarNotificacion(msg, 'error');

// === VALIDAR RUT ===
function validarRut(rut) {
    if (!/^(\d{7,8})([0-9K])$/.test(rut)) return false;
    const cuerpo = rut.slice(0, -1);
    const dv = rut.slice(-1).toUpperCase();
    let suma = 0, multiplo = 2;
    for (let i = cuerpo.length - 1; i >= 0; i--) {
        suma += parseInt(cuerpo[i]) * multiplo;
        multiplo = multiplo < 7 ? multiplo + 1 : 2;
    }
    const dvEsperado = (11 - (suma % 11)).toString();
    const dvCalculado = dvEsperado === '11' ? '0' : dvEsperado === '10' ? 'K' : dvEsperado;
    return dv === dvCalculado;
}

// === CARGAR PAÍSES ===
function cargarPaises() {
    const paises = ["Afganistán","Albania","Alemania","Andorra","Angola","Argentina","Australia","Austria","Bélgica","Bolivia","Brasil","Canadá","Chile","China","Colombia","Costa Rica","Cuba","Ecuador","Egipto","España","Estados Unidos","Francia","Alemania","Grecia","Honduras","India","Italia","Japón","México","Perú","Portugal","Reino Unido","Rusia","Suecia","Suiza","Turquía","Uruguay","Venezuela","Zimbabue"];
    const select = document.getElementById('pais');
    if (!select) return;
    select.innerHTML = '<option value="">Seleccionar país</option>';
    paises.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p;
        opt.textContent = p;
        select.appendChild(opt);
    });
}

// === CARGAR OPERACIONES Y TIPOS ===
function cargarOperacionesYTipos() {
    fetch('/api/get_operaciones.php')
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('operacion');
            if (!sel) return;
            sel.innerHTML = '<option value="">Seleccionar</option>';
            (data.operaciones || []).forEach(op => {
                const opt = document.createElement('option');
                opt.value = op;
                opt.textContent = op;
                sel.appendChild(opt);
            });
        });
    document.getElementById('operacion')?.addEventListener('change', function() {
        const op = this.value;
        const tipoSel = document.getElementById('tipo_oper');
        if (!op || !tipoSel) return;
        fetch(`/api/get_tipos_por_operacion.php?operacion=${encodeURIComponent(op)}`)
            .then(r => r.json())
            .then(data => {
                tipoSel.innerHTML = '<option value="">Seleccionar</option>';
                (data.tipos || []).forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t;
                    opt.textContent = t;
                    tipoSel.appendChild(opt);
                });
            });
    });
}

// === CALCULAR CONCATENADO ===
function calcularConcatenado() {
    const op = document.getElementById('operacion')?.value || '';
    const tipo = document.getElementById('tipo_oper')?.value || '';
    if (!op || !tipo) return;
    const hoy = new Date();
    const fecha = hoy.toISOString().slice(2, 10).replace(/-/g, '');
    const id = (parseInt(document.getElementById('id_prospect')?.value || '0') + 1).toString().padStart(2, '0');
    document.getElementById('concatenado').value = `${op}${tipo}${fecha}-${id}`;
}
document.getElementById('operacion')?.addEventListener('change', calcularConcatenado);
document.getElementById('tipo_oper')?.addEventListener('change', calcularConcatenado);

// === ACTUALIZAR TABLA DE SERVICIOS ===
function actualizarTabla() {
    const tbody = document.getElementById('servicios-body');
    if (!tbody) return;
    tbody.innerHTML = '';
    let tc = 0, tv = 0, tgc = 0, tgv = 0;
    servicios.forEach(s => {
        const c = parseFloat(s.costo) || 0;
        const v = parseFloat(s.venta) || 0;
        const gc = parseFloat(s.costogastoslocalesdestino) || 0;
        const gv = parseFloat(s.ventasgastoslocalesdestino) || 0;
        tc += c; tv += v; tgc += gc; tgv += gv;
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${s.servicio}</td><td>${s.trafico}</td><td>${s.base_calculo || ''}</td><td>${s.moneda}</td><td>${(parseFloat(s.tarifa)||0).toFixed(2)}</td><td>${c.toFixed(2)}</td><td>${v.toFixed(2)}</td><td>${gc.toFixed(2)}</td><td>${gv.toFixed(2)}</td><td><button onclick="editar(${servicios.indexOf(s)})">✏️</button> <button onclick="eliminar(${servicios.indexOf(s)})">🗑️</button></td>`;
        tbody.appendChild(tr);
    });
    document.getElementById('total-costo').textContent = tc.toFixed(2);
    document.getElementById('total-venta').textContent = tv.toFixed(2);
    document.getElementById('total-costogasto').textContent = tgc.toFixed(2);
    document.getElementById('total-ventagasto').textContent = tgv.toFixed(2);
}

// === CARGAR PROSPECTO ===
function seleccionarProspecto(id) {
    fetch(`/api/get_prospecto.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.prospecto) return error('Prospecto no encontrado');
            const p = data.prospecto;

            // Campos de texto
            ['razon_social','rut_empresa','fono_empresa','direccion','booking','incoterm','concatenado','fecha_alta','fecha_estado']
            .forEach(f => {
                const el = document.querySelector(`[name="${f}"]`);
                if (el && el.tagName === 'INPUT') el.value = p[f] || '';
            });

            // Comercial
            document.getElementById('id_comercial').value = p.id_comercial || '';
            document.getElementById('nombre').value = p.nombre || '';

            // Estado
            document.getElementById('estado').value = p.estado || 'Pendiente';

            // País
            const paisSel = document.getElementById('pais');
            if (paisSel && p.pais) {
                for (let opt of paisSel.options) {
                    if (opt.value === p.pais) {
                        opt.selected = true;
                        break;
                    }
                }
                if (!paisSel.value) {
                    const opt = document.createElement('option');
                    opt.value = p.pais;
                    opt.textContent = p.pais;
                    paisSel.appendChild(opt);
                    paisSel.value = p.pais;
                }
            }

            // Operación y tipo
            const opSel = document.getElementById('operacion');
            const tipoSel = document.getElementById('tipo_oper');
            if (opSel && p.operacion) {
                opSel.value = p.operacion;
                opSel.dispatchEvent(new Event('change'));
                setTimeout(() => {
                    if (tipoSel && p.tipo_oper) tipoSel.value = p.tipo_oper;
                }, 300);
            }

            // Notas
            const setNota = (name, val) => {
                let inp = document.querySelector(`input[name="${name}"]`);
                if (!inp) {
                    inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = name;
                    document.getElementById('form-prospecto').appendChild(inp);
                }
                inp.value = val || '';
                const modalInp = document.getElementById(`${name}_input`);
                if (modalInp) modalInp.value = val || '';
            };
            setNota('notas_comerciales', p.notas_comerciales);
            setNota('notas_operaciones', p.notas_operaciones);

            // Servicios
            servicios = (data.servicios || []).map(s => ({
                ...s,
                costo: parseFloat(s.costo) || 0,
                venta: parseFloat(s.venta) || 0,
                costogastoslocalesdestino: parseFloat(s.costogastoslocalesdestino) || 0,
                ventasgastoslocalesdestino: parseFloat(s.ventasgastoslocalesdestino) || 0
            }));
            actualizarTabla();

            // Campos ocultos
            document.getElementById('id_ppl').value = p.id_ppl || '';
            document.getElementById('id_prospect').value = p.id_prospect || '';

            exito('✅ Prospecto cargado');
        })
        .catch(err => error('Error al cargar prospecto'));
}

// === MODALES ===
function abrirModalComercial() {
    document.getElementById('modal-comercial').style.display = 'block';
}
function cerrarModalComercial() {
    document.getElementById('modal-comercial').style.display = 'none';
}
function abrirModalOperaciones() {
    document.getElementById('modal-operaciones').style.display = 'block';
}
function cerrarModalOperaciones() {
    document.getElementById('modal-operaciones').style.display = 'none';
}

// === GUARDAR NOTAS ===
function guardarNotasComerciales() {
    const id = document.getElementById('id_ppl')?.value;
    if (!id || id === '0') return error('Prospecto no válido');
    const val = document.getElementById('notas_comerciales_input').value.trim();
    fetch('/api/guardar_nota.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id_ppl: id, campo: 'notas_comerciales', valor: val})
    }).then(r => r.json()).then(d => {
        if (d.success) exito('Notas guardadas');
        else error(d.message || 'Error');
        cerrarModalComercial();
    });
}
function guardarNotasOperaciones() {
    const id = document.getElementById('id_ppl')?.value;
    if (!id || id === '0') return error('Prospecto no válido');
    const val = document.getElementById('notas_operaciones_input').value.trim();
    fetch('/api/guardar_nota.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id_ppl: id, campo: 'notas_operaciones', valor: val})
    }).then(r => r.json()).then(d => {
        if (d.success) exito('Notas guardadas');
        else error(d.message || 'Error');
        cerrarModalOperaciones();
    });
}

// === BÚSQUEDA INTELIGENTE ===
document.getElementById('busqueda-inteligente')?.addEventListener('input', async function() {
    const term = this.value.trim();
    const div = document.getElementById('resultados-busqueda');
    div.style.display = 'none';
    if (!term) return;
    try {
        const res = await fetch(`/api/buscar_inteligente.php?term=${encodeURIComponent(term)}`);
        const data = await res.json();
        div.innerHTML = '';
        if (data.length > 0) {
            data.forEach(p => {
                const d = document.createElement('div');
                d.style.padding = '0.8rem';
                d.style.cursor = 'pointer';
                d.innerHTML = `<strong>${p.razon_social}</strong><br><small>ID: ${p.concatenado} | RUT: ${p.rut_empresa}</small>`;
                d.onclick = () => {
                    seleccionarProspecto(p.id_ppl);
                    div.style.display = 'none';
                    this.value = '';
                };
                div.appendChild(d);
            });
            div.style.display = 'block';
        }
    } catch (e) {
        error('Error en búsqueda');
    }
});

// === GRABAR TODO ===
document.getElementById('btn-save-all')?.addEventListener('click', function() {
    const rut = document.querySelector('input[name="rut_empresa"]').value.trim();
    const razon = document.querySelector('input[name="razon_social"]').value.trim();
    if (!rut || !razon) return error('RUT y Razón Social son obligatorios');
    const rutLimpio = rut.replace(/\./g, '').replace('-', '').toUpperCase();
    if (!validarRut(rutLimpio)) return error('RUT inválido');

    const form = document.getElementById('form-prospecto');
    const modo = servicios.length > 0 ? 'servicios' : 'prospecto';
    let inp = form.querySelector('input[name="modo"]');
    if (!inp) {
        inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'modo';
        form.appendChild(inp);
    }
    inp.value = modo;
    if (modo === 'servicios') {
        inp = form.querySelector('input[name="servicios_json"]');
        if (!inp) {
            inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'servicios_json';
            form.appendChild(inp);
        }
        inp.value = JSON.stringify(servicios);
    }
    form.submit();
});

// === INICIALIZAR ===
document.addEventListener('DOMContentLoaded', () => {
    cargarPaises();
    cargarOperacionesYTipos();
    document.getElementById('btn-save-all').textContent = 'Grabar Todo';
});
</script>