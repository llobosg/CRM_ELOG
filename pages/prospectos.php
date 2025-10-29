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

    <!-- ... (sección Datos del Prospecto, sin cambios) ... -->
    <!-- (Mantén todo el HTML de "Datos del Prospecto" exactamente como lo tenías) -->

    <div class="card">
        <h3><i class="fas fa-truck"></i> Servicios Asociados</h3>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem;">
            <!-- Izquierda: Submodales y modales -->
            <div style="display: flex; gap: 0.8rem;">
                <button type="button" class="btn-comment" id="btn-costos-servicio">
                    <i class="fas fa-calculator"></i> Costos - Ventas
                </button>
                <button type="button" class="btn-comment" id="btn-gastos-locales">
                    <i class="fas fa-file-invoice-dollar"></i> Gastos Locales
                </button>
                <button type="button" class="btn-comment" onclick="abrirModalComercial()"><i class="fas fa-comments"></i> Comerciales</button>
                <button type="button" class="btn-comment" onclick="abrirModalOperaciones()"><i class="fas fa-clipboard-list"></i> Operaciones</button>
            </div>
            
            <!-- Derecha: Solo Grabar Todo (fuera del modal) -->
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

<!-- ========== MODALES PRINCIPALES ========== -->

<!-- Modal Comercial, Operaciones: sin cambios -->
<!-- (Mantén tus modales de Comerciales y Operaciones como estaban) -->

<!-- Modal Servicio -->
<div id="modal-servicio" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 1500px; width: 95%;">
        <h3><i class="fas fa-box"></i> Agregar Servicio para <span id="serv_titulo_concatenado">-</span></h3>
        <span class="close" onclick="cerrarModalServicio()">&times;</span>
        
        <!-- Campos del servicio (sin cambios) -->
        <!-- (Mantén todo el HTML interno del modal exactamente como lo tenías) -->

        <div class="modal-footer" style="text-align: right; margin-top: 1.5rem; gap: 0.8rem; display: flex; justify-content: space-between; align-items: center;">
            <!-- Izquierda: Submodales dentro del modal -->
            <div>
                <button type="button" class="btn-comment" id="btn-costos-servicio-modal"><i class="fas fa-calculator"></i> Costos - Ventas</button>
                <button type="button" class="btn-comment" id="btn-gastos-locales-modal"><i class="fas fa-file-invoice-dollar"></i> Gastos Locales</button>
            </div>
            
            <!-- Derecha: Volver y Agregar Servicio -->
            <div style="display: flex; gap: 0.8rem;">
                <button type="button" class="btn-secondary" onclick="cerrarModalServicioConConfirmacion()">Volver</button>
                <button type="button" class="btn-add" onclick="guardarServicio()">Agregar Servicio</button>
            </div>
        </div>
    </div>
</div>

<!-- ========== SUBMODALES ========== -->
<!-- (Pega aquí TODO el HTML de los submodales que ya tenías: submodal-costos y submodal-gastos-locales) -->
<?= file_get_contents(__DIR__ . '/submodales_servicio.html') ?>
<!-- O simplemente incluye el HTML que ya tienes -->

<!-- Toast de notificaciones -->
<div id="toast" class="toast" style="display:none;">
    <i class="fas fa-info-circle"></i> 
    <span id="toast-message">Mensaje</span>
</div>

<script>
// ===================================================================
// === 1. VARIABLES GLOBALES ===
// ===================================================================
let servicios = [];
let costosServicio = [];
let gastosLocales = [];
let servicioEnEdicion = null;
let tieneServiciosIniciales = false;
let estadoProspecto = 'Pendiente';

// ===================================================================
// === 2. FUNCIONES AUXILIARES ===
// ===================================================================
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

