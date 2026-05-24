<?php
require_once __DIR__ . '/../includes/auth_check.php';
// ✅ Nada más relacionado con roles
?>
<!-- Mini consola de depuración -->
<div id="debug-trace" style="margin: 1rem; padding: 0.5rem; background: #f0f8ff; border: 1px solid #87ceeb; border-radius: 4px; font-size: 0.85rem; display: none;"></div>

<!-- Búsqueda inteligente -->
<div style="height: 4rem;"></div>
<!-- Contenedor padre del input y resultados -->
<div style="margin: 1rem 0; position: relative;"> <!-- Añadido position: relative al contenedor padre -->
    <label><i class="fas fa-search"></i> Búsqueda Inteligente</label>
    <!-- El input ocupa el 100% del ancho disponible (hereda del contenedor padre) -->
    <input type="text" id="busqueda-inteligente" placeholder="Buscar por Concatenado, Razón Social, RUT..." style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px;" />
    <!-- El contenedor de resultados ahora se posiciona absolutamente respecto al contenedor padre -->
    <!-- Su ancho será el 100% del contenedor padre (el que tiene el margin), menos el padding del input y bordes -->
    <div id="resultados-busqueda" style="
        position: absolute;
        top: 100%; /* Colocar justo debajo del input */
        left: 0;   /* Alinear a la izquierda del contenedor padre */
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        max-height: 300px;
        overflow-y: auto;
        width: 100%; /* ✅ Ancho 100% del contenedor padre (ajustado por padding/border si es necesario) */
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        display: none;
    "></div>
</div>

