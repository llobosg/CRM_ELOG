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
    <!-- Mini consola de depuración -->
    <div id="debug-trace" style="margin: 1rem; padding: 0.5rem; background: #f0f8ff; border: 1px solid #87ceeb; border-radius: 4px; font-size: 0.85rem; display: none;"></div>
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
                    <!-- Texto gestionado por JS -->
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

<!-- Modal: Resultados de Búsqueda -->
<div id="modal-resultados" class="modal">
    <div class="modal-content" style="max-width:800px;position:relative;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h3><i class="fas fa-search"></i> Resultados de Búsqueda</h3>
            <button onclick="cerrarModalResultados()" style="background:#ccc;border:none;padding:0.5rem 1rem;border-radius:6px;cursor:pointer;">Volver</button>
        </div>
        <div class="table-container">
            <table id="tabla-resultados">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Razón Social</th>
                        <th>RUT</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="resultados-body"></tbody>
            </table>
        </div>
    </div>
</div>
<!-- Modal: Notas Comerciales -->
<div id="modal-comercial" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <h3><i class="fas fa-comments"></i> Notas Comerciales</h3>
        <span class="close" onclick="cerrarModalComercial()" style="cursor:pointer;">&times;</span>
        <textarea id="notas_comerciales_input" rows="6" placeholder="Escribe tus comentarios comerciales..." 
                  style="width:100%; padding: 0.8rem; margin: 1rem 0; border-radius: 6px; border: 1px solid #ccc; font-size: 0.9rem;"></textarea>
        <div class="modal-footer" style="text-align: right; gap: 0.5rem;">
            <button type="button" onclick="cerrarModalComercial()" style="background:#6c757d;">Cerrar</button>
            <button type="button" onclick="guardarNotasComerciales()" style="background:#009966;">Guardar</button>
        </div>
    </div>
</div>
<!-- Modal: Notas Operaciones -->
<div id="modal-operaciones" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <h3><i class="fas fa-clipboard-list"></i> Notas Operaciones</h3>
        <span class="close" onclick="cerrarModalOperaciones()" style="cursor:pointer;">&times;</span>
        <textarea id="notas_operaciones_input" rows="6" placeholder="Escribe tus comentarios de operaciones..." 
                  style="width:100%; padding: 0.8rem; margin: 1rem 0; border-radius: 6px; border: 1px solid #ccc; font-size: 0.9rem;"></textarea>
        <div class="modal-footer" style="text-align: right; gap: 0.5rem;">
            <button type="button" onclick="cerrarModalOperaciones()" style="background:#6c757d;">Cerrar</button>
            <button type="button" onclick="guardarNotasOperaciones()" style="background:#009966;">Guardar</button>
        </div>
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

<!-- ========================    Submodal: Costos/Ventas/Gastos    ============================================= -->
<!-- Submodal: Costos/Ventas/Gastos -->
<div id="submodal-costos" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:11000;">
    <div class="modal-content" style="max-width: 1400px; width: 95%; margin: 1.5rem auto; background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
        <h3><i class="fas fa-calculator"></i> Costos, Ventas y Gastos</h3>
        <span class="close" onclick="cerrarSubmodalCostos()" style="cursor:pointer; float:right; font-size:1.8rem; margin-top:-5px;">&times;</span>

        <!-- Formulario de entrada -->
        <div style="display: grid; grid-template-columns: repeat(10, 1fr); gap: 0.7rem; margin: 1.2rem 0; align-items: center; background: #f8f9fa; padding: 1rem; border-radius: 6px;">
            <select id="costo_concepto" style="grid-column: span 2; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; width: 100%;">
                <option value="">Seleccionar concepto</option>
                <!-- Se llenará desde API -->
            </select>
            <input type="text" id="costo_moneda" readonly style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; background: #e9ecef; text-align: center; width: 80px;" />
            <input type="number" id="costo_qty" step="0.01" min="0" placeholder="Qty" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; text-align: right; width: 80px;" />
            <input type="number" id="costo_costo" step="0.01" min="0" placeholder="Costo" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; background-color: #fff9db; text-align: right; width: 80px;" />
            <input type="text" id="costo_total_costo" readonly placeholder="Total Costo" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; background-color: #fff9db; text-align: right; width: 80px;" />
            <input type="number" id="costo_tarifa" step="0.01" min="0" placeholder="Tarifa" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; background-color: #e6f7ff; text-align: right; width: 80px;" />
            <input type="text" id="costo_total_tarifa" readonly placeholder="Total Tarifa" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; background-color: #e6f7ff; text-align: right; width: 80px;" />
            <select id="costo_aplica" style="grid-column: span 2; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; width: 100%;">
                <option value="">Seleccionar aplica</option>
                <!-- Se llenará según medio_transporte -->
            </select>
            <button type="button" onclick="guardarCosto()" style="grid-column: span 1; background: #009966; color: white; border: none; padding: 0.6rem; border-radius: 6px; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 0.3rem;">
                <i class="fas fa-plus"></i> Agregar
            </button>
        </div>

        <!-- Tabla de costos -->
        <div class="table-container" style="margin-top: 1.2rem; overflow-x: auto;">
            <table id="tabla-costos" style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
                <thead>
                    <tr style="background: #f1f3f5;">
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; font-size: 0.92rem;">Concepto</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; font-size: 0.92rem;">Moneda</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; font-size: 0.92rem;">Qty</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; background-color: #fff9db; font-size: 0.92rem;">Costo</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; background-color: #fff9db; font-size: 0.92rem;">Total Costo</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; background-color: #e6f7ff; font-size: 0.92rem;">Tarifa</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; background-color: #e6f7ff; font-size: 0.92rem;">Total Tarifa</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; font-size: 0.92rem;">Aplica</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; font-size: 0.92rem;">Acción</th>
                    </tr>
                </thead>
                <tbody id="costos-body"></tbody>
                <tfoot>
                    <tr style="font-weight: normal; background: #f9fafcff;">
                        <td colspan="4" style="padding: 0.6rem; text-align: right; border: 1px solid #ddd;">TOTAL COSTO:</td>
                        <td id="total-costo-costos" style="padding: 0.6rem; text-align: right; border: 1px solid #ddd; background-color: #fff9db;">0.00</td>
                        <td style="padding: 0.6rem; text-align: right; border: 1px solid #ddd;">TOTAL TARIFA:</td>
                        <td id="total-tarifa-costos" style="padding: 0.6rem; text-align: right; border: 1px solid #ddd; background-color: #e6f7ff;">0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Botón Volver -->
        <div style="text-align: right; margin-top: 1.5rem;">
            <button type="button" onclick="cerrarSubmodalCostos()" style="background: #6c757d; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; font-size: 0.95rem;">
                <i class="fas fa-arrow-left"></i> Volver
            </button>
        </div>
    </div>
