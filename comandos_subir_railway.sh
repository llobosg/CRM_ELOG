
/Applications/XAMPP/xamppfiles/bin/mysql \
  --host=trolley.proxy.rlwy.net \
  --port=15120 \
  --user=root \
  --password=KHlpwnWslYPEHvfSjcYOVItSUFNxxVDL \
  railway -e "DESCRIBE servicios;"

// ===== SIGEF PROD
/Applications/XAMPP/xamppfiles/bin/mysql \
  --host=crossover.proxy.rlwy.net \
  --port=27546 \
  --user=root \
  --password=BsyHHoiCkOcAHRulqExPQNqlzhIgHWGx \
  railway -e "RENAME TABLE VEHICULOS TO VEHICULO;"

/Applications/XAMPP/xamppfiles/bin/mysql \
  --host=trolley.proxy.rlwy.net \
  --port=15120 \
  --user=root \
  --password=KHlpwnWslYPEHvfSjcYOVItSUFNxxVDL \
  railway -e "-- Actualizar registros existentes con 100%
UPDATE costos_servicios 
SET porcentaje_concepto = 100.00 
WHERE porcentaje_concepto IS NULL OR porcentaje_concepto = 0;"
  

/Applications/XAMPP/xamppfiles/bin/mysql \
  --host=trolley.proxy.rlwy.net \
  --port=15120 \
  --user=root \
  --password=KHlpwnWslYPEHvfSjcYOVItSUFNxxVDL \
  railway -e "DESCRIBE costos_servicios;"


git add .
git commit -m "actualiza por %12"
git push origin main



 fila 1944 calculo %

<tfoot>
                        <tr style="font-weight: normal; background: #f9fafcff;">
                            <td colspan="4" style="padding: 0.6rem; text-align: right; border: 1px solid #ddd;">TOTAL COSTO:</td>
                            <td id="total-costo-costos" style="padding: 0.6rem; text-align: right; border: 1px solid #ddd; background-color: #fff9db;">0.00</td>
                            <td style="padding: 0.6rem; text-align: right; border: 1px solid #ddd;">TOTAL TARIFA:</td>
                            <td id="total-tarifa-costos" style="padding: 0.6rem; text-align: right; border: 1px solid #ddd; background-color: #e6f7ff;">0.00</td>
                            <td></td>
                        </tr>
                    </tfoot> 


# Pasar cambios de Prod a QA para igualar entornos
git checkout main
git pull origin main
git checkout qa
git merge main
git push origin qa


railway login

railway link

cd /Applications/XAMPP/xamppfiles/htdocs/crm_qwen
git checkout main
git pull origin main

cd /Applications/XAMPP/xamppfiles/htdocs/sigef
git checkout main
git pull origin main

cd "$(git rev-parse --show-toplevel)"


# 1. Guarda tus cambios actuales (por si algo falla)
git stash

# 2. Trae los cambios del remoto
git pull origin main

# 3. Recupera tus cambios locales
git stash pop

# 4. Si hay conflictos, resuélvelos, luego:
git add pages/prospectos.php pages/prospectos_logic.php

# 5. Confirma
git commit -m "Rollback a versión estable"

# 6. Envía
git push origin main


PROMPT OPTIMO
Contexto: módulo Ficha Cliente, modal-contacto, agregando nuevo contacto
Problema: click btn Grabar Contacto no actualiza tabla-contactos
Mensaje error: 
archivos relevantes: ficha_cliente.php (función guardarContacto())
Lo que ya intenté: 
pedido: favor 

echo "# SIGEF" >> README.md
git init
git add README.md
git commit -m "first commit"
git branch -M main
git remote add origin https://github.com/llobosg/SIGEF.git
git push -u origin main

cd /Applications/XAMPP/xamppfiles/htdocs/sigef

git add pages/personas_view.php

git add api/get_mantencion.php
git add api/get_mantencion_logic.php
git add includes/header.php


git add pages/monto_view.php
git add api/get_monto_busqueda.php


git add api/get_facturacion.php
git add api/facturacion_logic.php

git add pages/facturacion_view.php
git add pages/mantencion_view.php
git add includes/header.php

git add pages/monto_view.php
git commit -m "bloque script completo"
git push origin main   ===> Prod SIGEF

/Applications/XAMPP/xamppfiles/bin/mysql \
  --host=crossover.proxy.rlwy.net \
  --port=27546 \
  --user=root \
  --password=BsyHHoiCkOcAHRulqExPQNqlzhIgHWGx \
  railway -e "
    -- Establecer valores por defecto para evitar errores
ALTER TABLE MONTO 
MODIFY monto_p DECIMAL(10,2) NOT NULL DEFAULT 0,
MODIFY monto_f DECIMAL(10,2) NOT NULL DEFAULT 0;"


===== VERIFICAR REPOSITORIO CONECTADO ======
cd /ruta/a/VTLG
git init
git remote add origin https://github.com/llobosg/VTLG_prod.git
git remote -v
git add .
git commit -m "Primera versión del sitio web"
git branch -M main
git push -u origin main

===== INICIAR GIT DESDE CERO ======
# Eliminar historial local antiguo (seguro porque GitHub está vacío)
rm -rf .git

# Inicializar nuevo repositorio
git init
git checkout -b main

# Agregar todos los archivos (excepto los ignorados)
git add .

# Hacer el primer commit oficial
git commit -m "✅ Versión oficial: sistema SIGA con MySQLi, compatible Railway"

 ======= VTLG PROD =====
cd /Applications/XAMPP/xamppfiles/htdocs/VTLG

git add .
git commit -m "remesa mercancia en lista entrada"
git push origin main

====== MYSQL VTLG ===
/Applications/XAMPP/xamppfiles/bin/mysql \
  --host=nozomi.proxy.rlwy.net \
  --port=11739 \
  --user=root \
  --password=hwUzzIcqljzFCeTIFDVcarQWaDFrMGUn \
  railway -e "ALTER TABLE remesa MODIFY COLUMN mercancia_rms INT NULL;"
