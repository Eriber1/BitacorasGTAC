-- Crear base de datos
CREATE DATABASE IF NOT EXISTS bitacoras_gtac CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE bitacoras_gtac;

-- =====================================================
-- TABLA DE USUARIOS
-- =====================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar usuario administrador por defecto
-- Usuario: admin
-- Contraseña: admin123
INSERT INTO usuarios (usuario, password, nombre, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin@gtac.com');

-- =====================================================
-- TABLA DE BITÁCORAS
-- =====================================================
CREATE TABLE bitacoras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    os VARCHAR(50) NOT NULL,
    cliente VARCHAR(100) NOT NULL,
    sitio VARCHAR(200) NOT NULL,
    fecha DATE NOT NULL,
    brigada VARCHAR(200) NOT NULL,
    bitacora TEXT NOT NULL,
    foto_clock_in VARCHAR(255),
    foto_clock_out VARCHAR(255),
    foto_etiquetas VARCHAR(255),
    foto_extra VARCHAR(255),
    fm_acceso VARCHAR(100),
    noc_acceso VARCHAR(100),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_os (os),
    INDEX idx_sitio (sitio),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATOS DE EJEMPLO (opcional)
-- =====================================================
INSERT INTO bitacoras (os, cliente, sitio, fecha, brigada, bitacora, fm_acceso, noc_acceso) VALUES
('5356', 'BESTEL', 'Cd Del Carmen', '2025-10-23', 'Cortez Hidalgo Ulises Hibrani', 
'*se llega a sitio a las 6:50 am y se solicita folios de acceso diurnos a Huawei.
*Huawei comenta que aún sacará los folios diurnos.
*Me pasan los folios a las 10:10 a.m y Solicito acceso con el NOC
*Una ves con acceso entro al sitio y realizo clock in y EHS.
*Por indicaciones de GTAC inserto un módulo en rack A05 en la repisa 8800 t32 en la tarjeta TTX slot 23 puerto Tx7-Rx7
*Por indicaciones de GTAC inserto un módulo en el rack A06 repisa OSN 9800 M24 slot 10 tarjeta T230 puerto Tx4-Rx4.
*realizo las interconexiones de fibras para crear el Bridge.
*Realizo el etiquetado correspondiente en ambas puntas de la fibra.
*tomo evidencias fotográficas y las comparto con el grupo de Huawei e interno.
*Validan y me retiro de sitio.',
'Juan Pérez', 'NOC Central');