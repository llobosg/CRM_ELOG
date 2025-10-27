<!-- Mini consola de depuración -->
<div id="debug-trace" style="margin: 1rem; padding: 0.5rem; background: #f0f8ff; border: 1px solid #87ceeb; border-radius: 4px; font-size: 0.85rem; display: none;"></div>

<!-- ========   Para búsqueda inteligente  ==== -->
<!-- Espacio entre menú y búsqueda inteligente -->
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

<!-- ========                                    INICIO FORM              =================== --->
<!-- ========   SECCIÓN PROSPECTO   ======= -->
<form method="POST" id="form-prospecto" action="">
    <!-- Campos ocultos -->
    <input type="hidden" name="id_ppl" id="id_ppl" />
    <input type="hidden" name="id_prospect" id="id_prospect" />
    <!-- Sección Prospecto -->
    <div class="card" style="margin-bottom: 2rem;">
        <h3 style="margin:0 0 1rem 0; color:#3a4f63; font-size:1.1rem;"><i class="fas fa-user"></i> Datos del Prospecto</h3>
        <!-- Fila 1: RUT, Razón Social, Teléfono, Fecha Alta -->
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
        <!-- Fila 2: País, Dirección, Estado -->
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <!-- País -->
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">País</label>
            <select name="pais" id="pais" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
                <option value="">Seleccionar país</option>
                <!-- Llenado por JS -->
            </select>
            <!-- Dirección -->
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Dirección</label>
            <input type="text" name="direccion" id="direccion" 
                style="grid-column: span 3; width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" />
            <!-- Estado -->
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Estado</label>
            <select name="estado" id="estado" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
                <option value="Pendiente">Pendiente</option>
                <option value="Enviado">Enviado</option>
                <option value="Devuelto_pendiente">Devuelto_pendiente</option>
                <option value="CerradoOK">CerradoOK</option>
                <option value="Rechazado">Rechazado</option>
            </select>
        </div>
        <!-- Fila 3: Operación, Tipo Operación, Concatenado -->
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
        <!-- Fila 4: Booking, Comercial ID, Nombre, Incoterm + Botón Eliminar Prospecto -->
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Booking</label>
            <input type="text" name="booking" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" />
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Comercial ID</label>
            <input type="number" name="id_comercial" id="id_comercial" min="1" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" />
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Nombre</label>
            <input type="text" name="nombre" id="nombre" readonly style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; background: #f8f9fa; box-sizing: border-box;" />
            <label style="font-size: 0.9rem; font-weight: 500; color: #444;">Incoterm</label>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <input type="text" name="incoterm" style="flex: 1; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;" />
                <button type="button" id="btn-eliminar-prospecto" class="btn-delete" 
                        style="background: #dc3545; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 0.85rem; display: none;"
                        onclick="eliminarProspecto()">
                    🗑️ Eliminar
                </button>
            </div>
        </div>
        <!-- Espacio entre Prospecto y Servicios -->
        <div style="height: 1rem;"></div>
        <button type="button" id="btn-eliminar-prospecto" class="btn-delete" 
                style="background: #dc3545; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 0.85rem; display: none;"
                onclick="eliminarProspecto()">
            🗑️
        </button>
    </div>

    <!-- TABLA SERVICIOS -->
    <div class="card">
        <h3><i class="fas fa-truck"></i> <i class="fas fa-plane"></i> <i class="fas fa-ship"></i> Servicios Asociados</h3>
        <!-- Botones de acción + Volver + Grabar Todo -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem;">
            <div style="display: flex; gap: 1rem;">
                <button type="button" class="btn-add" style="font-size: 0.8rem;" onclick="abrirModalServicio()" id="btn-agregar-servicio" disabled>
                    <i class="fas fa-plus"></i> Agregar
                </button>
                <button type="button" class="btn-comment" onclick="abrirModalComercial()" style="font-size: 0.8rem;"><i class="fas fa-comments"></i> Comerciales</button>
                <button type="button" class="btn-comment" onclick="abrirModalOperaciones()" style="font-size: 0.8rem;"><i class="fas fa-clipboard-list"></i> Operaciones</button>
                <button type="button" id="btn-volver" class="btn-secondary" 
                        style="font-size: 0.8rem; background-color: #6c757d; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; display: none;">
                    <i class="fas fa-undo"></i> Volver
                </button>
            </div>
            <!-- Botón Grabar Todo -->
            <div id="contenedor-boton-prospecto" style="display: flex;">
                <button type="button" class="btn-primary" id="btn-save-all" style="min-width: 120px; padding: 0.6rem 1rem;">
                    Grabar Todo
                </button>
            </div>
        </div>

        <!-- Modal de confirmación personalizado -->
        <div id="modal-confirm" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10000;">
            <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:1.5rem; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.2); text-align:center; max-width:400px;">
                <p style="margin:0 0 1.2rem 0; font-size:1rem;">¿Desea volver sin guardar los cambios?</p>
                <div style="display:flex; gap:0.8rem; justify-content:center;">
                    <button type="button" id="btn-confirm-no" style="padding:0.5rem 1.2rem; background:#6c757d; color:white; border:none; border-radius:4px; cursor:pointer;">Cancelar</button>
                    <button type="button" id="btn-confirm-yes" style="padding:0.5rem 1.2rem; background:#009966; color:white; border:none; border-radius:4px; cursor:pointer;">Aceptar</button>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table id="tabla-servicios">
                <thead>
                    <tr>
                        <th style="width: 25%;">Servicio</th>
                        <th>Tráfico</th>
                        <th>Base Cálculo</th>
                        <th>Moneda</th>
                        <th>Tarifa</th>
                        <th>Costo</th>
                        <th>Venta</th>
                        <th>GDC</th>
                        <th>GDV</th>
                        <th>Acción</th>
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

    <!-- Modal: Servicio -->
    <div id="modal-servicio" class="modal">
        <div class="modal-content" style="max-width: 1500px; width: 95%;">
            <h3>
                <i class="fas fa-box"></i> Agregar Servicio para 
                <span style="color: #007bff; font-weight: bold;" id="serv_titulo_concatenado">-</span>
            </h3>
            <span class="close" onclick="cerrarModalServicio()" style="cursor:pointer;">&times;</span>
            <!-- Campos ocultos -->
            <input type="hidden" id="id_prospect_serv" name="id_prospect_serv" />
            <input type="hidden" id="concatenado_serv" name="concatenado_serv" />
            <input type="hidden" id="id_srvc_actual" />
            <!-- Formulario en grid de 8 columnas -->
            <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 0.8rem; margin-top: 1.2rem; align-items: center;">
                <!-- Fila 1 -->
                <label style="font-size: 0.9rem;">Servicio</label>
                <input type="text" id="serv_servicio" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label style="font-size: 0.9rem;">Medio Transporte</label>
                <select id="serv_medio_transporte" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                    <option value="">Seleccionar</option>
                </select>
                <label style="font-size: 0.9rem;">Commodity</label>
                <select id="serv_commodity" style="grid-column: span 3; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                    <option value="">Seleccionar</option>
                </select>
                <!-- Fila 2 -->
                <label style="font-size: 0.9rem;">Origen</label>
                <select id="serv_origen" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                    <option value="">Seleccionar</option>
                </select>
                <label style="font-size: 0.9rem;">País Origen</label>
                <input type="text" id="serv_pais_origen" readonly style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; background: #f9f9f9;" />
                <label style="font-size: 0.9rem;">Destino</label>
                <select id="serv_destino" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                    <option value="">Seleccionar</option>
                </select>
                <label style="font-size: 0.9rem;">País Destino</label>
                <input type="text" id="serv_pais_destino" readonly style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; background: #f9f9f9;" />
                <!-- Fila 3 -->
                <label style="font-size: 0.9rem;">Tránsito</label>
                <input type="text" id="serv_transito" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label style="font-size: 0.9rem;">Frecuencia</label>
                <input type="text" id="serv_frecuencia" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label style="font-size: 0.9rem;">Lugar Carga</label>
                <input type="text" id="serv_lugar_carga" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label style="font-size: 0.9rem;">Sector</label>
                <input type="text" id="serv_sector" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <!-- Fila 4 -->
                <label style="font-size: 0.9rem;">Mercancía</label>
                <input type="text" id="serv_mercancia" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label style="font-size: 0.9rem;">Bultos</label>
                <input type="number" id="serv_bultos" min="1" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label style="font-size: 0.9rem;">Peso (kg)</label>
                <input type="number" id="serv_peso" step="0.01" min="0" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label style="font-size: 0.9rem;">Volumen (m³)</label>
                <input type="number" id="serv_volumen" step="0.01" min="0" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <!-- Fila 5 -->
                <label style="font-size: 0.9rem;">Dimensiones</label>
                <input type="text" id="serv_dimensiones" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" placeholder="Ej: 120x80x90 cm" />
                <label style="font-size: 0.9rem;">Moneda</label>
                <select id="serv_moneda" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="CLP">CLP</option>
                </select>
                <label style="font-size: 0.9rem;">Tipo Cambio</label>
                <input type="number" id="serv_tipo_cambio" step="0.01" min="0" value="1" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label style="font-size: 0.9rem;">Proveedor Nac</label>
                <select id="serv_proveedor_nac" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                    <option value="">Seleccionar</option>
                    <!-- Se llenará desde API -->
                </select>
                <!-- Fila 6 -->
                <label style="font-size: 0.9rem;">AOL</label>
                <input type="text" id="serv_aol" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" maxlength="4" />
                <label style="font-size: 0.9rem;">AOD</label>
                <input type="text" id="serv_aod" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" maxlength="4" />
                <label style="font-size: 0.9rem;">Desconsolidación</label>
                <input type="text" id="serv_desconsolidacion" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label style="font-size: 0.9rem;">Agente</label>
                <select id="serv_agente" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                    <option value="">Seleccionar</option>
                    <!-- Se llenará desde API -->
                </select>
                <!-- Fila 7 -->
                <label style="font-size: 0.9rem;">Aerolínea</label>
                <input type="text" id="serv_aerolinea" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label style="font-size: 0.9rem;">Terrestre</label>
                <input type="text" id="serv_terrestre" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label style="font-size: 0.9rem;">Marítimo</label>
                <input type="text" id="serv_maritimo" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label style="font-size: 0.9rem;">Ref. Cliente</label>
                <input type="text" id="serv_ref_cliente" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
            </div>
            <!-- Botones de acción del modal Servicio -->
            <div class="modal-footer" style="text-align: right; margin-top: 1.5rem; gap: 0.8rem;">
                <button type="button" class="btn-comment" id="btn-costos-servicio"
                        style="background: #231b92ff; color: white; font-size: 0.8rem; margin-top: 1rem; 
                            cursor: pointer !important; 
                            pointer-events: auto !important; 
                            position: relative; 
                            z-index: 2;">
                    <i class="fas fa-calculator"></i> Costos - Ventas
                </button>
                <button type="button" class="btn-comment" id="btn-gastos-locales"
                        style="background: #8a2be2; color: white; font-size: 0.8rem; margin-top: 1rem; 
                            cursor: pointer !important; pointer-events: auto !important;">
                    <i class="fas fa-file-invoice-dollar"></i> Gastos Locales
                </button>
                <button type="button" onclick="cerrarModalServicioConConfirmacion()" style="background: #6c757d; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; cursor: pointer;">
                    Volver
                </button>
                <button type="button" onclick="guardarServicio()" style="background: #009966; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; cursor: pointer;">
                    Agregar Servicio
                </button>
            </div>
        </div>
    </div>

    <!-- Servicios en formato JSON -->
    <input type="hidden" name="servicios_json" id="servicios_json" />
