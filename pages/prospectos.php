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

    <!-- ========== DATOS DEL PROSPECTO ========== -->
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

    <!-- ========== SERVICIOS ASOCIADOS ========== -->
    <div class="card">
        <h3><i class="fas fa-truck"></i> Servicios Asociados</h3>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem;">
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

<!-- ========== MODALES ========== -->

<!-- Modal Comercial -->
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

<!-- Modal Operaciones -->
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

<!-- Modal Servicio -->
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
        <div class="modal-footer" style="text-align: right; margin-top: 1.5rem; gap: 0.8rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <!-- Botones de submodales dentro del modal -->
                <button type="button" class="btn-comment" id="btn-costos-servicio-dentro"><i class="fas fa-calculator"></i> Costos</button>
                <button type="button" class="btn-comment" id="btn-gastos-locales-dentro"><i class="fas fa-file-invoice-dollar"></i> Gastos</button>
            </div>
            <div style="display: flex; gap: 0.8rem;">
                <button type="button" class="btn-secondary" onclick="cerrarModalServicioConConfirmacion()">Volver</button>
                <button type="button" class="btn-add" onclick="guardarServicio()">Agregar Servicio</button>
            </div>
        </div>
    </div>
</div>

<!-- ========== SUBMODALES ========== -->

<!-- Submodal: Costos/Ventas/Gastos -->
<div id="submodal-costos" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:11000;">
    <div class="modal-content" style="max-width: 1400px; width: 95%; margin: 1.5rem auto; background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
        <h3><i class="fas fa-calculator"></i> Costos, Ventas y Gastos</h3>
        <span class="close" onclick="cerrarSubmodalCostos()" style="cursor:pointer; float:right; font-size:1.8rem; margin-top:-5px;">&times;</span>

        <!-- Formulario de entrada -->
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

            ['razon_social','rut_empresa','fono_empresa','direccion','booking','incoterm','concatenado','fecha_alta','fecha_estado'].forEach(f => {
                const el = document.querySelector(`[name="${f}"]`);
                if (el && el.tagName === 'INPUT') el.value = p[f] || '';
            });

            document.getElementById('id_comercial').value = p.id_comercial || '';
            document.getElementById('nombre').value = p.nombre || '';
            document.getElementById('estado').value = p.estado || 'Pendiente';

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

            servicios = (data.servicios || []).map(s => ({
                ...s,
                costo: parseFloat(s.costo) || 0,
                venta: parseFloat(s.venta) || 0,
                costogastoslocalesdestino: parseFloat(s.costogastoslocalesdestino) || 0,
                ventasgastoslocalesdestino: parseFloat(s.ventasgastoslocalesdestino) || 0
            }));
            tieneServiciosIniciales = servicios.length > 0;
            actualizarTabla();

            document.getElementById('id_ppl').value = p.id_ppl || '';
            document.getElementById('id_prospect').value = p.id_prospect || '';

            // ✅ SIEMPRE editable
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
function abrirSubmodalCostos() {
    const modalServicio = document.getElementById('modal-servicio');
    if (!modalServicio || modalServicio.style.display === 'none') {
        error('Abra primero el modal de Servicio');
        return;
    }
    
    if (servicioEnEdicion !== null && servicios[servicioEnEdicion]) {
        costosServicio = Array.isArray(servicios[servicioEnEdicion].costos) 
            ? [...servicios[servicioEnEdicion].costos] 
            : [];
    } else {
        costosServicio = [];
    }
    
    const monedaServicio = document.getElementById('serv_moneda')?.value || 'USD';
    document.getElementById('costo_moneda').value = monedaServicio;
    cargarConceptosCostos();
    const medioTransporte = document.getElementById('serv_medio_transporte')?.value || '';
    cargarAplicacionesCostos(medioTransporte);
    
    actualizarTablaCostos();
    document.getElementById('submodal-costos').style.display = 'block';
}

