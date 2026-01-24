<!-- FICHA CLIENTE -->
<div style="margin: 0;">
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
        <input type="hidden" id="rut" name="rut" />

        <!-- ========== DATOS DEL CLIENTE ========== -->
        <div class="card" style="margin-bottom: 2rem;">
            <h3>
                <i class="fas fa-user"></i> Datos del Cliente
                <button type="button" onclick="confirmarLimpiarFormulario()" 
                        style="float: right; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6c757d;">
                    &times;
                </button>
            </h3>

            <!-- Fila 1 -->
            <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; align-items: center; margin-bottom: 1rem;">
                <label>Nacional/Extranjero *</label>
                <select id="cliente_nacional_extranjero" name="cliente_nacional_extranjero" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="Nacional">Nacional</option>
                    <option value="Extranjero">Extranjero</option>
                </select>
                <label>País *</label>
                <select id="cliente_pais" name="cliente_pais" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="">Seleccionar</option>
                    <!-- Se llenará con JS -->
                </select>
            </div>

            <!-- Fila 2 -->
            <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; align-items: center; margin-bottom: 1rem;">
                <label>RUT *</label>
                <input type="text" id="cliente_rut" name="cliente_rut" style="width: 100%; padding: 0.5rem; background: #f8f9fa; border: 1px solid #ccc; border-radius: 6px;" />
                <label>Razón Social *</label>
                <input type="text" id="cliente_razon_social" name="cliente_razon_social" style="grid-column: span 3; width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" />
                <label>Giro</label>
                <input type="text" id="cliente_giro" name="cliente_giro" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" />
            </div>

            <!-- Fila 3 -->
            <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; align-items: center; margin-bottom: 1rem;">
                <label>Dirección</label>
                <input type="text" id="cliente_direccion" name="cliente_direccion" style="grid-column: span 3; width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" />
                <label>Comuna</label>
                <input type="text" id="cliente_comuna" name="cliente_comuna" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" />
                <label>Ciudad</label>
                <input type="text" id="cliente_ciudad" name="cliente_ciudad" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" />
            </div>

            <!-- Fila 4 -->
            <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; align-items: center; margin-bottom: 1rem;">
                <label>Fecha Creación</label>
                <input type="date" id="cliente_fecha_creacion" name="cliente_fecha_creacion" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" value="<?= date('Y-m-d') ?>" />
                <label>Comercial Asignado</label>
                <select name="id_comercial" id="cliente_nombre_comercial" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" >
                    <!-- opciones -->
                </select>
                <label>Tipo Vida</label>
                <select id="cliente_tipo_vida" name="cliente_tipo_vida" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="lead">Lead</option>
                    <option value="prospecto">Prospecto</option>
                    <option value="cotizando">Cotizando</option>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                    <option value="perdido">Perdido</option>
                </select>
                <label>Fecha Vida</label>
                <input type="date" id="cliente_fecha_vida" name="cliente_fecha_vida" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" value="<?= date('Y-m-d') ?>" />
            </div>

            <!-- Fila 5 -->
            <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; align-items: center;">
                <label>Rubro</label>
                <select id="cliente_rubro" name="cliente_rubro" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="industrial">Industrial</option>
                    <option value="minero">Minero</option>
                    <option value="servicios">Servicios</option>
                    <option value="retail">Retail</option>
                    <option value="insumos médicos">Insumos médicos</option>
                    <option value="construcción">Construcción</option>
                </select>
                <label>Potencial USD</label>
                <input type="number" id="cliente_potencial_usd" name="cliente_potencial_usd" step="0.01" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px;" />
            </div>
        </div>

        <!-- ========== LÍNEA DE CRÉDITO ========== -->
        <div class="card" style="margin-bottom: 2rem;">
            <h3><i class="fas fa-hand-holding-usd"></i> Línea de Crédito USD</h3>
            
            <!-- Fila 1 -->
            <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; align-items: center; margin-bottom: 1rem;">
                <label>Fecha Alta</label>
                <input type="date" id="credito_fecha_alta" name="credito_fecha_alta" style="width: 100%; padding: 0.5rem;" value="<?= date('Y-m-d') ?>" />
                <label>Plazo (días)</label>
                <select id="credito_plazo_dias" name="credito_plazo_dias" style="width: 100%; padding: 0.5rem;">
                    <option value="30">30</option>
                    <option value="45">45</option>
                    <option value="60">60</option>
                    <option value="Contado">Contado</option>
                </select>
                <label>Estado</label>
                <select id="credito_estado" name="credito_estado" style="width: 100%; padding: 0.5rem;">
                    <option value="vigente">Vigente</option>
                    <option value="suspendido">Suspendido</option>
                </select>
            </div>

            <!-- Fila 2 -->
            <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; align-items: center;">
                <label>Monto</label>
                <input type="number" id="credito_monto" name="credito_monto" step="0.01" style="width: 100%; padding: 0.5rem;" />
                <label>Usado</label>
                <input type="number" id="credito_usado" name="credito_usado" step="0.01" readonly style="width: 100%; padding: 0.5rem; background: #f8f9fa;" />
                <label>Saldo</label>
                <input type="number" id="credito_saldo" name="credito_saldo" step="0.01" readonly style="width: 100%; padding: 0.5rem; background: #f8f9fa;" />
                <div style="grid-column: span 2; text-align: right;">
                    <button type="button" class="btn-primary" onclick="guardarCliente()">
                        Guardar Ficha Cliente
                    </button>
                </div>
            </div>
        </div>

        <!-- ========== CONTACTOS ========== -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3><i class="fas fa-address-book"></i> Contactos</h3>
                <button type="button" id="btn-agregar-contacto" class="btn-add">
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
  <div class="modal-content" style="max-width: 1200px; margin: 2rem auto;">
    <h3><i class="fas fa-user-plus"></i> <span id="titulo-modal-contacto">Agregar Contacto</span></h3>
    <span class="close" onclick="cerrarModalContacto()">&times;</span>
    <input type="hidden" id="contacto_id" />

    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.8rem; margin-top: 1rem;">
      <!-- Fila 1 -->
      <label>Nombre *</label>
      <input type="text" id="nom_contacto" style="grid-column: span 1;" required />

      <label>Fono</label>
      <input type="text" id="fono_contacto" style="grid-column: span 1;" />

      <label>Email</label>
      <input type="email" id="email" style="grid-column: span 2;" placeholder="ejemplo@dominio.com" />

      <!-- Fila 2 -->
      <label>Rol</label>
      <select id="rol" style="grid-column: span 1;">
        <option value="comercial">Comercial</option>
        <option value="operaciones">Operaciones</option>
        <option value="finanzas">Finanzas</option>
        <option value="GG">GG</option>
        <option value="dueño">Dueño</option>
        <option value="admin y finanzas">Admin y Finanzas</option>
        <option value="encargado comex">Encargado Comex</option>
      </select>

      <label>Primario</label>
      <select id="primario" style="grid-column: span 1;">
        <option value="N">No</option>
        <option value="S">Sí</option>
      </select>
    </div>

    <div style="text-align: right; margin-top: 1.5rem;">
      <button type="button" class="btn-secondary" onclick="cerrarModalContacto()">Volver</button>
      <button type="button" class="btn-add" onclick="guardarContacto()">Agregar Contacto</button>
    </div>
  </div>