<!-- ==============================================   FORM  ============================================== -->
<form method="POST" id="form-prospecto" action="">
    <input type="hidden" name="id_ppl" id="id_ppl" />
    <input type="hidden" name="id_prospect" id="id_prospect" />
    <input type="hidden" name="razon_social" id="razon_social_hidden" />
    <input type="hidden" name="notas_comerciales" id="notas_comerciales" />
    <input type="hidden" name="notas_operaciones" id="notas_operaciones" />
    <input type="hidden" name="total_venta_prospecto" id="total_venta_prospecto" value="0.00" />
    <input type="hidden" id="prospecto_razon_social" value="<?= htmlspecialchars($prospecto['razon_social'] ?? '') ?>">
    <input type="hidden" id="prospecto_direccion" value="<?= htmlspecialchars($prospecto['direccion'] ?? '') ?>">
    <input type="hidden" id="prospecto_rut_empresa" value="<?= htmlspecialchars($prospecto['rut_empresa'] ?? '') ?>">
    <input type="hidden" id="prospecto_contacto_nombre" value="<?= htmlspecialchars($contacto['nombre'] ?? '') ?>">
    <input type="hidden" id="prospecto_notas_comerciales" value="<?php echo htmlspecialchars($prospecto['notas_comerciales'] ?? ''); ?>">
    <input type="hidden" id="prospecto_notas_operaciones" value="<?php echo htmlspecialchars($prospecto['notas_operaciones'] ?? ''); ?>">
    <input type="hidden" id="serv_sector" value="" />
    <input type="hidden" id="serv_transito" value="" />
    <input type="hidden" id="serv_mercancia" value="" />
    <input type="hidden" name="id_comercial" id="id_comercial" />

    <!-- ========== DATOS DEL PROSPECTO ========== -->
    <div class="card" style="margin-bottom: 2rem; position: relative;">
        <h3>
            <i class="fas fa-user"></i> Datos del Prospecto
            <button type="button" class="close-prospecto" onclick="reiniciarFormProspecto()" title="Reiniciar formulario">
                &times;
            </button>
        </h3>
        <!-- Fila 1 -->
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <label>Razón Social *</label>
            <select id="razon_social_select" style="grid-column: span 3; width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" /> 
                <option value="">Seleccionar cliente</option>
            </select>
            <input type="text" name="razon_social" id="razon_social_input" placeholder="O escribe nueva razón social" style="grid-column: span 3; width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" />
            
        </div>
        <!-- Fila 2 -->
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <label>RUT Empresa *</label>
            <input type="text" name="rut_empresa" id="rut_empresa" style="width: 100%; padding: 0.5rem; background: #f8f9fa; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" />
            <label>Fecha</label>
            <input type="date" name="fecha_alta" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" value="<?= date('Y-m-d') ?>" />
            <label>Estado</label>
            <select name="estado" id="estado" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-weight: bold; box-sizing: border-box;">
                <option value="Pendiente">Pendiente</option>
                <option value="Enviado">Enviado</option>
                <option value="Devuelto_pendiente">Devuelto_pendiente</option>
                <option value="CerradoOK">CerradoOK</option>
                <option value="Rechazado">Rechazado</option>
            </select>
        </div>
        <!-- Fila 3 -->
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <label>País</label>
            <input type="text" name="pais" id="pais" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" />
            <label>Dirección</label>
            <input type="text" name="direccion" id="direccion" style="grid-column: span 3; width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" />
        </div>
        <!-- Fila 4: Operación y Tipo Operación -->
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <label>Operación *</label>
            <select name="operacion" id="operacion" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" required>
                <option value="">Seleccionar</option>
            </select>
            <label>Tipo Operación *</label>
            <select name="tipo_oper" id="tipo_oper" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" required>
                <option value="">Seleccionar</option>
            </select>
            <label>Concatenado</label>
            <input type="text" name="concatenado" id="concatenado" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-weight: bold; box-sizing: border-box;" readonly />
            <label>Booking</label>
            <input type="text" name="booking" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" />
        </div>
        <!-- Fila 5 -->
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <label>Comercial Asignado</label>
            <input type="text" name="nombre" id="nombre" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" />
            <label>Contacto Primario Clte.</label>
            <input type="text" name="contacto" id="contacto" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; background: #f8f9fa; box-sizing: border-box;" />
            <label>Email</label>
            <input type="text" name="email" id="email" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; background: #f8f9fa; box-sizing: border-box;" />
            <label>Teléfono</label>
            <input type="tel" name="fono_empresa" id="fono_empresa" style="width: 100%; padding: 0.5rem; background: #f8f9fa; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" />
        </div>
    </div>

    <!-- ========== SERVICIOS ASOCIADOS ========== -->
    <div class="card">
        <h3><i class="fas fa-truck"></i> Servicios Asociados</h3>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem;">
            <div style="display: flex; gap: 0.8rem;">
                <button type="button" class="btn-comment" id="btn-adjuntos"  title="Ver Adjuntos del Prospecto"><i class="fas fa-paperclip"></i> Adjuntos</button>
            </div>
            <div style="display: flex; gap: 0.8rem;">
                <button type="button" class="btn-add" id="btn-agregar-servicio" style="display: none;">
                    <i class="fas fa-plus"></i> Agregar Servicio
                </button>
                <button type="button" class="btn-primary" id="btn-save-all">
                    💾 Grabar Todo
                </button>
            </div>
        </div>
        <div class="table-container">
            <table id="tabla-servicios">
                <thead>
                    <tr>
                        <th>Servicio</th><th>Tráfico</th><th>Moneda</th><th>Bultos</th><th>Peso</th><th>Volumen</th>
                        <th>Costo</th><th>Venta</th><th>GDC</th><th>GDV</th><th>Acción</th>
                    </tr>
                </thead>
                <tbody id="servicios-body"></tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="6" style="text-align: right; font-weight: bold;">Totales:</td>
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

    <!-- Submodal: Cubicador -->
    <div id="submodal-cubicador" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:11000;">
        <div class="modal-content" style="max-width: 600px; width: 90%; margin: 2rem auto; background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
            <h3><i class="fas fa-cube"></i> Calculadora de Volumen y Peso</h3>
            <span class="close" onclick="cerrarSubmodalCubicador()" style="cursor:pointer; float:right; font-size:1.8rem; margin-top:-5px;">&times;</span>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 1rem 0;">
                <label>Cantidad de bultos</label>
                <input type="number" id="cubicador_qty" min="1" value="1" />
                <label>Peso bruto total (kg)</label>
                <input type="number" id="cubicador_peso" min="0.1" step="0.01" />
                <label>Largo (cm)</label>
                <input type="number" id="cubicador_largo" min="1" />
                <label>Ancho (cm)</label>
                <input type="number" id="cubicador_ancho" min="1" />
                <label>Alto (cm)</label>
                <input type="number" id="cubicador_alto" min="1" />
            </div>
            <div style="margin: 1rem 0; padding: 1rem; background: #f8f9fa; border-radius: 6px;">
                <h4>Resultados</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div><strong>Volumen total:</strong></div>
                    <div id="cubicador_volumen">0.00 m³</div>
                    <div><strong>Peso volumétrico:</strong></div>
                    <div id="cubicador_peso_vol">0.00 kg</div>
                    <div><strong>Peso a considerar:</strong></div>
                    <div id="cubicador_peso_final">0.00 kg</div>
                </div>
            </div>
            <div style="text-align: right; margin-top: 1rem;">
                <button type="button" class="btn-secondary" onclick="cerrarSubmodalCubicador()">Cancelar</button>
                <button type="button" class="btn-primary" onclick="aplicarCubicacion()">Aplicar a Servicio</button>
            </div>
        </div>
    </div>

    <!-- ========== MODALES ========== -->
    <!-- Modal Comercial -->
    <div id="modal-comercial" class="modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 12000;">
        <div class="modal-content" style="max-width: 650px; margin: 2rem auto; background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
            <h3><i class="fas fa-comments"></i> Notas Comerciales</h3>
            <span class="close" onclick="cerrarModalComercial()" style="cursor:pointer; float:right; font-size:1.8rem; margin-top:-5px;">&times;</span>
            <textarea id="notas_comerciales_input" rows="6" placeholder="..." style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px; margin: 1rem 0;"></textarea>
            <div class="modal-footer" style="text-align: right; margin-top: 1rem;">
                <button type="button" onclick="cerrarModalComercial()" style="background: #6c757d; color: white; border: none; padding: 0.5rem 1.2rem; border-radius: 6px; margin-right: 0.5rem;">Cerrar</button>
                <button type="button" onclick="guardarNotasComerciales()" style="background: #007bff; color: white; border: none; padding: 0.5rem 1.2rem; border-radius: 6px;">Guardar</button>
            </div>
        </div>
    </div>

    <!-- Modal Operaciones -->
    <div id="modal-operaciones" class="modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 12000;">
        <div class="modal-content" style="max-width: 650px; margin: 2rem auto; background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
            <h3><i class="fas fa-clipboard-list"></i> Notas Operaciones</h3>
            <span class="close" onclick="cerrarModalOperaciones()" style="cursor:pointer; float:right; font-size:1.8rem; margin-top:-5px;">&times;</span>
            <textarea id="notas_operaciones_input" rows="6" placeholder="..." style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px; margin: 1rem 0;"></textarea>
            <div class="modal-footer" style="text-align: right; margin-top: 1rem;">
                <button type="button" onclick="cerrarModalOperaciones()" style="background: #6c757d; color: white; border: none; padding: 0.5rem 1.2rem; border-radius: 6px; margin-right: 0.5rem;">Cerrar</button>
                <button type="button" onclick="guardarNotasOperaciones()" style="background: #007bff; color: white; border: none; padding: 0.5rem 1.2rem; border-radius: 6px;">Guardar</button>
            </div>
        </div>
    </div>

    <!-- Modal Servicio -->
    <div id="modal-servicio" class="modal" style="display:none;">
        <div class="modal-content" style="max-width: 1500px; width: 95%;">
            <h3><i class="fas fa-box"></i> Agregar Servicio para <span id="serv_titulo_concatenado">-</span></h3>
            <span class="close" onclick="cerrarModalServicio()">&times;</span>
            <input type="hidden" id="id_prospect_serv" name="id_prospect_serv" />
            <input type="hidden" id="concatenado_serv" name="concatenado_serv" />
            <input type="hidden" id="id_srvc_edit" name="id_srvc_edit" value="">
            <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 0.8rem; margin-top: 1.2rem; align-items: center;">
                
                <!-- Fila 1 -->
                <label>Servicio</label>
                <input type="text" id="serv_servicio" style="grid-column: span 3; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label>Commodity</label>
                <select id="serv_commodity" style="grid-column: span 3; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; width: 100%;">
                    <option value="">Seleccionar</option>
                </select>
                
                <!-- Fila 2 -->
                <label>Medio Transporte</label>
                <select id="serv_medio_transporte" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                    <option value="">Seleccionar</option>
                </select>                
                <label>Frecuencia</label>
                <input type="text" id="serv_frecuencia" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label>Dirección carga</label>
                <input type="text" id="serv_lugar_carga" style="grid-column: span 3; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />

                <!-- Fila 3 -->
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
                
                <!-- Fila 4 -->
                <label>Validez</label>
                <input type="date" id="serv_validez" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label>Incoterm</label>
                <input type="text" id="serv_incoterm" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label>Ref. Cliente</label>
                <input type="text" id="serv_ref_cliente" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <div></div>
                <div></div>
                <!-- Fila 5 -->
                <label>Bultos</label>
                <input type="number" id="serv_bultos" min="1" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label>Dimensiones</label>
                <input type="text" id="serv_dimensiones" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" placeholder="Ej: 120x80x90 cm" />
                <label>Peso (kg)</label>
                <input type="number" id="serv_peso" step="0.01" min="0" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label>Volumen (m³)</label>
                <input type="number" id="serv_volumen" step="0.01" min="0" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                
                <!-- Fila 6 -->
                <label>AOL</label>
                <input type="text" id="serv_aol" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" maxlength="4" />
                <label>AOD</label>
                <input type="text" id="serv_aod" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" maxlength="4" />
                <label>Proveedor Nac</label>
                <!-- ✅ CORRECCIÓN AQUÍ: Forzar ancho al 100% del contenedor -->
                <select id="serv_proveedor_nac" style="grid-column: span 3; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; width: 100%; box-sizing: border-box;">
                    <option value="">Seleccionar</option>
                </select>

                <!-- Fila 7 -->
                <label>Moneda</label>
                <select id="serv_moneda" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="CLP">CLP</option>
                </select>
                <label>Tipo Cambio</label>
                <input type="number" id="serv_tipo_cambio" step="0.01" min="0" value="1" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label>Agente</label>
                <select id="serv_agente" style="grid-column: span 3; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; width: 100%;">
                    <option value="">Seleccionar</option>
                </select>

                <!-- Fila 8 -->
                <label>Observaciones</label>
                <input type="text" id="serv_transportador" style="grid-column: span 3; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
            </div>

            <div class="modal-footer" style="text-align: right; margin-top: 1.5rem; gap: 0.8rem; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <button type="button" class="btn-comment" id="btn-cubicador" onclick="abrirSubmodalCubicador()">
                        <i class="fas fa-calculator"></i> Cubicador
                    </button>
                    <button type="button" class="btn-comment" id="btn-costos-servicio-dentro"><i class="fas fa-calculator"></i> Gastos-Ventas Internac.</button>
                    <button type="button" class="btn-comment" id="btn-gastos-locales-dentro"><i class="fas fa-file-invoice-dollar"></i> Costos Locales</button>
                    <button type="button" class="btn-comment" id="btn-notas-servicio-dentro" onclick="abrirSubmodalNotasServicio()"><i class="fas fa-sticky-note"></i> Notas del Servicio</button>
                    <!-- NUEVO: Botón para Route Order -->
                    <button type="button" class="btn-comment" id="btn-route-order" onclick="abrirSubmodalRouteOrder()"><i class="fas fa-route"></i> Router Order</button>
                </div>
                <div style="display: flex; gap: 0.8rem;">
                    <button type="button" class="btn-secondary" onclick="cerrarModalServicioConConfirmacion()">Volver</button>
                    <button type="button" class="btn-add" id="btn-guardar-servicio-modal">Agregar Servicio</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Nuevo Submodal Notas del Servicio -->
    <div id="submodal-notas-servicio" class="modal" style="display:none;">
        <div class="modal-content" style="max-width: 650px; width: 95%; margin: 1.5rem auto; background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
            <h3><i class="fas fa-sticky-note"></i> Notas del Servicio</h3>
            <span class="close" onclick="cerrarSubmodalNotasServicio()" style="cursor:pointer; float:right; font-size:1.8rem; margin-top:-5px;">&times;</span>
            <div style="margin: 1.2rem 0;">
                <label for="nota_servicio_textarea">Comentarios:</label>
                <textarea id="nota_servicio_textarea" rows="6" placeholder="Ingrese notas o comentarios para este servicio..."></textarea>
            </div>
            <div class="modal-footer" style="text-align: right; margin-top: 1.5rem; gap: 0.8rem; display: flex; justify-content: flex-end; align-items: center;">
                <button type="button" onclick="cerrarSubmodalNotasServicio()">Cerrar</button>
                <button type="button" onclick="guardarNotasServicio()">Guardar</button>
            </div>
        </div>
    </div>

    <!-- Submodal: Costos/Ventas/Gastos -->
    <div id="submodal-costos" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:11000;">
        <div class="modal-content" style="max-width: 1300px; width: 95%; margin: 1.5rem auto; background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
            <h3><i class="fas fa-calculator"></i> Costos, Ventas y Gastos</h3>
            <span class="close" onclick="cerrarSubmodalCostos()" style="cursor:pointer; float:right; font-size:1.8rem; margin-top:-5px;">&times;</span>
            
            <!-- Labels descriptivos -->
            <div style="display: grid; grid-template-columns: 26ch 8ch 6ch 9ch 8ch 12ch 12ch 12ch 30ch; gap: 0.5rem; margin: 0.5rem 0; align-items: center; background: #f8f9fa; padding: 1rem; border-radius: 6px; font-weight: bold;">
                <div>Concepto</div>
                <div>Moneda</div>
                <div>Qty</div>
                <div>%</div>
                <div>Costo</div>
                <div>Total Costo</div>
                <div>Tarifa</div>
                <div>Total Tarifa</div>
                <div>Aplica</div>
                <div></div>
            </div>

            <!-- Formulario de entrada -->
            <div style="display: grid; grid-template-columns: 30ch 8ch 8ch 9ch 12ch 12ch 12ch 12ch 30ch 16ch; gap: 0.5rem; margin: 0.5rem 0; align-items: center; background: #f8f9fa; padding: 1rem; border-radius: 6px;">
                <select id="costo_concepto" style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; width: 100%;">
                    <option value="">Seleccionar concepto</option>
                </select>
                <input type="text" id="costo_moneda" readonly style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; background: #e9ecef; text-align: center;" />
                <input type="number" id="costo_qty" step="0.01" min="0" placeholder="Qty" style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; text-align: right;" />
                <input type="number" id="costo_porcentaje_concepto" step="0.01" min="0" max="100" value="100" placeholder="% Concepto" style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; text-align: right;" />
                <input type="number" id="costo_costo" step="0.01" min="0" placeholder="Costo" style="padding: 0.6rem; border: 1px solid #787676ff; border-radius: 6px; font-size: 0.95rem; background-color: #fff9db; text-align: right;" />
                <input type="text" id="costo_total_costo" readonly placeholder="Total Costo" style="padding: 0.6rem; border: 1px solid #787676ff; border-radius: 6px; font-size: 0.95rem; background-color: #fff9db; text-align: right;" />
                <input type="number" id="costo_tarifa" step="0.01" min="0" placeholder="Tarifa" style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; background-color: #e6f7ff; text-align: right;" />
                <input type="text" id="costo_total_tarifa" readonly placeholder="Total Tarifa" style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; background-color: #e6f7ff; text-align: right;" />
                <select id="costo_aplica" style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; width: 100%;">
                    <option value="">Seleccionar aplica</option>
                </select>
                <button type="button" onclick="guardarCosto()" style="background: #009966; color: white; border: none; padding: 0.6rem; border-radius: 6px; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 0.3rem;">
                    <i class="fas fa-plus"></i> Agregar
                </button>
            </div>

            <!-- Tabla de costos registrados -->
            <div class="table-container" style="margin-top: 1.2rem; overflow-x: auto;">
                <table id="tabla-costos" style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
                    <thead>
                        <tr style="background: #f1f3f5;">
                            <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; font-size: 0.92rem; width: 12ch;">Concepto</th>
                            <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; font-size: 0.92rem; width: 4ch;">Moneda</th>
                            <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; font-size: 0.92rem; width: 4ch;">Qty</th>
                            <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; font-size: 0.92rem; width: 4ch;">%</th>
                            <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; background-color: #fff9db; font-size: 0.92rem; width: 4ch; color: #000000;">Costo</th>
                            <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; background-color: #fff9db; font-size: 0.92rem; width: 6ch; color: #000000;">Total Costo</th>
                            <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; background-color: #e6f7ff; font-size: 0.92rem; width: 4ch; color: #000000;">Tarifa</th>
                            <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; background-color: #e6f7ff; font-size: 0.92rem; width: 6ch; color: #000000;">Total Tarifa</th>
                            <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; font-size: 0.92rem; width: 6ch;">Aplica</th>
                            <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; font-size: 0.92rem; width: 4ch;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="costos-body"></tbody>
                </table>
            </div>

            <!-- Botones de notas -->
            <button type="button" class="btn-comment" onclick="abrirModalComercial()"><i class="fas fa-comments"></i> Notas Comerciales</button>
            <button type="button" class="btn-comment" onclick="abrirModalOperaciones()"><i class="fas fa-clipboard-list"></i> Notas Operaciones</button>
            
            <!-- Botón de cierre -->
            <div style="text-align: right; margin-top: 1.5rem;">
                <button type="button" onclick="cerrarSubmodalCostos()" style="background: #6c757d; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; font-size: 0.95rem;">
                    <i class="fas fa-arrow-left"></i> Volver
                </button>
            </div>
        </div>
    </div>

    <!-- Submodal: Gastos Locales -->
    <div id="submodal-gastos-locales" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:11000;">
        <div class="modal-content" style="max-width: 1400px; width: 95%; margin: 1.5rem auto; background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
            <h3><i class="fas fa-file-invoice-dollar"></i> Gastos Locales</h3>
            <span class="close" onclick="cerrarSubmodalGastosLocales()" style="cursor:pointer; float:right; font-size:1.8rem; margin-top:-5px;">&times;</span>
            <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 0.7rem; margin: 1.2rem 0; align-items: center; background: #f8f9fa; padding: 1rem; border-radius: 6px;">
                <select id="gasto_tipo" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem;">
                    <option value="">Tipo</option>
                    <option value="Costo">Costo</option>
                    <option value="Ventas">Ventas</option>
                </select>
                <select id="gasto_gasto" style="grid-column: span 2; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem;">
                    <option value="">Gastos</option>
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
                            <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd;">Total</th>
                            <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="gastos-locales-body"></tbody>
                </table>
            </div>
            <!-- Nueva tabla de totales por moneda (estilo sutil) -->
            <div style="margin: 1.5rem 0;">
                <h4 style="margin-bottom: 1rem; font-size: 1rem; color: #09325cff;">Totales por Moneda</h4>
                <table style="width: 40%; border-collapse: collapse; border: 1px solid #084888ff; background-color: #fff;"> <!-- Fondo blanco -->
                    <thead>
                        <tr style="background-color: #3f6586ff; font-size: 0.9rem;"> <!-- Color de fondo pastel más suave, tamaño de fuente reducido -->
                            <th style="padding: 0.5rem; text-align: center; border: 1px solid #dee2e6; width: 15ch; font-weight: normal;">Moneda</th> <!-- Ancho fijo, sin bold -->
                            <th style="padding: 0.5rem; text-align: center; border: 1px solid #dee2e6; width: 15ch; font-weight: normal;">Costos</th> <!-- Ancho fijo, sin bold -->
                            <th style="padding: 0.5rem; text-align: center; border: 1px solid #dee2e6; width: 15ch; font-weight: normal;">Ventas</th> <!-- Ancho fijo, sin bold -->
                            <th style="padding: 0.5rem; text-align: center; border: 1px solid #dee2e6; width: 15ch; font-weight: normal;">Profit</th> <!-- Ancho fijo, sin bold -->
                            <th style="padding: 0.5rem; text-align: center; border: 1px solid #dee2e6; width: 15ch; font-weight: normal;">Profit %</th> <!-- Ancho fijo, sin bold -->
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 0.5rem; border: 1px solid #dee2e6; font-size: 0.85rem;">USD</td> <!-- Tamaño de fuente ligeramente más pequeño -->
                            <td id="cgld_usd" style="padding: 0.5rem; text-align: right; border: 1px solid #dee2e6; font-size: 0.85rem;">000.000.00</td>
                            <td id="vgld_usd" style="padding: 0.5rem; text-align: right; border: 1px solid #dee2e6; font-size: 0.85rem;">000.000.00</td>
                            <td id="pgld_usd" style="padding: 0.5rem; text-align: right; border: 1px solid #dee2e6; font-size: 0.85rem;">000.000.00</td>
                            <td id="ppgld_usd" style="padding: 0.5rem; text-align: right; border: 1px solid #dee2e6; font-size: 0.85rem;">0.00 %</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem; border: 1px solid #dee2e6; font-size: 0.85rem;">EUR</td>
                            <td id="cgld_eur" style="padding: 0.5rem; text-align: right; border: 1px solid #dee2e6; font-size: 0.85rem;">0.00</td>
                            <td id="vgld_eur" style="padding: 0.5rem; text-align: right; border: 1px solid #dee2e6; font-size: 0.85rem;">0.00</td>
                            <td id="pgld_eur" style="padding: 0.5rem; text-align: right; border: 1px solid #dee2e6; font-size: 0.85rem;">0.00</td>
                            <td id="ppgld_eur" style="padding: 0.5rem; text-align: right; border: 1px solid #dee2e6; font-size: 0.85rem;">0.00 %</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem; border: 1px solid #dee2e6; font-size: 0.85rem;">CLP</td>
                            <td id="cgld_clp" style="padding: 0.5rem; text-align: right; border: 1px solid #dee2e6; font-size: 0.85rem;">0.00</td>
                            <td id="vgld_clp" style="padding: 0.5rem; text-align: right; border: 1px solid #dee2e6; font-size: 0.85rem;">0.00</td>
                            <td id="pgld_clp" style="padding: 0.5rem; text-align: right; border: 1px solid #dee2e6; font-size: 0.85rem;">0.00</td>
                            <td id="ppgld_clp" style="padding: 0.5rem; text-align: right; border: 1px solid #dee2e6; font-size: 0.85rem;">0.00 %</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div style="text-align: right; margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 0.8rem;">
                <button type="button" onclick="cerrarSubmodalGastosLocales()" style="background: #6c757d; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; font-size: 0.95rem;">
                    <i class="fas fa-arrow-left"></i> Volver
                </button>
            </div>
        </div>
    </div>

    <!-- Submodal de Adjuntos -->
    <div id="submodal-adjuntos" class="modal" style="display: none; position: fixed; z-index: 10001; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div class="modal-content" style="background-color: white; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 60%; max-width: 600px; border-radius: 8px;">
            <h3><i class="fas fa-paperclip"></i> Adjuntos del Prospecto</h3>
            <!-- Botón de cierre con evento onclick -->
            <span class="close" onclick="cerrarSubmodalAdjuntos()" style="cursor: pointer; float: right; font-size: 1.8rem; margin-top: -5px;">&times;</span>
            <div id="lista-adjuntos" style="margin: 1rem 0; max-height: 200px; overflow-y: auto;">
                <!-- Los adjuntos se cargarán aquí dinámicamente -->
            </div>
            <div style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem;">
                <!-- Input de archivo -->
                <input type="file" id="archivo-input" name="archivo_adjunto" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif" style="flex-grow: 1;" />
                <!-- Botón Subir con type="button" -->
                <button type="button" class="btn-primary" onclick="subirAdjunto()">Subir</button>
                <button type="button" class="btn-secondary" onclick="cerrarSubmodalAdjuntos()">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Nuevo Submodal Route Order -->
    <div id="submodal-route-order" class="modal" style="display: none; position: fixed; z-index: 10001; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div class="modal-content" style="background-color: white; margin: 2% auto; padding: 20px; border: 1px solid #888; width: 95%; max-width: 1600px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); max-height: 90vh; overflow-y: auto;">
            <h3><i class="fas fa-route"></i> Route Order</h3>
            <span class="close" onclick="cerrarSubmodalRouteOrder()" style="cursor: pointer; float: right; font-size: 1.8rem; margin-top: -5px;">&times;</span>
            <div id="route-order-content" style="margin-top: 1rem;">
                <!-- El contenido del Route Order se cargará aquí dinámicamente -->
            </div>
            <div class="modal-footer" style="text-align: right; margin-top: 1.5rem; gap: 0.8rem; display: flex; justify-content: flex-end; align-items: center;">
                <button type="button" class="btn-primary" onclick="exportarRouteOrderAExcel()"><i class="fas fa-file-excel"></i> Exportar a Excel</button>
                <button type="button" class="btn-secondary" onclick="cerrarSubmodalRouteOrder()">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Toast de notificaciones -->
    <div id="toast" class="toast" style="display:none;">
        <i class="fas fa-info-circle"></i> 
        <span id="toast-message">Mensaje</span>
    </div>

    <script>
        console.log('✅ Script de prospectos iniciado');
        // ===================================================================
        // === 1. VARIABLES GLOBALES ===
        // ===================================================================
        let servicios = [];
        let costosServicio = [];
        let gastosLocales = [];
        let servicioEnEdicion = null;
        let tieneServiciosIniciales = false;
        let estadoProspecto = 'Pendiente';
        let datosRouteOrder = null; // ← Variable global
        window.editarServicio = editarServicio;
        const USER_ROLE = '<?php echo $_SESSION["rol"] ?? "comercial"; ?>';

        // ===================================================================
        // === 2. FUNCIONES AUXILIARES ===
        // ===================================================================
        function mostrarNotificacion(mensaje, tipo = 'info') {
            const tipoMap = {
                'exito': 'success',
                'error': 'error',
                'advertencia': 'warning',
                'info': 'info'
            };
            const claseTipo = tipoMap[tipo] || 'info';

            const toast = document.getElementById('toast');
            const msg = document.getElementById('toast-message');
            if (!toast || !msg) return;

            msg.textContent = mensaje;
            toast.className = 'toast ' + claseTipo; // Ej: 'toast success'

            // Mostrar con animación
            toast.style.display = 'flex';
            // Forzar reflow para que la transición funcione
            void toast.offsetWidth;
            toast.classList.add('show');

            // Ocultar después de 5 segundos
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 400); // tiempo de transición
            }, 5000);
        }

        function cargarClientesEnSelect() {
            fetch('/api/get_todos_clientes.php')
                .then(r => r.json())
                .then(data => {
                    const sel = document.getElementById('razon_social_select');
                    if (!sel) return;
                    sel.innerHTML = '<option value="">Seleccionar cliente</option>';
                    (data.clientes || []).forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.rut;
                        opt.textContent = c.razon_social;
                        sel.appendChild(opt);
                    });
                })
                .catch(err => error('No se pudieron cargar los clientes'));
        }

        function exito(msg) { mostrarNotificacion(msg, 'exito'); }
        function error(msg) { mostrarNotificacion(msg, 'error'); }
        function advertencia(msg) { mostrarNotificacion(msg, 'advertencia'); }

        function limpiarCamposContacto() {
            const contactoEl = document.getElementById('contacto');
            const emailEl = document.getElementById('email');
            if (contactoEl) contactoEl.value = '';
            if (emailEl) emailEl.value = '';
        }

        document.getElementById('razon_social_select')?.addEventListener('change', function() {
            const rut = this.value;
            if (!rut) {
                ['rut_empresa', 'fono_empresa', 'pais', 'direccion', 'nombre', 'id_comercial'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.value = '';
                });
                return;
            }
            fetch(`/api/get_cliente.php?rut=${encodeURIComponent(rut)}`)
        .then(r => r.json())
            .then(data => {
                if (data.existe) {
                    const c = data.cliente;
                    // Campos visibles
                    document.getElementById('rut_empresa').value = c.rut || '';
                    document.getElementById('pais').value = c.pais || '';
                    document.getElementById('direccion').value = c.direccion || '';
                    document.getElementById('nombre').value = c.nombre_comercial || ''; // ✅ Nombre comercial
                    
                    // Campos ocultos para guardar
                    document.querySelector('input[name="razon_social"]').value = c.razon_social || '';
                    document.querySelector('input[name="id_comercial"]').value = c.id_comercial || ''; // ✅ ID comercial
                    
                    // Cargar contactos
                    fetch(`/api/get_contactos.php?rut=${encodeURIComponent(rut)}`)
                        .then(r2 => r2.json())
                        .then(data2 => {
                            const primario = (data2.contactos || []).find(ct => ct.primario === 'S');
                            document.getElementById('fono_empresa').value = primario?.fono || '';
                            document.getElementById('contacto').value = primario?.nom_contacto || '';
                            document.getElementById('email').value = primario?.email || '';
                        });
                }
            });
        });

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

        function calcularConcatenado() {
            const opSelect = document.getElementById('operacion');
            const tipoSelect = document.getElementById('tipo_oper');
            const op = opSelect?.value || '';
            const tipo = tipoSelect?.value || '';
            
            if (!op || !tipo) {
                document.getElementById('concatenado').value = '';
                return;
            }

            // Extraer abreviaturas
            const opClean = op.replace(/[^a-zA-Z]/g, '').toUpperCase().substring(0, 2) || 'XX';
            const tipoClean = tipo.replace(/[^a-zA-Z]/g, '').toUpperCase().substring(0, 4) || 'XXXX';

            const fecha = new Date().toISOString().slice(2, 10).replace(/-/g, '');
            const idProspect = parseInt(document.getElementById('id_prospect')?.value || '0') + 1;
            const correlativo = idProspect.toString().padStart(2, '0');

            const concatenado = `${opClean}${tipoClean}${fecha}-${correlativo}`;
            document.getElementById('concatenado').value = concatenado;
        }

        function actualizarTabla() {
            const tbody = document.getElementById('servicios-body');
            if (!tbody) return;
            tbody.innerHTML = '';
            let tc = 0, tv = 0, tgc = 0, tgv = 0;
            servicios.forEach((s, index) => {
                console.log(`📊 [FILA ${index}] Servicio antes de render:`, s);
                // Asegurar que los campos existan (pueden venir como strings o undefined)
                // ✅ Corrección robusta para evitar NaN
                const c = (s.costo && !isNaN(parseFloat(s.costo))) ? parseFloat(s.costo) : 0;
                const v = (s.venta && !isNaN(parseFloat(s.venta))) ? parseFloat(s.venta) : 0;
                console.log(`💰 [FILA ${index}] Costo=${c}, Venta=${v}`);
                const gc = (s.costogastoslocalesdestino && !isNaN(parseFloat(s.costogastoslocalesdestino))) ? parseFloat(s.costogastoslocalesdestino) : 0;
                const gv = (s.ventasgastoslocalesdestino && !isNaN(parseFloat(s.ventasgastoslocalesdestino))) ? parseFloat(s.ventasgastoslocalesdestino) : 0;

                tc += c; tv += v; tgc += gc; tgv += gv;

                // ✅ Solo permitir notificar si el servicio ya fue guardado
                // ✅ Icono de "Completado" con tooltip y acción
                let iconoCostos = '';
                if (s.id_srvc && !s.id_srvc.startsWith('TEMP_')) {
                    const rolUsuario = '<?php echo $_SESSION["rol"] ?? "comercial"; ?>';
                    const puedeNotificar = (rolUsuario === 'comercial' || rolUsuario === 'admin');

                    if (s.estado_costos === 'pendiente' || (!s.costos || s.costos.length === 0)) {
                            iconoCostos = '<i class="fas fa-paper-plane" style="color: #0066cc; cursor: pointer;" title="Notificar a Pricing"></i>';
                        } else if (s.estado_costos === 'solicitado') {
                            iconoCostos = '<i class="fas fa-envelope" style="color: #ff9900;" title="Esperando costos"></i>';
                        } else if (s.estado_costos === 'completado') {
                            iconoCostos = '<i class="fas fa-check-circle" style="color: #009966; cursor: pointer;" title="Costos listos para informar al Comercial"></i>';
                        } else if (s.estado_costos === 'revisado') {
                            iconoCostos = '<i class="fas fa-check-double" style="color: #006600;" title="Aprobado por Pricing"></i>';
                        }
                }
                console.log('🔍 [DEBUG] Estado costos:', s.estado_costos);
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${s.servicio || ''}</td>
                    <td>${s.trafico || ''}</td>
                    <td>${s.moneda || 'USD'}</td>
                    <td>${s.bultos || ''}</td>
                    <td>${s.peso || ''}</td>
                    <td>${s.volumen || ''}</td>
                    <td style="text-align: right;">${c.toFixed(2)}</td>
                    <td style="text-align: right;">${v.toFixed(2)}</td>
                    <td style="text-align: right;">${gc.toFixed(2)}</td>
                    <td style="text-align: right;">${gv.toFixed(2)}</td>
                    <td>
                        ${iconoCostos}
                        <button type="button" class="btn-edit-servicio" data-index="${index}">✏️</button>
                        <button type="button" class="btn-delete-servicio" data-index="${index}">🗑️</button>
                        <i class="fas fa-sticky-note nota-servicio-icono" data-index="${index}" title="Notas del Servicio"></i>
                        <span class="nota-preview" title="${s.nota_srvc || ''}">${s.nota_srvc ? s.nota_srvc : '(Sin notas)'}</span>
                        <!-- Nueva columna con ícono de PDF -->
                        <i class="fas fa-file-pdf pdf-servicio-icono" data-index="${index}" style="cursor: pointer; color: #d9534f; margin-left: 0.5rem;" title="Generar Cotización PDF"></i>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('total-costo').textContent = tc.toFixed(2);
            document.getElementById('total-venta').textContent = tv.toFixed(2);
            document.getElementById('total-costogasto').textContent = tgc.toFixed(2);
            document.getElementById('total-ventagasto').textContent = tgv.toFixed(2);

            // Listeners para ícono de "Completado" (solo para Pricing)
            document.querySelectorAll('#tabla-servicios i.fa-check-circle').forEach(icon => {
                icon.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const index = Array.from(row.parentNode.children).indexOf(row);
                    const servicio = servicios[index];

                    // ✅ Validación: solo Pricing y estado "completado"
                    if (USER_ROLE !== 'pricing') {
                        error('Solo el rol Pricing puede notificar al Comercial.');
                        return;
                    }
                    if (servicio.estado_costos !== 'completado') {
                        error('El servicio debe tener costos completados.');
                        return;
                    }

                    if (confirm('¿Notificar al Comercial que los costos están listos?')) {
                        fetch('/api/notificar_costos.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                id_srvc: servicio.id_srvc,
                                estado: 'revisado',
                                usuario_id: <?php echo (int)($_SESSION["user_id"] ?? 0); ?>
                            })
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                servicios[index].estado_costos = 'revisado';
                                servicios[index].revisado_por = <?php echo (int)($_SESSION["user_id"] ?? 0); ?>;
                                servicios[index].fecha_revisado = new Date().toISOString().slice(0, 19).replace('T', ' ');
                                actualizarTabla();
                                exito(data.message || 'Notificación enviada al Comercial');
                            } else {
                                error('Error: ' + (data.message || 'Intente nuevamente'));
                            }
                        })
                        .catch(err => {
                            console.error('❌ Error al notificar al Comercial:', err);
                            error('No se pudo conectar con el servidor');
                        });
                    }
                });
            });

            // Listeners mejorados para ícono de notificación (✉️)
            document.querySelectorAll('#tabla-servicios i.fa-paper-plane').forEach(icon => {
                // Eliminar listener anterior para evitar duplicados
                const newIcon = icon.cloneNode(true);
                icon.parentNode.replaceChild(newIcon, icon);
                newIcon.addEventListener('click', function() {
                    console.log('🔍 [ENVIAR] Clic detectado en ícono de notificación');

                    const row = this.closest('tr');
                    if (!row) {
                        console.error('❌ [ENVIAR] No se encontró la fila del servicio');
                        return;
                    }
                    const index = Array.from(row.parentNode.children).indexOf(row);
                    if (index < 0 || index >= servicios.length) {
                        console.error('❌ [ENVIAR] Índice de servicio inválido:', index);
                        return;
                    }
                    const servicio = servicios[index];
                    console.log('📄 [ENVIAR] Servicio seleccionado:', servicio);

                    // ✅ Validación 1: ID permanente
                    if (!servicio.id_srvc || servicio.id_srvc.startsWith('TEMP_')) {
                        console.warn('⚠️ [ENVIAR] Servicio tiene ID temporal. Se requiere guardar primero.');
                        alert('Debe guardar el prospecto primero antes de solicitar costos.');
                        return;
                    }

                    // ✅ Validación 2: Rol del usuario
                    const rolUsuario = '<?php echo $_SESSION["rol"] ?? "comercial"; ?>';
                    if (rolUsuario !== 'comercial' && rolUsuario !== 'admin') {
                        console.warn('⚠️ [ENVIAR] Rol no autorizado:', rolUsuario);
                        alert('Acción no permitida. Solo el rol "Comercial" puede solicitar costos al equipo de Pricing.');
                        return;
                    }

                    // ✅ Validación 3: Estado actual
                    if (servicio.estado_costos === 'solicitado' || servicio.estado_costos === 'completado') {
                        console.warn('⚠️ [ENVIAR] El servicio ya fue notificado:', servicio.estado_costos);
                        alert('El servicio ya está en estado "' + servicio.estado_costos + '".');
                        return;
                    }

                    if (confirm('¿Solicitar costos al equipo de Pricing?')) {
                        console.log('📤 [ENVIAR] Enviando solicitud a API...');

                        fetch('/api/notificar_costos.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                id_srvc: servicio.id_srvc,
                                estado: 'solicitado',
                                usuario_id: <?php echo (int)($_SESSION['user_id'] ?? 0); ?>
                            })
                        })
                        .then(response => {
                            console.log('📨 [ENVIAR] Respuesta recibida. Status:', response.status);
                            return response.json();
                        })
                        .then(data => {
                            console.log('✅ [ENVIAR] Respuesta de la API:', data);
                            if (data.success) {
                                servicios[index].estado_costos = 'solicitado';
                                servicios[index].solicitado_por = <?php echo (int)($_SESSION['user_id'] ?? 0); ?>;
                                servicios[index].fecha_solicitado = new Date().toISOString().slice(0, 19).replace('T', ' ');
                                actualizarTabla();
                                exito('Notificación enviada a Pricing');
                            } else {
                                error('Error: ' + (data.message || 'Intente nuevamente'));
                            }
                        })
                        .catch(err => {
                            console.error('💥 [ENVIAR] Error de red:', err);
                            error('No se pudo conectar con el servidor');
                        });
                    } else {
                        console.log('ℹ️ [ENVIAR] Acción cancelada por el usuario');
                    }
                });
            });

            // Listeners de edición/eliminación
            document.querySelectorAll('.btn-edit-servicio').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = parseInt(this.getAttribute('data-index'));
                    editarServicio(index);
                });
            });
            document.querySelectorAll('.btn-delete-servicio').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = parseInt(this.getAttribute('data-index'));
                    eliminarServicio(index);
                });
            });

            // Abrir submodal de notas
            document.querySelectorAll('.nota-servicio-icono').forEach(icon => {
                icon.addEventListener('click', function() {
                    const index = parseInt(this.getAttribute('data-index'));
                    abrirSubmodalNotasServicio(index);
                });
            });

            // NUEVO: Generar PDF
            document.querySelectorAll('.pdf-servicio-icono').forEach(icon => {
                icon.addEventListener('click', function() {
                    const index = parseInt(this.getAttribute('data-index'));
                    generarPDFCotizacion(index);
                });
            });

        }

        // --- Función para generar el PDF (v3 - Solicita PDF desde el servidor) ---
        function generarPDFCotizacion(servicioIndex) {
            const servicio = servicios[servicioIndex];
            if (!servicio || !servicio.id_srvc) {
                error('Servicio no encontrado o no tiene ID válido.');
                return;
            }

            // Construir la URL para el PDF
            const urlPdf = `/api/pdf_servicio.php?id_srvc=${encodeURIComponent(servicio.id_srvc)}`;

            // Abrir el PDF en una nueva pestaña/ventana
            // Esto hará que el navegador haga una petición GET a pdf_servicio.php
            window.open(urlPdf, '_blank');
        }

        // === NUEVA FUNCIÓN: Gestión de notificaciones de costos ===
        function manejarNotificacionCostos(servicio, index) {
            const rolUsuario = '<?php echo $_SESSION["rol"] ?? "comercial"; ?>';
            const estadoActual = servicio.estado_costos || 'pendiente';

            // === Cualquiera puede enviar un servicio SIN costos ===
            if (estadoActual === 'pendiente') {
                if (!confirm('¿Solicitar costos al equipo de Pricing?')) return;
                enviarNotificacionCostos(servicio.id_srvc, 'solicitado', index);
                return;
            }

            // === Solo Pricing puede marcar como completado ===
            if (estadoActual === 'solicitado') {
                if (rolUsuario !== 'pricing') {
                    alert('Solo el rol Pricing puede marcar los costos como completados.');
                    return;
                }
                if (!servicio.costos || servicio.costos.length === 0) {
                    alert('Debe agregar al menos un costo antes de notificar.');
                    return;
                }
                if (!confirm('¿Notificar al Comercial que los costos están listos?')) return;
                enviarNotificacionCostos(servicio.id_srvc, 'completado', index);
                return;
            }

            // === Solo Comercial puede aprobar (opcional) ===
            if (estadoActual === 'completado') {
                if (rolUsuario !== 'comercial') {
                    alert('Solo el Comercial puede aprobar los costos.');
                    return;
                }
                if (!confirm('¿Confirmar que los costos han sido revisados?')) return;
                enviarNotificacionCostos(servicio.id_srvc, 'revisado', index);
                return;
            }

            alert('Acción no permitida en este estado.');
        }

        function enviarNotificacionCostos(idSrvc, nuevoEstado, index) {
            fetch('/api/notificar_costos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_srvc: idSrvc,
                    estado: nuevoEstado,
                    usuario_id: '<?php echo $_SESSION["user_id"] ?? 0; ?>',
                    rol: '<?php echo $_SESSION["rol"] ?? "comercial"; ?>'
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // ✅ Actualizar estado localmente
                    servicios[index].estado_costos = nuevoEstado;
                    if (nuevoEstado === 'solicitado') {
                        servicios[index].solicitado_por = '<?php echo $_SESSION["user_id"] ?? 0; ?>';
                        servicios[index].fecha_solicitado = new Date().toISOString().slice(0, 19).replace('T', ' ');
                    } else if (nuevoEstado === 'completado') {
                        servicios[index].completado_por = '<?php echo $_SESSION["user_id"] ?? 0; ?>';
                        servicios[index].fecha_completado = new Date().toISOString().slice(0, 19).replace('T', ' ');
                    }
                    // ✅ Refrescar la tabla para que el ícono cambie inmediatamente
                    actualizarTabla();
                    alert(data.message);
                } else {
                    alert('Error: ' + (data.message || 'Intente nuevamente'));
                }
            })
            .catch(() => alert('Error de conexión'));
        }

        // --- Submodal Notas Servicio ---
        // Variable para almacenar el índice del servicio que se está editando (si se abre desde la tabla)
        let servicioIndexActual = -1;

        function abrirSubmodalNotasServicio(index = -1) {
            servicioIndexActual = index; // Guardar el índice del servicio actual
            if (index >= 0 && index < servicios.length) {
                // Cargar la nota existente del servicio seleccionado
                document.getElementById('nota_servicio_textarea').value = servicios[index].nota_srvc || '';
            } else {
                // Si se abre desde el botón dentro del modal-servicio, pero sin un servicio en edición,
                // limpiar el textarea. Si se está editando un servicio, se puede precargar la nota si ya existía.
                // Para simplificar, aquí limpiamos.
                document.getElementById('nota_servicio_textarea').value = '';
                // Si se abre desde el modal de servicio, y se está editando, se podría cargar la nota aquí si se almacena temporalmente.
                // Por ahora, asumimos que si no hay índice, es para un nuevo servicio o se limpiaría.
                // Si se llama desde el botón del modal-servicio, servicioEnEdicion !== null implica edición.
                if (servicioEnEdicion !== null && servicios[servicioEnEdicion]) {
                    document.getElementById('nota_servicio_textarea').value = servicios[servicioEnEdicion].nota_srvc || '';
                }
            }
            document.getElementById('submodal-notas-servicio').style.display = 'block';
        }

        function cerrarSubmodalNotasServicio() {
            document.getElementById('submodal-notas-servicio').style.display = 'none';
        }

        function guardarNotasServicio() {
            const nuevaNota = document.getElementById('nota_servicio_textarea').value.trim();

            if (servicioIndexActual >= 0 && servicioIndexActual < servicios.length) {
                // Caso 1: Se abrió desde la tabla, actualizamos el array y la vista previa
                servicios[servicioIndexActual].nota_srvc = nuevaNota;
                // Actualizar la vista previa en la tabla
                const icono = document.querySelector(`.nota-servicio-icono[data-index="${servicioIndexActual}"]`);
                if (icono) {
                    let previewCell = icono.parentElement.querySelector('.nota-preview');
                    if (!previewCell) {
                        // Si no encuentra el span, lo creamos (esto es improbable si se cargó correctamente)
                        previewCell = document.createElement('span');
                        previewCell.className = 'nota-preview';
                        icono.parentElement.appendChild(document.createTextNode(' ')); // Espacio
                        icono.parentElement.appendChild(previewCell);
                    }
                    previewCell.textContent = nuevaNota ? nuevaNota : '(Sin notas)';
                    previewCell.title = nuevaNota; // Actualizar el título del tooltip
                }
                exito('Notas del servicio guardadas');
            } else if (servicioEnEdicion !== null && servicios[servicioEnEdicion]) {
                // Caso 2: Se abrió desde el botón dentro del modal-servicio en modo edición
                servicios[servicioEnEdicion].nota_srvc = nuevaNota;
                exito('Notas del servicio guardadas (temporalmente)');
            } else {
                // Caso 3: Se abrió desde el botón dentro del modal-servicio en modo creación
                // No hay un objeto en servicios[] aún, solo actualizamos una variable temporal o el objeto local en el modal.
                // Para que se guarde con el servicio, debemos asegurarnos que el campo nota_srvc se incluya en el objeto enviado a la API.
                // Esto se maneja en el paso 7.
                exito('Notas del servicio listas para guardar con el servicio');
            }
            cerrarSubmodalNotasServicio();
        }

        // ===================================================================
        // === 3. CARGA DE DATOS ===
        // ===================================================================
        function cargarOperacionesYTipos() {
            // Cargar operaciones
            fetch('/api/get_operaciones.php')
                .then(r => r.json())
                .then(data => {
                    const opSel = document.getElementById('operacion');
                    if (!opSel) return;
                    opSel.innerHTML = '<option value="">Seleccionar</option>';
                    (data.operaciones || []).forEach(op => {
                        const opt = document.createElement('option');
                        opt.value = op;
                        opt.textContent = op;
                        opSel.appendChild(opt);
                    });
                })
                .catch(err => console.error('Error al cargar operaciones:', err));

            // Listener para cargar tipos al cambiar operación
            const opSel = document.getElementById('operacion');
            if (opSel) {
                const handler = function() {
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
                            // Recalcular concatenado si ya hay un tipo seleccionado
                            setTimeout(() => {
                                if (tipoSel.value) calcularConcatenado();
                            }, 100);
                        })
                        .catch(err => console.error('Error al cargar tipos:', err));
                };
                opSel.removeEventListener('change', handler);
                opSel.addEventListener('change', handler);
            }

            // Listener para tipo_oper → recalcular concatenado
            const tipoSel = document.getElementById('tipo_oper');
            if (tipoSel) {
                const handler = function() {
                    if (document.getElementById('operacion').value) {
                        calcularConcatenado();
                    }
                };
                tipoSel.removeEventListener('change', handler);
                tipoSel.addEventListener('change', handler);
            }
        }

        function cargarDatosModalServicio(callback = null) {
            let cargas = 0;
            const total = 4;
            const check = () => {
                cargas++;
                if (cargas === total && callback) callback();
            };

            // 1. Commodity
            fetch('/api/get_commoditys.php')
                .then(r => r.json())
                .then(data => {
                    const sel = document.getElementById('serv_commodity');
                    if (sel) {
                        sel.innerHTML = '<option value="">Seleccionar</option>';
                        const list = Array.isArray(data)
                            ? data
                            : (Array.isArray(data.commoditys) ? data.commoditys : []);
                        list.forEach(item => {
                            const val = typeof item === 'string' ? item : (item.commodity || item);
                            const opt = document.createElement('option');
                            opt.value = val;
                            opt.textContent = val;
                            sel.appendChild(opt);
                        });
                    }
                    check();
                })
                .catch(() => check());

            // 2. Medios de transporte
            fetch('/api/get_medios_transporte.php')
                .then(r => r.json())
                .then(data => {
                    const sel = document.getElementById('serv_medio_transporte');
                    if (sel) {
                        sel.innerHTML = '<option value="">Seleccionar</option>';
                        const list = Array.isArray(data)
                            ? data
                            : (Array.isArray(data.medios_transporte) ? data.medios_transporte : []);
                        list.forEach(item => {
                            const val = typeof item === 'string' ? item : item;
                            const opt = document.createElement('option');
                            opt.value = val;
                            opt.textContent = val;
                            sel.appendChild(opt);
                        });
                    }
                    check();
                })
                .catch(() => check());

            // 3. Agentes
            fetch('/api/get_agentes.php')
                .then(r => r.json())
                .then(data => {
                    const sel = document.getElementById('serv_agente');
                    if (sel) {
                        sel.innerHTML = '<option value="">Seleccionar</option>';
                        const list = Array.isArray(data)
                            ? data
                            : (Array.isArray(data.agentes) ? data.agentes : []);
                        list.forEach(item => {
                            const val = typeof item === 'string' ? item : item;
                            const opt = document.createElement('option');
                            opt.value = val;
                            opt.textContent = val;
                            sel.appendChild(opt);
                        });
                    }
                    check();
                })
                .catch(() => check());

            // 4. Proveedores nacionales
            fetch('/api/get_proveedores_pnac.php')
                .then(r => r.json())
                .then(data => {
                    const sel = document.getElementById('serv_proveedor_nac');
                    if (sel) {
                        sel.innerHTML = '<option value="">Seleccionar</option>';
                        const list = Array.isArray(data)
                            ? data
                            : (Array.isArray(data.proveedores) ? data.proveedores : []);
                        list.forEach(item => {
                            const val = typeof item === 'string' ? item : item;
                            const opt = document.createElement('option');
                            opt.value = val;
                            opt.textContent = val;
                            sel.appendChild(opt);
                        });
                    }
                    check();
                })
                .catch(() => check());
        }

        function cargarLugaresPorMedio(medio, origenSeleccionado = null, paisOrigenSeleccionado = null) {
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

                    // Generar opciones para Origen
                    const origenOptions = lugares.map(l => 
                        `<option value="${l.lugar}" data-pais="${l.pais || ''}">${l.lugar}</option>`
                    ).join('');
                    origenSel.innerHTML = '<option value="">Seleccionar</option>' + origenOptions;

                    // Filtrar Destino: excluir dupla completa (lugar + pais)
                    const destinosFiltrados = lugares.filter(l => 
                        !(l.lugar === origenSeleccionado && l.pais === paisOrigenSeleccionado)
                    );

                    const destinoOptions = destinosFiltrados.map(l => 
                        `<option value="${l.lugar}" data-pais="${l.pais || ''}">${l.lugar}</option>`
                    ).join('');
                    destinoSel.innerHTML = '<option value="">Seleccionar</option>' + destinoOptions;

                    // Listener para Origen → País Origen + recargar Destino
                    const handlerOrigen = () => {
                        const opt = origenSel.options[origenSel.selectedIndex];
                        const lugar = opt?.value || '';
                        const pais = opt ? opt.getAttribute('data-pais') || '' : '';
                        document.getElementById('serv_pais_origen').value = pais;

                        // Recargar Destino excluyendo (lugar, pais)
                        const nuevosDestinos = lugares.filter(l => 
                            !(l.lugar === lugar && l.pais === pais)
                        );
                        const nuevasOpciones = nuevosDestinos.map(l => 
                            `<option value="${l.lugar}" data-pais="${l.pais || ''}">${l.lugar}</option>`
                        ).join('');
                        destinoSel.innerHTML = '<option value="">Seleccionar</option>' + nuevasOpciones;

                        // Limpiar país destino
                        document.getElementById('serv_pais_destino').value = '';
                    };

                    // Listener para Destino → País Destino
                    const handlerDestino = () => {
                        const opt = destinoSel.options[destinoSel.selectedIndex];
                        const pais = opt ? opt.getAttribute('data-pais') || '' : '';
                        document.getElementById('serv_pais_destino').value = pais;
                    };

                    // Limpiar y asignar listeners
                    origenSel.removeEventListener('change', handlerOrigen);
                    destinoSel.removeEventListener('change', handlerDestino);
                    origenSel.addEventListener('change', handlerOrigen);
                    destinoSel.addEventListener('change', handlerDestino);
                })
                .catch(err => {
                    console.error('Error al cargar lugares por medio:', err);
                    error('No se pudieron cargar los lugares para este medio de transporte');
                    return Promise.resolve();
                });
        }

        // ===================================================================
        // === 4. MANEJO DE PROSPECTOS ===
        // ===================================================================
        function seleccionarProspecto(id) {
            fetch(`/api/get_prospecto.php?id=${id}`)
                .then(r => {
                    if (!r.ok) throw new Error(`HTTP ${r.status}`);
                    return r.json();
                })
                .then(data => {
                    // ✅ "p" solo existe DENTRO de este bloque
                    if (!data.success || !data.prospecto) {
                        error('Prospecto no encontrado');
                        return;
                    }
                    const p = data.prospecto;

                    console.log('🔍 [DEBUG] Datos del prospecto:', p);
                    console.log('🔍 [DEBUG] Servicios recibidos:', data.servicios);

                    // === Actualizar campos ocultos ===
                    document.getElementById('prospecto_notas_comerciales').value = p.notas_comerciales || '';
                    document.getElementById('prospecto_notas_operaciones').value = p.notas_operaciones || '';
                    document.getElementById('prospecto_razon_social').value = p.razon_social || '';
                    document.getElementById('prospecto_direccion').value = p.direccion || '';
                    document.getElementById('prospecto_rut_empresa').value = p.rut_empresa || '';

                    // === Actualizar select de razón social ===
                    const razonSelect = document.getElementById('razon_social_select');
                    if (razonSelect) {
                        let optionFound = false;
                        for (let i = 0; i < razonSelect.options.length; i++) {
                            if (razonSelect.options[i].value === p.rut_empresa) {
                                razonSelect.selectedIndex = i;
                                optionFound = true;
                                break;
                            }
                        }
                        if (!optionFound && p.rut_empresa && p.razon_social) {
                            const opt = document.createElement('option');
                            opt.value = p.rut_empresa;
                            opt.textContent = p.razon_social;
                            razonSelect.appendChild(opt);
                            razonSelect.value = p.rut_empresa;
                        }
                    }

                    // === Llenar campos visibles ===
                    const fields = [
                        { id: 'rut_empresa', value: p.rut_empresa },
                        { id: 'fono_empresa', value: p.fono_empresa },
                        { id: 'direccion', value: p.direccion },
                        { id: 'booking', value: p.booking },
                        { id: 'incoterm', value: p.incoterm },
                        { id: 'fecha_alta', value: p.fecha_alta },
                        { id: 'fecha_estado', value: p.fecha_estado },
                        { id: 'nombre', value: p.nombre },
                        { id: 'pais', value: p.pais }
                    ];
                    fields.forEach(f => {
                        const el = document.getElementById(f.id);
                        if (el) el.value = f.value || '';
                    });

                    // === Cargar operación y tipo_oper ===
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

                    // === Notas ===
                    const setNota = (name, val) => {
                        const inp = document.getElementById(name);
                        const ta = document.getElementById(`${name}_input`);
                        if (inp) inp.value = val || '';
                        if (ta) ta.value = val || '';
                    };
                    setNota('notas_comerciales', p.notas_comerciales);
                    setNota('notas_operaciones', p.notas_operaciones);

                    // === Servicios ===
                    console.log('📥 [DEBUG] Servicios recibidos de la API:', data.servicios);

                    // Procesar servicios con protección contra errores
                    servicios = [];
                    if (Array.isArray(data.servicios)) {
                        servicios = data.servicios.map(servicio => {
                            // Asegurar que los campos numéricos sean números
                            return {
                                ...servicio,
                                costo: (servicio.costo && !isNaN(parseFloat(servicio.costo))) ? parseFloat(servicio.costo) : 0,
                                venta: (servicio.venta && !isNaN(parseFloat(servicio.venta))) ? parseFloat(servicio.venta) : 0,
                                costogastoslocalesdestino: (servicio.costogastoslocalesdestino && !isNaN(parseFloat(servicio.costogastoslocalesdestino))) ? parseFloat(servicio.costogastoslocalesdestino) : 0,
                                ventasgastoslocalesdestino: (servicio.ventasgastoslocalesdestino && !isNaN(parseFloat(servicio.ventasgastoslocalesdestino))) ? parseFloat(servicio.ventasgastoslocalesdestino) : 0,
                                bultos: parseInt(servicio.bultos) || 0,
                                peso: parseFloat(servicio.peso) || 0,
                                volumen: parseFloat(servicio.volumen) || 0
                            };
                        });
                    } else {
                        console.warn('⚠️ [DEBUG] data.servicios no es un array:', data.servicios);
                    }

                    console.log('✅ [DEBUG] Servicios procesados:', servicios);
                    actualizarTabla();

                    // ✅ CARGAR CONTACTO PRIMARIO DIRECTAMENTE
                    if (p.rut_empresa) {
                        fetch(`/api/get_contactos.php?rut=${encodeURIComponent(p.rut_empresa)}`)
                            .then(response => response.json())
                            .then(data => {
                                const contactoPrimario = (data.contactos || []).find(c => c.primario === 'S');
                                const contactoEl = document.getElementById('contacto');
                                const emailEl = document.getElementById('email');
                                if (contactoPrimario && contactoEl && emailEl) {
                                    contactoEl.value = contactoPrimario.nom_contacto || '';
                                    emailEl.value = contactoPrimario.email || '';
                                } else {
                                    if (contactoEl) contactoEl.value = '';
                                    if (emailEl) emailEl.value = '';
                                }
                            })
                            .catch(err => {
                                console.error('Error al cargar contactos:', err);
                                const contactoEl = document.getElementById('contacto');
                                const emailEl = document.getElementById('email');
                                if (contactoEl) contactoEl.value = '';
                                if (emailEl) emailEl.value = '';
                            });
                    }

                    // === Asignaciones clave ===
                    const idPplInput = document.getElementById('id_ppl');
                    const idPpl = document.getElementById('id_ppl')?.value;
                    const concatenadoInput = document.getElementById('concatenado');
                    if (idPplInput) idPplInput.value = p.id_ppl || '';
                    if (concatenadoInput) concatenadoInput.value = p.concatenado || '';

                    // === Habilitar campos ===
                    document.querySelectorAll('input:not([type="hidden"]):not([name="concatenado"])')
                        .forEach(i => { i.readOnly = false; i.style.backgroundColor = ''; });
                    document.querySelectorAll('select')
                        .forEach(s => s.disabled = false);

                    const btnAgregar = document.getElementById('btn-agregar-servicio');
                    if (btnAgregar && p.id_ppl > 0) {
                        btnAgregar.style.display = 'inline-flex';
                    }
                })
                .catch(err => {
                    console.error('Error al cargar prospecto:', err);
                    error('No se pudo cargar el prospecto');
                });
        }
        // ===================================================================
        // === 5. MODALES Y SUBMODALES ===
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

        // --- FUNCIÓN CORREGIDA: abrirModalServicio ---
        function abrirModalServicio(index = null) {
            const idPpl = document.getElementById('id_ppl')?.value;
            const idPplNum = parseInt(idPpl, 10);
            const concatenado = document.getElementById('concatenado')?.value;
            if (!idPpl || idPpl === '0' || !concatenado) {
                error('Guarde el prospecto primero antes de agregar servicios.');
                return;
            }

            // ✅ Usar los parámetros directamente, no del DOM
            if (!idPplNum || !concatenado) {
                error('Datos de prospecto incompletos');
                return;
            }

            // Limpiar modal
            const modalInputs = document.querySelectorAll('#modal-servicio input, #modal-servicio select, #modal-servicio textarea');
            modalInputs.forEach(el => {
                if (el.type === 'number') el.value = '';
                else if (el.type === 'text' || el.tagName === 'TEXTAREA') el.value = '';
                else if (el.tagName === 'SELECT') el.selectedIndex = 0;
            });

            // ✅ Asignar valores correctos
            document.getElementById('id_prospect_serv').value = idPplNum; // ← ¡Número!
            document.getElementById('concatenado_serv').value = concatenado;
            document.getElementById('serv_titulo_concatenado').textContent = concatenado;

            costosServicio = [];
            gastosLocales = [];

            // Cargar datos del modal (commodity, medios, etc.)
            cargarDatosModalServicio(() => {
                if (index !== null) {
                    // Editar servicio existente
                    servicioEnEdicion = index;
                    const s = servicios[index];
                    costosServicio = Array.isArray(s.costos) ? [...s.costos] : [];
                    gastosLocales = Array.isArray(s.gastos_locales) 
                        ? s.gastos_locales.map(g => ({
                            ...g,
                            monto: parseFloat(g.monto) || 0,
                            iva: parseFloat(g.iva) || 0
                        }))
                        : [];

                    // Rellenar campos básicos
                    document.getElementById('serv_servicio').value = s.servicio || '';
                    document.getElementById('serv_transportador').value = s.transportador || '';
                    document.getElementById('serv_incoterm').value = s.incoterm || '';
                    document.getElementById('serv_ref_cliente').value = s.ref_cliente || '';
                    document.getElementById('serv_transito').value = s.transito || '';
                    document.getElementById('serv_frecuencia').value = s.frecuencia || '';
                    document.getElementById('serv_lugar_carga').value = s.lugar_carga || '';
                    document.getElementById('serv_sector').value = s.sector || '';
                    document.getElementById('serv_mercancia').value = s.mercancia || '';
                    document.getElementById('serv_bultos').value = s.bultos || '';
                    document.getElementById('serv_peso').value = s.peso || '';
                    document.getElementById('serv_volumen').value = s.volumen || '';
                    document.getElementById('serv_dimensiones').value = s.dimensiones || '';
                    document.getElementById('serv_moneda').value = s.moneda || 'USD';
                    document.getElementById('serv_tipo_cambio').value = s.tipo_cambio || 1;
                    document.getElementById('serv_proveedor_nac').value = s.proveedor_nac || '';
                    document.getElementById('serv_aol').value = s.aol || '';
                    document.getElementById('serv_aod').value = s.aod || '';
                    document.getElementById('serv_agente').value = s.agente || '';
                    document.getElementById('serv_validez').value = s.validez || '';

                    // Cargar lugares si hay medio guardado
                    const medioGuardado = (s.trafico || '').trim();
                    if (medioGuardado) {
                        // ✅ PASAR ORIGEN + PAÍS_ORIGEN para filtrado preciso
                        cargarLugaresPorMedio(medioGuardado, s.origen, s.pais_origen).then(() => {
                            const origenSel = document.getElementById('serv_origen');
                            const destinoSel = document.getElementById('serv_destino');

                            // Preseleccionar Origen (por valor + país)
                            if (origenSel && s.origen && s.pais_origen) {
                                for (let i = 0; i < origenSel.options.length; i++) {
                                    const opt = origenSel.options[i];
                                    if (opt.value === s.origen && opt.getAttribute('data-pais') === s.pais_origen) {
                                        origenSel.selectedIndex = i;
                                        document.getElementById('serv_pais_origen').value = s.pais_origen;
                                        break;
                                    }
                                }
                            }

                            // Preseleccionar Destino (por valor + país)
                            if (destinoSel && s.destino && s.pais_destino) {
                                for (let i = 0; i < destinoSel.options.length; i++) {
                                    const opt = destinoSel.options[i];
                                    if (opt.value === s.destino && opt.getAttribute('data-pais') === s.pais_destino) {
                                        destinoSel.selectedIndex = i;
                                        document.getElementById('serv_pais_destino').value = s.pais_destino;
                                        break;
                                    }
                                }
                            }
                        });
                    }

                    // Cargar commodity y medio
                    const medioSel = document.getElementById('serv_medio_transporte');
                    const commoditySel = document.getElementById('serv_commodity');
                    if (medioSel && s.trafico) medioSel.value = s.trafico;
                    if (commoditySel && s.commodity) commoditySel.value = s.commodity;
                } else {
                    // Nuevo servicio
                    servicioEnEdicion = null;
                }
            });

            // Listener para cargar lugares al cambiar el medio de transporte
            const medioSel = document.getElementById('serv_medio_transporte');
            if (medioSel) {
                const newMedioSel = medioSel.cloneNode(true);
                medioSel.parentNode.replaceChild(newMedioSel, medioSel);
                newMedioSel.addEventListener('change', function() {
                    const medioSeleccionado = this.value;
                    if (medioSeleccionado) {
                        // --- CORRECCIÓN: Mapear medios específicos al genérico ---
                        let medioParaCarga = medioSeleccionado;
                        if (medioSeleccionado === 'Marítimo FCL' || medioSeleccionado === 'Marítimo LCL') {
                            medioParaCarga = 'Marítimo';
                        }
                        // Puedes añadir más mapeos aquí si aplica para Aéreo o Terrestre en el futuro
                        // else if (medioSeleccionado === 'Aéreo Internacional' || medioSeleccionado === 'Aéreo Nacional') {
                        //     medioParaCarga = 'Aéreo';
                        // }
                        // else if (medioSeleccionado === 'Terrestre Regional') {
                        //     medioParaCarga = 'Terrestre';
                        // }
                        // --- FIN CORRECCIÓN ---

                        // Llamar a la función con el valor mapeado
                        cargarLugaresPorMedio(medioParaCarga); // Sin origen → cargar todos
                    } else {
                        document.getElementById('serv_origen').innerHTML = '<option value="">Seleccionar</option>';
                        document.getElementById('serv_destino').innerHTML = '<option value="">Seleccionar</option>';
                        document.getElementById('serv_pais_origen').value = '';
                        document.getElementById('serv_pais_destino').value = '';
                    }
                });
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

        // === FUNCIÓN AUXILIAR: Extrae la base (sin correlativo) desde el concatenado ===
        function extraerBaseDesdeConcatenado(concatenado) {
            if (!concatenado || !concatenado.includes('-')) return null;
            const partes = concatenado.split('-');
            if (partes.length < 2) return null;
            // Todo menos el último segmento (que es el correlativo del prospecto)
            return partes.slice(0, -1).join('-');
        }

        // === FUNCIÓN AUXILIAR: Genera id_srvc permanente correctamente ===
        function generarIdSrvcPermanente(concatenado, cantidadServicios) {
            const base = extraerBaseDesdeConcatenado(concatenado);
            if (!base) {
                console.error('❌ No se pudo extraer la base desde el concatenado:', concatenado);
                return null;
            }
            const correlativo = (cantidadServicios + 1).toString().padStart(2, '0');
            return `${base}-${correlativo}`; // Ej: EXAIR251119-01
        }

        // === FUNCIÓN PRINCIPAL: guardarServicio ===
        function guardarServicio() {
            console.log('🔍 [SERVICIO] Iniciando guardarServicio en BD');
            const servicio = document.getElementById('serv_servicio').value.trim();
            if (!servicio) {
                error('Servicio es obligatorio');
                return;
            }
            const origen = document.getElementById('serv_origen').value;
            const destino = document.getElementById('serv_destino').value;
            if (origen && destino && origen === destino) {
                error('Origen y Destino no pueden ser iguales');
                return;
            }

            const idPpl = document.getElementById('id_prospect_serv')?.value;
            if (!idPpl || idPpl === '0') {
                error('Prospecto no válido');
                return;
            }

            const totalVenta = costosServicio.reduce((sum, c) => sum + (c.total_tarifa || 0), 0);
            const rutCliente = document.getElementById('rut_empresa')?.value.trim();

            // Guardar servicio sin validación de crédito
            enviarServicioABD();
        }

        function enviarServicioABD() {
            const idPpl = document.getElementById('id_prospect_serv')?.value;
            const concatenado = document.getElementById('concatenado_serv')?.value;

            if (!idPpl || isNaN(parseInt(idPpl)) || !concatenado) {
                error('Prospecto no válido');
                return;
            }

            // ✅ Incluir costos y gastos en el objeto de datos
            // === Función auxiliar para parsear números de forma segura ===
            function parseNumber(value) {
                if (typeof value === 'number') return value;
                if (typeof value === 'string') {
                    const cleaned = value.trim().replace(/,/g, '');
                    return cleaned ? parseFloat(cleaned) : 0;
                }
                return 0;
            }

            // === Verificar si hay costos reales (qty > 0 y costo > 0) ===
            const tieneCostosReales = costosServicio.some(c => {
                const qty = parseNumber(c.qty);
                const costo = parseNumber(c.costo);
                // Log interno (opcional, para depuración)
                // console.log('🔍 [DEBUG] Costo item - Qty:', qty, 'Costo:', costo, 'Válido:', (qty > 0 && costo > 0));
                return qty > 0 && costo > 0;
            });

            // === Recalcular costo y venta totales desde los costos individuales ===
            const totalCosto = costosServicio.reduce((sum, c) => sum + (parseNumber(c.qty) * parseNumber(c.costo)), 0);
            const totalVenta = costosServicio.reduce((sum, c) => sum + (parseNumber(c.qty) * parseNumber(c.tarifa)), 0);

            const data = {
                modo: servicioEnEdicion !== null ? 'editar' : 'crear',
                id_srvc: servicioEnEdicion !== null ? servicios[servicioEnEdicion].id_srvc : null,
                id_prospect: parseInt(idPpl), // ← ¡Convertir a entero!
                servicio: document.getElementById('serv_servicio').value.trim(),
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
                desconsolidac: '0',
                aol: document.getElementById('serv_aol').value,
                aod: document.getElementById('serv_aod').value,
                agente: document.getElementById('serv_agente').value,
                transportador: document.getElementById('serv_transportador').value,
                incoterm: document.getElementById('serv_incoterm').value,
                ref_cliente: document.getElementById('serv_ref_cliente').value,

                costo: totalCosto,
                venta: totalVenta,

                costogastoslocalesdestino: gastosLocales.filter(g => g.tipo === 'Costo').reduce((sum, g) => sum + (g.monto || 0), 0),
                ventasgastoslocalesdestino: gastosLocales.filter(g => g.tipo === 'Ventas').reduce((sum, g) => sum + (g.monto || 0), 0),

                // ✅ ¡ESTAS SON LAS LÍNEAS CLAVE!
                costos: [...costosServicio],
                gastos_locales: [...gastosLocales],
                estado_costos: tieneCostosReales ? 'completado' : 'pendiente',
                nota_srvc: servicios[servicioEnEdicion]?.nota_srvc || '', // Tomar la nota del servicio en edición o vacío si es nuevo
                validez: document.getElementById('serv_validez').value
            };

            console.log('✅ [SERVICIO] Datos a enviar:', data);
            fetch('/api/guardar_servicio.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    // Inyectar datos del prospecto en el nuevo servicio
                    const prospectoRazonSocial = document.getElementById('razon_social_select').selectedOptions[0]?.textContent || '';
                    const prospectoRut = document.getElementById('rut_empresa')?.value || '';
                    const prospectoDireccion = document.getElementById('direccion')?.value || '';
                    const prospectoContacto = document.getElementById('contacto')?.value || '';

                    const servicioGuardado = {
                        ...data,
                        id_srvc: res.id_srvc,
                        // Campos heredados del prospecto
                        razon_social: prospectoRazonSocial,
                        rut_empresa: prospectoRut,
                        direccion: prospectoDireccion,
                        contacto_nombre: prospectoContacto
                    };

                    // ✅ Recargar todos los servicios desde la API
                    const idPpl = document.getElementById('id_ppl')?.value;
                    if (idPpl && idPpl !== '0') {
                        fetch(`/api/get_prospecto.php?id=${idPpl}`)
                            .then(r => r.json())
                            .then(data => {
                                if (data.success && data.servicios) {
                                    servicios = data.servicios.map(s => ({
                                        ...s,
                                        costo: parseFloat(s.costo) || 0,
                                        venta: parseFloat(s.venta) || 0,
                                        costogastoslocalesdestino: parseFloat(s.costogastoslocalesdestino) || 0,
                                        ventasgastoslocalesdestino: parseFloat(s.ventasgastoslocalesdestino) || 0
                                    }));
                                    actualizarTabla();
                                }
                            })
                            .catch(err => error('Error al recargar servicios'));
                    } else {
                        // Fallback si no hay id_ppl
                        if (servicioEnEdicion !== null) {
                            servicios[servicioEnEdicion] = servicioGuardado;
                        } else {
                            servicios.push(servicioGuardado);
                        }
                        actualizarTabla();
                    }
                    cerrarModalServicio();
                    exito('Servicio guardado en la base de datos');
                } else {
                    error('Error: ' + (res.message || 'Intente nuevamente'));
                }
                console.log('🔍 [DEBUG] CostosServicio:', costosServicio);
                console.log('🔍 [DEBUG] TieneCostosReales:', tieneCostosReales);
            })
            .catch(err => {
                console.error('Error al guardar servicio:', err);
                error('Error de conexión al guardar el servicio');
            });
        }

        // --- Submodales ---
        function abrirSubmodalCostos() {
            if (document.getElementById('modal-servicio').style.display === 'none') {
                return error('Abra primero el modal de Servicio');
            }

            // ✅ Cargar datos actuales del servicio
            if (servicioEnEdicion !== null) {
                costosServicio = Array.isArray(servicios[servicioEnEdicion].costos) ? [...servicios[servicioEnEdicion].costos] : [];
            }

            // ✅ Establecer moneda
            document.getElementById('costo_moneda').value = document.getElementById('serv_moneda')?.value || 'USD';

            // ✅ Cargar conceptos y aplicaciones
            fetch('/api/get_conceptos_costos.php')
                .then(r => r.json())
                .then(data => {
                    const sel = document.getElementById('costo_concepto');
                    if (sel) {
                        sel.innerHTML = '<option value="">Seleccionar concepto</option>';
                        (data.conceptos || data).forEach(c => {
                            const opt = document.createElement('option');
                            opt.value = c.concepto || c;
                            opt.textContent = c.concepto || c;
                            sel.appendChild(opt);
                        });
                    }
                });

            const medio = document.getElementById('serv_medio_transporte')?.value || '';
            fetch(`/api/get_aplicaciones_costos.php?medio=${encodeURIComponent(medio)}`)
                .then(r => r.json())
                .then(data => {
                    const sel = document.getElementById('costo_aplica');
                    if (sel) {
                        sel.innerHTML = '<option value="">Seleccionar aplica</option>';
                        (Array.isArray(data) ? data : (data.aplicaciones || [])).forEach(item => {
                            const val = typeof item === 'string' ? item : item.aplica;
                            if (val) {
                                const opt = document.createElement('option');
                                opt.value = val;
                                opt.textContent = val;
                                sel.appendChild(opt);
                            }
                        });
                    }
                });

            // ? Determinar permisos por rol
            const rolUsuario = '<?php echo $_SESSION["rol"] ?? "comercial"; ?>';

            // ? Pricing y Admin pueden editar qty, costo
            const puedeEditarQtyCosto = (rolUsuario === 'pricing' || rolUsuario === 'admin');

            // ? Comercial, Pricing y Admin pueden editar tarifa y total_tarifa
            const puedeEditarTarifa = (rolUsuario === 'comercial' || rolUsuario === 'pricing' || rolUsuario === 'admin');

            // ? Comercial, Finanzas, Pricing y Admin pueden elegir concepto y aplica
            const puedeEditarConceptoAplica = (rolUsuario === 'comercial' || rolUsuario === 'finanzas' || rolUsuario === 'pricing' || rolUsuario === 'admin');

            // ? Control de edición por campo
            const campoQty = document.getElementById('costo_qty');
            const campoCosto = document.getElementById('costo_costo');
            const campoTarifa = document.getElementById('costo_tarifa');

            if (campoQty) campoQty.disabled = !puedeEditarQtyCosto;
            if (campoCosto) campoCosto.disabled = !puedeEditarQtyCosto;
            if (campoTarifa) campoTarifa.disabled = !puedeEditarTarifa;

            // ? Habilitar concepto y aplica según permisos
            ['costo_concepto', 'costo_aplica'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.disabled = !puedeEditarConceptoAplica;
            });

            // ? Actualizar total_costo y total_tarifa cuando cambian qty, costo, tarifa o porcentaje_concepto
            ['costo_qty', 'costo_costo', 'costo_tarifa', 'costo_porcentaje_concepto'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    const handler = () => {
                        const qty = parseFloat(document.getElementById('costo_qty').value) || 0;
                        const costo = parseFloat(document.getElementById('costo_costo').value) || 0;
                        const tarifa = parseFloat(document.getElementById('costo_tarifa').value) || 0;
                        const porcentaje = parseFloat(document.getElementById('costo_porcentaje_concepto').value) || 0;

                        // ✅ Total Costo = (costo * %) * qty
                        const totalCosto = (costo * (porcentaje / 100)) * qty;

                        // ✅ Total Tarifa = tarifa * qty (sin porcentaje)
                        const totalTarifa = tarifa * qty;

                        document.getElementById('costo_total_costo').value = totalCosto.toFixed(2);
                        document.getElementById('costo_total_tarifa').value = totalTarifa.toFixed(2);
                    };

                    // Evitar listeners duplicados
                    el.removeEventListener('input', handler);
                    el.addEventListener('input', handler);
                }
            });

            // ✅ Actualizar tabla y mostrar modal
            actualizarTablaCostos();
            document.getElementById('submodal-costos').style.display = 'block';
        }

        // --- Submodal Gastos Locales ---
        function abrirSubmodalGastosLocales() {
            if (document.getElementById('modal-servicio').style.display === 'none') return error('Abra primero el modal de Servicio');

            // Cargar datos actuales del servicio (si está en edición)
            // Asegurar que gastosLocales sea un array y que sus valores numéricos sean números
            if (servicioEnEdicion !== null) {
                gastosLocales = Array.isArray(servicios[servicioEnEdicion].gastos_locales) ? servicios[servicioEnEdicion].gastos_locales.map(g => ({
                    ...g,
                    // Asegurar que monto e iva sean números, con 0 como valor por defecto si no lo son
                    monto: parseFloat(g.monto) || 0,
                    iva: parseFloat(g.iva) || 0
                })) : [];
            } else {
                // Si no está en edición, asegurar que gastosLocales sea un array vacío o el que esté en el estado actual
                gastosLocales = Array.isArray(gastosLocales) ? gastosLocales.map(g => ({
                    ...g,
                    monto: parseFloat(g.monto) || 0,
                    iva: parseFloat(g.iva) || 0
                })) : [];
            }

            // Cargar tipos de gastos (si aplica)
            const tipo = document.getElementById('gasto_tipo')?.value;
            if (tipo) {
                fetch(`/api/get_gastos_locales.php?tipo=${encodeURIComponent(tipo)}`)
                    .then(r => r.json())
                    .then(data => {
                        const sel = document.getElementById('gasto_gasto');
                        if (sel) {
                            sel.innerHTML = '<option value="">Seleccionar gasto</option>';
                            (Array.isArray(data) ? data : (data.gastos_locales || [])).forEach(item => {
                                const val = typeof item === 'string' ? item : (item.gasto || item);
                                if (val) {
                                    const opt = document.createElement('option');
                                    opt.value = val;
                                    opt.textContent = val;
                                    sel.appendChild(opt);
                                }
                            });
                        }
                    });
            }
            // Actualizar tabla y mostrar modal
            actualizarTablaGastosLocales();
            document.getElementById('submodal-gastos-locales').style.display = 'block';
        }

        ['costo_qty', 'costo_costo', 'costo_tarifa'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', () => {
                const qty = parseFloat(document.getElementById('costo_qty').value) || 0;
                const costo = parseFloat(document.getElementById('costo_costo').value) || 0;
                const tarifa = parseFloat(document.getElementById('costo_tarifa').value) || 0;
                document.getElementById('costo_total_costo').value = (qty * costo).toFixed(2);
                document.getElementById('costo_total_tarifa').value = (qty * tarifa).toFixed(2);
            });
        });

        function actualizarTablaGastosLocales() {
            const tbody = document.getElementById('gastos-locales-body');
            if (!tbody) return;
            tbody.innerHTML = '';

            // Inicializar totales por moneda
            let totales = {
                'USD': { costo: 0, venta: 0 },
                'EUR': { costo: 0, venta: 0 },
                'CLP': { costo: 0, venta: 0 }
            };

            gastosLocales.forEach((g, i) => {
                // Asegurar que monto e iva sean números antes de usarlos
                const monto = parseFloat(g.monto) || 0;
                const iva = parseFloat(g.iva) || 0;
                const tipo = g.tipo || '';
                const moneda = g.moneda || 'CLP'; // Valor por defecto

                // Calcular subtotal (monto * (1 + iva/100)) si es afecto
                const esAfecto = g.afecto === 'SI' || g.afecto === true;
                const subtotal = esAfecto ? monto * (1 + iva / 100) : monto;

                // Acumular totales por tipo y moneda
                if (tipo.toLowerCase() === 'costo') {
                    totales[moneda.toUpperCase()].costo += subtotal;
                } else if (tipo.toLowerCase() === 'ventas') { // O 'sales' si se usa inglés
                    totales[moneda.toUpperCase()].venta += subtotal;
                }

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${tipo}</td>
                    <td>${g.gasto}</td>
                    <td>${moneda}</td>
                    <td style="text-align:right;">${monto.toFixed(2)}</td>
                    <td>${g.afecto}</td>
                    <td style="text-align:right;">${iva.toFixed(2)}</td>
                    <td style="text-align:right;">${subtotal.toFixed(2)}</td>
                    <td style="text-align: center;">
                        <i class="fas fa-pencil-alt edit-gasto-icon" data-index="${i}" style="cursor: pointer; color: #007bff; margin-right: 0.5rem;" title="Editar Gasto"></i>
                        <button type="button" onclick="eliminarGastoLocal(${i})">🗑️</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Calcular profit y profit % por moneda
            for (const moneda in totales) {
                const t = totales[moneda];
                t.profit = t.venta - t.costo;
                t.profit_pct = t.venta > 0 ? ((t.venta - t.costo) / t.venta * 100) : 0;
            }

            // Actualizar los elementos HTML con los totales calculados
            document.getElementById('cgld_usd').textContent = totales['USD'].costo.toFixed(2);
            document.getElementById('vgld_usd').textContent = totales['USD'].venta.toFixed(2);
            document.getElementById('pgld_usd').textContent = totales['USD'].profit.toFixed(2);
            document.getElementById('ppgld_usd').textContent = totales['USD'].profit_pct.toFixed(2) + ' %';

            document.getElementById('cgld_eur').textContent = totales['EUR'].costo.toFixed(2);
            document.getElementById('vgld_eur').textContent = totales['EUR'].venta.toFixed(2);
            document.getElementById('pgld_eur').textContent = totales['EUR'].profit.toFixed(2);
            document.getElementById('ppgld_eur').textContent = totales['EUR'].profit_pct.toFixed(2) + ' %';

            document.getElementById('cgld_clp').textContent = totales['CLP'].costo.toFixed(2);
            document.getElementById('vgld_clp').textContent = totales['CLP'].venta.toFixed(2);
            document.getElementById('pgld_clp').textContent = totales['CLP'].profit.toFixed(2);
            document.getElementById('ppgld_clp').textContent = totales['CLP'].profit_pct.toFixed(2) + ' %';

            // Opcional: Actualizar también los totales antiguos si se usan en otro lugar
            // const tv = totales['USD'].venta + totales['EUR'].venta + totales['CLP'].venta;
            // const tc = totales['USD'].costo + totales['EUR'].costo + totales['CLP'].costo;
            // document.getElementById('total-venta-gastos').textContent = tv.toFixed(2);
            // document.getElementById('total-costo-gastos').textContent = tc.toFixed(2);
            // document.getElementById('profit-local').textContent = (tv - tc).toFixed(2);
            // const pct = tv > 0 ? ((tv - tc) / tv * 100) : 0;
            // document.getElementById('profit-porcentaje').textContent = pct.toFixed(2) + ' %';

            // --- Añadir listeners para los íconos de edición ---
            setTimeout(() => {
                document.querySelectorAll('.edit-gasto-icon').forEach(icon => {
                    icon.addEventListener('click', function() {
                        const index = parseInt(this.getAttribute('data-index'));
                        editarGastoLocal(index);
                    });
                });
            }, 0);
        }

        // Mantener también la corrección en eliminarGastoLocal
        function eliminarGastoLocal(i) {
            if (confirm('¿Eliminar este gasto?')) {
                gastosLocales.splice(i, 1);
                actualizarTablaGastosLocales(); // Actualizar tabla y totales
                exito('Gasto eliminado');
            }
        }

        // Y en cerrarSubmodalGastosLocales
        function cerrarSubmodalGastosLocales() {
            if (servicioEnEdicion !== null && servicios[servicioEnEdicion]) {
                // Asegurar que los gastos locales se actualicen en el objeto servicio
                // y que los valores numéricos se mantengan como números
                servicios[servicioEnEdicion].gastos_locales = gastosLocales.map(g => ({
                    ...g,
                    monto: parseFloat(g.monto) || 0,
                    iva: parseFloat(g.iva) || 0
                }));
            }
            document.getElementById('submodal-gastos-locales').style.display = 'none';
        }

        function guardarCosto() {
            const concepto = document.getElementById('costo_concepto').value;
            const aplica = document.getElementById('costo_aplica').value;
            const moneda = document.getElementById('costo_moneda').value || 'CLP';
            const qty = parseFloat(document.getElementById('costo_qty').value) || 0;
            const porcentaje_concepto = parseFloat(document.getElementById('costo_porcentaje_concepto').value) || 100;
            const costo = parseFloat(document.getElementById('costo_costo').value) || 0;
            const tarifa = parseFloat(document.getElementById('costo_tarifa').value) || 0;
            
            if (!concepto || !aplica) return error('Concepto y Aplica son obligatorios');
            
            // ✅ Cálculo CORRECTO del total_costo: (costo * %) * qty
            let total_costo = (costo * (porcentaje_concepto / 100)) * qty;
            
            // ✅ Cálculo CORRECTO del total_tarifa: tarifa * qty
            let total_tarifa = tarifa * qty;
            
            const nuevo = { 
                concepto, 
                moneda, 
                qty, 
                porcentaje_concepto,
                costo, 
                total_costo, 
                tarifa, 
                total_tarifa, 
                aplica 
            };
            
            if (window.indiceCostoEdicion !== undefined) {
                costosServicio[window.indiceCostoEdicion] = nuevo;
                delete window.indiceCostoEdicion;
            } else {
                costosServicio.push(nuevo);
            }
            
            actualizarTablaCostos();
            
            // Limpiar campos
            ['costo_concepto', 'costo_qty', 'costo_porcentaje_concepto', 'costo_costo', 'costo_tarifa', 'costo_aplica'].forEach(id => {
                if (id.includes('concepto') || id.includes('aplica')) {
                    document.getElementById(id).selectedIndex = 0;
                } else {
                    document.getElementById(id).value = '';
                }
            });
            document.getElementById('costo_total_costo').value = '0.00';
            document.getElementById('costo_total_tarifa').value = '0.00';
            exito('Costo guardado');
        }

        function actualizarTablaCostos() {
            const tbody = document.getElementById('costos-body');
            if (!tbody) return;
            tbody.innerHTML = '';
            
            let tc = 0, tt = 0;
            costosServicio.forEach((c, i) => {
                const qty = parseFloat(c.qty) || 0;
                const costo = parseFloat(c.costo) || 0;
                const tarifa = parseFloat(c.tarifa) || 0;
                const porcentaje_concepto = parseFloat(c.porcentaje_concepto) || 100; // ✅
                
                // ✅ Cálculo correcto con porcentaje
                const total_costo = (costo * (porcentaje_concepto / 100)) * qty;
                const total_tarifa = tarifa * qty;
                
                tc += total_costo;
                tt += total_tarifa;
                
                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td>${c.concepto}</td>
                <td>${c.moneda}</td>
                <td style="text-align: right;">${qty.toFixed(2)}</td>
                <td style="text-align: right;">${porcentaje_concepto.toFixed(2)}%</td> <!-- ✅ Mostrar % -->
                <td style="text-align: right; background-color: #fff9db;">${costo.toFixed(2)}</td>
                <td style="text-align: right; background-color: #fff9db;">${total_costo.toFixed(2)}</td>
                <td style="text-align: right; background-color: #e6f7ff;">${tarifa.toFixed(2)}</td>
                <td style="text-align: right; background-color: #e6f7ff;">${total_tarifa.toFixed(2)}</td>
                <td>${c.aplica}</td>
                <td>
                    <button type="button" onclick="editarCosto(${i})">✏️</button>
                    <button type="button" onclick="eliminarCosto(${i})">🗑️</button>
                </td>
                `;
                tbody.appendChild(tr);
            });
            
            // ✅ Solo actualizar los totales si los elementos existen
            const totalCostoEl = document.getElementById('total-costo-costos');
            const totalTarifaEl = document.getElementById('total-tarifa-costos');
            
            if (totalCostoEl) totalCostoEl.textContent = tc.toFixed(2);
            if (totalTarifaEl) totalTarifaEl.textContent = tt.toFixed(2);
        }

        function editarCosto(i) {
            const c = costosServicio[i];
            if (!c) return;
            
            document.getElementById('costo_concepto').value = c.concepto || '';
            document.getElementById('costo_qty').value = c.qty || '';
            document.getElementById('costo_porcentaje_concepto').value = c.porcentaje_concepto || 100;
            document.getElementById('costo_costo').value = c.costo || '';
            document.getElementById('costo_tarifa').value = c.tarifa || '';
            document.getElementById('costo_aplica').value = c.aplica || '';
            
            // Recalcular totales con fórmula CORRECTA
            const qty = parseFloat(c.qty) || 0;
            const porcentaje = parseFloat(c.porcentaje_concepto) || 100;
            const costo = parseFloat(c.costo) || 0;
            const tarifa = parseFloat(c.tarifa) || 0;
            
            // ✅ Fórmula correcta
            const total_costo = (costo * (porcentaje / 100)) * qty;
            const total_tarifa = qty * tarifa;
            
            document.getElementById('costo_total_costo').value = total_costo.toFixed(2);
            document.getElementById('costo_total_tarifa').value = total_tarifa.toFixed(2);
            
            window.indiceCostoEdicion = i;
        }

        function eliminarCosto(i) {
            if (confirm('¿Eliminar costo?')) {
                costosServicio.splice(i, 1);
                actualizarTablaCostos();
                exito('Costo eliminado');
            }
        }

        function cerrarSubmodalCostos() {
            if (servicioEnEdicion !== null) {
                servicios[servicioEnEdicion].costos = [...costosServicio];
            }
            document.getElementById('submodal-costos').style.display = 'none';
        }

        document.getElementById('gasto_tipo')?.addEventListener('change', function() {
            const tipo = this.value;
            if (!tipo) {
                document.getElementById('gasto_gasto').innerHTML = '<option value="">Gastos</option>';
                return;
            }
            fetch(`/api/get_gastos_locales.php?tipo=${encodeURIComponent(tipo)}`)
                .then(r => r.json())
                .then(data => {
                    const sel = document.getElementById('gasto_gasto');
                    if (sel) {
                        sel.innerHTML = '<option value="">Gastos</option>';
                        (data.gastos || []).forEach(g => {
                            const opt = document.createElement('option');
                            opt.value = g;
                            opt.textContent = g;
                            sel.appendChild(opt);
                        });
                    }
                });
        });

        function guardarGastoLocal() {
            const tipo = document.getElementById('gasto_tipo').value;
            const gasto = document.getElementById('gasto_gasto').value;
            const moneda = document.getElementById('gasto_moneda').value;
            const monto = parseFloat(document.getElementById('gasto_monto').value) || 0;
            const afecto = document.getElementById('gasto_afecto').value;
            const iva = parseFloat(document.getElementById('gasto_iva').value) || 0;
            if (!tipo || !gasto) return error('Tipo y Gasto son obligatorios');
            gastosLocales.push({ tipo, gasto, moneda, monto, afecto, iva });
            actualizarTablaGastosLocales();
            ['gasto_tipo', 'gasto_gasto', 'gasto_moneda', 'gasto_monto', 'gasto_afecto', 'gasto_iva'].forEach(id => {
                if (id.includes('tipo') || id.includes('gasto') || id.includes('moneda') || id.includes('afecto')) {
                    document.getElementById(id).selectedIndex = 0;
                } else {
                    document.getElementById(id).value = '';
                }
            });
            exito('Gasto local agregado');
        }

        function editarGastoLocal(index) {
            const gasto = gastosLocales[index];
            if (!gasto) {
                error('Gasto no encontrado.');
                return;
            }

            // Cargar los valores en los campos del formulario
            document.getElementById('gasto_tipo').value = gasto.tipo || '';
            // --- CORRECCIÓN: Seleccionar la opción correcta en el select gasto_gasto ---
            const gastoSelect = document.getElementById('gasto_gasto');
            gastoSelect.value = gasto.gasto || ''; // Intentar seleccionar por valor
            document.getElementById('gasto_moneda').value = gasto.moneda || 'USD';
            document.getElementById('gasto_monto').value = gasto.monto || '';
            document.getElementById('gasto_afecto').value = gasto.afecto || 'NO';
            document.getElementById('gasto_iva').value = gasto.iva || '';

            // Cambiar el botón "Agregar" a "Actualizar" temporalmente
            const btnAgregar = document.querySelector('#submodal-gastos-locales button[onclick="guardarGastoLocal()"]');
            if (btnAgregar) {
                btnAgregar.textContent = 'Actualizar';
                // Almacenar el índice del gasto que se está editando en un lugar accesible
                window.indiceGastoEdicion = index;

                // Cambiar la acción del botón para que actualice en lugar de agregar
                btnAgregar.onclick = function() {
                    actualizarGastoLocal(window.indiceGastoEdicion);
                    // Restaurar botón a su estado original después de actualizar
                    btnAgregar.textContent = 'Agregar';
                    btnAgregar.onclick = function() { guardarGastoLocal(); };
                    // Limpiar la variable global
                    delete window.indiceGastoEdicion;
                };
            }
        }

        function actualizarGastoLocal(index) {
            const tipo = document.getElementById('gasto_tipo').value;
            const gasto_nombre = document.getElementById('gasto_gasto').value;
            const moneda = document.getElementById('gasto_moneda').value;
            const monto = parseFloat(document.getElementById('gasto_monto').value) || 0;
            const afecto = document.getElementById('gasto_afecto').value;
            const iva = parseFloat(document.getElementById('gasto_iva').value) || 0;

            if (!tipo || !gasto_nombre) {
                error('Tipo y Gasto son obligatorios');
                return;
            }

            // Actualizar el objeto en el array
            gastosLocales[index] = { tipo, gasto: gasto_nombre, moneda, monto, afecto, iva };

            // Actualizar la tabla visual
            actualizarTablaGastosLocales();

            // Limpiar campos (opcional, puedes dejar los valores si prefieres que sea como un "update & add next")
            ['gasto_tipo', 'gasto_gasto', 'gasto_moneda', 'gasto_monto', 'gasto_afecto', 'gasto_iva'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    if (el.tagName === 'SELECT') {
                        el.selectedIndex = 0; // Seleccionar primera opción
                    } else {
                        el.value = '';
                    }
                }
            });

            exito('Gasto local actualizado');
        }

        // === FUNCIONES DE SERVICIOS ===
        function editarServicio(index) {
            if (index < 0 || index >= servicios.length) {
                error('Índice inválido');
                return;
            }

            // ✅ Obtener el objeto del servicio del array global 'servicios' usando el índice
            const servicioSeleccionado = servicios[index];

            // ✅ Asignar el ID del servicio al campo oculto del modal
            document.getElementById('id_srvc_edit').value = servicioSeleccionado.id_srvc || '';

            // Continuar con la lógica de abrir el modal y cargar los datos
            abrirModalServicio(index);
        }

        function eliminarServicio(index) {
            if (index < 0 || index >= servicios.length) return;

            const servicio = servicios[index];
            // ✅ Validar que el servicio tenga un ID permanente
            if (!servicio.id_srvc || servicio.id_srvc.startsWith('TEMP_')) {
                // Si es temporal, eliminar localmente sin API
                servicios.splice(index, 1);
                actualizarTabla();
                exito('Servicio eliminado');
                return;
            }

            // ✅ Validar que no tenga costos/gastos (opcional, según regla de negocio)
            if ((servicio.costos && servicio.costos.length > 0) || (servicio.gastos_locales && servicio.gastos_locales.length > 0)) {
                return error('No se puede eliminar: tiene costos o gastos asociados.');
            }

            if (confirm('¿Eliminar este servicio de forma permanente?')) {
                fetch('/api/eliminar_servicio.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_srvc: servicio.id_srvc })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        servicios.splice(index, 1);
                        actualizarTabla();
                        exito('Servicio eliminado correctamente');
                    } else {
                        error('Error: ' + (data.message || 'Intente nuevamente'));
                    }
                })
                .catch(err => {
                    console.error('Error al eliminar servicio:', err);
                    error('No se pudo conectar con el servidor');
                });
            }
        }

        // ===================================================================
        // === 6. CUBICADOR ===
        // ===================================================================
        function abrirSubmodalCubicador() {
            document.getElementById('cubicador_qty').value = document.getElementById('serv_bultos').value || 1;
            document.getElementById('cubicador_peso').value = document.getElementById('serv_peso').value || '';
            document.getElementById('cubicador_largo').value = '';
            document.getElementById('cubicador_ancho').value = '';
            document.getElementById('cubicador_alto').value = '';
            calcularCubicacion();
            const ids = ['cubicador_qty', 'cubicador_peso', 'cubicador_largo', 'cubicador_ancho', 'cubicador_alto'];
            ids.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    const clone = el.cloneNode(true);
                    el.parentNode.replaceChild(clone, el);
                    clone.addEventListener('input', calcularCubicacion);
                }
            });
            document.getElementById('submodal-cubicador').style.display = 'block';
        }

        function calcularCubicacion() {
            const qty = parseFloat(document.getElementById('cubicador_qty').value) || 0;
            const pesoPorBulto = parseFloat(document.getElementById('cubicador_peso').value) || 0;
            const largo = parseFloat(document.getElementById('cubicador_largo').value) || 0;
            const ancho = parseFloat(document.getElementById('cubicador_ancho').value) || 0;
            const alto = parseFloat(document.getElementById('cubicador_alto').value) || 0;
            const pesoRealTotal = pesoPorBulto * qty;
            const volumenCm3 = largo * ancho * alto * qty;
            const volumenM3 = volumenCm3 / 1000000;
            const pesoVolumetrico = volumenCm3 / 5000;
            const pesoFinal = Math.max(pesoRealTotal, pesoVolumetrico);
            document.getElementById('cubicador_volumen').textContent = volumenM3.toFixed(3) + ' m³';
            document.getElementById('cubicador_peso_vol').textContent = pesoVolumetrico.toFixed(2) + ' kg';
            document.getElementById('cubicador_peso_final').textContent = pesoFinal.toFixed(2) + ' kg';
        }

        function aplicarCubicacion() {
            const qty = document.getElementById('cubicador_qty').value;
            const pesoFinal = parseFloat(document.getElementById('cubicador_peso_final').textContent);
            const volumen = document.getElementById('cubicador_volumen').textContent.split(' ')[0];
            const l = document.getElementById('cubicador_largo').value;
            const a = document.getElementById('cubicador_ancho').value;
            const h = document.getElementById('cubicador_alto').value;
            document.getElementById('serv_bultos').value = qty;
            document.getElementById('serv_peso').value = pesoFinal;
            document.getElementById('serv_volumen').value = volumen;
            document.getElementById('serv_dimensiones').value = `${l}x${a}x${h} cm`;
            cerrarSubmodalCubicador();
            exito('Cubicación aplicada');
        }

        function cerrarSubmodalCubicador() {
            document.getElementById('submodal-cubicador').style.display = 'none';
        }

        function cargarPaises() {
            const selectPais = document.getElementById('pais') || document.getElementById('cliente_pais');
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
                    // Fallback básico
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

        function reiniciarFormProspecto() {
            // Verificar si hay cambios relevantes
            const idPpl = document.getElementById('id_ppl')?.value;
            const tieneId = idPpl && idPpl !== '0';
            const tieneServicios = servicios.length > 0;
            const tieneNotasComerciales = (document.getElementById('notas_comerciales')?.value || '').trim() !== '';
            const tieneNotasOperaciones = (document.getElementById('notas_operaciones')?.value || '').trim() !== '';

            if (tieneId || tieneServicios || tieneNotasComerciales || tieneNotasOperaciones) {
                const confirmar = confirm(
                    '⚠️ ATENCIÓN:\n\nEstá a punto de salir de Prospectos.\n' +
                    'Todos los datos no guardados (Prospecto, Servicios, Notas) se perderán.\n\n' +
                    '¿Desea continuar?'
                );
                if (!confirmar) {
                    advertencia('Abandono cancelado por el usuario');
                    return;
                }
            }

            // Limpiar formulario
            document.getElementById('form-prospecto').reset();

            // Limpiar campos ocultos y no reseteables
            ['id_ppl', 'id_prospect', 'razon_social', 'notas_comerciales', 'notas_operaciones'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });

            // Limpiar selects personalizados
            const selects = ['razon_social_select', 'operacion', 'tipo_oper', 'estado'];
            selects.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.selectedIndex = 0;
            });

            // NUEVO: Limpiar campos de contacto
            limpiarCamposContacto();

            // Limpiar servicios
            servicios = [];
            actualizarTabla();

            // Resetear concatenado
            document.getElementById('concatenado').value = '';

            // Ocultar botón de agregar servicio
            const btnAgregar = document.getElementById('btn-agregar-servicio');
            if (btnAgregar) btnAgregar.style.display = 'none';

            window.location.href = '/?page=prospectos_listas';
        }

        function validarCreditoAntesDeCerrar(rutCliente, totalVenta, callback) {
            console.log('✅ Validando crédito para RUT:', rutCliente, 'con venta total de:', totalVenta);
            
            if (!rutCliente || totalVenta <= 0) {
                callback(); // Sin validación necesaria
                console.log('✅ Sale por no hay RUT o venta total <= 0');
                return;
            }

            fetch(`/api/get_saldo_credito.php?rut=${encodeURIComponent(rutCliente)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.error) {
                        error(data.error);
                        return;
                    }
                    
                    if (totalVenta > data.saldo_credito) {
                        // ✅ Enviar notificación a finanzas y continuar
                        const prospectoId = document.getElementById('id_ppl')?.value;
                        
                        fetch('/api/notificar_sobregiro.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                rut_cliente: rutCliente,
                                total_venta: totalVenta,
                                saldo_credito: data.saldo_credito,
                                prospecto_id: prospectoId
                            })
                        })
                        .then(r => r.json())
                        .then(notifData => {
                            if (notifData.success) {
                                // ✅ Mostrar aviso amarillo (no error)
                                alert(`⚠️ Atención: El servicio excede el crédito disponible (${data.saldo_credito}). 
        Se ha notificado a Finanzas para su revisión.`);
                                console.log('✅ Notificación enviada a finanzas, continuando con el proceso');
                            } else {
                                console.warn('⚠️ No se pudo notificar a finanzas, pero continuando...');
                            }
                            // ✅ Continuar con el proceso normal
                            callback();
                        })
                        .catch(err => {
                            console.error('Error al notificar sobregiro:', err);
                            // ✅ Aún así continuar (no es crítico)
                            alert(`⚠️ Atención: El servicio excede el crédito disponible (${data.saldo_credito}). 
        No se pudo notificar a Finanzas, pero el proceso continuará.`);
                            callback();
                        });
                    } else {
                        // ✅ Crédito suficiente
                        callback();
                    }
                })
                .catch(() => {
                    error('Error al verificar crédito');
                });
        }

        // ===================================================================
        // === 7. INICIALIZACIÓN PRO (LIMPIO Y ESTABLE)
        // ===================================================================
        document.addEventListener('DOMContentLoaded', () => {

            console.log('✅ Prospectos inicializado PRO');

            // =========================
            // 1. CARGAS INICIALES
            // =========================
            cargarPaises();
            cargarOperacionesYTipos();
            cargarClientesEnSelect();

            // =========================
            // EVENTO: SELECT CLIENTE
            // =========================
            const selectCliente = document.getElementById('razon_social_select');

            if (selectCliente) {
                selectCliente.addEventListener('change', async function() {

                    const rut = this.value;

                    if (!rut) {
                        limpiarFormularioCliente();
                        return;
                    }

                    try {
                        const res = await fetch(`/api/get_cliente.php?rut=${encodeURIComponent(rut)}`);
                        const text = await res.text();

                        let data;

                        try {
                            data = JSON.parse(text);
                        } catch (e) {
                            console.error('❌ RESPUESTA NO JSON:', text);
                            throw new Error('El servidor devolvió HTML en vez de JSON');
                        }

                        if (!data.existe) return;

                        const c = data.cliente;

                        setClienteEnFormulario(c);

                        // cargar contacto
                        const res2 = await fetch(`/api/get_contactos.php?rut=${encodeURIComponent(rut)}`);
                        const data2 = await res2.json();

                        const primario = (data2.contactos || []).find(ct => ct.primario === 'S');

                        document.getElementById('contacto').value = primario?.nom_contacto || '';
                        document.getElementById('email').value = primario?.email || '';
                        document.getElementById('fono_empresa').value = primario?.fono_contacto || '';

                    } catch (e) {
                        error('Error cargando cliente');
                        console.error(e);
                    }
                });
            }

            // ===============================
            // 2. MENSAJES URL
            // ===============================
            const params = new URLSearchParams(window.location.search);
            const msg = params.get('exito');

            if (msg) {
                exito(decodeURIComponent(msg));
                history.replaceState({}, document.title, window.location.pathname + '?page=prospectos');
            }

            // ===============================
            // 3. EVENTOS CAMPOS
            // ===============================
            ['operacion', 'tipo_oper'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('change', calcularConcatenado);
            });

            // ===============================
            // 4. BOTÓN AGREGAR SERVICIO
            // ===============================
            const btnAgregarServicio = document.getElementById('btn-agregar-servicio');

            if (btnAgregarServicio) {
                btnAgregarServicio.addEventListener('click', () => {

                    const idPpl = document.getElementById('id_ppl')?.value.trim();
                    const concatenado = document.getElementById('concatenado')?.value.trim();

                    const idNum = parseInt(idPpl, 10);

                    if (!idNum || idNum <= 0) {
                        error('Debe guardar el prospecto antes de agregar servicios');
                        return;
                    }

                    if (!concatenado) {
                        error('Código de prospecto no disponible');
                        return;
                    }

                    abrirModalServicio();
                });
            }

            // ===============================
            // 5. BOTÓN GUARDAR SERVICIO MODAL
            // ===============================
            document.getElementById('btn-guardar-servicio-modal')
                ?.addEventListener('click', guardarServicio);

            // ===============================
            // 6. BOTÓN GRABAR TODO (CLAVE)
            // ===============================
            const btnSave = document.getElementById('btn-save-all');

            if (btnSave) {

                btnSave.addEventListener('click', guardarProspecto);

            } else {
                console.warn('⚠️ btn-save-all no encontrado');
            }

            // ===============================
            // 7. COSTOS / GASTOS
            // ===============================
            document.getElementById('btn-costos-servicio-dentro')
                ?.addEventListener('click', abrirSubmodalCostos);

            document.getElementById('btn-gastos-locales-dentro')
                ?.addEventListener('click', abrirSubmodalGastosLocales);

            // ===============================
            // 8. BUSCADOR INTELIGENTE
            // ===============================
            const inputBusqueda = document.getElementById('busqueda-inteligente');

            if (inputBusqueda) {
                inputBusqueda.addEventListener('input', async function() {

                    const term = this.value.trim();
                    const div = document.getElementById('resultados-busqueda');

                    if (!div) return;

                    div.style.display = 'none';

                    if (!term) return;

                    try {
                        const res = await fetch(`/api/buscar_inteligente.php?term=${encodeURIComponent(term)}`);
                        const text = await res.text();

                        let data;

                        try {
                            data = JSON.parse(text);
                        } catch (e) {
                            console.error('❌ RESPUESTA NO JSON:', text);
                            throw new Error('El servidor devolvió HTML en vez de JSON');
                        }

                        div.innerHTML = '';

                        if (data.length > 0) {
                            data.forEach(p => {
                                const d = document.createElement('div');

                                d.style.padding = '0.8rem';
                                d.style.cursor = 'pointer';

                                d.innerHTML = `
                                    <strong>${p.razon_social}</strong><br>
                                    <small>ID: ${p.concatenado} | RUT: ${p.rut_empresa}</small>
                                `;

                                d.addEventListener('click', () => {
                                    seleccionarProspecto(p.id_ppl);
                                    div.style.display = 'none';
                                    inputBusqueda.value = '';
                                });

                                div.appendChild(d);
                            });

                            div.style.display = 'block';
                        }

                    } catch (e) {
                        error('Error en búsqueda de prospectos');
                    }
                });
            }

            // ===============================
            // 9. SYNC RAZÓN SOCIAL (CRÍTICO)
            // ===============================
            document.getElementById('razon_social_input')
                ?.addEventListener('input', function() {
                    document.getElementById('razon_social_hidden').value = this.value;
                });

            const select = document.getElementById('razon_social_select');
            const input = document.getElementById('razon_social_input');

            if (!select || !input) return;

            // === SI EL USUARIO ESCRIBE → MODO NUEVO CLIENTE ===
            input.addEventListener('input', () => {
                select.value = '';
                activarModoManual();
            });

        });


        // ===================================================================
        // === GUARDAR PROSPECTO PRO (ÚNICO Y CORRECTO)
        // ===================================================================
        async function guardarProspecto(e) {

            e.preventDefault();

            try {

                console.log('💾 Guardando prospecto...');

                const form = document.getElementById('form-prospecto');

                if (!form) throw new Error('Formulario no encontrado');

                const formData = new FormData(form);

                // =========================
                // NORMALIZAR RAZÓN SOCIAL
                // =========================
                const inputManual = document.getElementById('razon_social_input')?.value || '';
                const select = document.getElementById('razon_social_select');

                const selectedText = select?.options[select.selectedIndex]?.text || '';

                const razonFinal = inputManual.trim() || (select?.value ? selectedText : '');

                if (!razonFinal) {
                    advertencia('Debes ingresar la razón social');
                    return;
                }

                // const duplicado = await detectarDuplicado(razonFinal);
                // if (duplicado) return;

                formData.set('razon_social', razonFinal);

                // =========================
                // DETECTAR NUEVO CLIENTE
                // =========================
                const esNuevoCliente = !select?.value;
                formData.append('es_nuevo_cliente', esNuevoCliente ? '1' : '0');

                console.log('Cliente nuevo:', esNuevoCliente);

                // =========================
                // REQUEST
                // =========================
               const res = await fetch('api/guardar_prospecto_full.php', {
                    method: 'POST',
                    body: formData
                });

                const text = await res.text();

                let data; // ✅ declarar UNA sola vez

                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('❌ RESPUESTA HTML:', text);
                    throw new Error('El backend devolvió HTML');
                }

                console.log('Respuesta:', data);

                if (data.ok) {

                    exito('✅ Prospecto guardado correctamente');

                    // =========================
                    // SETEAR ID DEL PROSPECTO
                    // =========================
                    const idPplInput = document.getElementById('id_ppl');
                    if (idPplInput) {
                        idPplInput.value = data.id_ppl;
                    }

                    // =========================
                    // MANTENER DATOS CLAVE
                    // =========================
                    const operacion = document.getElementById('operacion')?.value;
                    const tipoOper = document.getElementById('tipo_oper')?.value;

                    // =========================
                    // RECARGAR CLIENTE EN SELECT
                    // =========================
                    if (data.id_cliente) {
                        const rut = formData.get('rut_empresa');
                        const select = document.getElementById('razon_social_select');

                        if (select && rut) {
                            select.value = rut;
                            select.dispatchEvent(new Event('change'));
                        }
                    }

                    // =========================
                    // RESTAURAR CAMPOS
                    // =========================
                    if (operacion) document.getElementById('operacion').value = operacion;
                    if (tipoOper) document.getElementById('tipo_oper').value = tipoOper;

                    // =========================
                    // RECALCULAR CONCATENADO
                    // =========================
                    if (typeof calcularConcatenado === 'function') {
                        calcularConcatenado();
                    }

                    // =========================
                    // MOSTRAR BOTÓN SERVICIO
                    // =========================
                    const btnAgregar = document.getElementById('btn-agregar-servicio');
                    if (btnAgregar && data.id_ppl > 0) {
                        btnAgregar.style.display = 'inline-flex';
                    }

                    // =========================
                    // LIMPIAR SOLO CAMPOS NO CRÍTICOS
                    // =========================
                    limpiarCamposProspectoParcial();
                }

            } catch (err) {

                console.error('❌ Error:', err);

                error(err.message);
            }
        }

        // Función para limpiar los campos de contacto
        function limpiarCamposContacto() {
            document.getElementById('contacto').value = '';
            document.getElementById('email').value = '';
        }

        // Evento para detectar cambios en el campo rut_empresa
        document.getElementById('rut_empresa').addEventListener('change', function() {
            const rut = this.value.trim();
            if (rut) {
                cargarContactoPrimario(rut);
            } else {
                limpiarCamposContacto();
            }
        });

        // --- Variables globales para adjuntos ---
        let adjuntosProspecto = []; // Array para almacenar los adjuntos del prospecto actual
        let idProspectoActual = null; // Para saber a qué prospecto pertenecen los adjuntos

        // --- Funciones para manejo de Adjuntos ---

        // Abrir submodal de adjuntos
        function abrirSubmodalAdjuntos() {
            const idPpl = document.getElementById('id_ppl')?.value;
            if (!idPpl || idPpl === '0') {
                error('No hay un prospecto seleccionado para adjuntar archivos.');
                return;
            }
            idProspectoActual = idPpl; // Guardar ID del prospecto actual
            cargarAdjuntosProspecto(idPpl); // Cargar adjuntos del prospecto
            document.getElementById('submodal-adjuntos').style.display = 'block';
            // Prevenir el submit del form principal si el submodal está dentro de él
            event?.preventDefault?.(); // Agregar esta línea si el evento click lo provee
        }

        // Cerrar submodal de adjuntos
        function cerrarSubmodalAdjuntos() {
            document.getElementById('submodal-adjuntos').style.display = 'none';
            // Opcional: Limpiar el input de archivo al cerrar
            document.getElementById('archivo-input').value = '';
            // Prevenir el submit del form principal si el submodal está dentro de él
            event?.preventDefault?.(); // Agregar esta línea si el evento click lo provee
        }

        // Cargar adjuntos desde la API
        function cargarAdjuntosProspecto(idPpl) {
            // Limpiar lista antes de cargar
            document.getElementById('lista-adjuntos').innerHTML = '<p style="color: #666; text-align: center;">Cargando...</p>';

            fetch(`/api/get_adjuntos_prospecto.php?id_prospect=${encodeURIComponent(idPpl)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const adjuntos = data.adjuntos || [];
                        if (adjuntos.length === 0) {
                            document.getElementById('lista-adjuntos').innerHTML = '<p style="color: #666; text-align: center;">No hay adjuntos para este prospecto.</p>';
                            return;
                        }
                        document.getElementById('lista-adjuntos').innerHTML = adjuntos.map(adj => `
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem; border-bottom: 1px solid #eee;">
                                <a href="${adj.ruta_archivo}" target="_blank" style="text-decoration: none; color: #007bff; flex-grow: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <i class="fas fa-file"></i> ${adj.nombre_archivo}
                                </a>
                                <button type="button" class="btn-delete" onclick="eliminarAdjunto(${adj.id_adjunto})" style="margin-left: 0.5rem; background: #dc3545; color: white; border: none; padding: 0.3rem 0.6rem; border-radius: 4px; cursor: pointer; font-size: 0.85rem;">
                                    🗑️
                                </button>
                            </div>
                        `).join('');
                    } else {
                        document.getElementById('lista-adjuntos').innerHTML = `<p style="color: #dc3545; text-align: center;">Error: ${data.message || 'No se pudieron cargar los adjuntos.'}</p>`;
                    }
                })
                .catch(err => {
                    console.error('Error al cargar adjuntos:', err);
                    document.getElementById('lista-adjuntos').innerHTML = '<p style="color: #dc3545; text-align: center;">Error de conexión al cargar adjuntos.</p>';
                });
        }

        // Subir un adjunto
        function subirAdjunto() {
            const input = document.getElementById('archivo-input');
            const archivo = input.files[0];
            if (!archivo) {
                error('Seleccione un archivo para subir.');
                return;
            }

            if (!idProspectoActual) {
                error('No se puede subir: No hay prospecto seleccionado.');
                return;
            }

            const formData = new FormData();
            formData.append('archivo', archivo);
            formData.append('id_prospect', idProspectoActual);

            // Opcional: Mostrar indicador de carga
            document.getElementById('lista-adjuntos').innerHTML = '<p style="color: #666; text-align: center;">Subiendo archivo...</p>';

            fetch('/api/subir_adjunto_prospecto.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errData => { throw new Error(errData.message || `HTTP error! status: ${response.status}`); });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    exito(data.message || 'Archivo subido correctamente.');
                    input.value = ''; // Limpiar input
                    cargarAdjuntosProspecto(idProspectoActual); // Recargar lista
                } else {
                    error('Error al subir archivo: ' + (data.message || 'Intente nuevamente'));
                }
            })
            .catch(err => {
                console.error('Error en la solicitud de subida:', err);
                error('Error de conexión al subir el archivo: ' + err.message);
                // Recargar lista por si acaso la subida falló pero el estado del frontend quedó desactualizado
                cargarAdjuntosProspecto(idProspectoActual);
            });
        }

        // Eliminar un adjunto
        function eliminarAdjunto(idAdjunto) {
            if (!confirm('¿Eliminar este archivo adjunto?')) return;

            fetch('/api/eliminar_adjunto_prospecto.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_adjunto: idAdjunto })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errData => { throw new Error(errData.message || `HTTP error! status: ${response.status}`); });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    exito(data.message || 'Archivo eliminado correctamente.');
                    cargarAdjuntosProspecto(idProspectoActual); // Recargar lista
                } else {
                    error('Error al eliminar archivo: ' + (data.message || 'Intente nuevamente'));
                }
            })
            .catch(err => {
                console.error('Error en la solicitud de eliminación:', err);
                error('Error de conexión al eliminar el archivo: ' + err.message);
                // Recargar lista por si acaso la eliminación falló pero el estado del frontend quedó desactualizado
                cargarAdjuntosProspecto(idProspectoActual);
            });
        }

        // Asignar listener al botón de adjuntos (ajusta el selector si es necesario)
        // Busca el botón real en tu HTML principal (por ejemplo, en la barra de herramientas del formulario prospecto)
        // y asegúrate de que su ID o clase coincida con el selector aquí.
        // Ejemplo (ajusta 'btn-adjuntos' por el ID o clase real):
        document.getElementById('btn-adjuntos')?.addEventListener('click', function(event) {
            event.preventDefault(); // Prevenir cualquier comportamiento por defecto del botón
            abrirSubmodalAdjuntos();
        });

        // --- Funciones para manejo del submodal Route Order ---
        function abrirModalTransporteNac(accion) {
            // Obtener datos del contexto actual
            const idPpl = document.getElementById('id_ppl')?.value || '';
            const idSrvc = document.getElementById('id_srvc_edit')?.value || '';
            if (!idPpl || !idSrvc) {
                alert('No hay un servicio seleccionado.');
                return;
            }

            if (accion === 'editar') {
                // Cargar registro existente
                fetch(`/pages/ro_transp_nac_logic.php?action=get&id_srvc=${encodeURIComponent(idSrvc)}`)
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            mostrarFormularioTransporteNac(res.data, 'editar');
                        } else {
                            alert('No existe registro. Usa "Grabar Transporte" para crear uno.');
                            mostrarFormularioTransporteNac(null, 'crear');
                        }
                    })
                    .catch(e => {
                        console.error(e);
                        alert('Error al cargar el registro.');
                    });
            } else {
                mostrarFormularioTransporteNac(null, 'crear');
            }
        }

        function mostrarFormularioTransporteNac(data, modo) {
            let html = `
                <div style="padding: 1rem; background: #f9f9f9; border: 1px solid #ccc; border-radius: 6px;">
                    <h4 style="margin-top: 0;">${modo === 'crear' ? 'Nuevo' : 'Editar'} Transporte Nacional</h4>
                    <input type="hidden" id="transp_nac_id" value="${data?.id_transp_nac || ''}">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; font-size: 9pt;">
                        <div>
                            <label>Moneda:</label>
                            <select id="transp_nac_moneda" style="width: 100%;">
                                <option value="CLP" ${(!data?.moneda || data.moneda === 'CLP') ? 'selected' : ''}>CLP</option>
                                <option value="USD" ${data?.moneda === 'USD' ? 'selected' : ''}>USD</option>
                                <option value="EUR" ${data?.moneda === 'EUR' ? 'selected' : ''}>EUR</option>
                            </select>
                        </div>
                        <div>
                            <label>Costo:</label>
                            <input type="number" id="transp_nac_costo" step="0.01" value="${data?.costo || '0.00'}" style="width: 100%;">
                        </div>
                        <div>
                            <label>Venta:</label>
                            <input type="number" id="transp_nac_venta" step="0.01" value="${data?.venta || '0.00'}" style="width: 100%;">
                        </div>
                        <div>
                            <label>Acepta:</label>
                            <select id="transp_nac_acepta" style="width: 100%;">
                                <option value="Si" ${data?.acepta === 'Si' ? 'selected' : ''}>Si</option>
                                <option value="No" ${!data?.acepta || data.acepta === 'No' ? 'selected' : ''}>No</option>
                            </select>
                        </div>
                        <div>
                            <label>Afecto:</label>
                            <select id="transp_nac_afecto" style="width: 100%;">
                                <option value="Si" ${data?.afecto === 'Si' ? 'selected' : ''}>Si</option>
                                <option value="No" ${!data?.afecto || data.afecto === 'No' ? 'selected' : ''}>No</option>
                            </select>
                        </div>
                        <div>
                            <label>Transportista:</label>
                            <input type="text" id="transp_nac_transportista" value="${data?.transportista || ''}" style="width: 100%;">
                        </div>
                        <div>
                            <label>Direc. Retiro:</label>
                            <input type="text" id="transp_nac_direc_retiro" value="${data?.direc_retiro || ''}" style="width: 100%;">
                        </div>
                        <div>
                            <label>Contacto Retiro:</label>
                            <input type="text" id="transp_nac_contacto_retiro" value="${data?.contacto_retiro || ''}" style="width: 100%;">
                        </div>
                        <div>
                            <label>Fono Retiro:</label>
                            <input type="text" id="transp_nac_fono_retiro" value="${data?.fono_retiro || ''}" style="width: 100%;">
                        </div>
                        <div>
                            <label>Direc. Entrega:</label>
                            <input type="text" id="transp_nac_direc_entrega" value="${data?.direc_entrega || ''}" style="width: 100%;">
                        </div>
                        <div>
                            <label>Fono Entrega:</label>
                            <input type="text" id="transp_nac_fono_entrega" value="${data?.fono_entrega || ''}" style="width: 100%;">
                        </div>
                        <div>
                            <label>Empresa Entrega:</label>
                            <input type="text" id="transp_nac_empresa_entrega" value="${data?.empresa_entrega || ''}" style="width: 100%;">
                        </div>
                        <div>
                            <label>Contacto Entrega:</label>
                            <input type="text" id="transp_nac_contacto_entrega" value="${data?.contacto_entrega || ''}" style="width: 100%;">
                        </div>
                    </div>
                    <div style="margin-top: 1rem; text-align: right;">
                        <button onclick="guardarTransporteNac()" style="background: #007bff; color: white; padding: 0.3rem 0.6rem; border: none; border-radius: 4px; margin-right: 0.5rem;">Guardar</button>
                        <button onclick="cerrarFormularioTransporteNac()" style="background: #6c757d; color: white; padding: 0.3rem 0.6rem; border: none; border-radius: 4px;">Cancelar</button>
                    </div>
                </div>
            `;
            document.getElementById('route-order-content').insertAdjacentHTML('beforeend', `<div id="modal-transporte-nac">${html}</div>`);
        }

        function cerrarFormularioTransporteNac() {
            const modal = document.getElementById('modal-transporte-nac');
            if (modal) modal.remove();
        }

        function guardarTransporteNac() {
            const idPpl = document.getElementById('id_ppl')?.value || '';
            const idSrvc = document.getElementById('id_srvc_edit')?.value || '';
            const id = document.getElementById('transp_nac_id')?.value || null;

            const data = {
                id_transp_nac: id || null,
                id_prospect: idPpl,
                id_srvc: idSrvc,
                moneda: document.getElementById('transp_nac_moneda').value,
                costo: document.getElementById('transp_nac_costo').value,
                venta: document.getElementById('transp_nac_venta').value,
                acepta: document.getElementById('transp_nac_acepta').value,
                afecto: document.getElementById('transp_nac_afecto').value,
                transportista: document.getElementById('transp_nac_transportista').value,
                direc_retiro: document.getElementById('transp_nac_direc_retiro').value,
                contacto_retiro: document.getElementById('transp_nac_contacto_retiro').value,
                fono_retiro: document.getElementById('transp_nac_fono_retiro').value,
                direc_entrega: document.getElementById('transp_nac_direc_entrega').value,
                fono_entrega: document.getElementById('transp_nac_fono_entrega').value,
                empresa_entrega: document.getElementById('transp_nac_empresa_entrega').value,
                contacto_entrega: document.getElementById('transp_nac_contacto_entrega').value
            };

            fetch('/pages/ro_transp_nac_logic.php?action=save', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    alert(res.message);
                    cerrarFormularioTransporteNac();
                    
                    // === Recargar y mostrar los datos guardados ===
                    const idSrvc = document.getElementById('id_srvc_edit')?.value || '';
                    fetch(`/pages/ro_transp_nac_logic.php?action=get&id_srvc=${encodeURIComponent(idSrvc)}`)
                        .then(r2 => r2.json())
                        .then(res2 => {
                            if (res2.success && res2.data) {
                                renderizarTablaTransporteNac(res2.data);
                                renderizarCamposTransporteNac(res2.data);
                            } else {
                                renderizarTablaTransporteNac(null);
                                renderizarCamposTransporteNac(null);
                            }
                        })
                        .catch(e => {
                            console.error('Error al recargar transporte:', e);
                            renderizarTablaTransporteNac(null);
                            renderizarCamposTransporteNac(null);
                        });
                    // ================================================
                } else {
                    alert('Error: ' + res.message);
                }
            })
            .catch(e => {
                console.error(e);
                alert('Error de conexión.');
            });
        }

        function eliminarTransporteNac() {
            if (!confirm('¿Eliminar registro de Transporte Nacional?')) return;

            const idSrvc = document.getElementById('id_srvc_edit')?.value || '';
            fetch(`/pages/ro_transp_nac_logic.php?action=get&id_srvc=${encodeURIComponent(idSrvc)}`)
                .then(r => r.json())
                .then(res => {
                    if (res.success && res.data?.id_transp_nac) {
                        const id = res.data.id_transp_nac;
                        fetch(`/pages/ro_transp_nac_logic.php?action=delete&id=${id}`)
                            .then(r => r.json())
                            .then(res2 => {
                                alert(res2.message || 'Eliminado.');
                            })
                            .catch(e => alert('Error al eliminar.'));
                    } else {
                        alert('No hay registro para eliminar.');
                    }
                })
                .catch(e => alert('Error al verificar registro.'));
            renderizarTablaTransporteNac(null);
            renderizarCamposTransporteNac(null);
        }

        // Renderiza la TABLA de Transporte Nacional (7 columnas)
        function renderizarTablaTransporteNac(data = null) {
            const tbody = document.querySelector('#tabla-transporte-nac tbody');
            if (!tbody) return;

            if (data) {
                const profit = (parseFloat(data.venta) || 0) - (parseFloat(data.costo) || 0);
                tbody.innerHTML = `
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 0.3rem;">${sanitizeText(data.concepto || 'NACIONAL')}</td>
                        <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">${sanitizeText(data.moneda || 'CLP')}</td>
                        <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">${parseFloat(data.costo).toLocaleString('es-CL', { minimumFractionDigits: 2 })}</td>
                        <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">${parseFloat(data.venta).toLocaleString('es-CL', { minimumFractionDigits: 2 })}</td>
                        <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">${profit.toLocaleString('es-CL', { minimumFractionDigits: 2 })}</td>
                        <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">${sanitizeText(data.acepta || 'No')}</td>
                        <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">${sanitizeText(data.afecto || 'No')}</td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 0.3rem;"></td>
                        <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;"></td>
                        <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;"></td>
                        <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;"></td>
                        <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;"></td>
                        <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;"></td>
                        <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;"></td>
                    </tr>
                `;
            }
        }

        // Renderiza los CAMPOS de transporte (a la derecha de los labels)
        function renderizarCamposTransporteNac(data = null) {
            const contenedor = document.getElementById('campos-transporte-nac');
            if (!contenedor) return;

            const getVal = (val) => sanitizeText(val || '&nbsp;');

            contenedor.innerHTML = `
                <div style="display: grid; grid-template-columns: max-content auto 1fr max-content auto; gap: 0.5rem; font-size: 9pt; margin-top: 0.8rem;">
                    <!-- Columna 1: Labels izquierda -->
                    <div><strong>TRANSPORTISTA:</strong></div>
                    <div>${getVal(data?.transportista)}</div>
                    <div></div>
                    <div><strong>DIREC. ENTREGA:</strong></div>
                    <div>${getVal(data?.direc_entrega)}</div>

                    <div><strong>DIREC. RETIRO:</strong></div>
                    <div>${getVal(data?.direc_retiro)}</div>
                    <div></div>
                    <div><strong>FONO:</strong></div>
                    <div>${getVal(data?.fono_entrega)}</div>

                    <div><strong>CONTACTO:</strong></div>
                    <div>${getVal(data?.contacto_retiro)}</div>
                    <div></div>
                    <div><strong>EMPRESA:</strong></div>
                    <div>${getVal(data?.empresa_entrega)}</div>

                    <div><strong>FONO:</strong></div>
                    <div>${getVal(data?.fono_retiro)}</div>
                    <div></div>
                    <div><strong>CONTACTO:</strong></div>
                    <div>${getVal(data?.contacto_entrega)}</div>
                </div>
            `;
        }

        function sanitizeText(text) {
            if (typeof text !== 'string') {
                return text == null ? '' : String(text);
            }
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
                '/': '&#x2F;'
            };
            return text.replace(/[&<>"'\/]/g, function (s) {
                return map[s];
            });
        }

        function abrirSubmodalRouteOrder() {
            console.log('🔍 [ROUTE_ORDER] Iniciando apertura de submodal.');
            const idPpl = document.getElementById('id_ppl')?.value;
            const concatenado = document.getElementById('concatenado')?.value;

            // Extraer datos del prospecto desde campos ocultos
            const operacion = document.getElementById('operacion')?.value || '';
            const razonSocial = document.getElementById('prospecto_razon_social')?.value || '';
            const direccion = document.getElementById('prospecto_direccion')?.value || '';
            const rutEmpresa = document.getElementById('prospecto_rut_empresa')?.value || '';
            const contactoNombre = document.getElementById('prospecto_contacto_nombre')?.value || '';
            const notas_comerciales = document.getElementById('prospecto_notas_comerciales')?.value || '';
            const notas_operaciones = document.getElementById('prospecto_notas_operaciones')?.value || '';

            const prospectoCompleto = {
                operacion: operacion,
                razon_social: razonSocial,
                direccion: direccion,
                rut_empresa: rutEmpresa,
                contacto_nombre: contactoNombre,
                notas_comerciales: notas_comerciales,
                notas_operaciones: notas_operaciones
            };

            console.log('📄 [ROUTE_ORDER] Datos del prospecto:', {
                id_ppl: idPpl,
                concatenado: concatenado,
                prospecto: prospectoCompleto
            });

            if (!idPpl || idPpl === '0') {
                console.log('⚠️ [ROUTE_ORDER] No hay un prospecto seleccionado (id_ppl vacío o 0).');
                error('No hay un prospecto seleccionado.');
                return;
            }

            const idSrvcHidden = document.getElementById('id_srvc_edit');
            let idSrvc = null;
            let servicioSeleccionadoParaRO = null;

            if (idSrvcHidden && idSrvcHidden.value) {
                idSrvc = idSrvcHidden.value;
                servicioSeleccionadoParaRO = servicios.find(s => s.id_srvc === idSrvc);
                console.log('📖 [ROUTE_ORDER] Servicio obtenido del campo oculto del modal. ID:', idSrvc, 'Encontrado en array global:', !!servicioSeleccionadoParaRO);
            } else {
                console.log('⚠️ [ROUTE_ORDER] No hay ID de servicio en el modal (posiblemente en modo "Agregar Servicio").');
                error('No hay un servicio seleccionado para generar el Route Order.');
                return;
            }

            if (!idSrvc) {
                console.log('⚠️ [ROUTE_ORDER] El ID del servicio (idSrvc) obtenido del modal es nulo o vacío.');
                error('No se puede generar Route Order: El servicio no tiene un ID definido.');
                return;
            }

            if (idSrvc.startsWith('TEMP_')) {
                console.log('⚠️ [ROUTE_ORDER] El ID del servicio obtenido del modal es temporal (TEMP_). No se puede generar para servicios temporales.');
                error('Solo se puede generar Route Order para servicios ya guardados.');
                return;
            }

            if (!servicioSeleccionadoParaRO) {
                console.warn('⚠️ [ROUTE_ORDER] El ID de servicio del modal (' + idSrvc + ') no se encontró en el array global de servicios.');
                error('Datos inconsistentes del servicio.');
                return;
            }

            console.log('✅ [ROUTE_ORDER] ID del servicio válido para generar RO:', idSrvc);

            cargarDatosRouteOrder(idSrvc, concatenado, servicioSeleccionadoParaRO, prospectoCompleto);
            document.getElementById('submodal-route-order').style.display = 'block';
            console.log('🖼️ [ROUTE_ORDER] Submodal de Route Order mostrado.');
        }

        function cerrarSubmodalRouteOrder() {
            document.getElementById('submodal-route-order').style.display = 'none';
            datosRouteOrder = null; // Limpiar datos al cerrar
        }

        function cargarDatosRouteOrder(idSrvc, concatenadoProspecto, servicioLocal = null, prospectoCompleto = {}) {
            // Mostrar indicador de carga
            document.getElementById('route-order-content').innerHTML = '<p style="text-align: center;">Cargando datos del Route Order...</p>';

            if (servicioLocal) {
                datosRouteOrder = {  // ← Asignación a la variable global (sin let/const)
                    servicio: servicioLocal,
                    prospecto: {
                        concatenado: concatenadoProspecto,
                        ...prospectoCompleto
                    },
                    costos: servicioLocal.costos || [],
                    gastos_locales: servicioLocal.gastos_locales || []
                };
                renderizarRouteOrder(datosRouteOrder);
            } else {
                fetch(`/api/get_servicio.php?id_srvc=${encodeURIComponent(idSrvc)}`)
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success && data.servicio) {
                            datosRouteOrder = {  // ← Asignación a la variable global
                                servicio: data.servicio,
                                prospecto: {
                                    concatenado: concatenadoProspecto,
                                    ...prospectoCompleto
                                },
                                costos: data.servicio.costos || [],
                                gastos_locales: data.servicio.gastos_locales || []
                            };
                            renderizarRouteOrder(datosRouteOrder);
                        } else {
                            error('Error al cargar los datos del servicio para Route Order.');
                            document.getElementById('route-order-content').innerHTML = '<p style="text-align: center; color: red;">Error al cargar los datos.</p>';
                        }
                    })
                    .catch(err => {
                        console.error('Error al cargar servicio para Route Order:', err);
                        error('Error de conexión al cargar los datos del servicio.');
                        document.getElementById('route-order-content').innerHTML = '<p style="text-align: center; color: red;">Error de conexión.</p>';
                    });
            }
        }

        // --- Función principal para renderizar el contenido del submodal Route Order ---
        function renderizarRouteOrder(datos) {
            if (!datos || !datos.servicio) {
                document.getElementById('route-order-content').innerHTML = '<p style="text-align: center; color: red;">No hay datos para mostrar.</p>';
                return;
            }

            const s_raw = datos.servicio;
            const p = datos.prospecto;
            const costos_raw = datos.costos || [];
            const gastos_locales_raw = datos.gastos_locales || [];

            const s = {
                ...s_raw,
                tipo_cambio: parseFloat(s_raw.tipo_cambio) || 1,
                costo: parseFloat(s_raw.costo) || 0,
                venta: parseFloat(s_raw.venta) || 0,
                costogastoslocalesdestino: parseFloat(s_raw.costogastoslocalesdestino) || 0,
                ventasgastoslocalesdestino: parseFloat(s_raw.ventasgastoslocalesdestino) || 0,
                peso: parseFloat(s_raw.peso) || 0,
                volumen: parseFloat(s_raw.volumen) || 0,
                bultos: parseInt(s_raw.bultos) || 0,
                iva: parseInt(s_raw.iva) || 19,
                validez: s_raw.validez || ''
            };

            const costos = costos_raw.map(c => ({
                ...c,
                qty: parseFloat(c.qty) || 0,
                costo: parseFloat(c.costo) || 0,
                tarifa: parseFloat(c.tarifa) || 0,
                total_costo: parseFloat(c.total_costo) || 0,
                total_tarifa: parseFloat(c.total_tarifa) || 0
            }));

            const gastos_locales = gastos_locales_raw.map(g => ({
                ...g,
                monto: parseFloat(g.monto) || 0,
                iva: parseFloat(g.iva) || 0
            }));

            const tipoTrafico = (s.trafico || '').toLowerCase();
            let textoTransporte = 'TRANSPORTE';
            if (tipoTrafico.includes('mar')) textoTransporte = 'NAVIERA';
            else if (tipoTrafico.includes('aer')) textoTransporte = 'AEROLINEA';
            else if (tipoTrafico.includes('ter') || tipoTrafico.includes('land')) textoTransporte = 'TRANSPORTE';

            // --- LOG de diagnóstico: contenido del servicio (s) y prospecto (p) ---
            console.log('🔍 [ROUTE_ORDER] Diagnóstico SHIPPER/CONSIGNATARIO:');
            console.log('  operacion:', p?.operacion);
            console.log('  p.razon_social:', p?.razon_social);
            console.log('  s.razon_social:', s?.razon_social);
            console.log('  p.direccion:', p?.direccion);
            console.log('  s.direccion:', s?.direccion);
            console.log('  p.contacto_nombre:', p?.contacto_nombre);
            console.log('  s.contacto_nombre:', s?.contacto_nombre);
            console.log('  p.rut_empresa:', p?.rut_empresa);
            console.log('  s.rut_empresa:', s?.rut_empresa);

            // --- Lógica corregida para SHIPPER / CONSIGNATARIO ---
            const operacion = (p?.operacion || '').toLowerCase();
            let shipperRS = '';
            let shipperDireccion = '';
            let shipperContacto = '';
            let shipperRut = '';
            let consignatarioRS = '';
            let consignatarioDireccion = '';
            let consignatarioContacto = '';
            let consignatarioRut = '';

            // Priorizar datos del servicio (s), ya que el prospecto (p) puede no tenerlos
            const razonSocial = s.razon_social || p?.razon_social || '';
            const direccion = s.direccion || p?.direccion || '';
            const contactoNombre = s.contacto_nombre || p?.contacto_nombre || '';
            const rutEmpresa = s.rut_empresa || p?.rut_empresa || '';

            console.log('  → razonSocial seleccionada:', razonSocial);
            console.log('  → direccion seleccionada:', direccion);
            console.log('  → contactoNombre seleccionado:', contactoNombre);
            console.log('  → rutEmpresa seleccionado:', rutEmpresa);

            if (operacion === 'im') {
                consignatarioRS = razonSocial;
                consignatarioDireccion = direccion;
                consignatarioContacto = contactoNombre;
                consignatarioRut = rutEmpresa;
            } else {
                shipperRS = razonSocial;
                shipperDireccion = direccion;
                shipperContacto = contactoNombre;
                shipperRut = rutEmpresa;
            }

            // Cálculo de totales
            let totalCostos = 0, totalVenta = 0, totalTotalCosto = 0, totalTotalTarifa = 0;
            costos.forEach(c => {
                totalCostos += c.costo;
                totalVenta += c.tarifa;
                totalTotalCosto += c.total_costo;
                totalTotalTarifa += c.total_tarifa;
            });

            let totalGastosCostos = 0, totalGastosVentas = 0;
            gastos_locales.forEach(g => {
                const esAfecto = (g.afecto || 'NO').toUpperCase() === 'SI';
                const subtotal = esAfecto ? g.monto * (1 + g.iva / 100) : g.monto;
                if ((g.tipo || '').toUpperCase() === 'COSTO') totalGastosCostos += subtotal;
                else if ((g.tipo || '').toUpperCase() === 'VENTAS') totalGastosVentas += subtotal;
            });

            const totalCostoFinal = s.costo + totalGastosCostos;
            const totalVentaFinal = s.venta + totalGastosVentas;
            const profitLocal = totalVentaFinal - totalCostoFinal;
            const profitPorcentaje = totalVentaFinal > 0 ? ((totalVentaFinal - totalCostoFinal) / totalVentaFinal) * 100 : 0;

            // Cargar estado de crédito
            const rutCliente = s.rut_empresa;
            let simboloCredito = ' &nbsp;';
            let simboloContado = ' &nbsp;';

            const renderFinal = (credito, contado) => {
                _renderizarRouteOrderConCredito(
                    datos, s, p, costos, gastos_locales,
                    totalCostos, totalVenta, totalTotalCosto, totalTotalTarifa,
                    totalGastosCostos, totalGastosVentas,
                    shipperRS, shipperDireccion, shipperContacto, shipperRut,
                    consignatarioRS, consignatarioDireccion, consignatarioContacto, consignatarioRut,
                    totalCostoFinal, totalVentaFinal, profitLocal, profitPorcentaje,
                    textoTransporte, credito, contado
                );
            };

            // Definir variables en el scope de renderizarRouteOrder
            let creditoSimbolo = ' &nbsp;';
            let contadoSimbolo = ' &nbsp;';

            if (rutCliente) {
                fetch(`/api/get_estado_credito_cliente.php?rut=${encodeURIComponent(rutCliente)}`)
                    .then(response => response.ok ? response.json() : Promise.reject())
                    .then(creditoData => {
                        if (creditoData.success && creditoData.estado_credito) {
                            const estado = creditoData.estado_credito.toLowerCase();
                            if (estado === 'vigente' || estado === 'activo') {
                                creditoSimbolo = ' ✅';
                                contadoSimbolo = ' &nbsp;';
                            } else {
                                creditoSimbolo = ' &nbsp;';
                                contadoSimbolo = ' ✅';
                            }
                        } else {
                            creditoSimbolo = ' &nbsp;';
                            contadoSimbolo = ' ✅';
                        }
                        // === Agregar a datosRouteOrder ===
                        if (datosRouteOrder) {
                            datosRouteOrder.estado_credito = {
                                credito: creditoSimbolo,
                                contado: contadoSimbolo
                            };
                        }
                        renderFinal(creditoSimbolo, contadoSimbolo);
                    })
                    .catch(() => {
                        creditoSimbolo = ' &nbsp;';
                        contadoSimbolo = ' ✅';
                        if (datosRouteOrder) {
                            datosRouteOrder.estado_credito = {
                                credito: creditoSimbolo,
                                contado: contadoSimbolo
                            };
                        }
                        renderFinal(creditoSimbolo, contadoSimbolo);
                    });
            } else {
                // Sin RUT, asumir contado
                creditoSimbolo = ' &nbsp;';
                contadoSimbolo = ' ✅';
                if (datosRouteOrder) {
                    datosRouteOrder.estado_credito = {
                        credito: creditoSimbolo,
                        contado: contadoSimbolo
                    };
                }
                renderFinal(creditoSimbolo, contadoSimbolo);
            }
            const idSrvc = document.getElementById('id_srvc_edit')?.value || '';
            // === Cargar y mostrar datos de Transporte Nacional al abrir el submodal ===
            if (idSrvc) {
                fetch(`/pages/ro_transp_nac_logic.php?action=get&id_srvc=${encodeURIComponent(idSrvc)}`)
                    .then(r => r.json())
                    .then(res => {
                        if (res.success && res.data) {
                            renderizarTablaTransporteNac(res.data);
                            renderizarCamposTransporteNac(res.data);
                        } else {
                            renderizarTablaTransporteNac(null);
                            renderizarCamposTransporteNac(null);
                        }
                    })
                    .catch(e => {
                        console.error('Error al cargar Transporte Nacional:', e);
                        renderizarTablaTransporteNac(null);
                        renderizarCamposTransporteNac(null);
                    });
            } else {
                renderizarTablaTransporteNac(null);
                renderizarCamposTransporteNac(null);
            }

            // Al final de renderizarRouteOrder, después de mostrar el HTML
            if (idSrvc) {
                fetch(`/pages/ro_transp_nac_logic.php?action=get&id_srvc=${encodeURIComponent(idSrvc)}`)
                    .then(r => r.json())
                    .then(res => {
                        if (res.success && res.data) {
                            // === AGREGAR transporte_nac a datosRouteOrder ===
                            if (datosRouteOrder) {
                                datosRouteOrder.transporte_nac = res.data;
                            }
                            renderizarTablaTransporteNac(res.data);
                            renderizarCamposTransporteNac(res.data);
                        } else {
                            if (datosRouteOrder) {
                                datosRouteOrder.transporte_nac = null;
                            }
                            renderizarTablaTransporteNac(null);
                            renderizarCamposTransporteNac(null);
                        }
                    })
                    .catch(e => {
                        console.error('Error al cargar Transporte Nacional:', e);
                        if (datosRouteOrder) {
                            datosRouteOrder.transporte_nac = null;
                        }
                        renderizarTablaTransporteNac(null);
                        renderizarCamposTransporteNac(null);
                    });
            }
        }

        // --- Función auxiliar para construir el HTML con el estado de crédito y datos del servicio ---
        function _renderizarRouteOrderConCredito(datos, s, p, costos, gastos_locales, totalCostos, totalVenta, totalTotalCosto, totalTotalTarifa, totalGastosCostos, totalGastosVentas, shipperRS, shipperDireccion, shipperContacto, shipperRut, consignatarioRS, consignatarioDireccion, consignatarioContacto, consignatarioRut, totalCostoFinal, totalVentaFinal, profitLocal, profitPorcentaje, textoTransporte, simboloCredito, simboloContado) {
            let html = `
                <div style="font-size: 9pt; line-height: 1.4;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div style="text-align: left;">
                            <strong>Nº Cotización:</strong> ${p?.concatenado || s.concatenado || 'N/A'}<br>
                            <strong>TRÁFICO:</strong> <strong>${s.trafico || ''}</strong><br>
                        </div>
                        <div style="text-align: left;">
                            <strong>TIPO CAMBIO CLIENTE:</strong> ${(s.tipo_cambio || 1).toFixed(4)}<br>
                            <strong>AGENTE / OFICINA:</strong> ${s.agente || ''}<br>
                            <strong>REF. CLIENTE:</strong> ${s.ref_cliente || ''}<br>
                            <strong>PROV. NACIONAL:</strong> ${s.proveedor_nac || ''}<br>
                            <strong>TERRESTRE:</strong><br>
                            <strong>DESCONSOLIDACIÓN:</strong> ${s.desconsolidac || ''}<br>
                            <strong>GRÚAS:</strong><br>
                            <strong>EMBALAJE:</strong>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div style="border: 1px solid #ccc; border-radius: 6px; padding: 1rem; background-color: #f9f9f9;">
                            <h4 style="margin: 0 0 0.8rem 0; font-size: 10pt; font-weight: bold; color: #007bff;">SHIPPER</h4>
                            <div><strong>Razón Social:</strong> ${sanitizeText(shipperRS)}</div>
                            <div><strong>Dirección:</strong> ${sanitizeText(shipperDireccion)}</div>
                            <div><strong>Contacto:</strong> ${sanitizeText(shipperContacto)}</div>
                            <div><strong>R.U.T.:</strong> ${sanitizeText(shipperRut)}</div>
                        </div>
                        <div style="border: 1px solid #ccc; border-radius: 6px; padding: 1rem; background-color: #f9f9f9;">
                            <h4 style="margin: 0 0 0.8rem 0; font-size: 10pt; font-weight: bold; color: #28a745;">CONSIGNATARIO</h4>
                            <div><strong>Razón Social:</strong> ${sanitizeText(consignatarioRS)}</div>
                            <div><strong>Dirección:</strong> ${sanitizeText(consignatarioDireccion)}</div>
                            <div><strong>Contacto:</strong> ${sanitizeText(consignatarioContacto)}</div>
                            <div><strong>R.U.T.:</strong> ${sanitizeText(consignatarioRut)}</div>
                        </div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong>INCOTERM:</strong> ${s.incoterm || ''}<br>
                        <strong>COMMODITY:</strong> ${s.commodity || ''}<br>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem;">
                            <div><strong>VOLUMEN:</strong> ${(s.volumen || 0).toFixed(2)}</div>
                            <div><strong>PESO BRUTO:</strong> ${(s.peso || 0).toFixed(2)} kg</div>
                            <div><strong>DIMENSIONES:</strong> ${s.dimensiones || ''}</div>
                            <div><strong>UNIDADES:</strong> ${s.bultos || 0}</div>
                        </div>
                        <div><strong>POD:</strong> ${s.destino || ''}</div>
                        <div><strong>POL:</strong> ${s.origen || ''}</div>
                        <div><strong>COLOADER:</strong></div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong>NOTAS ADICIONALES:</strong><br>
                        <div style="white-space: pre-line; margin-left: 1rem;">${sanitizeText(s.nota_srvc || '')}</div>
                    </div>

                    <!-- PROFIT SHARE ACTUALIZADO -->
                                            <h4 style="margin-top: 2rem; margin-bottom: 1rem;">PROFIT SHARE</h4>
                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                                <!-- BLOQUE COSTOS -->
                                                <div>
                                                    <h5 style="margin-bottom: 0.5rem;">Costos</h5>
                                                    <table style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
                                                        <thead>
                                                            <tr style="background-color: #f2f2f2;">
                                                                <th style="border: 1px solid #ddd; text-align: left; padding: 0.3rem;">Concepto</th>
                                                                <th style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">Qty</th>
                                                                <th style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">Costo</th>
                                                                <th style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">Total</th>
                                                                <th style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">Aplica</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                    `;

                                    // Calcular total costo real (qty * costo)
                                    let totalCostoCalculado = 0;
                                    costos.forEach(c => {
                                        const qty = c.qty || 0;
                                        const costo = c.costo || 0;
                                        const total = qty * costo;
                                        totalCostoCalculado += total;
                                        html += `
                                                            <tr>
                                                                <td style="border: 1px solid #ddd; padding: 0.3rem;">${sanitizeText(c.concepto || '')}</td>
                                                                <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">${qty.toFixed(2)}</td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">${costo.toFixed(2)}</td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">${total.toFixed(2)}</td>
                                                                <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">${sanitizeText(c.aplica || '')}</td>
                                                            </tr>
                                        `;
                                    });

                                    html += `
                                                        </tbody>
                                                        <tfoot>
                                                            <tr style="font-weight: bold;">
                                                                <td colspan="3" style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">TOTAL COSTO:</td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">${totalCostoCalculado.toLocaleString('es-CL', { minimumFractionDigits: 2 })}</td>
                                                                <td style="border: 1px solid #ddd;"></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>

                                                    <!-- CONDICIONES COMERCIALES (izquierda) -->
                                                    <h4 style="margin-top: 2rem; margin-bottom: 1rem;">CONDICIONES COMERCIALES</h4>
                                                    <div style="display: grid; grid-template-columns: 1fr; gap: 0.5rem;">
                                                        <div><strong>CREDITO:</strong>${simboloCredito}</div>
                                                        <div><strong>CONTADO:</strong>${simboloContado}</div>
                                                    </div>

                                                    <!-- TRANSPORTE NACIONAL (izquierda) -->
                                                    <h4 style="margin-top: 2rem; margin-bottom: 1rem;">TRANSPORTE NACIONAL</h4>
                                                    <table id="tabla-transporte-nac" style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
                                                        <thead>
                                                            <tr style="background-color: #f2f2f2;">
                                                                <th style="border: 1px solid #ddd; text-align: left; padding: 0.3rem;">Concepto</th>
                                                                <th style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">Moneda</th>
                                                                <th style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">Costo</th>
                                                                <th style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">Venta</th>
                                                                <th style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">Profit</th>
                                                                <th style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">Acepta</th>
                                                                <th style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">Afecto</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td style="border: 1px solid #ddd; padding: 0.3rem;"></td>
                                                                <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;"></td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;"></td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;"></td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;"></td>
                                                                <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;"></td>
                                                                <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>

                                                    <!-- CAMPOS TRANSPORTE (dinámicos) -->
                                                    <div id="campos-transporte-nac" style="margin-top: 0.8rem;"></div>

                                                    <!-- BOTONES TRANSPORTE NACIONAL -->
                                                    <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                                        <button type="button" onclick="abrirModalTransporteNac('crear')" 
                                                            style="padding: 0.3rem 0.6rem; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 8.5pt;">
                                                            🚚 Grabar Transporte
                                                        </button>
                                                        <button type="button" onclick="abrirModalTransporteNac('editar')" 
                                                            style="padding: 0.3rem 0.6rem; background-color: #ffc107; color: black; border: none; border-radius: 4px; cursor: pointer; font-size: 8.5pt;">
                                                            ✏️ Editar
                                                        </button>
                                                        <button type="button" onclick="eliminarTransporteNac()" 
                                                            style="padding: 0.3rem 0.6rem; background-color: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 8.5pt;">
                                                            🗑️ Eliminar
                                                        </button>
                                                    </div>

                                                    <!-- SEGURO (izquierda) -->
                                                    <h4 style="margin-top: 2rem; margin-bottom: 1rem;">SEGURO</h4>
                                                    <table style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
                                                        <thead>
                                                            <tr style="background-color: #f2f2f2;">
                                                                <th style="border: 1px solid #ddd; text-align: left; padding: 0.3rem;">Concepto</th>
                                                                <th style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">Moneda</th>
                                                                <th style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">Costo</th>
                                                                <th style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">Venta</th>
                                                                <th style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">Min.</th>
                                                                <th style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">V.Venta</th>
                                                                <th style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">Aplica</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td style="border: 1px solid #ddd; padding: 0.3rem;"></td>
                                                                <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;"></td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;"></td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;"></td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;"></td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;"></td>
                                                                <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <!-- BLOQUE VENTAS -->
                                                <div>
                                                    <h5 style="margin-bottom: 0.5rem;">Ventas</h5>
                                                    <table style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
                                                        <thead>
                                                            <tr style="background-color: #f2f2f2;">
                                                                <th style="border: 1px solid #ddd; text-align: left; padding: 0.3rem;">Concepto</th>
                                                                <th style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">Qty</th>
                                                                <th style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">Venta</th>
                                                                <th style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">Total</th>
                                                                <th style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">Aplica</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                    `;

                                    // Calcular total venta real (qty * tarifa)
                                    let totalVentaCalculado = 0;
                                    costos.forEach(c => {
                                        const qty = c.qty || 0;
                                        const tarifa = c.tarifa || 0;
                                        const total = qty * tarifa;
                                        totalVentaCalculado += total;
                                        html += `
                                                            <tr>
                                                                <td style="border: 1px solid #ddd; padding: 0.3rem;">${sanitizeText(c.concepto || '')}</td>
                                                                <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">${qty.toFixed(2)}</td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">${tarifa.toFixed(2)}</td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">${total.toFixed(2)}</td>
                                                                <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">${sanitizeText(c.aplica || '')}</td>
                                                            </tr>
                                        `;
                                    });

                                    // Calcular profit y profit %
                                    const totalProfit = totalVentaCalculado - totalCostoCalculado;
                                    const totalProfitPorcentaje = totalVentaCalculado > 0 ? ((totalProfit / totalVentaCalculado) * 100) : 0;

                                    html += `
                                                        </tbody>
                                                        <tfoot>
                                                            <tr style="font-weight: bold;">
                                                                <td colspan="3" style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">TOTAL VENTAS:</td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">${totalVentaCalculado.toLocaleString('es-CL', { minimumFractionDigits: 2 })}</td>
                                                                <td style="border: 1px solid #ddd;"></td>
                                                            </tr>
                                                            <tr style="font-weight: bold;">
                                                                <td colspan="3" style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">TOTAL PROFIT:</td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">${totalProfit.toLocaleString('es-CL', { minimumFractionDigits: 2 })}</td>
                                                                <td style="border: 1px solid #ddd;"></td>
                                                            </tr>
                                                            <tr style="font-weight: bold;">
                                                                <td colspan="3" style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">TOTAL PROFIT %:</td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">${totalProfitPorcentaje.toFixed(2)}%</td>
                                                                <td style="border: 1px solid #ddd;"></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>

                                                    <!-- GASTOS LOCALES EN DESTINO (VENTAS) -->
                                                    <h5 style="margin-top: 1.5rem; margin-bottom: 0.5rem;">Gastos Locales en Destino</h5>
                                                    <table style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
                                                        <thead>
                                                            <tr style="background-color: #f2f2f2;">
                                                                <th style="border: 1px solid #ddd; text-align: left; padding: 0.3rem;">Concepto</th>
                                                                <th style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">Moneda</th>
                                                                <th style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">Afecto</th>
                                                                <th style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">Monto</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                    `;

                                    // Filtrar y renderizar solo Gastos Locales de tipo "Ventas"
                                    let totalGastosVentasMonto = 0;
                                    const gastosVentas = gastos_locales.filter(g => (g.tipo || '').toUpperCase() === 'VENTAS');
                                    gastosVentas.forEach(g => {
                                        const monto = parseFloat(g.monto) || 0;
                                        totalGastosVentasMonto += monto;
                                        html += `
                                                            <tr>
                                                                <td style="border: 1px solid #ddd; padding: 0.3rem;">${sanitizeText(g.gasto || '')}</td>
                                                                <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">${sanitizeText(g.moneda || '')}</td>
                                                                <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">${sanitizeText(g.afecto || '')}</td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">${monto.toLocaleString('es-CL', { minimumFractionDigits: 2 })}</td>
                                                            </tr>
                                        `;
                                    });

                                    html += `
                                                        </tbody>
                                                        <tfoot>
                                                            <tr style="font-weight: bold;">
                                                                <td colspan="3" style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">TOTAL MONTO:</td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">${totalGastosVentasMonto.toLocaleString('es-CL', { minimumFractionDigits: 2 })}</td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>

                                                    <!-- GASTOS LOCALES EN DESTINO COSTO -->
                                                    <h5 style="margin-top: 1.5rem; margin-bottom: 0.5rem;">Gastos Locales en Destino Costo</h5>
                                                    <table style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
                                                        <thead>
                                                            <tr style="background-color: #f2f2f2;">
                                                                <th style="border: 1px solid #ddd; text-align: left; padding: 0.3rem;">Concepto</th>
                                                                <th style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">Moneda</th>
                                                                <th style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">Afecto</th>
                                                                <th style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">Monto</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                    `;

                                    // Filtrar y renderizar solo Gastos Locales de tipo "Costo"
                                    let totalGastosCostosMonto = 0;
                                    const gastosCostos = gastos_locales.filter(g => (g.tipo || '').toUpperCase() === 'COSTO');
                                    gastosCostos.forEach(g => {
                                        const monto = parseFloat(g.monto) || 0;
                                        totalGastosCostosMonto += monto;
                                        html += `
                                                            <tr>
                                                                <td style="border: 1px solid #ddd; padding: 0.3rem;">${sanitizeText(g.gasto || '')}</td>
                                                                <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">${sanitizeText(g.moneda || '')}</td>
                                                                <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">${sanitizeText(g.afecto || '')}</td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">${monto.toLocaleString('es-CL', { minimumFractionDigits: 2 })}</td>
                                                            </tr>
                                        `;
                                    });

                                    html += `
                                                        </tbody>
                                                        <tfoot>
                                                            <tr style="font-weight: bold;">
                                                                <td colspan="3" style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">TOTAL MONTO:</td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">${totalGastosCostosMonto.toLocaleString('es-CL', { minimumFractionDigits: 2 })}</td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>

                                                    <!-- TERCER BLOQUE: TOTAL GASTOS LOCALES MÁS PROFIT LOCAL -->
                                                    <h5 style="margin-top: 1.5rem; margin-bottom: 0.5rem;">Total Gastos Locales más Profit Local</h5>
                                                    <table style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
                                                        <thead>
                                                            <tr style="background-color: #f2f2f2;">
                                                                <th style="border: 1px solid #ddd; text-align: left; padding: 0.3rem;"></th>
                                                                <th style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">Moneda</th>
                                                                <th style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">Monto</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">Total Venta:</td>
                                                                <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">CLP</td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">${totalGastosVentasMonto.toLocaleString('es-CL', { minimumFractionDigits: 2 })}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">Total Costo:</td>
                                                                <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">CLP</td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">${totalGastosCostosMonto.toLocaleString('es-CL', { minimumFractionDigits: 2 })}</td>
                                                            </tr>
                                                            <tr style="font-weight: bold;">
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">Profit Local:</td>
                                                                <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;">CLP</td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">${(totalGastosVentasMonto - totalGastosCostosMonto).toLocaleString('es-CL', { minimumFractionDigits: 2 })}</td>
                                                            </tr>
                                                            <tr style="font-weight: bold;">
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">Profit %:</td>
                                                                <td style="border: 1px solid #ddd; text-align: center; padding: 0.3rem;"></td>
                                                                <td style="border: 1px solid #ddd; text-align: right; padding: 0.3rem;">
                                                                    ${(totalGastosVentasMonto > 0 
                                                                        ? ((totalGastosVentasMonto - totalGastosCostosMonto) / totalGastosVentasMonto * 100).toFixed(2) + '%' 
                                                                        : '0.00%')}
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>


                    <!-- NOTAS -->
                    <h4 style="margin-top: 2rem; margin-bottom: 1rem;">NOTAS A OPERACIONES</h4>
                    <div>${sanitizeText(s.notas_operaciones || '')}</div>
                    <h4 style="margin-top: 2rem; margin-bottom: 1rem;">NOTAS COMERCIALES</h4>
                    <div>${sanitizeText(s.notas_comerciales || '')}</div>
                </div>
            `;

            document.getElementById('route-order-content').innerHTML = html;
        }

        // --- Función para exportar a Excel ---
        function exportarRouteOrderAExcel() {
            if (!datosRouteOrder) {
                error('No hay datos para exportar.');
                return;
            }

            // Opcional: Mostrar indicador de carga
            // document.getElementById('route-order-content').innerHTML = '<p style="text-align: center;">Generando Excel...</p>';

            // Hacer una petición al backend para generar el archivo Excel
            fetch('/api/exportar_route_order_excel.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(datosRouteOrder)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                // Si la respuesta es correcta, debería ser un archivo para descargar
                return response.blob(); // Obtener el archivo como Blob
            })
            .then(blob => {
                // Crear un objeto URL para el blob
                const downloadUrl = window.URL.createObjectURL(blob);
                // Crear un enlace temporal para descargar el archivo
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = `Route_Order_${datosRouteOrder.servicio.concatenado || 'N/A'}.xlsx`; // Nombre del archivo
                document.body.appendChild(link); // Añadir al DOM
                link.click(); // Simular clic para descargar
                document.body.removeChild(link); // Limpiar
                window.URL.revokeObjectURL(downloadUrl); // Liberar el objeto URL
                exito('Archivo Excel descargado correctamente.');
            })
            .catch(err => {
                console.error('Error al exportar a Excel:', err);
                error('No se pudo generar el archivo Excel.');
                // Opcional: Volver a renderizar el contenido original si falla la exportación
                // if (datosRouteOrder) renderizarRouteOrder(datosRouteOrder);
            });
        }
        document.getElementById('cliente_nombre').addEventListener('input', async (e) => {

            const q = e.target.value;

            if (q.length < 3) return;

            const res = await fetch(`../api/buscar_clientes.php?q=${q}`);
            const text = await res.text();

            let data;

            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('❌ RESPUESTA NO JSON:', text);
                throw new Error('El servidor devolvió HTML en vez de JSON');
            }

            // mostrar dropdown tipo CRM
        });

        document.getElementById('razon_social').addEventListener('input', function () {

            // 🔥 Rompe vínculo con cliente existente
            document.getElementById('razon_social_hidden').value = '';

            // Limpia datos auto cargados
            document.getElementById('rut_empresa').value = '';
            document.getElementById('pais').value = '';
            document.getElementById('direccion').value = '';
            document.getElementById('fono_empresa').value = '';

        });

        function cargarContactoPrimario(rut) {
            return fetch(`/api/get_contactos.php?rut=${encodeURIComponent(rut)}`)
                .then(r => r.json())
                .then(data => {
                    const primario = (data.contactos || []).find(ct => ct.primario === 'S');

                    document.getElementById('fono_empresa').value = primario?.fono || '';
                    document.getElementById('contacto').value = primario?.nom_contacto || '';
                    document.getElementById('email').value = primario?.email || '';
                });
        }
        function activarModoManual() {

            document.getElementById('rut_empresa').readOnly = false;
            document.getElementById('pais').readOnly = false;
            document.getElementById('direccion').readOnly = false;
            document.getElementById('fono_empresa').readOnly = false;
            document.getElementById('contacto').readOnly = false;
            document.getElementById('email').readOnly = false;

            document.getElementById('rut_empresa').style.background = '#fff';
            document.getElementById('direccion').style.background = '#fff';

        }

        function validarRUT(rut) {
            rut = rut.replace(/\./g, '').replace('-', '');

            if (rut.length < 2) return false;

            const cuerpo = rut.slice(0, -1);
            let dv = rut.slice(-1).toUpperCase();

            let suma = 0;
            let multiplo = 2;

            for (let i = cuerpo.length - 1; i >= 0; i--) {
                suma += multiplo * parseInt(cuerpo.charAt(i));
                multiplo = multiplo < 7 ? multiplo + 1 : 2;
            }

            const dvEsperado = 11 - (suma % 11);

            let dvFinal =
                dvEsperado === 11 ? '0' :
                dvEsperado === 10 ? 'K' :
                dvEsperado.toString();

            return dvFinal === dv;
        }

        async function detectarDuplicado(nombre) {

            const res = await fetch(`/api/buscar_cliente_similar.php?term=${encodeURIComponent(nombre)}`);
            const data = await res.json();

            if (data.length > 0) {

                advertencia('⚠️ Cliente similar encontrado');

                console.log('Coincidencias:', data);

                return true;
            }

            return false;
        }

        function setClienteEnFormulario(c) {

            document.getElementById('rut_empresa').value = c.rut || '';
            document.getElementById('pais').value = c.pais || '';
            document.getElementById('direccion').value = c.direccion || '';

            document.getElementById('nombre').value = c.nombre_comercial || '';
            document.getElementById('id_comercial').value = c.id_comercial || '';

        }

       function limpiarCamposProspecto() {

            console.log('🧹 Limpiando formulario (modo seguro)');

            const campos = [
                'booking',
                'notas_comerciales',
                'notas_operaciones'
            ];

            campos.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.value = '';
                } else {
                    console.warn(`⚠️ Campo no encontrado: ${id}`);
                }
            });
        }

        async function fetchJSON(url, options = {}) {
            const res = await fetch(url, options);
            const text = await res.text();

            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('❌ ERROR FETCH:', url);
                console.error('❌ RESPUESTA:', text);
                throw new Error('Respuesta inválida del servidor');
            }
        }
        // Exponer funciones globales
        window.guardarServicio = guardarServicio;
        window.abrirModalServicio = abrirModalServicio;
        window.eliminarServicio = eliminarServicio;
    </script>
</form>