<!-- Mini consola de depuración -->
<div id="debug-trace" style="margin: 1rem; padding: 0.5rem; background: #f0f8ff; border: 1px solid #87ceeb; border-radius: 4px; font-size: 0.85rem; display: none;"></div>

<!-- Búsqueda inteligente -->
<div style="height: 4rem;"></div>
<div style="margin: 1rem 0;">
    <label><i class="fas fa-search"></i> Búsqueda Inteligente</label>
    <input type="text" id="busqueda-inteligente" placeholder="Buscar por Concatenado, Razón Social, RUT..." style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px;" />
    <div id="resultados-busqueda" style="position: absolute; background: white; border: 1px solid #ddd; border-radius: 8px; max-height: 300px; overflow-y: auto; width: 95%; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.1); display: none;"></div>
</div>

<form method="POST" id="form-prospecto" action="">
    <input type="hidden" name="id_ppl" id="id_ppl" />
    <input type="hidden" name="id_prospect" id="id_prospect" />

    <div class="card" style="margin-bottom: 2rem;">
        <h3><i class="fas fa-user"></i> Datos del Prospecto</h3>
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <label>RUT Empresa *</label>
            <input type="text" name="rut_empresa" required style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" />
            <label>Razón Social *</label>
            <input type="text" name="razon_social" required style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" />
            <label>Teléfono</label>
            <input type="tel" name="fono_empresa" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" />
            <label>Fecha</label>
            <input type="date" name="fecha_alta" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" value="<?= date('Y-m-d') ?>" />
        </div>
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <label>País</label>
            <select name="pais" id="pais" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;">
                <option value="">Seleccionar país</option>
            </select>
            <label>Dirección</label>
            <input type="text" name="direccion" id="direccion" style="grid-column: span 3; width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" />
            <label>Estado</label>
            <select name="estado" id="estado" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;">
                <option value="Pendiente">Pendiente</option>
                <option value="Enviado">Enviado</option>
                <option value="Devuelto_pendiente">Devuelto_pendiente</option>
                <option value="CerradoOK">CerradoOK</option>
                <option value="Rechazado">Rechazado</option>
            </select>
        </div>
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <label>Operación</label>
            <select name="operacion" id="operacion" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" required>
                <option value="">Seleccionar</option>
            </select>
            <label>Tipo Operación</label>
            <select name="tipo_oper" id="tipo_oper" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" required>
                <option value="">Seleccionar</option>
            </select>
            <label>Concatenado</label>
            <input type="text" name="concatenado" id="concatenado" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-weight: bold; box-sizing: border-box;" readonly />
        </div>
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <label>Booking</label>
            <input type="text" name="booking" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" />
            <label>Comercial ID</label>
            <input type="number" name="id_comercial" id="id_comercial" min="1" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" />
            <label>Nombre</label>
            <input type="text" name="nombre" id="nombre" readonly style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; background: #f8f9fa; box-sizing: border-box;" />
            <label>Incoterm</label>
            <input type="text" name="incoterm" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" />
        </div>
    </div>

    <div class="card">
        <h3><i class="fas fa-truck"></i> Servicios Asociados</h3>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem;">
            <div style="display: flex; gap: 1rem;">
                <button type="button" class="btn-add" id="btn-agregar-servicio" disabled>
                    <i class="fas fa-plus"></i> Agregar
                </button>
                <button type="button" class="btn-comment" onclick="abrirModalComercial()"><i class="fas fa-comments"></i> Comerciales</button>
                <button type="button" class="btn-comment" onclick="abrirModalOperaciones()"><i class="fas fa-clipboard-list"></i> Operaciones</button>
            </div>
            <button type="button" class="btn-primary" id="btn-save-all">Grabar Todo</button>
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
        <textarea id="notas_comerciales_input" rows="6" placeholder="..."></textarea>
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
        <textarea id="notas_operaciones_input" rows="6" placeholder="..."></textarea>
        <div class="modal-footer">
            <button type="button" onclick="cerrarModalOperaciones()">Cerrar</button>
            <button type="button" onclick="guardarNotasOperaciones()">Guardar</button>
        </div>
    </div>
</div>