function cargarConceptosCostos() {
    fetch('/api/get_conceptos_costos.php')
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('costo_concepto');
            if (!sel) return;
            sel.innerHTML = '<option value="">Seleccionar concepto</option>';
            (data.conceptos || data || []).forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.concepto || c;
                opt.textContent = c.concepto || c;
                sel.appendChild(opt);
            });
        })
        .catch(err => {
            console.error('Error al cargar conceptos:', err);
            error('No se pudieron cargar los conceptos de costo');
        });
}

function cargarAplicacionesCostos(medio) {
    fetch(`/api/get_aplicaciones_costos.php?medio=${encodeURIComponent(medio)}`)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('costo_aplica');
            if (!sel) return;
            sel.innerHTML = '<option value="">Seleccionar aplica</option>';
            const opciones = Array.isArray(data) ? data : (data.aplicaciones || []);
            opciones.forEach(item => {
                const valor = typeof item === 'string' ? item : item.aplica;
                if (valor) {
                    const opt = document.createElement('option');
                    opt.value = valor;
                    opt.textContent = valor;
                    sel.appendChild(opt);
                }
            });
        })
        .catch(err => {
            console.error('Error al cargar aplicaciones:', err);
            error('No se pudieron cargar las opciones de "Aplica"');
        });
}

['costo_qty', 'costo_costo', 'costo_tarifa'].forEach(id => {
    document.getElementById(id).addEventListener('input', calcularTotalesCostos);
});

function calcularTotalesCostos() {
    const qty = parseFloat(document.getElementById('costo_qty').value) || 0;
    const costo = parseFloat(document.getElementById('costo_costo').value) || 0;
    const tarifa = parseFloat(document.getElementById('costo_tarifa').value) || 0;
    
    document.getElementById('costo_total_costo').value = (qty * costo).toFixed(2);
    document.getElementById('costo_total_tarifa').value = (qty * tarifa).toFixed(2);
}

function guardarCosto() {
    const concepto = document.getElementById('costo_concepto').value;
    const aplica = document.getElementById('costo_aplica').value;
    const qty = parseFloat(document.getElementById('costo_qty').value) || 0;
    const costo = parseFloat(document.getElementById('costo_costo').value) || 0;
    const tarifa = parseFloat(document.getElementById('costo_tarifa').value) || 0;
    const moneda = document.getElementById('costo_moneda').value || 'CLP';

    if (!concepto || !aplica) {
        error('Concepto y Aplica son obligatorios');
        return;
    }

    const nuevoCosto = {
        concepto,
        moneda,
        qty,
        costo,
        total_costo: qty * costo,
        tarifa,
        total_tarifa: qty * tarifa,
        aplica
    };

    if (window.indiceCostoEdicion !== undefined) {
        costosServicio[window.indiceCostoEdicion] = nuevoCosto;
        delete window.indiceCostoEdicion;
    } else {
        costosServicio.push(nuevoCosto);
    }

    actualizarTablaCostos();
    limpiarFormularioCostos();
    exito('Costo guardado');
}