</form>

<!-- Modales adicionales (Resultados, Comercial, Operaciones, Submodales) -->
<!-- ... (todo el HTML de modales ya está en tu archivo original y se mantiene igual) ... -->

<!-- SCRIPTS -->
<script>
// === VARIABLES GLOBALES ===
let servicios = [];
let costosServicio = [];
let gastosLocales = [];
let servicioEnEdicion = null;
let estadoProspecto = 'Pendiente';
let tieneServiciosIniciales = false;

// === NOTIFICACIONES ===
function mostrarNotificacion(mensaje, tipo = 'info') {
    const toast = document.getElementById('toast');
    const msgElement = document.getElementById('toast-message');
    if (!toast || !msgElement) return;
    msgElement.textContent = mensaje;
    toast.className = 'toast';
    let icono = 'fa-info-circle';
    switch (tipo) {
        case 'exito': toast.classList.add('success'); icono = 'fa-check-circle'; break;
        case 'error': toast.classList.add('error'); icono = 'fa-times-circle'; break;
        case 'warning': toast.classList.add('warning'); icono = 'fa-exclamation-triangle'; break;
        default: toast.classList.add('info');
    }
    const iconElement = toast.querySelector('i');
    if (iconElement) iconElement.className = `fas ${icono}`;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 5000);
}
const exito = (msg) => mostrarNotificacion(msg, 'exito');
const error = (msg) => mostrarNotificacion(msg, 'error');