<div id="modal-servicio" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 1500px; width: 95%;">
        <h3><i class="fas fa-box"></i> Agregar Servicio para <span id="serv_titulo_concatenado">-</span></h3>
        <span class="close" onclick="cerrarModalServicio()">&times;</span>
        <input type="hidden" id="id_prospect_serv" name="id_prospect_serv" />
        <input type="hidden" id="concatenado_serv" name="concatenado_serv" />
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 0.8rem; margin-top: 1.2rem; align-items: center;">
            <label>Servicio</label>
            <input type="text" id="serv_servicio" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
            <label>Medio Transporte</label>
            <select id="serv_medio_transporte" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                <option value="">Seleccionar</option>
            </select>
            <label>Commodity</label>
            <select id="serv_commodity" style="grid-column: span 3; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                <option value="">Seleccionar</option>
            </select>
            <label>Origen</label>
            <select id="serv_origen" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                <option value="">Seleccionar</option>
            </select>
            <label>País Origen</label>
            <input type="text" id="serv_pais_origen" readonly style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; background: #f9f9f9;" />
            <label>Destino</label>
            <select id="serv_destino" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                <option value="">Seleccionar</option>
            </select>
            <label>País Destino</label>
            <input type="text" id="serv_pais_destino" readonly style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; background: #f9f9f9;" />
            <label>Tránsito</label>
            <input type="text" id="serv_transito" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
            <label>Frecuencia</label>
            <input type="text" id="serv_frecuencia" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
            <label>Lugar Carga</label>
            <input type="text" id="serv_lugar_carga" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
            <label>Sector</label>
            <input type="text" id="serv_sector" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
            <label>Mercancía</label>
            <input type="text" id="serv_mercancia" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
            <label>Bultos</label>
            <input type="number" id="serv_bultos" min="1" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
            <label>Peso (kg)</label>
            <input type="number" id="serv_peso" step="0.01" min="0" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
            <label>Volumen (m³)</label>
            <input type="number" id="serv_volumen" step="0.01" min="0" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
            <label>Dimensiones</label>
            <input type="text" id="serv_dimensiones" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" placeholder="Ej: 120x80x90 cm" />
            <label>Moneda</label>
            <select id="serv_moneda" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                <option value="USD">USD</option>
                <option value="EUR">EUR</option>
                <option value="CLP">CLP</option>
            </select>
            <label>Tipo Cambio</label>
            <input type="number" id="serv_tipo_cambio" step="0.01" min="0" value="1" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
            <label>Proveedor Nac</label>
            <select id="serv_proveedor_nac" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                <option value="">Seleccionar</option>
            </select>
            <label>AOL</label>
            <input type="text" id="serv_aol" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" maxlength="4" />
            <label>AOD</label>
            <input type="text" id="serv_aod" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" maxlength="4" />
            <label>Desconsolidación</label>
            <input type="text" id="serv_desconsolidacion" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
            <label>Agente</label>
            <select id="serv_agente" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                <option value="">Seleccionar</option>
            </select>
            <label>Aerolínea</label>
            <input type="text" id="serv_aerolinea" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
            <label>Terrestre</label>
            <input type="text" id="serv_terrestre" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
            <label>Marítimo</label>
            <input type="text" id="serv_maritimo" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
            <label>Ref. Cliente</label>
            <input type="text" id="serv_ref_cliente" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
        </div>
        <div class="modal-footer" style="text-align: right; margin-top: 1.5rem; gap: 0.8rem;">
            <button type="button" class="btn-comment" id="btn-costos-servicio"><i class="fas fa-calculator"></i> Costos - Ventas</button>
            <button type="button" class="btn-comment" id="btn-gastos-locales"><i class="fas fa-file-invoice-dollar"></i> Gastos Locales</button>
            <button type="button" class="btn-secondary" onclick="cerrarModalServicioConConfirmacion()">Volver</button>
            <button type="button" class="btn-add" onclick="guardarServicio()">Agregar Servicio</button>
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
let costosServicio = [];
let gastosLocales = [];
let servicioEnEdicion = null;
let tieneServiciosIniciales = false;
let estadoProspecto = 'Pendiente';

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
    const paises = ["Chile", "Argentina", "Perú", "Colombia", "México", "Estados Unidos", "España"];
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
        tr.innerHTML = `<td>${s.servicio}</td><td>${s.trafico}</td><td>${s.base_calculo || ''}</td><td>${s.moneda}</td><td>${(parseFloat(s.tarifa)||0).toFixed(2)}</td><td>${c.toFixed(2)}</td><td>${v.toFixed(2)}</td><td>${gc.toFixed(2)}</td><td>${gv.toFixed(2)}</td><td><button onclick="editarServicio(${servicios.indexOf(s)})">✏️</button> <button onclick="eliminarServicio(${servicios.indexOf(s)})">🗑️</button></td>`;
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
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.prospecto) return error('Prospecto no encontrado');
            const p = data.prospecto;

            // Campos de texto
            ['razon_social','rut_empresa','fono_empresa','direccion','booking','incoterm','concatenado','fecha_alta','fecha_estado'].forEach(f => {
                const el = document.querySelector(`[name="${f}"]`);
                if (el && el.tagName === 'INPUT') el.value = p[f] || '';
            });

            // Comercial
            document.getElementById('id_comercial').value = p.id_comercial || '';
            document.getElementById('nombre').value = p.nombre || '';

            // Estado
            document.getElementById('estado').value = p.estado || 'Pendiente';

            // País (select)
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
                fetch(`/api/get_tipos_por_operacion.php?operacion=${encodeURIComponent(p.operacion)}`)
                    .then(r => r.json())
                    .then(data => {
                        tipoSel.innerHTML = '<option value="">Seleccionar</option>';
                        (data.tipos || []).forEach(t => {
                            const opt = document.createElement('option');
                            opt.value = t;
                            opt.textContent = t;
                            tipoSel.appendChild(opt);
                        });
                        if (p.tipo_oper) tipoSel.value = p.tipo_oper;
                    });
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
                document.getElementById(`${name}_input`).value = val || '';
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
            tieneServiciosIniciales = servicios.length > 0;
            actualizarTabla();

            // Campos ocultos
            document.getElementById('id_ppl').value = p.id_ppl || '';
            document.getElementById('id_prospect').value = p.id_prospect || '';

            // Regla de negocio
            const inputs = document.querySelectorAll('input:not([type="hidden"]):not([name="concatenado"])');
            const selects = document.querySelectorAll('select');
            const esEditable = !tieneServiciosIniciales;
            inputs.forEach(input => {
                input.readOnly = !esEditable;
                input.style.backgroundColor = esEditable ? '' : '#f9f9f9';
            });
            selects.forEach(select => {
                select.disabled = !esEditable;
            });

            // Botones
            document.getElementById('btn-agregar-servicio').disabled = !esEditable;
            document.getElementById('btn-save-all').textContent = tieneServiciosIniciales ? 'Grabar Todo' : 'Actualizar';
        });
}

