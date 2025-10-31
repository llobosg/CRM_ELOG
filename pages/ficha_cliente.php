<!-- FICHA CLIENTE -->
<div style="margin: 1rem;">
    <h2><i class="fas fa-id-card"></i> Ficha Cliente</h2>

    <!-- Búsqueda inteligente: RUT, Razón Social, Giro, Nombre Comercial -->
    <div style="margin: 1rem 0;">
        <label><i class="fas fa-search"></i> Buscar Cliente</label>
        <input type="text" id="busqueda-cliente" placeholder="RUT, Razón Social, Giro o Comercial..." 
            style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px;" />
        <div id="resultados-busqueda-cliente" 
            style="position: absolute; background: white; border: 1px solid #ddd; border-radius: 8px; 
                    max-height: 300px; overflow-y: auto; width: 95%; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.1); display: none;"></div>
    </div>

    <form id="form-cliente">
        <input type="hidden" id="rut_cliente" name="rut" />

        <!-- ========== DATOS DEL CLIENTE ========== -->
        <div class="card" style="margin-bottom: 2rem;">
            <h3><i class="fas fa-user"></i> Datos del Cliente</h3>

            <!-- Fila 1 -->
            <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; align-items: center; margin-bottom: 1rem;">
                <label>Nacional/Extranjero *</label>
                <select id="cliente_nacional_extranjero" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="Nacional">Nacional</option>
                    <option value="Extranjero">Extranjero</option>
                </select>
                <label>País *</label>
                <select id="cliente_pais" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="">Seleccionar</option>
                    <!-- Se llenará con JS -->
                </select>
            </div>

            <!-- Fila 2 -->
            <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; align-items: center; margin-bottom: 1rem;">
                <label>RUT *</label>
                <input type="text" id="cliente_rut" style="width: 100%; padding: 0.5rem; background: #f8f9fa; border: 1px solid #ccc; border-radius: 6px;" />
                <label>Razón Social *</label>
                <input type="text" id="cliente_razon_social" style="grid-column: span 3; width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" />
                <label>Giro</label>
                <input type="text" id="cliente_giro" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" />
            </div>

            <!-- Fila 3 -->
            <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; align-items: center; margin-bottom: 1rem;">
                <label>Dirección</label>
                <input type="text" id="cliente_direccion" style="grid-column: span 3; width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" />
                <label>Comuna</label>
                <input type="text" id="cliente_comuna" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" />
                <label>Ciudad</label>
                <input type="text" id="cliente_ciudad" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" />
            </div>

            <!-- Fila 4 -->
            <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; align-items: center; margin-bottom: 1rem;">
                <label>Fecha Creación</label>
                <input type="date" id="cliente_fecha_creacion" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" value="<?= date('Y-m-d') ?>" />
                <label>Comercial Asignado</label>
                <input type="text" id="cliente_nombre_comercial" readonly style="width: 100%; padding: 0.5rem; background: #f8f9fa; border: 1px solid #ccc; border-radius: 6px;" />
                <label>Tipo Vida</label>
                <select id="cliente_tipo_vida" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="lead">Lead</option>
                    <option value="prospecto">Prospecto</option>
                    <option value="cotizando">Cotizando</option>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                    <option value="perdido">Perdido</option>
                </select>
                <label>Fecha Vida</label>
                <input type="date" id="cliente_fecha_vida" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" value="<?= date('Y-m-d') ?>" />
            </div>

            <!-- Fila 5 -->
            <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; align-items: center;">
                <label>Rubro</label>
                <select id="cliente_rubro" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="industrial">Industrial</option>
                    <option value="minero">Minero</option>
                    <option value="servicios">Servicios</option>
                    <option value="retail">Retail</option>
                    <option value="insumos médicos">Insumos médicos</option>
                    <option value="construcción">Construcción</option>
                </select>
                <label>Potencial USD</label>
                <input type="number" id="cliente_potencial_usd" step="0.01" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" />
            </div>
        </div>

        <!-- ========== LÍNEA DE CRÉDITO ========== -->
        <div class="card" style="margin-bottom: 2rem;">
            <h3><i class="fas fa-hand-holding-usd"></i> Línea de Crédito USD</h3>
            
            <!-- Fila 1 -->
            <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; align-items: center; margin-bottom: 1rem;">
                <label>Fecha Alta</label>
                <input type="date" id="credito_fecha_alta" style="width: 100%; padding: 0.5rem;" value="<?= date('Y-m-d') ?>" />
                <label>Plazo (días)</label>
                <select id="credito_plazo_dias" style="width: 100%; padding: 0.5rem;">
                    <option value="30">30</option>
                    <option value="45">45</option>
                    <option value="60">60</option>
                    <option value="Contado">Contado</option>
                </select>
                <label>Estado</label>
                <select id="credito_estado" style="width: 100%; padding: 0.5rem;">
                    <option value="vigente">Vigente</option>
                    <option value="suspendido">Suspendido</option>
                </select>
            </div>

            <!-- Fila 2 -->
            <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; align-items: center;">
                <label>Monto</label>
                <input type="number" id="credito_monto" step="0.01" style="width: 100%; padding: 0.5rem;" />
                <label>Usado</label>
                <input type="number" id="credito_usado" step="0.01" readonly style="width: 100%; padding: 0.5rem; background: #f8f9fa;" />
                <label>Saldo</label>
                <input type="number" id="credito_saldo" step="0.01" readonly style="width: 100%; padding: 0.5rem; background: #f8f9fa;" />
                <div style="grid-column: span 2; text-align: right;">
                    <button type="button" id="btn-guardar-ficha" class="btn-primary" style="padding: 0.6rem 1.2rem;">
                        Guardar Ficha Cliente
                    </button>
                </div>
            </div>
        </div>

        <!-- ========== CONTACTOS ========== -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3><i class="fas fa-address-book"></i> Contactos</h3>
                <button type="button" class="btn-add" onclick="abrirModalContacto()">
                    <i class="fas fa-plus"></i> Agregar Contacto
                </button>
            </div>
            <table id="tabla-contactos" class="table-container">
                <thead>
                    <tr>
                        <th>Nombre</th><th>Rol</th><th>Primario</th><th>Fono</th><th>Email</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="contactos-body"></tbody>
            </table>
        </div>
    </form>