</div>

<!-- ======================================================================================================= -->
<!-- Submodal: Gastos Locales -->
<div id="submodal-gastos-locales" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:11000;">
    <div class="modal-content" style="max-width: 1400px; width: 95%; margin: 1.5rem auto; background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
        <h3><i class="fas fa-file-invoice-dollar"></i> Gastos Locales</h3>
        <span class="close" onclick="cerrarSubmodalGastosLocales()" style="cursor:pointer; float:right; font-size:1.8rem; margin-top:-5px;">&times;</span>
        
        <!-- Formulario de entrada -->
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 0.7rem; margin: 1.2rem 0; align-items: center; background: #f8f9fa; padding: 1rem; border-radius: 6px;">
            <select id="gasto_tipo" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem;">
                <option value="">Tipo</option>
                <option value="Costo">Costo</option>
                <option value="Ventas">Ventas</option>
            </select>
            <select id="gasto_gasto" style="grid-column: span 2; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem;">
                <option value="">Gastos</option>
                <!-- Se llenará dinámicamente -->
            </select>
            <select id="gasto_moneda" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem;">
                <option value="USD">USD</option>
                <option value="CLP">CLP</option>
                <option value="EUR">EUR</option>
            </select>
            <input type="number" id="gasto_monto" step="0.01" min="0" placeholder="Monto" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; text-align: right;" />
            <select id="gasto_afecto" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem;">
                <option value="SI">SI</option>
                <option value="NO">NO</option>
            </select>
            <input type="number" id="gasto_iva" step="0.01" min="0" placeholder="IVA %" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; text-align: right;" />
            <button type="button" onclick="guardarGastoLocal()" style="grid-column: span 1; background: #009966; color: white; border: none; padding: 0.6rem; border-radius: 6px; font-size: 0.9rem;">
                <i class="fas fa-plus"></i> Agregar
            </button>
        </div>

        <!-- Tabla de gastos locales -->
        <div class="table-container" style="margin-top: 1.2rem; overflow-x: auto;">
            <table id="tabla-gastos-locales" style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
                <thead>
                    <tr style="background: #f1f3f5;">
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd;">Tipo</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd;">Gastos</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd;">Moneda</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd;">Monto</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd;">Afecto</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd;">IVA %</th>
                        <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd;">Acción</th>
                    </tr>
                </thead>
                <tbody id="gastos-locales-body"></tbody>
            </table>
        </div>

        <!-- Cuadro totalizador -->
        <div style="display: grid; grid-template-columns: repeat(4, max-content); gap: 1.5rem 2rem; margin: 1.5rem 0; padding: 1rem; background: #f8f9fa; border-radius: 6px; justify-content: start; align-items: center;">
            <div><strong>TOTAL VENTA:</strong></div>
            <div id="total-venta-gastos" style="font-weight: bold; text-align: right; min-width: 80px;">0.00</div>
            
            <div><strong>TOTAL COSTO:</strong></div>
            <div id="total-costo-gastos" style="font-weight: bold; text-align: right; min-width: 80px;">0.00</div>
            
            <div><strong>PROFIT LOCAL:</strong></div>
            <div id="profit-local" style="font-weight: bold; text-align: right; min-width: 80px;">0.00</div>
            
            <div><strong>PROFIT %:</strong></div>
            <div id="profit-porcentaje" style="font-weight: bold; text-align: right; min-width: 80px;">0.00 %</div>
        </div>

        <!-- Botones de acción -->
        <div style="text-align: right; margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 0.8rem;">
            <button type="button" onclick="cerrarSubmodalGastosLocales()" style="background: #6c757d; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; font-size: 0.95rem;">
                <i class="fas fa-arrow-left"></i> Volver
            </button>
        </div>
    </div>
</div>
<script>
    console.log('✅ Rol cargado:', USER_ROLE);
</script>