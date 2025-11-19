<?php
require_once __DIR__ . '/../includes/auth_check.php';
?>
<!-- Mini consola de depuración -->
<div id="debug-trace" style="margin: 1rem; padding: 0.5rem; background: #f0f8ff; border: 1px solid #87ceeb; border-radius: 4px; font-size: 0.85rem; display: none;"></div>

<!-- Búsqueda inteligente -->
<div style="height: 4rem;"></div>
<div style="margin: 1rem 0;">
    <label><i class="fas fa-search"></i> Búsqueda Inteligente</label>
    <input type="text" id="busqueda-inteligente" placeholder="Buscar por Concatenado, Razón Social, RUT..." style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px;" />
    <div id="resultados-busqueda" style="position: absolute; background: white; border: 1px solid #ddd; border-radius: 8px; max-height: 300px; overflow-y: auto; width: 95%; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.1); display: none;"></div>
</div>

<!-- ==============================================   FORM  ============================================== -->
<form method="POST" id="form-prospecto" action="">
    <input type="hidden" name="id_ppl" id="id_ppl" />
    <input type="hidden" name="id_prospect" id="id_prospect" />
    <input type="hidden" name="razon_social" />
    <input type="hidden" name="notas_comerciales" id="notas_comerciales" />
    <input type="hidden" name="notas_operaciones" id="notas_operaciones" />
    <input type="hidden" name="total_venta_prospecto" id="total_venta_prospecto" value="0.00" />

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
            <select name="razon_social_select" id="razon_social_select" required style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;">
                <option value="">Seleccionar cliente</option>
            </select>
            <label>RUT Empresa *</label>
            <input type="text" name="rut_empresa" id="rut_empresa" readonly style="width: 100%; padding: 0.5rem; background: #f8f9fa; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" />
            <label>Teléfono</label>
            <input type="tel" name="fono_empresa" id="fono_empresa" readonly style="width: 100%; padding: 0.5rem; background: #f8f9fa; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" />
            <label>Fecha</label>
            <input type="date" name="fecha_alta" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" value="<?= date('Y-m-d') ?>" />
        </div>
        <!-- Fila 2 -->
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <label>País</label>
            <input type="text" name="pais" id="pais" readonly style="width: 100%; padding: 0.5rem; background: #f8f9fa; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" />
            <label>Dirección</label>
            <input type="text" name="direccion" id="direccion" readonly style="grid-column: span 3; width: 100%; padding: 0.5rem; background: #f8f9fa; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" />
            <label>Estado</label>
            <select name="estado" id="estado" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;">
                <option value="Pendiente">Pendiente</option>
                <option value="Enviado">Enviado</option>
                <option value="Devuelto_pendiente">Devuelto_pendiente</option>
                <option value="CerradoOK">CerradoOK</option>
                <option value="Rechazado">Rechazado</option>
            </select>
        </div>
        <!-- Fila 3: Operación y Tipo Operación -->
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
        <!-- Fila 4 -->
        <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 1rem; margin-bottom: 1.2rem; align-items: center;">
            <label>Comercial Asignado</label>
            <input type="text" name="nombre" id="nombre" readonly style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; background: #f8f9fa; box-sizing: border-box;" />
        </div>
    </div>

    <!-- ========== SERVICIOS ASOCIADOS ========== -->
    <div class="card">
        <h3><i class="fas fa-truck"></i> Servicios Asociados</h3>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem;">
            <div style="display: flex; gap: 0.8rem;">
                <button type="button" class="btn-comment" onclick="abrirModalComercial()"><i class="fas fa-comments"></i> Comerciales</button>
                <button type="button" class="btn-comment" onclick="abrirModalOperaciones()"><i class="fas fa-clipboard-list"></i> Operaciones</button>
            </div>
            <div style="display: flex; gap: 0.8rem;">
                <button type="button" class="btn-add" id="btn-agregar-servicio" style="display: none;">
                    <i class="fas fa-plus"></i> Agregar Servicio
                </button>
                <button type="button" class="btn-primary" id="btn-save-all">Grabar Todo</button>
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
    <div id="modal-comercial" class="modal" style="display:none;">
        <div class="modal-content" style="max-width: 650px; margin: 2rem auto;">
            <h3><i class="fas fa-comments"></i> Notas Comerciales</h3>
            <span class="close" onclick="cerrarModalComercial()">&times;</span>
            <textarea id="notas_comerciales_input" rows="6" placeholder="..."></textarea>
            <div class="modal-footer">
                <button type="button" onclick="cerrarModalComercial()">Cerrar</button>
                <button type="button" onclick="guardarNotasComerciales()">Guardar</button>
            </div>
        </div>
    </div>

    <!-- Modal Operaciones -->
    <div id="modal-operaciones" class="modal" style="display:none;">
        <div class="modal-content" style="max-width: 650px; margin: 2rem auto;">
            <h3><i class="fas fa-clipboard-list"></i> Notas Operaciones</h3>
            <span class="close" onclick="cerrarModalOperaciones()">&times;</span>
            <textarea id="notas_operaciones_input" rows="6" placeholder="..."></textarea>
            <div class="modal-footer">
                <button type="button" onclick="cerrarModalOperaciones()">Cerrar</button>
                <button type="button" onclick="guardarNotasOperaciones()">Guardar</button>
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
            <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 0.8rem; margin-top: 1.2rem; align-items: center;">
                <!-- Fila 1 -->
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
                <!-- Fila 2 -->
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
                <!-- Fila 3 -->
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
                <!-- Fila 4 -->
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
                <label>Agente</label>
                <select id="serv_agente" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                    <option value="">Seleccionar</option>
                </select>
                <!-- Fila 5 -->
                <label>Transportador</label>
                <input type="text" id="serv_transportador" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label>Incoterm</label>
                <input type="text" id="serv_incoterm" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
                <label>Ref. Cliente</label>
                <input type="text" id="serv_ref_cliente" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;" />
            </div>
            <div class="modal-footer" style="text-align: right; margin-top: 1.5rem; gap: 0.8rem; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <button type="button" class="btn-comment" id="btn-cubicador" onclick="abrirSubmodalCubicador()">
                        <i class="fas fa-calculator"></i> Cubicador
                    </button>
                    <button type="button" class="btn-comment" id="btn-costos-servicio-dentro"><i class="fas fa-calculator"></i> Costos - Ventas</button>
                    <button type="button" class="btn-comment" id="btn-gastos-locales-dentro"><i class="fas fa-file-invoice-dollar"></i> Gastos Locales</button>
                </div>
                <div style="display: flex; gap: 0.8rem;">
                    <button type="button" class="btn-secondary" onclick="cerrarModalServicioConConfirmacion()">Volver</button>
                    <button type="button" class="btn-add" id="btn-guardar-servicio-modal">Agregar Servicio</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Submodal: Costos/Ventas/Gastos -->
    <div id="submodal-costos" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:11000;">
        <div class="modal-content" style="max-width: 1400px; width: 95%; margin: 1.5rem auto; background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
            <h3><i class="fas fa-calculator"></i> Costos, Ventas y Gastos</h3>
            <span class="close" onclick="cerrarSubmodalCostos()" style="cursor:pointer; float:right; font-size:1.8rem; margin-top:-5px;">&times;</span>
            <div style="display: grid; grid-template-columns: repeat(10, 1fr); gap: 0.7rem; margin: 1.2rem 0; align-items: center; background: #f8f9fa; padding: 1rem; border-radius: 6px;">
                <select id="costo_concepto" style="grid-column: span 2; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; width: 100%;">
                        <option value="">Seleccionar concepto</option>
                </select>    
                <input type="text" id="costo_moneda" readonly style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; background: #e9ecef; text-align: center; width: 80px;" />
                <input type="number" id="costo_qty" step="0.01" min="0" placeholder="Qty" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; text-align: right; width: 80px;" />
                <input type="number" id="costo_costo" step="0.01" min="0" placeholder="Costo" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; background-color: #fff9db; text-align: right; width: 80px;" />
                <input type="text" id="costo_total_costo" readonly placeholder="Total Costo" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; background-color: #fff9db; text-align: right; width: 80px;" />
                <input type="number" id="costo_tarifa" step="0.01" min="0" placeholder="Tarifa" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; background-color: #e6f7ff; text-align: right; width: 80px;" />
                <input type="text" id="costo_total_tarifa" readonly placeholder="Total Tarifa" style="grid-column: span 1; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; background-color: #e6f7ff; text-align: right; width: 80px;" />
                <select id="costo_aplica" style="grid-column: span 2; padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.95rem; width: 100%;">
                    <option value="">Seleccionar aplica</option>
                </select>
                <button type="button" onclick="guardarCosto()" style="grid-column: span 1; background: #009966; color: white; border: none; padding: 0.6rem; border-radius: 6px; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 0.3rem;">
                    <i class="fas fa-plus"></i> Agregar
                </button>
            </div>
            <div class="table-container" style="margin-top: 1.2rem; overflow-x: auto;">
                <table id="tabla-costos" style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
                    <thead>
                        <tr style="background: #f1f3f5;">
                            <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; font-size: 0.92rem;">Concepto</th>
                            <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; font-size: 0.92rem;">Moneda</th>
                            <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd; font-size: 0.92rem;">Qty</th>
                            <th style="padding: 0.6rem; text-align: center; border: 1px solid #2d2a2aff; background-color: #2d2a2aff; font-size: 0.92rem;">Costo</th>
                            <th style="padding: 0.6rem; text-align: center; border: 1px solid #2d2a2aff; background-color: #2d2a2aff; font-size: 0.92rem;">Total Costo</th>
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
                            <th style="padding: 0.6rem; text-align: center; border: 1px solid #ddd;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="gastos-locales-body"></tbody>
                </table>
            </div>
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
            <div style="text-align: right; margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 0.8rem;">
                <button type="button" onclick="cerrarSubmodalGastosLocales()" style="background: #6c757d; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; font-size: 0.95rem;">
                    <i class="fas fa-arrow-left"></i> Volver
                </button>
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
        window.editarServicio = editarServicio;

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

        function exito(msg) { mostrarNotificacion(msg, 'exito'); }
        function error(msg) { mostrarNotificacion(msg, 'error'); }
        function advertencia(msg) { mostrarNotificacion(msg, 'advertencia'); }

        // ===================================================================
        // === FUNCIONES DE CLIENTE Y PROSPECTO ===
        // ===================================================================
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

        document.getElementById('razon_social_select')?.addEventListener('change', function() {
            const rut = this.value;
            if (!rut) {
                ['rut_empresa', 'fono_empresa', 'pais', 'direccion', 'nombre'].forEach(id => {
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
                        document.getElementById('rut_empresa').value = c.rut || '';
                        document.getElementById('pais').value = c.pais || '';
                        document.getElementById('direccion').value = c.direccion || '';
                        document.getElementById('nombre').value = c.nombre_comercial || '';
                        document.querySelector('input[name="razon_social"]').value = c.razon_social || '';
                        fetch(`/api/get_contactos.php?rut=${encodeURIComponent(rut)}`)
                            .then(r2 => r2.json())
                            .then(data2 => {
                                const primario = (data2.contactos || []).find(ct => ct.primario === 'S');
                                document.getElementById('fono_empresa').value = primario?.fono || '';
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
                const c = parseFloat(s.costo) || 0;
                const v = parseFloat(s.venta) || 0;
                const gc = parseFloat(s.costogastoslocalesdestino) || 0;
                const gv = parseFloat(s.ventasgastoslocalesdestino) || 0;
                tc += c; tv += v; tgc += gc; tgv += gv;

                // ✅ Estado de costos
                let estadoCostos = s.estado_costos || 'pendiente';
                if (!s.costos || s.costos.length === 0) {
                    if (s.id_srvc && !s.id_srvc.startsWith('TEMP_')) {
                        estadoCostos = 'pendiente'; // Servicio guardado sin costos
                    } else {
                        estadoCostos = 'temporal'; // Servicio no guardado
                    }
                }

                // ✅ Ícono
                let iconoCostos = '';
                if (estadoCostos === 'pendiente') {
                    iconoCostos = '<i class="fas fa-paper-plane" style="color: #0066cc; cursor: pointer;" title="Notificar a Pricing"></i>';
                } else if (estadoCostos === 'solicitado') {
                    iconoCostos = '<i class="fas fa-envelope" style="color: #ff9900;" title="Esperando costos de Pricing"></i>';
                } else if (estadoCostos === 'completado') {
                    iconoCostos = '<i class="fas fa-envelope-open" style="color: #009966;" title="Costos listos para revisión"></i>';
                } else if (estadoCostos === 'revisado') {
                    iconoCostos = '<i class="fas fa-check-circle" style="color: #006644;" title="Aprobado por Comercial"></i>';
                }

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${s.servicio || ''}</td>
                    <td>${s.trafico || ''}</td>
                    <td>${s.moneda || 'USD'}</td>
                    <td>${s.bultos || ''}</td>
                    <td>${s.peso || ''}</td>
                    <td>${s.volumen || ''}</td>
                    <td>${c.toFixed(2)}</td>
                    <td>${v.toFixed(2)}</td>
                    <td>${gc.toFixed(2)}</td>
                    <td>${gv.toFixed(2)}</td>
                    <td>
                        ${iconoCostos}
                        <button type="button" class="btn-edit-servicio" data-index="${index}">✏️</button>
                        <button type="button" class="btn-delete-servicio" data-index="${index}">🗑️</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('total-costo').textContent = tc.toFixed(2);
            document.getElementById('total-venta').textContent = tv.toFixed(2);
            document.getElementById('total-costogasto').textContent = tgc.toFixed(2);
            document.getElementById('total-ventagasto').textContent = tgv.toFixed(2);

            // ✅ Listeners para ícono de notificación
            document.querySelectorAll('#tabla-servicios i.fa-paper-plane').forEach(icon => {
                icon.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const index = Array.from(row.parentNode.children).indexOf(row);
                    const servicio = servicios[index];

                    if (!servicio.id_srvc || servicio.id_srvc.startsWith('TEMP_')) {
                        alert('Debe guardar el prospecto primero antes de solicitar costos.');
                        return;
                    }

                    if (confirm('¿Solicitar costos al equipo de Pricing?')) {
                        fetch('/api/notificar_costos.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                id_srvc: servicio.id_srvc,
                                estado: 'solicitado',
                                usuario_id: '<?php echo $_SESSION["user_id"] ?? 0; ?>',
                                rol: '<?php echo $_SESSION["rol"] ?? "comercial"; ?>'
                            })
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                servicios[index].estado_costos = 'solicitado';
                                actualizarTabla();
                                exito('Notificación enviada a Pricing');
                            } else {
                                error('Error: ' + (data.message || 'Intente nuevamente'));
                            }
                        })
                        .catch(() => error('Error de conexión'));
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
                // === Validación antes de r.json()
                .then(r => {
                    if (!r.ok) {
                        throw new Error(`HTTP ${r.status}`);
                    }
                    return r.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('❌ Respuesta no es JSON:', text);
                            throw new Error('La API devolvió HTML en lugar de JSON');
                        }
                    });
                })
                .then(data => {
                    if (!data.success || !data.prospecto) return error('Prospecto no encontrado');
                    const p = data.prospecto;

                    // === Actualizar el select de Razón Social ===
                    const razonSelect = document.getElementById('razon_social_select');
                    if (razonSelect) {
                        let optionFound = false;
                        for (let i = 0; i < razonSelect.options.length; i++) {
                            const opt = razonSelect.options[i];
                            if (opt.value === p.rut_empresa) {
                                opt.selected = true;
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

                    // === Cargar campos del formulario ===
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

                    // === Notas comerciales y operaciones ===
                    const setNota = (name, val) => {
                        const inp = document.getElementById(name);
                        if (inp) inp.value = val || '';
                        const ta = document.getElementById(`${name}_input`);
                        if (ta) ta.value = val || '';
                    };
                    setNota('notas_comerciales', p.notas_comerciales);
                    setNota('notas_operaciones', p.notas_operaciones);

                    // === Cargar servicios (si existen) ===
                    servicios = (data.servicios || []).map(s => ({
                        ...s,
                        costo: parseFloat(s.costo) || 0,
                        venta: parseFloat(s.venta) || 0,
                        costogastoslocalesdestino: parseFloat(s.costogastoslocalesdestino) || 0,
                        ventasgastoslocalesdestino: parseFloat(s.ventasgastoslocalesdestino) || 0
                    }));
                    tieneServiciosIniciales = servicios.length > 0;
                    actualizarTabla();

                    // ✅✅✅ ASIGNACIONES CLAVE PARA EL BOTÓN "AGREGAR SERVICIO" ✅✅✅
                    const idPplInput = document.getElementById('id_ppl');
                    const concatenadoInput = document.getElementById('concatenado');
                    if (idPplInput) idPplInput.value = p.id_ppl || '';
                    if (concatenadoInput) concatenadoInput.value = p.concatenado || '';

                    // === Habilitar campos y botones ===
                    const inputs = document.querySelectorAll('input:not([type="hidden"]):not([name="concatenado"])');
                    const selects = document.querySelectorAll('select');
                    inputs.forEach(i => { i.readOnly = false; i.style.backgroundColor = ''; });
                    selects.forEach(s => s.disabled = false);
                    // Mostrar botón de agregar servicio si el prospecto ya existe
                    const btnAgregar = document.getElementById('btn-agregar-servicio');
                    if (btnAgregar && p.id_ppl && p.id_ppl > 0) {
                        btnAgregar.style.display = 'inline-flex'; // o 'inline-block'
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

        function abrirModalServicio(index = null) {
            const idPpl = document.getElementById('id_ppl')?.value;
            const concatenado = document.getElementById('concatenado')?.value;
            if (!idPpl || idPpl === '0' || !concatenado) {
                error('Guarde el prospecto primero antes de agregar servicios.');
                return;
            }

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

            // Cargar datos del modal (commodity, medios, etc.)
            cargarDatosModalServicio(() => {
                if (index !== null) {
                    // Editar servicio existente
                    servicioEnEdicion = index;
                    const s = servicios[index];
                    costosServicio = Array.isArray(s.costos) ? [...s.costos] : [];
                    gastosLocales = Array.isArray(s.gastos_locales) ? [...s.gastos_locales] : [];

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
                    const medio = this.value;
                    if (medio) {
                        cargarLugaresPorMedio(medio); // Sin origen → cargar todos
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

        function guardarServicio() {
            console.log('🔍 [SERVICIO] Iniciando guardarServicio');
            const servicio = document.getElementById('serv_servicio').value.trim();
            if (!servicio) {
                console.log('⚠️ [SERVICIO] Validación fallida: Servicio es obligatorio');
                error('Servicio es obligatorio');
                return;
            }
            const origen = document.getElementById('serv_origen').value;
            const destino = document.getElementById('serv_destino').value;
            if (origen && destino && origen === destino) {
                console.log('⚠️ [SERVICIO] Validación fallida: Origen y Destino son iguales');
                error('Origen y Destino no pueden ser el mismo lugar');
                return;
            }

            // ✅ Obtener el estado actual del prospecto
            const estadoProspecto = document.getElementById('estado')?.value || 'Pendiente';
            const rutCliente = document.getElementById('rut_empresa')?.value.trim();
            const totalVentaServicio = costosServicio.reduce((sum, c) => sum + (c.total_tarifa || 0), 0);

            // ✅ Validar costos SOLO si el estado es "Enviado" o "CerradoOK"
            if (estadoProspecto === 'Enviado' || estadoProspecto === 'CerradoOK') {
                if (costosServicio.length === 0) {
                    error('Debe agregar al menos un costo al servicio antes de enviarlo.');
                    return;
                }
            }

            // ✅ Validar crédito solo si hay RUT y monto > 0
            if (rutCliente && totalVentaServicio > 0) {
                fetch(`/api/get_saldo_credito.php?rut=${encodeURIComponent(rutCliente)}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.error) {
                            error(data.error);
                            return;
                        }
                        if (totalVentaServicio > data.saldo_credito) {
                            error(`Sobregiro detectado: El servicio supera el saldo de crédito disponible (${data.saldo_credito}). 
                                Solicite un aumento de límite en Ficha Cliente.`);
                            return;
                        }
                        ejecutarGuardarServicio();
                    })
                    .catch(err => {
                        console.error('Error al validar crédito:', err);
                        error('No se pudo verificar la línea de crédito.');
                    });
            } else {
                // ✅ Guardar sin validación de crédito ni costos (si estado es Pendiente)
                ejecutarGuardarServicio();
            }
        }

        // === Función que realiza el guardado real ===
        function ejecutarGuardarServicio() {
            const servicio = document.getElementById('serv_servicio').value.trim();
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
                desconsolidac: '0',
                aol: document.getElementById('serv_aol').value,
                aod: document.getElementById('serv_aod').value,
                agente: document.getElementById('serv_agente').value,
                transportador: document.getElementById('serv_transportador').value,
                incoterm: document.getElementById('serv_incoterm').value,
                ref_cliente: document.getElementById('serv_ref_cliente').value,
                costo: costosServicio.reduce((sum, c) => sum + (c.total_costo || 0), 0),
                venta: costosServicio.reduce((sum, c) => sum + (c.total_tarifa || 0), 0),
                costogastoslocalesdestino: gastosLocales.filter(g => g.tipo === 'Costo').reduce((sum, g) => sum + (g.monto || 0), 0),
                ventasgastoslocalesdestino: gastosLocales.filter(g => g.tipo === 'Ventas').reduce((sum, g) => sum + (g.monto || 0), 0),
                costos: [...costosServicio],
                gastos_locales: [...gastosLocales],
                // ✅ Estado de costos: "pendiente" si no hay costos
                estado_costos: costosServicio.length > 0 ? 'completado' : 'pendiente'
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

        // --- Submodales ---
        function abrirSubmodalCostos() {
            const rolUsuario = '<?php echo $_SESSION["rol"] ?? "comercial"; ?>';
            const esPricing = (rolUsuario === 'pricing' || rolUsuario === 'admin');

            // Deshabilitar edición si no es Pricing
            document.getElementById('costo_qty').disabled = !esPricing;
            document.getElementById('costo_costo').disabled = !esPricing;
            document.getElementById('costo_tarifa').disabled = !esPricing;
            document.getElementById('costo_concepto').disabled = !esPricing;
            document.getElementById('costo_aplica').disabled = !esPricing;

            // Ocultar botón "Agregar" si no es Pricing
            const btnAgregar = document.querySelector('#submodal-costos button[onclick="guardarCosto()"]');
            if (btnAgregar) {
                btnAgregar.style.display = esPricing ? 'flex' : 'none';
            }

            // Continúa proceso normal
            if (document.getElementById('modal-servicio').style.display === 'none') return error('Abra primero el modal de Servicio');
            if (servicioEnEdicion !== null) {
                costosServicio = Array.isArray(servicios[servicioEnEdicion].costos) ? [...servicios[servicioEnEdicion].costos] : [];
            }
            document.getElementById('costo_moneda').value = document.getElementById('serv_moneda')?.value || 'USD';
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
            actualizarTablaCostos();
            document.getElementById('submodal-costos').style.display = 'block';
        }

        function abrirSubmodalGastosLocales() {
            if (document.getElementById('modal-servicio').style.display === 'none') return error('Abra primero el modal de Servicio');
            if (servicioEnEdicion !== null) {
                gastosLocales = Array.isArray(servicios[servicioEnEdicion].gastos_locales) ? [...servicios[servicioEnEdicion].gastos_locales] : [];
            }
            const tipo = document.getElementById('gasto_tipo')?.value;
            if (tipo) {
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
            }
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

        function guardarCosto() {
            const concepto = document.getElementById('costo_concepto').value;
            const aplica = document.getElementById('costo_aplica').value;
            const qty = parseFloat(document.getElementById('costo_qty').value) || 0;
            const costo = parseFloat(document.getElementById('costo_costo').value) || 0;
            const tarifa = parseFloat(document.getElementById('costo_tarifa').value) || 0;
            const moneda = document.getElementById('costo_moneda').value || 'CLP';
            if (!concepto || !aplica) return error('Concepto y Aplica son obligatorios');
            const nuevo = { concepto, moneda, qty, costo, total_costo: qty * costo, tarifa, total_tarifa: qty * tarifa, aplica };
            if (window.indiceCostoEdicion !== undefined) {
                costosServicio[window.indiceCostoEdicion] = nuevo;
                delete window.indiceCostoEdicion;
            } else {
                costosServicio.push(nuevo);
            }
            actualizarTablaCostos();
            ['costo_concepto', 'costo_qty', 'costo_costo', 'costo_tarifa', 'costo_aplica'].forEach(id => {
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
                const tcosto = qty * costo;
                const ttarifa = qty * tarifa;
                tc += tcosto; tt += ttarifa;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${c.concepto}</td>
                    <td>${c.moneda}</td>
                    <td style="text-align: right;">${qty.toFixed(2)}</td>
                    <td style="text-align: right; background-color: #fff9db;">${costo.toFixed(2)}</td>
                    <td style="text-align: right; background-color: #fff9db;">${tcosto.toFixed(2)}</td>
                    <td style="text-align: right; background-color: #e6f7ff;">${tarifa.toFixed(2)}</td>
                    <td style="text-align: right; background-color: #e6f7ff;">${ttarifa.toFixed(2)}</td>
                    <td>${c.aplica}</td>
                    <td>
                        <button type="button" onclick="editarCosto(${i})">✏️</button>
                        <button type="button" onclick="eliminarCosto(${i})">🗑️</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            document.getElementById('total-costo-costos').textContent = tc.toFixed(2);
            document.getElementById('total-tarifa-costos').textContent = tt.toFixed(2);
        }

        function editarCosto(i) {
            const c = costosServicio[i];
            if (!c) return;
            document.getElementById('costo_concepto').value = c.concepto || '';
            document.getElementById('costo_qty').value = c.qty || '';
            document.getElementById('costo_costo').value = c.costo || '';
            document.getElementById('costo_tarifa').value = c.tarifa || '';
            document.getElementById('costo_aplica').value = c.aplica || '';
            document.getElementById('costo_total_costo').value = (parseFloat(c.qty || 0) * parseFloat(c.costo || 0)).toFixed(2);
            document.getElementById('costo_total_tarifa').value = (parseFloat(c.qty || 0) * parseFloat(c.tarifa || 0)).toFixed(2);
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

        function actualizarTablaGastosLocales() {
            const tbody = document.getElementById('gastos-locales-body');
            if (!tbody) return;
            tbody.innerHTML = '';
            let tv = 0, tc = 0;
            gastosLocales.forEach((g, i) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${g.tipo}</td>
                    <td>${g.gasto}</td>
                    <td>${g.moneda}</td>
                    <td style="text-align:right;">${g.monto.toFixed(2)}</td>
                    <td>${g.afecto}</td>
                    <td style="text-align:right;">${g.iva.toFixed(2)}</td>
                    <td><button type="button" onclick="eliminarGastoLocal(${i})">🗑️</button></td>
                `;
                tbody.appendChild(tr);
                if (g.tipo === 'Ventas') tv += g.monto;
                if (g.tipo === 'Costo') tc += g.monto;
            });
            document.getElementById('total-venta-gastos').textContent = tv.toFixed(2);
            document.getElementById('total-costo-gastos').textContent = tc.toFixed(2);
            document.getElementById('profit-local').textContent = (tv - tc).toFixed(2);
            const pct = tv > 0 ? ((tv - tc) / tv * 100) : 0;
            document.getElementById('profit-porcentaje').textContent = pct.toFixed(2) + ' %';
        }

        function eliminarGastoLocal(i) {
            if (confirm('¿Eliminar este gasto?')) {
                gastosLocales.splice(i, 1);
                actualizarTablaGastosLocales();
                exito('Gasto eliminado');
            }
        }

        function cerrarSubmodalGastosLocales() {
            if (servicioEnEdicion !== null) {
                servicios[servicioEnEdicion].gastos_locales = [...gastosLocales];
            }
            document.getElementById('submodal-gastos-locales').style.display = 'none';
        }

        // === FUNCIONES DE SERVICIOS ===
        function editarServicio(index) {
            if (index < 0 || index >= servicios.length) return error('Índice inválido');
            abrirModalServicio(index);
        }

        function eliminarServicio(index) {
            if (index < 0 || index >= servicios.length) return;
            const s = servicios[index];
            if ((s.costos && s.costos.length > 0) || (s.gastos_locales && s.gastos_locales.length > 0)) {
                return error('No se puede eliminar: tiene costos o gastos asociados.');
            }
            if (confirm('¿Eliminar este servicio?')) {
                servicios.splice(index, 1);
                actualizarTabla();
                exito('Servicio eliminado');
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
                    '⚠️ ATENCIÓN:\n\nEstá a punto de reiniciar el formulario.\n' +
                    'Todos los datos no guardados (prospecto, servicios, notas) se perderán.\n\n' +
                    '¿Desea continuar?'
                );
                if (!confirmar) {
                    advertencia('Reinicio cancelado por el usuario');
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

            // Limpiar servicios
            servicios = [];
            actualizarTabla();

            // Resetear concatenado
            document.getElementById('concatenado').value = '';

            // Ocultar botón de agregar servicio
            const btnAgregar = document.getElementById('btn-agregar-servicio');
            if (btnAgregar) btnAgregar.style.display = 'none';

            exito('Formulario reiniciado correctamente');
        }

        // ===================================================================
        // === 7. INICIALIZACIÓN ===
        // ===================================================================
        document.addEventListener('DOMContentLoaded', () => {
            cargarPaises();
            cargarOperacionesYTipos();
            cargarClientesEnSelect();

            const params = new URLSearchParams(window.location.search);
            const msg = params.get('exito');
            if (msg) {
                exito(decodeURIComponent(msg));
                history.replaceState({}, document.title, window.location.pathname + '?page=prospectos');
            }

            ['operacion', 'tipo_oper'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('change', calcularConcatenado);
            });

            // === BOTÓN: Agregar Servicio ===
            const btnAgregarServicio = document.getElementById('btn-agregar-servicio');
            if (btnAgregarServicio) {
                btnAgregarServicio.addEventListener('click', function() {
                    const idPplInput = document.getElementById('id_ppl');
                    const concatenadoInput = document.getElementById('concatenado');

                    if (!idPplInput || !concatenadoInput) {
                        console.error('❌ [Agregar Servicio] Campos ocultos no encontrados en el DOM');
                        error('Error interno: campos del formulario no disponibles');
                        return;
                    }

                    const idPpl = idPplInput.value.trim();
                    const concatenado = concatenadoInput.value.trim();

                    // Validar que id_ppl sea un número entero > 0
                    const idPplNum = parseInt(idPpl, 10);
                    const idValido = !isNaN(idPplNum) && idPplNum > 0;
                    const concatValido = concatenado.length > 0;

                    console.log('🔍 [Agregar Servicio] Valores actuales:', { idPpl, concatenado, idValido, concatValido });

                    if (!idValido || !concatValido) {
                        error('Debe seleccionar un prospecto válido antes de agregar servicios.');
                        return;
                    }

                    abrirModalServicio();
                });
            }

            const btnGuardarModal = document.getElementById('btn-guardar-servicio-modal');
            if (btnGuardarModal) {
                btnGuardarModal.addEventListener('click', guardarServicio);
            }

            // === BOTÓN: Grabar Todo ===
            const btnGrabarTodo = document.getElementById('btn-save-all');
            if (btnGrabarTodo) {
                btnGrabarTodo.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('🔍 [GRABAR TODO] Iniciando validación...');

                    const rut = document.getElementById('rut_empresa')?.value.trim();
                    const razonSelect = document.getElementById('razon_social_select');
                    const razon = razonSelect?.selectedOptions[0]?.textContent.trim();
                    const operacion = document.getElementById('operacion')?.value;
                    const tipoOper = document.getElementById('tipo_oper')?.value;
                    const concatenado = document.getElementById('concatenado')?.value;
                    const estado = document.getElementById('estado')?.value || 'Pendiente';

                    console.log('📋 [GRABAR TODO] Valores:', { rut, razon, operacion, tipoOper, concatenado, estado });

                    if (!rut || !razon) {
                        console.log('❌ [GRABAR TODO] Error: RUT o Razón Social vacíos');
                        error('RUT y Razón Social son obligatorios');
                        return;
                    }
                    if (!operacion || !tipoOper) {
                        console.log('❌ [GRABAR TODO] Error: Operación o Tipo Operación vacíos');
                        error('Operación y Tipo Operación son obligatorios');
                        return;
                    }
                    if (!concatenado) {
                        console.log('❌ [GRABAR TODO] Error: Concatenado vacío');
                        error('El campo Concatenado no puede estar vacío');
                        return;
                    }

                    const rutLimpio = rut.replace(/\./g, '').replace('-', '').toUpperCase();
                    if (!validarRut(rutLimpio)) {
                        console.log('❌ [GRABAR TODO] Error: RUT inválido');
                        error('RUT inválido');
                        return;
                    }

                    // ✅ Validación condicional: solo si estado es Enviado o CerradoOK
                    if (estado === 'Enviado' || estado === 'CerradoOK') {
                        const tieneServiciosSinCostos = servicios.some(s => !s.costos || s.costos.length === 0);
                        if (tieneServiciosSinCostos) {
                            console.log('❌ [GRABAR TODO] Error: Servicios sin costos en estado final');
                            error('No se puede enviar el prospecto: todos los servicios deben tener costos asociados.');
                            return;
                        }
                    }

                    const form = document.getElementById('form-prospecto');
                    const modo = servicios.length > 0 ? 'servicios' : 'prospecto';
                    console.log('📤 [GRABAR TODO] Modo:', modo);

                    // Asegurar campos ocultos
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
                        console.log('📦 [GRABAR TODO] JSON de servicios:', inp.value);
                    }

                    if (confirm('¿Enviar el formulario?\nVerifique la consola (F12) y copie los logs.')) {
                        console.log('✅ [GRABAR TODO] ¡Formulario enviado!');
                        form.submit();
                    } else {
                        console.log('⚠️ [GRABAR TODO] Envío cancelado por el usuario');
                        error('Envío cancelado');
                    }
                });
            }

            const btnCostos = document.getElementById('btn-costos-servicio-dentro');
            const btnGastos = document.getElementById('btn-gastos-locales-dentro');
            if (btnCostos) btnCostos.addEventListener('click', abrirSubmodalCostos);
            if (btnGastos) btnGastos.addEventListener('click', abrirSubmodalGastosLocales);

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
                    error('Error en búsqueda de prospectos');
                }
            });

            // === Cargar prospecto desde la URL (si viene con ?id_ppl=...) ===
            const urlParams = new URLSearchParams(window.location.search);
            const idFromUrl = urlParams.get('id_ppl');
            if (idFromUrl && !isNaN(idFromUrl)) {
                setTimeout(() => {
                    console.log('🔄 Cargando prospecto desde URL:', idFromUrl);
                    seleccionarProspecto(parseInt(idFromUrl));
                }, 300);
                // Limpiar la URL para evitar recargas innecesarias
                history.replaceState({}, document.title, window.location.pathname + '?page=prospectos');
            }

            // === Restringir edición de costos por rol ===
            const originalAbrirSubmodalCostos = abrirSubmodalCostos;
            window.abrirSubmodalCostos = function() {
                const rolUsuario = '<?php echo $_SESSION["rol"] ?? "comercial"; ?>';
                if (rolUsuario !== 'pricing' && rolUsuario !== 'admin') {
                    alert('Solo el rol Pricing puede editar costos.');
                    return;
                }
                originalAbrirSubmodalCostos();
            };
        });

        // Exponer funciones globales
        window.guardarServicio = guardarServicio;
        window.abrirModalServicio = abrirModalServicio;
        window.eliminarServicio = eliminarServicio;
    </script>
</form>