// === MODALES ===
function abrirModalComercial() { document.getElementById('modal-comercial').style.display = 'block'; }
function cerrarModalComercial() { document.getElementById('modal-comercial').style.display = 'none'; }
function abrirModalOperaciones() { document.getElementById('modal-operaciones').style.display = 'block'; }
function cerrarModalOperaciones() { document.getElementById('modal-operaciones').style.display = 'none'; }

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

// === SERVICIOS ===
function editarServicio(index) {
    servicioEnEdicion = index;
    abrirModalServicio(index);
}
function eliminarServicio(index) {
    servicios.splice(index, 1);
    actualizarTabla();
}
function abrirModalServicio(index = null) {
    const idPpl = document.getElementById('id_ppl')?.value;
    const concatenado = document.getElementById('concatenado')?.value;
    if (!idPpl || !concatenado) return error('Guarde el prospecto primero');
    document.getElementById('id_prospect_serv').value = idPpl;
    document.getElementById('concatenado_serv').value = concatenado;
    document.getElementById('serv_titulo_concatenado').textContent = concatenado;
    if (index !== null) {
        servicioEnEdicion = index;
        const s = servicios[index];
        costosServicio = Array.isArray(s.costos) ? [...s.costos] : [];
        gastosLocales = Array.isArray(s.gastos_locales) ? [...s.gastos_locales] : [];
        // Rellenar después de cargar datos
        cargarDatosModalServicio(() => {
            document.getElementById('serv_servicio').value = s.servicio || '';
            document.getElementById('serv_medio_transporte').value = s.trafico || '';
            document.getElementById('serv_commodity').value = s.commodity || '';
            document.getElementById('serv_origen').value = s.origen || '';
            document.getElementById('serv_pais_origen').value = s.pais_origen || '';
            document.getElementById('serv_destino').value = s.destino || '';
            document.getElementById('serv_pais_destino').value = s.pais_destino || '';
            document.getElementById('serv_transito').value = s.transito || '';
            document.getElementById('serv_frecuencia').value = s.frecuencia || '';
            document.getElementById('serv_lugar_carga').value = s.lugar_carga || '';
            document.getElementById('serv_sector').value = s.sector || '';
            document.getElementById('serv_mercancia').value = s.mercancia || '';
            document.getElementById('serv_bultos').value = s.bultos || '';
            document.getElementById('serv_peso').value = s.peso || '';
            document.getElementById('serv_volumen').value = s.volumen || '';
            document.getElementById('serv_dimensiones').value = s.dimensiones || '';
            document.getElementById('serv_moneda').value = s.moneda || 'CLP';
            document.getElementById('serv_tipo_cambio').value = s.tipo_cambio || 1;
            document.getElementById('serv_proveedor_nac').value = s.proveedor_nac || '';
            document.getElementById('serv_ref_cliente').value = s.ref_cliente || '';
            document.getElementById('serv_desconsolidacion').value = s.desconsolidac || '';
            document.getElementById('serv_aol').value = s.aol || '';
            document.getElementById('serv_aod').value = s.aod || '';
            document.getElementById('serv_agente').value = s.agente || '';
            document.getElementById('serv_aerolinea').value = s.aerolinea || '';
            document.getElementById('serv_terrestre').value = s.terrestre || '';
            document.getElementById('serv_maritimo').value = s.naviera || '';
        });
    } else {
        servicioEnEdicion = null;
        costosServicio = [];
        gastosLocales = [];
        cargarDatosModalServicio();
    }
    document.getElementById('modal-servicio').style.display = 'flex';
}
function cerrarModalServicio() {
    document.getElementById('modal-servicio').style.display = 'none';
}
function cerrarModalServicioConConfirmacion() {
    if (confirm('¿Desea cancelar sin guardar los cambios?')) {
        cerrarModalServicio();
    }
}
function guardarServicio() {
    const servicio = document.getElementById('serv_servicio').value.trim();
    if (!servicio) return error('Servicio es obligatorio');
    const nuevo = {
        id_srvc: servicioEnEdicion !== null ? servicios[servicioEnEdicion].id_srvc : `TEMP_${Date.now()}`,
        id_prospect: document.getElementById('id_prospect_serv').value,
        servicio: servicio,
        trafico: document.getElementById('serv_medio_transporte').value,
        commodity: document.getElementById('serv_commodity').value,
        origen: document.getElementById('serv_origen').value,
        pais_origen: document.getElementById('serv_pais_origen').value,
        destino: document.getElementById('serv_destino').value,
        pais_destino: document.getElementById('serv_pais_destino').value,
        transito: document.getElementById('serv_transito').value,
        frecuencia: document.getElementById('serv_frecuencia').value,
        lugar_carga: document.getElementById('serv_lugar_carga').value,
        sector: document.getElementById('serv_sector').value,
        mercancia: document.getElementById('serv_mercancia').value,
        bultos: document.getElementById('serv_bultos').value,
        peso: document.getElementById('serv_peso').value,
        volumen: document.getElementById('serv_volumen').value,
        dimensiones: document.getElementById('serv_dimensiones').value,
        moneda: document.getElementById('serv_moneda').value,
        tipo_cambio: document.getElementById('serv_tipo_cambio').value,
        proveedor_nac: document.getElementById('serv_proveedor_nac').value,
        desconsolidac: document.getElementById('serv_desconsolidacion').value,
        aol: document.getElementById('serv_aol').value,
        aod: document.getElementById('serv_aod').value,
        agente: document.getElementById('serv_agente').value,
        aerolinea: document.getElementById('serv_aerolinea').value,
        terrestre: document.getElementById('serv_terrestre').value,
        naviera: document.getElementById('serv_maritimo').value,
        ref_cliente: document.getElementById('serv_ref_cliente').value,
        costo: costosServicio.reduce((sum, c) => sum + (c.total_costo || 0), 0),
        venta: costosServicio.reduce((sum, c) => sum + (c.total_tarifa || 0), 0),
        costogastoslocalesdestino: gastosLocales.filter(g => g.tipo === 'Costo').reduce((sum, g) => sum + (g.monto || 0), 0),
        ventasgastoslocalesdestino: gastosLocales.filter(g => g.tipo === 'Ventas').reduce((sum, g) => sum + (g.monto || 0), 0),
        costos: [...costosServicio],
        gastos_locales: [...gastosLocales]
    };
    if (servicioEnEdicion !== null) {
        servicios[servicioEnEdicion] = nuevo;
        exito('Servicio actualizado');
    } else {
        servicios.push(nuevo);
        exito('Servicio agregado');
    }
    actualizarTabla();
    cerrarModalServicio();
}

