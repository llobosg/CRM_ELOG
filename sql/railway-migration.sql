-- ===================================================================
-- MIGRACIÓN COMPLETA PARA RAILWAY.APP
-- CRM Aduanas - Datos Reales + Mantenedores
-- Fecha: Octubre 2025
-- ===================================================================

-- === 1. CREAR BASE DE DATOS (Railway lo hace automáticamente) ===
-- CREATE DATABASE IF NOT EXISTS railway;
-- USE railway;

-- === 2. TABLAS DE MANTENEDORES (estructura + datos base) ===

-- Agentes
CREATE TABLE IF NOT EXISTS agentes (
    id_ppl INT AUTO_INCREMENT PRIMARY KEY,
    ig_agente VARCHAR(200),
    razon_social VARCHAR(200) NOT NULL,
    rut_empresa VARCHAR(50),
    fono_empresa VARCHAR(50),
    pais VARCHAR(100),
    direccion VARCHAR(300),
    region VARCHAR(100),
    comuna VARCHAR(100),
    estado VARCHAR(50) DEFAULT 'Activo',
    fecha_alta DATE DEFAULT (CURRENT_DATE),
    fecha_baja DATE
);

-- Aplicación Costos
CREATE TABLE IF NOT EXISTS aplicacion_costos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aplica VARCHAR(100) NOT NULL,
    medio_transporte VARCHAR(100)
);

-- Comerciales
CREATE TABLE IF NOT EXISTS comerciales (
    id_comercial INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    cargo VARCHAR(100
);

-- Tráfico
CREATE TABLE IF NOT EXISTS trafico (
    id_trafico INT AUTO_INCREMENT PRIMARY KEY,
    trafico VARCHAR(100) NOT NULL,
    subtrafico VARCHAR(200)
);

-- Commodity
CREATE TABLE IF NOT EXISTS commodity (
    id_comm INT AUTO_INCREMENT PRIMARY KEY,
    commodity VARCHAR(200) NOT NULL,
    cod_comm VARCHAR(50)
);

-- Conceptos
CREATE TABLE IF NOT EXISTS conceptos_costos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    concepto VARCHAR(200) NOT NULL
);

-- Lugares
CREATE TABLE IF NOT EXISTS lugares (
    id_lugar INT AUTO_INCREMENT PRIMARY KEY,
    medio_transporte VARCHAR(100),
    detalle_lugar VARCHAR(200) NOT NULL,
    pais_lugar VARCHAR(100)
);

-- Medios de Transporte
CREATE TABLE IF NOT EXISTS medios_transporte (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pais VARCHAR(100),
    ciudad VARCHAR(100),
    tipo VARCHAR(50),
    medio VARCHAR(100),
    nombre VARCHAR(200) NOT NULL,
    codigo_iata VARCHAR(10)
);

-- Operación
CREATE TABLE IF NOT EXISTS operacion (
    id_prospect INT,
    id_oper INT AUTO_INCREMENT PRIMARY KEY,
    operacion VARCHAR(100) NOT NULL,
    tipo_oper VARCHAR(100) NOT NULL,
    detalle_tipo_oper VARCHAR(200)
);

-- Proveedores Nacionales
CREATE TABLE IF NOT EXISTS proveedor_pnac (
    id_pnac INT AUTO_INCREMENT PRIMARY KEY,
    cod_pnac VARCHAR(50),
    nombre_pnac VARCHAR(200) NOT NULL
);

-- Incoterms
CREATE TABLE IF NOT EXISTS incoterm (
    id_incoterm INT AUTO_INCREMENT PRIMARY KEY,
    incoterm VARCHAR(10) NOT NULL,
    detalle VARCHAR(200)
);

-- Contactos
CREATE TABLE IF NOT EXISTS contactos (
    id_ppl INT,
    id_ctco INT AUTO_INCREMENT PRIMARY KEY,
    nom_contacto VARCHAR(200) NOT NULL,
    fono_contacto VARCHAR(50),
    email VARCHAR(200),
    cargo VARCHAR(100),
    tipo VARCHAR(50),
    estado VARCHAR(50) DEFAULT 'Activo',
    fecha_alta DATE DEFAULT (CURRENT_DATE),
    fecha_baja DATE
);