// === CARGAR DATOS INICIALES ===
document.addEventListener('DOMContentLoaded', () => {
    // Cargar países
    const selectPais = document.getElementById('pais');
    if (selectPais) {
        const paises = ["Afganistán", "Albania", "Alemania", "Andorra", "Angola", "Antigua y Barbuda",
            "Arabia Saudita", "Argelia", "Argentina", "Armenia", "Australia", "Austria",
            "Azerbaiyán", "Bahamas", "Bangladés", "Barbados", "Baréin", "Bélgica",
            "Belice", "Benín", "Bielorrusia", "Birmania", "Bolivia", "Bosnia y Herzegovina",
            "Botsuana", "Brasil", "Brunéi", "Bulgaria", "Burkina Faso", "Burundi",
            "Bután", "Cabo Verde", "Camboya", "Camerún", "Canadá", "Catar",
            "Chad", "Chile", "China", "Chipre", "Ciudad del Vaticano", "Colombia",
            "Comoras", "Corea del Norte", "Corea del Sur", "Costa de Marfil", "Costa Rica",
            "Croacia", "Cuba", "Dinamarca", "Dominica", "Ecuador", "Egipto", "El Salvador",
            "Emiratos Árabes Unidos", "Eritrea", "Eslovaquia", "Eslovenia", "España",
            "Estados Unidos", "Estonia", "Etiopía", "Filipinas", "Finlandia", "Fiyi",
            "Francia", "Gabón", "Gambia", "Georgia", "Ghana", "Granada", "Grecia",
            "Guatemala", "Guinea", "Guinea Ecuatorial", "Guinea-Bisáu", "Guyana",
            "Haití", "Honduras", "Hungría", "India", "Indonesia", "Irak", "Irán",
            "Irlanda", "Islandia", "Islas Marshall", "Islas Salomón", "Israel", "Italia",
            "Jamaica", "Japón", "Jordania", "Kazajistán", "Kenia", "Kirguistán", "Kiribati",
            "Kuwait", "Laos", "Lesoto", "Letonia", "Líbano", "Liberia", "Libia", "Liechtenstein",
            "Lituania", "Luxemburgo", "Madagascar", "Malasia", "Malaui", "Maldivas", "Malí",
            "Malta", "Marruecos", "Mauricio", "Mauritania", "México", "Micronesia", "Moldavia",
            "Mónaco", "Mongolia", "Montenegro", "Mozambique", "Namibia", "Nauru", "Nepal",
            "Nicaragua", "Níger", "Nigeria", "Noruega", "Nueva Zelanda", "Omán", "Países Bajos",
            "Pakistán", "Palaos", "Panamá", "Papúa Nueva Guinea", "Paraguay", "Perú", "Polonia",
            "Portugal", "Reino Unido", "República Centroafricana", "República Checa", "República Democrática del Congo",
            "República Dominicana", "Ruanda", "Rumania", "Rusia", "Samoa", "San Cristóbal y Nieves",
            "San Marino", "San Vicente y las Granadinas", "Santa Lucía", "Santo Tomé y Príncipe",
            "Senegal", "Serbia", "Seychelles", "Sierra Leona", "Singapur", "Siria", "Somalia",
            "Sri Lanka", "Suazilandia", "Sudáfrica", "Sudán", "Sudán del Sur", "Suecia", "Suiza",
            "Surinam", "Tailandia", "Tanzania", "Tayikistán", "Timor Oriental", "Togo", "Tonga",
            "Trinidad y Tobago", "Túnez", "Turkmenistán", "Turquía", "Tuvalu", "Ucrania", "Uganda",
            "Uruguay", "Uzbekistán", "Vanuatu", "Venezuela", "Vietnam", "Yemen", "Yibuti", "Zambia", "Zimbabue"
        ];
        selectPais.innerHTML = '<option value="">Seleccionar país</option>';
        paises.forEach(pais => {
            const opt = document.createElement('option');
            opt.value = pais;
            opt.textContent = pais;
            selectPais.appendChild(opt);
        });
    }

    // Cargar operaciones
    const selectOperacion = document.getElementById('operacion');
    const selectTipoOper = document.getElementById('tipo_oper');
    if (selectOperacion && selectTipoOper) {
        fetch('/api/get_operaciones.php')
            .then(res => res.json())
            .then(data => {
                selectOperacion.innerHTML = '<option value="">Seleccionar</option>';
                (data.operaciones || []).forEach(op => {
                    const opt = document.createElement('option');
                    opt.value = op;
                    opt.textContent = op;
                    selectOperacion.appendChild(opt);
                });
            })
            .catch(err => console.error('Error al cargar operaciones:', err));

        selectOperacion.addEventListener('change', function () {
            const operacion = this.value;
            selectTipoOper.disabled = !operacion;
            selectTipoOper.innerHTML = '<option value="">Cargando...</option>';
            if (!operacion) {
                selectTipoOper.innerHTML = '<option value="">Seleccionar operación</option>';
                return;
            }
            fetch(`/api/get_tipos_por_operacion.php?operacion=${encodeURIComponent(operacion)}`)
                .then(res => res.json())
                .then(data => {
                    selectTipoOper.innerHTML = '<option value="">Seleccionar</option>';
                    (data.tipos || []).forEach(tipo => {
                        const opt = document.createElement('option');
                        opt.value = tipo;
                        opt.textContent = tipo;
                        selectTipoOper.appendChild(opt);
                    });
                })
                .catch(err => {
                    selectTipoOper.innerHTML = '<option value="">Error</option>';
                });
        });
    }

    // Calcular concatenado
    const calcularConcatenado = () => {
        const operacion = document.getElementById('operacion')?.value || '';
        const tipoOper = document.getElementById('tipo_oper')?.value || '';
        if (!operacion || !tipoOper) {
            document.getElementById('concatenado').value = '';
            return;
        }
        const opClean = operacion.replace(/[^a-zA-Z]/g, '').toUpperCase().substring(0, 2) || 'XX';
        const tipoClean = tipoOper.replace(/[^a-zA-Z]/g, '').toUpperCase().substring(0, 4) || 'XXXX';
        const prefijo = opClean + tipoClean;
        const hoy = new Date();
        const fechaCorta = hoy.toISOString().slice(2, 10).replace(/-/g, '');
        const idProspect = document.getElementById('id_prospect')?.value || '0';
        const correlativo = String(parseInt(idProspect) + 1).padStart(2, '0');
        document.getElementById('concatenado').value = `${prefijo}${fechaCorta}-${correlativo}`;
    };

    document.getElementById('operacion')?.addEventListener('change', calcularConcatenado);
    document.getElementById('tipo_oper')?.addEventListener('change', calcularConcatenado);

    // Botón Grabar Todo
    const btn = document.getElementById('btn-save-all');
    if (btn) btn.textContent = 'Grabar Todo';

    // Listeners de botones
    document.getElementById('btn-agregar-servicio')?.addEventListener('click', () => abrirModalServicio());
    document.getElementById('btn-save-all')?.addEventListener('click', () => {
        const form = document.getElementById('form-prospecto');
        const tieneServicios = servicios.length > 0;
        const modo = tieneServicios ? 'servicios' : 'prospecto';
        let inputModo = form.querySelector('input[name="modo"]');
        if (!inputModo) {
            inputModo = document.createElement('input');
            inputModo.type = 'hidden';
            inputModo.name = 'modo';
            form.appendChild(inputModo);
        }
        inputModo.value = modo;
        if (tieneServicios) {
            let inputJSON = form.querySelector('input[name="servicios_json"]');
            if (!inputJSON) {
                inputJSON = document.createElement('input');
                inputJSON.type = 'hidden';
                inputJSON.name = 'servicios_json';
                form.appendChild(inputJSON);
            }
            inputJSON.value = JSON.stringify(servicios);
        }
        form.submit();
    });

    // === FUNCIÓN DE CARGA DE PROSPECTO ===
    window.seleccionarProspecto = (id) => {
        fetch(`/api/get_prospecto.php?id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success || !data.prospecto) {
                    error('❌ Prospecto no encontrado');
                    return;
                }
                const p = data.prospecto;
                // Asignar campos
                ['razon_social', 'rut_empresa', 'fono_empresa', 'direccion', 'booking', 'incoterm', 'concatenado', 'fecha_alta', 'fecha_estado'].forEach(field => {
                    const el = document.querySelector(`[name="${field}"]`);
                    if (el) el.value = p[field] || '';
                });
                document.getElementById('id_comercial').value = p.id_comercial || '';
                document.getElementById('nombre').value = p.nombre || '';
                document.getElementById('estado').value = p.estado || 'Pendiente';
                document.getElementById('id_ppl').value = p.id_ppl || '';
                document.getElementById('id_prospect').value = p.id_prospect || '';

                // Notas
                const setNota = (name, value) => {
                    let input = document.querySelector(`input[name="${name}"]`);
                    if (!input) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = name;
                        document.getElementById('form-prospecto').appendChild(input);
                    }
                    input.value = value || '';
                    const modalInput = document.getElementById(`${name}_input`);
                    if (modalInput) modalInput.value = value || '';
                };
                setNota('notas_comerciales', p.notas_comerciales);
                setNota('notas_operaciones', p.notas_operaciones);

                // Servicios
                servicios = (data.servicios || []).map(s => ({
                    ...s,
                    costo: parseFloat(s.costo) || 0,
                    venta: parseFloat(s.venta) || 0,
                    costogastoslocalesdestino: parseFloat(s.costogastoslocalesdestino) || 0,
                    ventasgastoslocalesdestino: parseFloat(s.ventasgastoslocalesdestino) || 0,
                    costos: s.costos || [],
                    gastos_locales: s.gastos_locales || []
                }));

                // Actualizar UI
                actualizarTabla();
                document.getElementById('btn-agregar-servicio').disabled = false;
                document.getElementById('btn-volver').style.display = 'inline-block';
                exito('✅ Prospecto cargado');
            })
            .catch(err => {
                console.error('Error al cargar prospecto:', err);
                error('❌ Error al cargar el prospecto');
            });
    };

    // === ACTUALIZAR TABLA ===
    function actualizarTabla() {
        const tbody = document.getElementById('servicios-body');
        if (!tbody) return;
        tbody.innerHTML = '';
        let total_costo = 0, total_venta = 0, total_costogasto = 0, total_ventagasto = 0;
        servicios.forEach((s, index) => {
            const costo = parseFloat(s.costo) || 0;
            const venta = parseFloat(s.venta) || 0;
            const costogasto = parseFloat(s.costogastoslocalesdestino) || 0;
            const ventagasto = parseFloat(s.ventasgastoslocalesdestino) || 0;
            total_costo += costo;
            total_venta += venta;
            total_costogasto += costogasto;
            total_ventagasto += ventagasto;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="padding: 0.5rem; text-align: left; border-bottom: 1px solid #eee;">${s.servicio}</td>
                <td style="padding: 0.5rem; text-align: center; border-bottom: 1px solid #eee;">${s.trafico}</td>
                <td style="padding: 0.5rem; text-align: right; border-bottom: 1px solid #eee;">${s.base_calculo || ''}</td>
                <td style="padding: 0.5rem; text-align: center; border-bottom: 1px solid #eee;">${s.moneda}</td>
                <td style="padding: 0.5rem; text-align: right; border-bottom: 1px solid #eee;">${(parseFloat(s.tarifa) || 0).toFixed(2)}</td>
                <td style="padding: 0.5rem; text-align: right; border-bottom: 1px solid #eee;">${costo.toFixed(2)}</td>
                <td style="padding: 0.5rem; text-align: right; border-bottom: 1px solid #eee;">${venta.toFixed(2)}</td>
                <td style="padding: 0.5rem; text-align: right; border-bottom: 1px solid #eee;">${costogasto.toFixed(2)}</td>
                <td style="padding: 0.5rem; text-align: right; border-bottom: 1px solid #eee;">${ventagasto.toFixed(2)}</td>
                <td style="padding: 0.5rem; text-align: center; border-bottom: 1px solid #eee;">
                    <button type="button" class="btn-edit" onclick="editarServicio(${index})" style="margin-right: 0.5rem;">✏️</button>
                    <button type="button" class="btn-delete" onclick="eliminarServicio(${index})">🗑️</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
        document.getElementById('total-costo').textContent = total_costo.toFixed(2);
        document.getElementById('total-venta').textContent = total_venta.toFixed(2);
        document.getElementById('total-costogasto').textContent = total_costogasto.toFixed(2);
        document.getElementById('total-ventagasto').textContent = total_ventagasto.toFixed(2);
    }
    window.actualizarTabla = actualizarTabla;

    // === FUNCIONES AUXILIARES ===
    function editarServicio(index) { abrirModalServicio(index); }
    function eliminarServicio(index) { servicios.splice(index, 1); actualizarTabla(); }
    function abrirModalServicio(index = null) { /* Implementación básica */ alert('Modal de servicio'); }
    function abrirModalComercial() { /* Implementación básica */ alert('Notas comerciales'); }
    function abrirModalOperaciones() { /* Implementación básica */ alert('Notas operaciones'); }
    window.abrirModalComercial = abrirModalComercial;
    window.abrirModalOperaciones = abrirModalOperaciones;
});
</script>