</div>

<!-- Modal Contacto -->
<div id="modal-contacto" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 700px; margin: 2rem auto;">
        <h3><i class="fas fa-user-plus"></i> <span id="titulo-modal-contacto">Agregar Contacto</span></h3>
        <span class="close" onclick="cerrarModalContacto()">&times;</span>
        <input type="hidden" id="contacto_id" />
        <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 0.8rem; margin-top: 1rem;">
            <label>Nombre *</label>
            <input type="text" id="contacto_nombre" style="grid-column: span 2;" required />
            <label>Rol *</label>
            <select id="contacto_rol" style="grid-column: span 2;" required>
                <option value="Comercial">Comercial</option>
                <option value="Operaciones">Operaciones</option>
                <option value="Finanzas">Finanzas</option>
                <option value="GG">GG</option>
                <option value="Dueño">Dueño</option>
                <option value="Admin y Finanzas">Admin y Finanzas</option>
                <option value="Encargado Comex">Encargado Comex</option>
            </select>
            <label>Primario</label>
            <select id="contacto_primario" style="width: 100%;">
                <option value="N">No</option>
                <option value="S">Sí</option>
            </select>
            <label>Fono</label>
            <input type="text" id="contacto_fono" style="width: 100%;" />
            <label>Email</label>
            <input type="email" id="contacto_email" style="grid-column: span 2;" />
        </div>
        <div style="text-align: right; margin-top: 1.5rem;">
            <button type="button" class="btn-secondary" onclick="cerrarModalContacto()">Volver</button>
            <button type="button" class="btn-add" onclick="guardarContacto()">Agregar Contacto</button>
        </div>
    </div>