function actualizarTablaCostos() {
    const tbody = document.getElementById('costos-body');
    const totalCostoEl = document.getElementById('total-costo-costos');
    const totalTarifaEl = document.getElementById('total-tarifa-costos');
    
    if (!tbody || !totalCostoEl || !totalTarifaEl) return;

    tbody.innerHTML = '';
    let totalCosto = 0, totalTarifa = 0;

    costosServicio.forEach((c, i) => {
        const costo = parseFloat(c.costo) || 0;
        const tarifa = parseFloat(c.tarifa) || 0;
        const qty = parseFloat(c.qty) || 0;
        const totalCostoItem = costo * qty;
        const totalTarifaItem = tarifa * qty;

        totalCosto += totalCostoItem;
        totalTarifa += totalTarifaItem;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${c.concepto}</td>
            <td>${c.moneda}</td>
            <td style="text-align: right;">${qty.toFixed(2)}</td>
            <td style="text-align: right; background-color: #fff9db;">${costo.toFixed(2)}</td>
            <td style="text-align: right; background-color: #fff9db;">${totalCostoItem.toFixed(2)}</td>
            <td style="text-align: right; background-color: #e6f7ff;">${tarifa.toFixed(2)}</td>
            <td style="text-align: right; background-color: #e6f7ff;">${totalTarifaItem.toFixed(2)}</td>
            <td>${c.aplica}</td>
            <td style="text-align: center;">
                <button type="button" class="btn-edit" onclick="editarCosto(${i})" style="margin-right: 0.3rem; padding: 0.2rem 0.4rem;">✏️</button>
                <button type="button" class="btn-delete" onclick="eliminarCosto(${i})" style="padding: 0.2rem 0.4rem;">🗑️</button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    totalCostoEl.textContent = totalCosto.toFixed(2);
    totalTarifaEl.textContent = totalTarifa.toFixed(2);
}

function editarCosto(index) {
    const c = costosServicio[index];
    if (!c) return;

    document.getElementById('costo_concepto').value = c.concepto || '';
    document.getElementById('costo_qty').value = (c.qty !== undefined) ? c.qty : '';
    document.getElementById('costo_costo').value = (c.costo !== undefined) ? c.costo : '';
    document.getElementById('costo_tarifa').value = (c.tarifa !== undefined) ? c.tarifa : '';
    document.getElementById('costo_aplica').value = c.aplica || '';
    document.getElementById('costo_total_costo').value = (c.total_costo !== undefined) ? c.total_costo.toFixed(2) : '0.00';
    document.getElementById('costo_total_tarifa').value = (c.total_tarifa !== undefined) ? c.total_tarifa.toFixed(2) : '0.00';

    window.indiceCostoEdicion = index;
}

function eliminarCosto(index) {
    if (confirm('¿Eliminar costo?')) {
        costosServicio.splice(index, 1);
        actualizarTablaCostos();
        exito('Costo eliminado');
    }
}

function limpiarFormularioCostos() {
    document.getElementById('costo_concepto').selectedIndex = 0;
    document.getElementById('costo_qty').value = '';
    document.getElementById('costo_costo').value = '';
    document.getElementById('costo_tarifa').value = '';
    document.getElementById('costo_aplica').selectedIndex = 0;
    document.getElementById('costo_total_costo').value = '0.00';
    document.getElementById('costo_total_tarifa').value = '0.00';
}

function cerrarSubmodalCostos() {
    document.getElementById('submodal-costos').style.display = 'none';
}

// --- Gastos Locales ---
function abrirSubmodalGastosLocales() {
    const modalServicio = document.getElementById('modal-servicio');
    if (!modalServicio || modalServicio.style.display === 'none') {
        error('Abra primero el modal de Servicio');
        return;
    }
    
    if (servicioEnEdicion !== null && servicios[servicioEnEdicion]) {
        gastosLocales = Array.isArray(servicios[servicioEnEdicion].gastos_locales) 
            ? [...servicios[servicioEnEdicion].gastos_locales] 
            : [];
    } else {
        gastosLocales = [];
    }
    
    cargarGastosPorTipo();
    actualizarTablaGastosLocales();
    document.getElementById('submodal-gastos-locales').style.display = 'block';
}

document.getElementById('gasto_tipo')?.addEventListener('change', cargarGastosPorTipo);

function cargarGastosPorTipo() {
    const tipo = document.getElementById('gasto_tipo')?.value;
    if (!tipo) {
        document.getElementById('gasto_gasto').innerHTML = '<option value="">Gastos</option>';
        return;
    }
    fetch(`/api/get_gastos_locales.php?tipo=${encodeURIComponent(tipo)}`)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('gasto_gasto');
            if (!sel) return;
            sel.innerHTML = '<option value="">Gastos</option>';
            (data.gastos || []).forEach(g => {
                const opt = document.createElement('option');
                opt.value = g;
                opt.textContent = g;
                sel.appendChild(opt);
            });
        });
}