-- === 3. TABLAS PRINCIPALES ===

-- Prospectos
CREATE TABLE IF NOT EXISTS prospectos (
    id_ppl INT AUTO_INCREMENT PRIMARY KEY,
    id_prospect INT NOT NULL,
    razon_social VARCHAR(200) NOT NULL,
    rut_empresa VARCHAR(50) NOT NULL,
    fono_empresa VARCHAR(50),
    pais VARCHAR(100),
    direccion VARCHAR(300),
    operacion VARCHAR(100),
    tipo_oper VARCHAR(100),
    estado VARCHAR(50) DEFAULT 'Pendiente',
    concatenado VARCHAR(50) UNIQUE NOT NULL,
    booking VARCHAR(100),
    incoterm VARCHAR(50),
    id_comercial INT,
    nombre VARCHAR(100),
    notas_comerciales TEXT,
    notas_operaciones TEXT,
    fecha_alta DATE DEFAULT (CURRENT_DATE),
    fecha_estado DATE DEFAULT (CURRENT_DATE),
    total_costo DECIMAL(15,2) DEFAULT 0,
    total_venta DECIMAL(15,2) DEFAULT 0,
    total_costogastoslocalesdestino DECIMAL(15,2) DEFAULT 0,
    total_ventasgastoslocalesdestino DECIMAL(15,2) DEFAULT 0,
    id_srvc INT,
    agente VARCHAR(200)
);

-- Servicios
CREATE TABLE IF NOT EXISTS servicios (
    id_ppl INT,
    id_srvc VARCHAR(20) PRIMARY KEY,
    id_prospect INT NOT NULL,
    servicio VARCHAR(200) NOT NULL,
    nombre_corto VARCHAR(100),
    tipo VARCHAR(100),
    trafico VARCHAR(100),
    sub_trafico VARCHAR(100),
    base_calculo VARCHAR(100),
    moneda VARCHAR(10) DEFAULT 'CLP',
    tarifa DECIMAL(15,2) DEFAULT 0,
    iva DECIMAL(5,2) DEFAULT 19,
    estado VARCHAR(50) DEFAULT 'Activo',
    costo DECIMAL(15,2) DEFAULT 0,
    venta DECIMAL(15,2) DEFAULT 0,
    costogastoslocalesdestino DECIMAL(15,2) DEFAULT 0,
    ventasgastoslocalesdestino DECIMAL(15,2) DEFAULT 0,
    ciudad VARCHAR(100),
    pais VARCHAR(100),
    direc_serv VARCHAR(300),
    tipo_cambio DECIMAL(4,2) DEFAULT 1,
    commodity VARCHAR(300),
    origen VARCHAR(300),
    pais_origen VARCHAR(300),
    destino VARCHAR(300),
    pais_destino VARCHAR(300),
    transito VARCHAR(100),
    frecuencia VARCHAR(200),
    lugar_carga VARCHAR(400),
    sector VARCHAR(400),
    mercancia VARCHAR(300),
    bultos INT DEFAULT 0,
    peso DECIMAL(4,2) DEFAULT 0,
    volumen VARCHAR(400),
    dimensiones VARCHAR(200),
    agente VARCHAR(300),
    aol VARCHAR(4),
    aod VARCHAR(4),
    aerolinea VARCHAR(100),
    naviera VARCHAR(100),
    terrestre VARCHAR(300),
    ref_cliente VARCHAR(100),
    proveedor_nac VARCHAR(300),
    desconsolidac VARCHAR(100),
    incoterm VARCHAR(50),
    carga_peligrosa VARCHAR(10) DEFAULT 'NO',
    clase INT,
    un INT
);