// === CARGAR DATOS DEL MODAL DE SERVICIO ===
function cargarDatosModalServicio(callback = null) {
    let cargas = 0;
    const total = 5;

    const check = () => {
        cargas++;
        if (cargas === total && callback) callback();
    };

    // Commodity
    fetch('/api/get_commoditys.php')
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('serv_commodity');
            if (sel) {
                sel.innerHTML = '<option value="">Seleccionar</option>';
                (data.commoditys || []).forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.commodity || c;
                    opt.textContent = c.commodity || c;
                    sel.appendChild(opt);
                });
            }
            check();
        });

    // Medios de transporte
    fetch('/api/get_medios_transporte.php')
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('serv_medio_transporte');
            if (sel) {
                sel.innerHTML = '<option value="">Seleccionar</option>';
                (data.medios_transporte || []).forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m;
                    opt.textContent = m;
                    sel.appendChild(opt);
                });
            }
            check();
        });

    // Agentes
    fetch('/api/get_agentes.php')
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('serv_agente');
            if (sel) {
                sel.innerHTML = '<option value="">Seleccionar</option>';
                (data.agentes || []).forEach(a => {
                    const opt = document.createElement('option');
                    opt.value = a;
                    opt.textContent = a;
                    sel.appendChild(opt);
                });
            }
            check();
        });

    // Proveedores nacionales
    fetch('/api/get_proveedores_pnac.php')
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('serv_proveedor_nac');
            if (sel) {
                sel.innerHTML = '<option value="">Seleccionar</option>';
                (data.proveedores || []).forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p;
                    opt.textContent = p;
                    sel.appendChild(opt);
                });
            }
            check();
        });

    // Lugares (Origen/Destino)
    fetch('/api/get_lugares.php')
        .then(r => r.json())
        .then(data => {
            const origenSel = document.getElementById('serv_origen');
            const destinoSel = document.getElementById('serv_destino');
            if (origenSel && destinoSel) {
                const options = '<option value="">Seleccionar</option>' +
                    (data.lugares || []).map(l => 
                        `<option value="${l.lugar}" data-pais="${l.pais || ''}">${l.lugar}</option>`
                    ).join('');
                origenSel.innerHTML = options;
                destinoSel.innerHTML = options;
                // Eventos para países
                origenSel.addEventListener('change', function() {
                    const opt = this.options[this.selectedIndex];
                    document.getElementById('serv_pais_origen').value = opt ? opt.getAttribute('data-pais') || '' : '';
                });
                destinoSel.addEventListener('change', function() {
                    const opt = this.options[this.selectedIndex];
                    document.getElementById('serv_pais_destino').value = opt ? opt.getAttribute('data-pais') || '' : '';
                });
            }
            check();
        });
}

// === INICIALIZAR ===
document.addEventListener('DOMContentLoaded', () => {
    cargarPaises();
    cargarOperacionesYTipos();
    document.getElementById('operacion')?.addEventListener('change', calcularConcatenado);
    document.getElementById('tipo_oper')?.addEventListener('change', calcularConcatenado);
    document.getElementById('btn-save-all').textContent = 'Grabar Todo';
    document.getElementById('btn-agregar-servicio').addEventListener('click', () => abrirModalServicio());
});
</script>