</div>

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

    function formatearRutParaMostrar(rut) {
        let rutLimpio = rut.replace(/\./g, '').replace('-', '').toUpperCase();
        if (!validarRut(rutLimpio)) return null;
        const cuerpo = rutLimpio.slice(0, -1);
        const dv = rutLimpio.slice(-1);
        return cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + '-' + dv;
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
                    limpiarFormularioCliente();
                    document.getElementById('rut').value = rut;
                    contactos = [];
                    actualizarTablaContactos();
                }
            });
    }

    function cargarCliente(cliente) {
        console.log('📥 Iniciando carga de cliente:', cliente);

        const rutFormateado = formatearRutParaMostrar(cliente.rut) || cliente.rut;
        const rutEl = document.getElementById('cliente_rut');
        if (rutEl) {
            rutEl.value = rutFormateado;
        }

        const campos = [
            ['cliente_razon_social', 'razon_social'],
            ['cliente_nacional_extranjero', 'nacional_extranjero'],
            ['cliente_pais', 'pais'],
            ['cliente_direccion', 'direccion'],
            ['cliente_comuna', 'comuna'],
            ['cliente_ciudad', 'ciudad'],
            ['cliente_giro', 'giro'],
            ['cliente_fecha_creacion', 'fecha_creacion'],
            ['cliente_tipo_vida', 'tipo_vida'],
            ['cliente_fecha_vida', 'fecha_vida'],
            ['cliente_rubro', 'rubro'],
            ['cliente_potencial_usd', 'potencial_usd'],
            ['credito_fecha_alta', 'fecha_alta_credito'],
            ['credito_plazo_dias', 'plazo_dias'],
            ['credito_estado', 'estado_credito'],
            ['credito_monto', 'monto_credito'],
            ['credito_usado', 'usado_credito'],
            ['credito_saldo', 'saldo_credito']
        ];

        campos.forEach(([id, key]) => {
            const el = document.getElementById(id);
            const valor = cliente[key] ?? '';
            if (el) {
                el.value = valor;
            }
        });

        // Cargar comercial asignado
        const comercialSel = document.getElementById('cliente_nombre_comercial');
        if (comercialSel && cliente.id_comercial) {
            console.log('👥 Comercial asignado ID:', cliente.id_comercial, 'Nombre:', cliente.nombre_comercial);
            let optionFound = false;
            for (let opt of comercialSel.options) {
                if (opt.value == cliente.id_comercial) {
                    opt.selected = true;
                    optionFound = true;
                    break;
                }
            }
            if (!optionFound && cliente.nombre_comercial) {
                const opt = document.createElement('option');
                opt.value = cliente.id_comercial;
                opt.textContent = cliente.nombre_comercial;
                comercialSel.appendChild(opt);
                comercialSel.value = cliente.id_comercial;
                console.log('➕ Comercial añadido como opción personalizada con ID:', cliente.id_comercial);
            }
        }

        if (cliente.rut) {
            cargarContactos(cliente.rut);
        }

        console.log('✅ Carga de cliente completada');
    }

    function cargarContactos(rut) {
        if (!rut) return;
        fetch(`/api/get_contactos.php?rut=${encodeURIComponent(rut)}`)
            .then(r => r.json())
            .then(data => {
                contactos = data.contactos || [];
                actualizarTablaContactos();
            })
            .catch(err => {
                console.error('Error al cargar contactos:', err);
                error('No se pudieron cargar los contactos');
            });
    }

    function actualizarTablaContactos() {
        const tbody = document.getElementById('contactos-body');
        tbody.innerHTML = '';
        contactos.forEach((c, i) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${c.nom_contacto}</td>
                <td>${c.rol}</td>
                <td>${c.primario === 'S' ? '✅' : ''}</td>
                <td>${c.fono_contacto || ''}</td>
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
        if (typeof index === 'object' && index !== null && index.type) {
            index = null;
        }
        contactoEnEdicion = (index === null || index === undefined) ? null : index;

        const nomContacto = document.getElementById('nom_contacto');
        const fonoContacto = document.getElementById('fono_contacto');
        const email = document.getElementById('email');
        const rol = document.getElementById('rol');
        const primario = document.getElementById('primario');

        if (nomContacto) nomContacto.value = '';
        if (fonoContacto) fonoContacto.value = '';
        if (email) email.value = '';
        if (rol) rol.value = 'comercial';
        if (primario) primario.value = 'N';

        if (index !== null && contactos[index]) {
            const c = contactos[index];
            if (nomContacto) nomContacto.value = c.nom_contacto || '';
            if (fonoContacto) fonoContacto.value = c.fono_contacto || '';
            if (email) email.value = c.email || '';
            if (rol) rol.value = c.rol || 'comercial';
            if (primario) primario.value = c.primario || 'N';
            document.getElementById('titulo-modal-contacto').textContent = 'Actualizar Contacto';
        } else {
            document.getElementById('titulo-modal-contacto').textContent = 'Agregar Contacto';
        }

        document.getElementById('modal-contacto').style.display = 'block';
    }

    function guardarContacto() {
        const nombre = document.getElementById('nom_contacto').value.trim();
        if (!nombre) return error('Nombre es obligatorio');

        const rutCliente = document.getElementById('cliente_rut').value.trim();
        if (!rutCliente) return error('RUT del cliente no disponible');

        const contacto = {
            rut_cliente: rutCliente,
            nom_contacto: nombre,
            fono_contacto: document.getElementById('fono_contacto').value,
            email: document.getElementById('email').value,
            rol: document.getElementById('rol').value,
            primario: document.getElementById('primario').value
        };

        if (contactoEnEdicion !== null && typeof contactoEnEdicion === 'number') {
            contactos[contactoEnEdicion] = contacto;
            window.exito('Contacto actualizado');
        } else {
            contactos.push(contacto);
            window.exito('Contacto agregado');
        }

        actualizarTablaContactos();
        cerrarModalContacto();
    }

    function editarContacto(index) {
        abrirModalContacto(index);
    }

    function cerrarModalContacto() {
        document.getElementById('modal-contacto').style.display = 'none';
    }

    function eliminarContacto(index) {
        if (confirm('¿Eliminar contacto?')) {
            contactos.splice(index, 1);
            actualizarTablaContactos();
        }
    }

    function limpiarFormularioCliente() {
        document.getElementById('cliente_rut').value = '';
        document.getElementById('cliente_razon_social').value = '';
        document.getElementById('cliente_nacional_extranjero').value = 'Nacional';
        document.getElementById('cliente_pais').value = '';
        document.getElementById('cliente_direccion').value = '';
        document.getElementById('cliente_comuna').value = '';
        document.getElementById('cliente_ciudad').value = '';
        document.getElementById('cliente_giro').value = '';
        document.getElementById('cliente_fecha_creacion').value = '';
        document.getElementById('cliente_nombre_comercial').value = '';
        document.getElementById('cliente_tipo_vida').value = 'lead';
        document.getElementById('cliente_fecha_vida').value = '';
        document.getElementById('cliente_rubro').value = '';
        document.getElementById('cliente_potencial_usd').value = '';

        document.getElementById('credito_fecha_alta').value = '';
        document.getElementById('credito_plazo_dias').value = '30';
        document.getElementById('credito_estado').value = 'vigente';
        document.getElementById('credito_monto').value = '';
        document.getElementById('credito_usado').value = '';
        document.getElementById('credito_saldo').value = '';

        contactos = [];
        actualizarTablaContactos();
    }

    function confirmarLimpiarFormulario() {
        if (confirm('¿Desea limpiar todos los datos del formulario? Se perderán los cambios no guardados.')) {
            limpiarFormularioCliente();
            exito('Formulario limpiado');
        }
    }

    // ===================================================================
    // === FUNCIÓN CORREGIDA PARA GUARDAR CLIENTE ===
    // ===================================================================
    function guardarCliente() {
        const rutMostrado = document.getElementById('cliente_rut').value.trim();
        if (!rutMostrado) {
            error('RUT es obligatorio');
            return;
        }

        const rutLimpio = rutMostrado.replace(/\./g, '').replace('-', '').toUpperCase();
        if (!/^(\d{7,8})([0-9K])$/.test(rutLimpio)) {
            error('RUT inválido');
            return;
        }

        const formData = new FormData();
        
        // Datos del cliente
        formData.append('rut', rutLimpio);
        formData.append('razon_social', document.getElementById('cliente_razon_social').value);
        formData.append('nacional_extranjero', document.getElementById('cliente_nacional_extranjero').value);
        formData.append('pais', document.getElementById('cliente_pais').value);
        formData.append('direccion', document.getElementById('cliente_direccion').value);
        formData.append('comuna', document.getElementById('cliente_comuna').value);
        formData.append('ciudad', document.getElementById('cliente_ciudad').value);
        formData.append('giro', document.getElementById('cliente_giro').value);
        formData.append('fecha_creacion', document.getElementById('cliente_fecha_creacion').value);
        formData.append('id_comercial', document.getElementById('cliente_nombre_comercial').value);
        formData.append('tipo_vida', document.getElementById('cliente_tipo_vida').value);
        formData.append('fecha_vida', document.getElementById('cliente_fecha_vida').value);
        formData.append('rubro', document.getElementById('cliente_rubro').value);
        formData.append('potencial_usd', document.getElementById('cliente_potencial_usd').value);
        formData.append('fecha_alta_credito', document.getElementById('credito_fecha_alta').value);
        formData.append('plazo_dias', document.getElementById('credito_plazo_dias').value);
        formData.append('estado_credito', document.getElementById('credito_estado').value);
        formData.append('monto_credito', document.getElementById('credito_monto').value);

        // Contactos como JSON
        formData.append('contactos', JSON.stringify(contactos));

        console.log('Enviando FormData:', Object.fromEntries(formData));

        fetch('?page=ficha_cliente', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                exito('Ficha cliente guardada correctamente');
                limpiarFormularioCliente();
            } else {
                error(data.message || 'Error al guardar');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            error('Error de conexión con el servidor');
        });
    }

    // ===================================================================
    // === INICIALIZACIÓN AL CARGAR LA PÁGINA ===
    // ===================================================================
    document.addEventListener('DOMContentLoaded', function() {
        // Cargar listas iniciales
        const selectPais = document.getElementById('cliente_pais');
        if (selectPais) {
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
                    error('Error al cargar países');
                });
        }

        // Cargar comerciales
        const selectComercial = document.getElementById('cliente_nombre_comercial');
        if (selectComercial) {
            fetch('/api/get_comercial.php')
                .then(r => r.json())
                .then(data => {
                    selectComercial.innerHTML = '<option value="">Seleccionar comercial</option>';
                    (data.comerciales || []).forEach(comercial => {
                        const option = document.createElement('option');
                        option.value = comercial.id_comercial;
                        option.textContent = comercial.nombre;
                        selectComercial.appendChild(option);
                    });
                })
                .catch(err => {
                    console.error('Error al cargar comerciales:', err);
                    error('No se pudieron cargar los comerciales asignados');
                });
        }

        // Notificaciones
        function mostrarNotificacion(mensaje, tipo = 'info') {
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `<i class="fas fa-info-circle"></i> ${mensaje}`;
            document.body.appendChild(toast);
            
            let icono = 'fa-info-circle';
            switch (tipo) {
                case 'exito': 
                    toast.classList.add('success'); 
                    icono = 'fa-check-circle'; 
                    break;
                case 'error': 
                    toast.classList.add('error'); 
                    icono = 'fa-times-circle'; 
                    break;
                case 'warning': 
                    toast.classList.add('warning'); 
                    icono = 'fa-exclamation-triangle'; 
                    break;
                default: 
                    toast.classList.add('info');
            }
            toast.querySelector('i').className = `fas ${icono}`;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => document.body.removeChild(toast), 300);
            }, 5000);
        }

        window.exito = (msg) => mostrarNotificacion(msg, 'exito');
        window.error = (msg) => mostrarNotificacion(msg, 'error');
        window.warning = (msg) => mostrarNotificacion(msg, 'warning');
        window.info = (msg) => mostrarNotificacion(msg, 'info');

        // Manejar éxito en URL
        const urlParams = new URLSearchParams(window.location.search);
        const mensajeExito = urlParams.get('exito');
        if (mensajeExito) {
            exito(decodeURIComponent(mensajeExito));
            history.replaceState({}, document.title, '?page=ficha_cliente');
        }

        // Validar RUT duplicado
        document.getElementById('cliente_rut')?.addEventListener('blur', async function() {
            const rut = this.value.trim();
            if (!rut) return;

            const rutFormateado = formatearRutParaMostrar(rut);
            if (!rutFormateado) {
                error('RUT inválido');
                this.value = '';
                return;
            }
            this.value = rutFormateado;

            try {
                const res = await fetch(`/api/validar_rut_cliente.php?rut=${encodeURIComponent(rutFormateado)}`);
                const data = await res.json();
                if (data.existe) {
                    error('El RUT ya está registrado en Ficha Cliente');
                    this.value = '';
                }
            } catch (e) {
                console.error('Error al validar RUT:', e);
            }
        });

        // Búsqueda inteligente
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
                        d.innerHTML = `<strong>${c.razon_social || 'Sin razón social'}</strong><br><small>RUT: ${c.rut || 'N/A'} | Giro: ${c.giro || ''}</small>`;
                        d.onclick = () => {
                            div.style.display = 'none';
                            this.value = '';
                            cargarCliente(c);
                        };
                        div.appendChild(d);
                    });
                    div.style.display = 'block';
                }
            } catch (e) {
                error('Error en búsqueda inteligente');
            }
        });

        // Listeners de botones
        document.getElementById('btn-agregar-contacto')?.addEventListener('click', () => abrirModalContacto());
    });
</script>