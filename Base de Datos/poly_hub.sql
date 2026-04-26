-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS poly_hub;
USE poly_hub;

-- 1. Crear tabla USUARIOS [cite: 4, 5]
CREATE TABLE USUARIOS (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    saldo DECIMAL(10, 2) DEFAULT 0.00,
    fechaRegistro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. Crear tabla SEGUIDORES [cite: 2]
CREATE TABLE SEGUIDORES (
    id_seguidor INT,
    id_seguido INT,
    fechaSeguimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_seguidor, id_seguido),
    FOREIGN KEY (id_seguidor) REFERENCES USUARIOS(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_seguido) REFERENCES USUARIOS(id_usuario) ON DELETE CASCADE
);

-- 3. Crear tabla LICENCIAS [cite: 6]
CREATE TABLE LICENCIAS (
    id_licencia INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    url_terminos VARCHAR(255)
);

-- 4. Crear tabla CATEGORIAS [cite: 25]
CREATE TABLE CATEGORIAS (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

-- 5. Crear tabla ETIQUETAS [cite: 21, 22]
CREATE TABLE ETIQUETAS (
    id_etiqueta INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

-- 6. Crear tabla ASSETS [cite: 16, 17]
CREATE TABLE ASSETS (
    id_asset INT AUTO_INCREMENT PRIMARY KEY,
    id_creador INT,
    id_licencia INT,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) DEFAULT 0.00,
    portadaurl VARCHAR(255),
    fechaPublicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_creador) REFERENCES USUARIOS(id_usuario) ON DELETE SET NULL,
    FOREIGN KEY (id_licencia) REFERENCES LICENCIAS(id_licencia) ON DELETE RESTRICT
);

-- 7. Crear tabla RESENAS [cite: 7, 8, 9]
CREATE TABLE RESENAS (
    id_resena INT AUTO_INCREMENT PRIMARY KEY,
    id_asset INT,
    id_usuario INT,
    calificacion INT CHECK (calificacion BETWEEN 1 AND 5),
    comentario TEXT,
    fechaCreacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_asset) REFERENCES ASSETS(id_asset) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES USUARIOS(id_usuario) ON DELETE CASCADE
);

-- 8. Crear tabla MOVIMIENTOSBILLETERA [cite: 10, 11, 12, 13, 14]
CREATE TABLE MOVIMIENTOSBILLETERA (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    tipoMovimiento VARCHAR(50) NOT NULL, -- Ej: 'Recarga', 'Compra', 'Retiro'
    monto DECIMAL(10, 2) NOT NULL,
    id_assetRelacionado INT NULL, -- Puede ser NULL si es solo una recarga de saldo
    fechaMovimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
    descripcion TEXT,
    FOREIGN KEY (id_usuario) REFERENCES USUARIOS(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_assetRelacionado) REFERENCES ASSETS(id_asset) ON DELETE SET NULL
);

-- 9. Crear tabla LIBRERIAUSUARIOS [cite: 15]
CREATE TABLE LIBRERIAUSUARIOS (
    id_transaccion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    id_asset INT,
    montoPagado DECIMAL(10, 2) NOT NULL,
    fechaAdquisicion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES USUARIOS(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_asset) REFERENCES ASSETS(id_asset) ON DELETE CASCADE
);

-- 10. Crear tabla pivote ASSET_ETIQUETAS [cite: 23]
CREATE TABLE ASSET_ETIQUETAS (
    id_asset INT,
    id_etiqueta INT,
    PRIMARY KEY (id_asset, id_etiqueta),
    FOREIGN KEY (id_asset) REFERENCES ASSETS(id_asset) ON DELETE CASCADE,
    FOREIGN KEY (id_etiqueta) REFERENCES ETIQUETAS(id_etiqueta) ON DELETE CASCADE
);

-- 11. Crear tabla pivote ASSET_CATEGORIAS [cite: 26]
CREATE TABLE ASSET_CATEGORIAS (
    id_asset INT,
    id_categoria INT,
    PRIMARY KEY (id_asset, id_categoria),
    FOREIGN KEY (id_asset) REFERENCES ASSETS(id_asset) ON DELETE CASCADE,
    FOREIGN KEY (id_categoria) REFERENCES CATEGORIAS(id_categoria) ON DELETE CASCADE
);

-- 12. Crear tabla ARCHIVOSASSET [cite: 24]
CREATE TABLE ARCHIVOSASSET (
    id_archivo INT AUTO_INCREMENT PRIMARY KEY,
    id_asset INT,
    nombrearchivo VARCHAR(255) NOT NULL,
    tamanoMB DECIMAL(8, 2),
    version VARCHAR(50),
    FOREIGN KEY (id_asset) REFERENCES ASSETS(id_asset) ON DELETE CASCADE
);