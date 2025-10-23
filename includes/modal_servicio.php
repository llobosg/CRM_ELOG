<!-- Modal: Agregar Servicio -->
<div id="modal-servicio" class="modal" style="display: none; 
    position: fixed; 
    top: 0; 
    left: 0; 
    width: 100%; 
    height: 100%; 
    background: rgba(0,0,0,0.5); 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    z-index: 1000;">
    <div class="modal-content" style="width: 80%; max-width: 800px; max-height: 90vh; overflow-y: auto; background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); padding: 0;">
        <h3 style="margin: 0; padding: 1rem 1.5rem; background: #3a4f63; color: white; font-size: 1.2rem;"><i class="fas fa-truck"></i> Agregar Servicio</h3>
        <span class="close" onclick="cerrarModalServicio()" style="position: absolute; top: 1rem; right: 1rem; font-size: 1.5rem; cursor: pointer; z-index: 10;">&times;</span>
        <form id="form-servicio-modal" onsubmit="guardarServicioModal(event)" style="padding: 2rem;">
            <input type="hidden" name="id_prospect" id="id_prospect_serv" />
            
            <!-- Fila 1: Concatenado, Servicio, Nombre Corto -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.2rem;">
                <div style="display: flex; flex-direction: column;">
                    <label>Concatenado</label>
                    <input type="text" name="concatenado_serv" id="concatenado_serv" readonly 
                           style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; background: #f8f9fa; font-weight: bold;" />
                </div>
                <div style="display: flex; flex-direction: column;">
                    <label>Servicio *</label>
                    <input type="text" name="servicio" required 
                           style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px;" />
                </div>
                <div style="display: flex; flex-direction: column;">
                    <label>Nombre Corto</label>
                    <input type="text" name="nombre_corto" 
                           style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px;" />
                </div>
            </div>

            <!-- Fila 2: Tipo, País, Ciudad -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.2rem;">
                <div style="display: flex; flex-direction: column;">
                    <label>Tipo</label>
                    <select name="tipo" style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px;">
                        <option value="Origen">Origen</option>
                        <option value="Destino">Destino</option>
                    </select>
                </div>
                <div style="display: flex; flex-direction: column;">
                    <label>País *</label>
                    <input type="text" name="pais" id="pais_serv" list="lista-paises-serv" required 
                           style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px;" />
                    <datalist id="lista-paises-serv"></datalist>
                </div>
                <div style="display: flex; flex-direction: column;">
                    <label>Ciudad *</label>
                    <input type="text" name="ciudad" id="ciudad_serv" list="lista-ciudades-serv" required 
                           style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px;" />
                    <datalist id="lista-ciudades-serv"></datalist>
                </div>
            </div>

            <!-- Fila 3: Medio de Transporte, Título Dinámico, Detalle -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.2rem;">
                <div style="display: flex; flex-direction: column;">
                    <label>Medio de Transporte *</label>
                    <select name="medio_transporte" id="medio_transporte" required 
                            style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px;">
                        <option value="">Seleccionar</option>
                        <option value="Aéreo">Aéreo</option>
                        <option value="Terrestre">Terrestre</option>
                        <option value="Marítimo">Marítimo</option>
                    </select>
                </div>
                <div style="display: flex; flex-direction: column;">
                    <label id="label_detalle_mt">Detalle</label>
                    <input type="text" name="detalle_mt" id="detalle_mt" readonly 
                           style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; background: #f8f9fa;" />
                </div>
                <div style="display: flex; flex-direction: column;">
                    <label>Código IATA (opcional)</label>
                    <input type="text" name="codigo_iata" id="codigo_iata" readonly 
                           style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px; background: #f8f9fa;" />
                </div>
            </div>

            <!-- ✅ NUEVA FILA 4: Dirección del servicio -->
            <div style="display: grid; grid-template-columns: 1fr; gap: 1rem; margin-bottom: 1.2rem;">
                <div style="display: flex; flex-direction: column;">
                    <label>Dirección del servicio</label>
                    <input type="text" name="direc_serv" id="direc_serv" maxlength="300"
                           style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px;"
                           placeholder="Ej: Av. Siempre Viva 123, Santiago, Chile" />
                </div>
            </div>
            <!-- Fin nueva fila -->

            <!-- Fila 5: IVA, Estado -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.2rem;">
                <div style="display: flex; flex-direction: column;">
                    <label>IVA (%)</label>
                    <input type="number" name="iva" step="0.1" value="19"
                           style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px;" />
                </div>
                <div style="display: flex; flex-direction: column;">
                    <label>Estado</label>
                    <select name="estado" style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px;">
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>
            </div>

            <!-- Fila 6: Costo, Venta, GDC, GDV -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.2rem;">
                <div style="display: flex; flex-direction: column;">
                    <label>Costo</label>
                    <input type="number" name="costo" step="0.01"
                           style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px;" />
                </div>
                <div style="display: flex; flex-direction: column;">
                    <label>Venta</label>
                    <input type="number" name="venta" step="0.01"
                           style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px;" />
                </div>
                <div style="display: flex; flex-direction: column;">
                    <label>Gasto Destino Costo</label>
                    <input type="number" name="costogastoslocalesdestino" step="0.01"
                           style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px;" />
                </div>
                <div style="display: flex; flex-direction: column;">
                    <label>Gasto Destino Venta</label>
                    <input type="number" name="ventasgastoslocalesdestino" step="0.01"
                           style="padding: 0.6rem; border: 1px solid #ccc; border-radius: 6px;" />
                </div>
            </div>

            <!-- Botones -->
            <div style="text-align: center; margin-top: 2rem; display: flex; justify-content: center; gap: 1rem;">
                <button type="button" onclick="cerrarModalServicio()" 
                        style="padding: 0.8rem 2rem; font-size: 1rem; background: #6c757d; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    <i class="fas fa-arrow-left"></i> Volver
                </button>
                <button type="submit" 
                        style="padding: 0.8rem 2rem; font-size: 1rem; background: #009966; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    <i class="fas fa-save"></i> Grabar Servicio
                </button>
            </div>
        </form>
    </div>
</div>