function calcularConcatenado() {
    const op = document.getElementById('operacion')?.value || '';
    const tipo = document.getElementById('tipo_oper')?.value || '';
    if (!op || !tipo) return;
    const hoy = new Date();
    const fecha = hoy.toISOString().slice(2, 10).replace(/-/g, '');
    const id = (parseInt(document.getElementById('id_prospect')?.value || '0') + 1).toString().padStart(2, '0');
    document.getElementById('concatenado').value = `${op}${tipo}${fecha}-${id}`;
}

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
        tr.innerHTML = `<td>${s.servicio}</td><td>${s.trafico}</td><td>${s.base_calculo || ''}</td><td>${s.moneda}</td><td>${(parseFloat(s.tarifa)||0).toFixed(2)}</td><td>${c.toFixed(2)}</td><td>${v.toFixed(2)}</td><td>${gc.toFixed(2)}</td><td>${gv.toFixed(2)}</td><td><button type="button" onclick="editarServicio(${servicios.indexOf(s)})">✏️</button> <button type="button" onclick="eliminarServicio(${servicios.indexOf(s)})">🗑️</button></td>`;
        tbody.appendChild(tr);
    });
    document.getElementById('total-costo').textContent = tc.toFixed(2);
    document.getElementById('total-venta').textContent = tv.toFixed(2);
    document.getElementById('total-costogasto').textContent = tgc.toFixed(2);
    document.getElementById('total-ventagasto').textContent = tgv.toFixed(2);
}

// ===================================================================
// === 3. CARGA DE DATOS (API) ===
// ===================================================================
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

function cargarDatosModalServicio(callback = null) {
    let cargas = 0;
    const total = 4;

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
}

// ===================================================================
// === 4. CARGA DE LUGARES (Origen/Destino) ===
// ===================================================================
function cargarLugaresPorMedio(medio, origenSeleccionado = null) {
    const origenSel = document.getElementById('serv_origen');
    const destinoSel = document.getElementById('serv_destino');
    if (!origenSel || !destinoSel) return Promise.resolve();

    if (!medio) {
        origenSel.innerHTML = '<option value="">Seleccionar</option>';
        destinoSel.innerHTML = '<option value="">Seleccionar</option>';
        return Promise.resolve();
    }

    return fetch(`/api/get_lugares_por_medio.php?medio=${encodeURIComponent(medio)}`)
        .then(r => r.json())
        .then(data => {
            const lugares = data.lugares || [];
            const optionsHtml = lugares.map(l => 
                `<option value="${l.lugar}" data-pais="${l.pais || ''}">${l.lugar}</option>`
            ).join('');

            origenSel.innerHTML = '<option value="">Seleccionar</option>' + optionsHtml;

            const destinosFiltrados = origenSeleccionado
                ? lugares.filter(l => l.lugar !== origenSeleccionado)
                : lugares;
            const destinoHtml = destinosFiltrados.map(l => 
                `<option value="${l.lugar}" data-pais="${l.pais || ''}">${l.lugar}</option>`
            ).join('');
            destinoSel.innerHTML = '<option value="">Seleccionar</option>' + destinoHtml;

            // Actualizar países
            const actualizarPais = (selectId, paisId) => {
                const sel = document.getElementById(selectId);
                const pais = document.getElementById(paisId);
                if (!sel || !pais) return;
                const handler = () => {
                    const opt = sel.options[sel.selectedIndex];
                    pais.value = opt ? opt.getAttribute('data-pais') || '' : '';
                };
                sel.removeEventListener('change', handler);
                sel.addEventListener('change', handler);
            };

            actualizarPais('serv_origen', 'serv_pais_origen');
            actualizarPais('serv_destino', 'serv_pais_destino');
        })
        .catch(err => {
            console.error('Error al cargar lugares:', err);
            error('No se pudieron cargar los lugares para este medio');
            return Promise.resolve();
        });
}