-- Costos Servicios
CREATE TABLE IF NOT EXISTS costos_servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_servicio VARCHAR(20) NOT NULL,
    concepto VARCHAR(200) NOT NULL,
    moneda VARCHAR(10) DEFAULT 'CLP',
    qty DECIMAL(10,2) DEFAULT 1,
    costo DECIMAL(15,2) DEFAULT 0,
    total_costo DECIMAL(15,2) DEFAULT 0,
    tarifa DECIMAL(15,2) DEFAULT 0,
    total_tarifa DECIMAL(15,2) DEFAULT 0,
    aplica VARCHAR(100)
);

-- Gastos Locales Detalle
CREATE TABLE IF NOT EXISTS gastos_locales_detalle (
    id_gld INT AUTO_INCREMENT PRIMARY KEY,
    id_servicio VARCHAR(20) NOT NULL,
    tipo VARCHAR(20) NOT NULL, -- 'Costo' o 'Ventas'
    gasto VARCHAR(200) NOT NULL,
    moneda VARCHAR(10) DEFAULT 'CLP',
    monto DECIMAL(15,2) DEFAULT 0,
    afecto VARCHAR(10) DEFAULT 'SI', -- 'SI' o 'NO'
    iva DECIMAL(5,2) DEFAULT 0
);

-- === 4. DATOS DE MANTENEDORES BÁSICOS (ejemplos seguros) ===

-- Incoterms básicos
INSERT IGNORE INTO incoterm (incoterm, detalle) VALUES
('EXW', 'En fábrica'),
('FCA', 'Franco transportista'),
('FOB', 'Franco a bordo'),
('CIF', 'Costo, seguro y flete'),
('DAP', 'Entregado en lugar'),
('DDP', 'Entregado derechos pagados');

-- Tráfico básico
INSERT IGNORE INTO trafico (trafico) VALUES
('Marítimo'),
('Aéreo'),
('Terrestre');

-- Commodity básico
INSERT IGNORE INTO commodity (commodity) VALUES
('ALUMINIO'),
('COBRE'),
('MAÍZ'),
('TRIGO'),
('EQUIPO MÉDICO'),
('AUTOMÓVILES');

-- Conceptos básicos
INSERT IGNORE INTO conceptos (concepto) VALUES
('Almacenaje'),
('Manipulación'),
('Despacho'),
('Inspección'),
('Flete'),
('Seguro');

-- Proveedores nacionales básicos
INSERT IGNORE INTO proveedor_pnac (nombre_pnac) VALUES
('Transportes H&H'),
('Logística Andina'),
('Marítima del Sur');

-- === 5. ÍNDICES PARA MEJOR RENDIMIENTO ===

CREATE INDEX idx_prospectos_concatenado ON prospectos(concatenado);
CREATE INDEX idx_prospectos_rut ON prospectos(rut_empresa);
CREATE INDEX idx_servicios_prospect ON servicios(id_prospect);
CREATE INDEX idx_costos_servicio ON costos_servicios(id_servicio);
CREATE INDEX idx_gastos_servicio ON gastos_locales_detalle(id_servicio);
CREATE INDEX idx_lugares_medio ON lugares(medio_transporte);

-- === 6. CLAVES FORÁNEAS (opcional, Railway lo permite) ===

-- ALTER TABLE servicios ADD CONSTRAINT fk_servicios_prospecto 
-- FOREIGN KEY (id_prospect) REFERENCES prospectos(id_ppl) ON DELETE CASCADE;

-- ALTER TABLE costos_servicios ADD CONSTRAINT fk_costos_servicio 
-- FOREIGN KEY (id_servicio) REFERENCES servicios(id_srvc) ON DELETE CASCADE;

-- ALTER TABLE gastos_locales_detalle ADD CONSTRAINT fk_gastos_servicio 
-- FOREIGN KEY (id_servicio) REFERENCES servicios(id_srvc) ON DELETE CASCADE;

-- ===================================================================
-- ✅ LISTO PARA MIGRAR A RAILWAY
-- 1. Ejecuta este script en tu BD local para verificar
-- 2. Exporta tus datos reales con: 
--    mysqldump -u root -p --no-create-db --skip-triggers --compact --complete-insert crm_aduanas > datos-reales.sql
-- 3. Importa en Railway con: 
--    railway db connect < datos-reales.sql
-- ===================================================================