function guardarGastoLocal() {
    const tipo = document.getElementById('gasto_tipo').value;
    const gasto = document.getElementById('gasto_gasto').value;
    const moneda = document.getElementById('gasto_moneda').value;
    const monto = parseFloat(document.getElementById('gasto_monto').value) || 0;
    const afecto = document.getElementById('gasto_afecto').value;
    const iva = parseFloat(document.getElementById('gasto_iva').value) || 0;

    if (!tipo || !gasto) {
        return error('Tipo y Gasto son obligatorios');
    }

    const nuevoGasto = { tipo, gasto, moneda, monto, afecto, iva };
    gastosLocales.push(nuevoGasto);
    actualizarTablaGastosLocales();
    limpiarFormularioGastos();
    exito('Gasto local agregado');
}

function actualizarTablaGastosLocales() {
    const tbody = document.getElementById('gastos-locales-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    let totalVenta = 0, totalCosto = 0;
    
    gastosLocales.forEach((g, i) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${g.tipo}</td>
            <td>${g.gasto}</td>
            <td>${g.moneda}</td>
            <td style="text-align:right;">${g.monto.toFixed(2)}</td>
            <td>${g.afecto}</td>
            <td style="text-align:right;">${g.iva.toFixed(2)}</td>
            <td style="text-align:center;">
                <button type="button" onclick="eliminarGastoLocal(${i})">🗑️</button>
            </td>
        `;
        tbody.appendChild(tr);
        
        if (g.tipo === 'Ventas') totalVenta += g.monto;
        if (g.tipo === 'Costo') totalCosto += g.monto;
    });
    
    document.getElementById('total-venta-gastos').textContent = totalVenta.toFixed(2);
    document.getElementById('total-costo-gastos').textContent = totalCosto.toFixed(2);
    document.getElementById('profit-local').textContent = (totalVenta - totalCosto).toFixed(2);
    const profitPct = totalVenta > 0 ? ((totalVenta - totalCosto) / totalVenta * 100) : 0;
    document.getElementById('profit-porcentaje').textContent = profitPct.toFixed(2) + ' %';
}

function eliminarGastoLocal(index) {
    if (confirm('¿Eliminar este gasto?')) {
        gastosLocales.splice(index, 1);
        actualizarTablaGastosLocales();
        exito('Gasto eliminado');
    }
}

function limpiarFormularioGastos() {
    document.getElementById('gasto_tipo').selectedIndex = 0;
    document.getElementById('gasto_gasto').selectedIndex = 0;
    document.getElementById('gasto_moneda').value = 'USD';
    document.getElementById('gasto_monto').value = '';
    document.getElementById('gasto_afecto').value = 'SI';
    document.getElementById('gasto_iva').value = '';
}

function cerrarSubmodalGastosLocales() {
    document.getElementById('submodal-gastos-locales').style.display = 'none';
}

// ===================================================================
// === 9. INICIALIZACIÓN ===
// ===================================================================
document.addEventListener('DOMContentLoaded', () => {
    cargarPaises();
    cargarOperacionesYTipos();
    document.getElementById('operacion')?.addEventListener('change', calcularConcatenado);
    document.getElementById('tipo_oper')?.addEventListener('change', calcularConcatenado);

    document.getElementById('btn-save-all').textContent = 'Grabar Todo';
    document.getElementById('btn-agregar-servicio').addEventListener('click', () => abrirModalServicio());

    // Submodales desde la sección principal (con validación)
    document.getElementById('btn-costos-servicio')?.addEventListener('click', () => {
        error('Abra un servicio primero para gestionar costos');
    });
    document.getElementById('btn-gastos-locales')?.addEventListener('click', () => {
        error('Abra un servicio primero para gestionar gastos');
    });

    // Submodales desde dentro del modal de servicio
    document.getElementById('btn-costos-servicio-dentro')?.addEventListener('click', abrirSubmodalCostos);
    document.getElementById('btn-gastos-locales-dentro')?.addEventListener('click', abrirSubmodalGastosLocales);

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

    // Cargar prospecto desde URL
    const urlParams = new URLSearchParams(window.location.search);
    const idFromUrl = urlParams.get('id_ppl');
    if (idFromUrl && !isNaN(idFromUrl)) {
        const cleanUrl = window.location.pathname + '?page=prospectos';
        history.replaceState({}, document.title, cleanUrl);
        setTimeout(() => seleccionarProspecto(parseInt(idFromUrl)), 300);
    }
});
</script>