// ===================================================================
// === 5. MANEJO DE PROSPECTOS ===
// ===================================================================
function seleccionarProspecto(id) {
    fetch(`/api/get_prospecto.php?id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.prospecto) return error('Prospecto no encontrado');
            const p = data.prospecto;

            // Rellenar campos (sin cambios)
            ['razon_social','rut_empresa','fono_empresa','direccion','booking','incoterm','concatenado','fecha_alta','fecha_estado'].forEach(f => {
                const el = document.querySelector(`[name="${f}"]`);
                if (el && el.tagName === 'INPUT') el.value = p[f] || '';
            });

            document.getElementById('id_comercial').value = p.id_comercial || '';
            document.getElementById('nombre').value = p.nombre || '';
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

            // ✅ ELIMINADO: regla de negocio de edición
            // Los campos SIEMPRE son editables

            // ✅ Botón Agregar Servicio SIEMPRE habilitado
            document.getElementById('btn-agregar-servicio').disabled = false;
        });
}

// ===================================================================
// === 6. MODALES PRINCIPALES ===
// ===================================================================
function abrirModalComercial() { document.getElementById('modal-comercial').style.display = 'block'; }
function cerrarModalComercial() { document.getElementById('modal-comercial').style.display = 'none'; }
function abrirModalOperaciones() { document.getElementById('modal-operaciones').style.display = 'block'; }
function cerrarModalOperaciones() { document.getElementById('modal-operaciones').style.display = 'none'; }

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

// ===================================================================
// === 7. MODAL DE SERVICIO ===
// ===================================================================
function abrirModalServicio(index = null) {
    const idPpl = document.getElementById('id_ppl')?.value;
    const concatenado = document.getElementById('concatenado')?.value;
    if (!idPpl || !concatenado) return error('Guarde el prospecto primero');

    // Limpiar modal
    const modalInputs = document.querySelectorAll('#modal-servicio input, #modal-servicio select, #modal-servicio textarea');
    modalInputs.forEach(el => {
        if (el.type === 'number') el.value = '';
        else if (el.type === 'text' || el.tagName === 'TEXTAREA') el.value = '';
        else if (el.tagName === 'SELECT') el.selectedIndex = 0;
    });

    document.getElementById('id_prospect_serv').value = idPpl;
    document.getElementById('concatenado_serv').value = concatenado;
    document.getElementById('serv_titulo_concatenado').textContent = concatenado;

    costosServicio = [];
    gastosLocales = [];

    if (index !== null) {
        servicioEnEdicion = index;
        const s = servicios[index];
        costosServicio = Array.isArray(s.costos) ? [...s.costos] : [];
        gastosLocales = Array.isArray(s.gastos_locales) ? [...s.gastos_locales] : [];

        cargarDatosModalServicio(() => {
            // Rellenar campos básicos
            document.getElementById('serv_servicio').value = s.servicio || '';
            document.getElementById('serv_ref_cliente').value = s.ref_cliente || '';
            document.getElementById('serv_desconsolidacion').value = s.desconsolidac || '';
            document.getElementById('serv_aol').value = s.aol || '';
            document.getElementById('serv_aod').value = s.aod || '';
            document.getElementById('serv_agente').value = s.agente || '';
            document.getElementById('serv_aerolinea').value = s.aerolinea || '';
            document.getElementById('serv_terrestre').value = s.terrestre || '';
            document.getElementById('serv_maritimo').value = s.naviera || '';
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

            // Asignar medio y commodity
            const medioGuardado = (s.trafico || '').trim();
            const commodityGuardado = (s.commodity || '').trim();

            const medioSel = document.getElementById('serv_medio_transporte');
            const commoditySel = document.getElementById('serv_commodity');

            if (medioSel) {
                for (let opt of medioSel.options) {
                    if (opt.value.trim() === medioGuardado) {
                        opt.selected = true;
                        break;
                    }
                }
            }
            if (commoditySel) {
                for (let opt of commoditySel.options) {
                    if (opt.value.trim() === commodityGuardado) {
                        opt.selected = true;
                        break;
                    }
                }
            }

            // Cargar lugares
            if (medioGuardado) {
                cargarLugaresPorMedio(medioGuardado, s.origen).then(() => {
                    const origenSel = document.getElementById('serv_origen');
                    const destinoSel = document.getElementById('serv_destino');

                    if (origenSel && s.origen) {
                        for (let opt of origenSel.options) {
                            if (opt.value === s.origen) {
                                opt.selected = true;
                                break;
                            }
                        }
                        origenSel.dispatchEvent(new Event('change'));
                    }

                    if (destinoSel && s.destino) {
                        for (let opt of destinoSel.options) {
                            if (opt.value === s.destino) {
                                opt.selected = true;
                                break;
                            }
                        }
                        destinoSel.dispatchEvent(new Event('change'));
                    }
                });
            }
        });
    } else {
        servicioEnEdicion = null;
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

    const origen = document.getElementById('serv_origen').value;
    const destino = document.getElementById('serv_destino').value;
    if (origen && destino && origen === destino) {
        return error('Origen y Destino no pueden ser el mismo lugar');
    }

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
        exito('Servicio actualizado correctamente');
    } else {
        servicios.push(nuevo);
        exito('Servicio agregado correctamente');
    }
    actualizarTabla();
    cerrarModalServicio();
}

// ===================================================================
// === 8. SUBMODALES: COSTOS Y GASTOS LOCALES ===
// ===================================================================
// (Pega aquí TODO el código JS de los submodales que ya tenías)
// Incluyendo: abrirSubmodalCostos, guardarCosto, etc.
// Y también las funciones de Gastos Locales

// Por brevedad, asumimos que ya están definidas aquí
// (Puedes copiar y pegar tu código existente en este bloque)

// ===================================================================
// === 9. INICIALIZACIÓN ===
// ===================================================================
document.addEventListener('DOMContentLoaded', () => {
    cargarPaises();
    cargarOperacionesYTipos();
    document.getElementById('operacion')?.addEventListener('change', calcularConcatenado);
    document.getElementById('tipo_oper')?.addEventListener('change', calcularConcatenado);

    // Listeners principales
    document.getElementById('btn-save-all').textContent = 'Grabar Todo';
    document.getElementById('btn-agregar-servicio').addEventListener('click', () => abrirModalServicio());

    // Listeners de submodales (fuera del modal)
    document.getElementById('btn-costos-servicio')?.addEventListener('click', () => {
        error('Abra un servicio primero para gestionar costos');
    });
    document.getElementById('btn-gastos-locales')?.addEventListener('click', () => {
        error('Abra un servicio primero para gestionar gastos');
    });

    // Listeners dentro del modal de servicio
    document.getElementById('btn-costos-servicio-modal')?.addEventListener('click', abrirSubmodalCostos);
    document.getElementById('btn-gastos-locales-modal')?.addEventListener('click', abrirSubmodalGastosLocales);

    // Búsqueda inteligente
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

    // Botón Volver (en sección principal)
    document.getElementById('btn-volver')?.addEventListener('click', function() {
        const tieneCambios = 
            document.querySelector('input[name="razon_social"]')?.value.trim() !== '' ||
            servicios.length > 0;

        if (tieneCambios) {
            if (!confirm('¿Desea salir sin guardar los cambios?\n\nSi ha modificado datos, se perderán.')) {
                return;
            }
        }

        document.getElementById('form-prospecto').reset();
        servicios = [];
        actualizarTabla();
        document.getElementById('id_ppl').value = '';
        document.getElementById('id_prospect').value = '';

        // Habilitar todo
        const inputs = document.querySelectorAll('input:not([type="hidden"]):not([name="concatenado"])');
        const selects = document.querySelectorAll('select');
        inputs.forEach(input => {
            input.readOnly = false;
            input.style.backgroundColor = '';
        });
        selects.forEach(select => {
            select.disabled = false;
        });
        document.getElementById('btn-agregar-servicio').disabled = false;
    });

    // Grabar Todo
    document.getElementById('btn-save-all')?.addEventListener('click', function(e) {
        e.preventDefault();
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

    // Editar y eliminar (con type="button" para evitar submit)
    window.editarServicio = function(index) {
        try {
            if (index < 0 || index >= servicios.length) throw new Error('Índice inválido');
            abrirModalServicio(index);
        } catch (e) {
            console.error('Error al editar:', e);
            error('No se pudo abrir el servicio');
        }
    };
    window.eliminarServicio = function(index) {
        if (confirm('¿Eliminar este servicio?')) {
            servicios.splice(index, 1);
            actualizarTabla();
            exito('Servicio eliminado');
        }
    };

    // Cargar prospecto desde URL (tras guardar)
    const urlParams = new URLSearchParams(window.location.search);
    const idFromUrl = urlParams.get('id_ppl');
    if (idFromUrl && !isNaN(idFromUrl)) {
        const cleanUrl = window.location.pathname + '?page=prospectos';
        history.replaceState({}, document.title, cleanUrl);
        setTimeout(() => seleccionarProspecto(parseInt(idFromUrl)), 300);
    }
});
</script>