</div>
<!-- =================================================================== --->
<!-- === INICIALIZACIÓN AL CARGAR LA PÁGINA === -->
<!-- =================================================================== -->
<script>
    let contactos = [];
    let contactoEnEdicion = null;

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

    function buscarCliente() {
        const rut = document.getElementById('rut_cliente_buscar').value.trim();
        if (!rut) return alert('Ingrese un RUT válido');
        fetch(`/api/get_cliente.php?rut=${encodeURIComponent(rut)}`)
            .then(r => r.json())
            .then(data => {
                if (data.existe) {
                    cargarCliente(data.cliente);
                    cargarContactos(rut);
                } else {
                    // Nuevo cliente
                    limpiarFormulario();
                    document.getElementById('rut_cliente').value = rut;
                    document.getElementById('cliente_rut').value = rut;
                    contactos = [];
                    actualizarTablaContactos();
                }
            });
    }

    function cargarCliente(cliente) {
        document.getElementById('rut_cliente').value = cliente.rut;
        document.getElementById('cliente_rut').value = cliente.rut;
        document.getElementById('cliente_razon_social').value = cliente.razon_social;
        document.getElementById('cliente_nacional_extranjero').value = cliente.nacional_extranjero;
        document.getElementById('cliente_pais').value = cliente.pais || '';
        document.getElementById('cliente_direccion').value = cliente.direccion || '';
        document.getElementById('cliente_comuna').value = cliente.comuna || '';
        document.getElementById('cliente_ciudad').value = cliente.ciudad || '';
        document.getElementById('cliente_giro').value = cliente.giro || '';
        document.getElementById('cliente_fecha_creacion').value = cliente.fecha_creacion || '';
        document.getElementById('cliente_id_comercial').value = cliente.id_comercial || '';
        document.getElementById('cliente_nombre_comercial').value = cliente.nombre_comercial || '';
        document.getElementById('cliente_tipo_vida').value = cliente.tipo_vida;
        document.getElementById('cliente_fecha_vida').value = cliente.fecha_vida || '';
        document.getElementById('cliente_rubro').value = cliente.rubro || '';
        document.getElementById('cliente_potencial_usd').value = cliente.potencial_usd || '';

        document.getElementById('credito_fecha_alta').value = cliente.fecha_alta_credito || '';
        document.getElementById('credito_plazo_dias').value = cliente.plazo_dias || '30';
        document.getElementById('credito_estado').value = cliente.estado_credito || 'vigente';
        document.getElementById('credito_monto').value = cliente.monto_credito || 0;
        document.getElementById('credito_usado').value = cliente.usado_credito || 0;
        document.getElementById('credito_saldo').value = cliente.saldo_credito || 0;
    }

    function cargarComerciales() {
        fetch('/api/get_comerciales.php')
            .then(r => r.json())
            .then(data => {
                const sel = document.getElementById('cliente_id_comercial');
                if (!sel) return;
                sel.innerHTML = '<option value="">Seleccionar</option>';
                (data.comerciales || []).forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id_comercial;
                    opt.textContent = `${c.id_comercial} - ${c.nombre}`;
                    sel.appendChild(opt);
                });
            });
    }

    // Listener para cargar nombre_comercial al seleccionar comercial
    document.getElementById('cliente_id_comercial')?.addEventListener('change', function() {
        const id = this.value;
        if (!id) {
            document.getElementById('cliente_nombre_comercial').value = '';
            return;
        }
        fetch(`/api/get_comercial.php?id=${id}`)
            .then(r => r.json())
            .then(data => {
                if (data && data.nombre) {
                    document.getElementById('cliente_nombre_comercial').value = data.nombre;
                }
            });
    });

    function cargarPaises() {
        const selectPais = document.getElementById('cliente_pais');
        if (!selectPais) return;

        fetch('/api/get_paises.php')
            .then(r => r.json())
            .then(data => {
                selectPais.innerHTML = '<option value="">Seleccionar país</option>';
                (data.paises || []).forEach(pais => {
                    const opt = document.createElement('option');
                    opt.value = pais;
                    opt.textContent = pais;
                    selectPais.appendChild(opt);
                });
            })
            .catch(err => {
                console.error('Error al cargar países:', err);
                // Fallback mínimo
                const fallback = ["Chile", "Argentina", "Perú", "Colombia", "México", "Estados Unidos", "España"];
                selectPais.innerHTML = '<option value="">Seleccionar país</option>';
                fallback.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p;
                    opt.textContent = p;
                    selectPais.appendChild(opt);
                });
            });
    }

    function guardarCliente() {
        const rut = document.querySelector('input[name="rut_cliente"]').value.trim();
        if (!rut) return error('RUT es obligatorio');
        const rutLimpio = rut.replace(/\./g, '').replace('-', '').toUpperCase();
        if (!validarRut(rutLimpio)) return error('RUT inválido');
            const cliente = {
            rut: rut,
            razon_social: document.getElementById('cliente_razon_social').value,
            nacional_extranjero: document.getElementById('cliente_nacional_extranjero').value,
            pais: document.getElementById('cliente_pais').value,
            direccion: document.getElementById('cliente_direccion').value,
            comuna: document.getElementById('cliente_comuna').value,
            ciudad: document.getElementById('cliente_ciudad').value,
            giro: document.getElementById('cliente_giro').value,
            fecha_creacion: document.getElementById('cliente_fecha_creacion').value,
            id_comercial: document.getElementById('cliente_id_comercial').value,
            nombre_comercial: document.getElementById('cliente_nombre_comercial').value,
            tipo_vida: document.getElementById('cliente_tipo_vida').value,
            fecha_vida: document.getElementById('cliente_fecha_vida').value,
            rubro: document.getElementById('cliente_rubro').value,
            potencial_usd: document.getElementById('cliente_potencial_usd').value,
            fecha_alta_credito: document.getElementById('credito_fecha_alta').value,
            plazo_dias: document.getElementById('credito_plazo_dias').value,
            estado_credito: document.getElementById('credito_estado').value,
            monto_credito: document.getElementById('credito_monto').value,
            contactos: contactos
        };
        fetch('/pages/ficha_cliente_logic.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(cliente)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Ficha cliente guardada correctamente');
            } else {
                alert('Error: ' + data.message);
            }
        });
    }

    // ===================================================================
    // === INICIALIZACIÓN AL CARGAR LA PÁGINA ===
    // ===================================================================
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Cargar listas iniciales
        cargarPaises();

        // 2. Inicializar búsqueda inteligente
        document.getElementById('busqueda-cliente')?.addEventListener('input', async function() {
            const term = this.value.trim();
            const div = document.getElementById('resultados-busqueda-cliente');
            div.style.display = 'none';
            if (!term) return;
            try {
                const res = await fetch(`/api/buscar_cliente_inteligente.php?term=${encodeURIComponent(term)}`);
                const data = await res.json();
                div.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(c => {
                        const d = document.createElement('div');
                        d.style.padding = '0.8rem';
                        d.style.cursor = 'pointer';
                        d.innerHTML = `<strong>${c.razon_social}</strong><br><small>RUT: ${c.rut} | Giro: ${c.giro || ''}</small>`;
                        d.onclick = () => {
                            cargarCliente(c);
                            div.style.display = 'none';
                            this.value = '';
                        };
                        div.appendChild(d);
                    });
                    div.style.display = 'block';
                }
            } catch (e) {
                error('Error al buscar cliente');
            }
        });

        // 3. Listeners de botones
        document.getElementById('btn-agregar-contacto')?.addEventListener('click', function() {
            const rut = document.getElementById('cliente_rut').value;
            if (!rut) {
                error('Debe seleccionar o crear un cliente primero.');
                return;
            }
            abrirModalContacto();
        });

        document.getElementById('btn-guardar-ficha')?.addEventListener('click', guardarCliente);

        function limpiarFormulario() {
            const campos = [
                'cliente_razon_social', 'cliente_nacional_extranjero', 'cliente_pais',
                'cliente_direccion', 'cliente_comuna', 'cliente_ciudad', 'cliente_giro',
                'cliente_fecha_creacion', 'cliente_id_comercial', 'cliente_nombre_comercial',
                'cliente_tipo_vida', 'cliente_fecha_vida', 'cliente_rubro', 'cliente_potencial_usd',
                'credito_fecha_alta', 'credito_plazo_dias', 'credito_estado',
                'credito_monto', 'credito_usado', 'credito_saldo'
            ];
            campos.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = el.type === 'date' ? '<?= date("Y-m-d") ?>' : '';
            });
        }

        function cargarContactos(rut) {
            fetch(`/api/get_contactos.php?rut=${encodeURIComponent(rut)}`)
                .then(r => r.json())
                .then(data => {
                    contactos = data.contactos || [];
                    actualizarTablaContactos();
                });
        }

        function actualizarTablaContactos() {
            const tbody = document.getElementById('contactos-body');
            tbody.innerHTML = '';
            contactos.forEach((c, i) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${c.nombre}</td>
                    <td>${c.rol}</td>
                    <td>${c.primario === 'S' ? '✅' : ''}</td>
                    <td>${c.fono || ''}</td>
                    <td>${c.email || ''}</td>
                    <td>
                        <button type="button" onclick="editarContacto(${i})">✏️</button>
                        <button type="button" onclick="eliminarContacto(${i})">🗑️</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function abrirModalContacto(index = null) {
            const rut = document.getElementById('cliente_rut').value;
            if (!rut) {
                error('Debe guardar el cliente primero para agregar contactos.');
                return;
            }
            contactoEnEdicion = index;
            if (index !== null) {
                const c = contactos[index];
                document.getElementById('contacto_id').value = c.id_contacto || '';
                document.getElementById('contacto_nombre').value = c.nombre;
                document.getElementById('contacto_rol').value = c.rol;
                document.getElementById('contacto_primario').value = c.primario;
                document.getElementById('contacto_fono').value = c.fono || '';
                document.getElementById('contacto_email').value = c.email || '';
                document.getElementById('titulo-modal-contacto').textContent = 'Editar Contacto';
            } else {
                document.getElementById('contacto_id').value = '';
                document.getElementById('contacto_nombre').value = '';
                document.getElementById('contacto_rol').value = 'comercial';
                document.getElementById('contacto_primario').value = 'N';
                document.getElementById('contacto_fono').value = '';
                document.getElementById('contacto_email').value = '';
                document.getElementById('titulo-modal-contacto').textContent = 'Agregar Contacto';
            }
            document.getElementById('modal-contacto').style.display = 'block';
        }

        function cerrarModalContacto() {
            document.getElementById('modal-contacto').style.display = 'none';
        }

        function guardarContacto() {
            const nombre = document.getElementById('contacto_nombre').value.trim();
            if (!nombre) return alert('Nombre es obligatorio');
            const nuevo = {
                id_contacto: document.getElementById('contacto_id').value || null,
                rut_cliente: document.getElementById('rut_cliente').value,
                nombre: nombre,
                rol: document.getElementById('contacto_rol').value,
                primario: document.getElementById('contacto_primario').value,
                fono: document.getElementById('contacto_fono').value,
                email: document.getElementById('contacto_email').value
            };
            if (contactoEnEdicion !== null) {
                contactos[contactoEnEdicion] = nuevo;
            } else {
                contactos.push(nuevo);
            }
            actualizarTablaContactos();
            cerrarModalContacto();
        }

        function editarContacto(index) {
            abrirModalContacto(index);
        }

        function eliminarContacto(index) {
            if (confirm('¿Eliminar contacto?')) {
                contactos.splice(index, 1);
                actualizarTablaContactos();
            }
        }
    });
</script>