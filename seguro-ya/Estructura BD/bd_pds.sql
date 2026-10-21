-- Esquema principal de Seguro Ya con cuentas y datos ficticios para evaluación.
CREATE DATABASE IF NOT EXISTS tp_programacion_sistemas
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE tp_programacion_sistemas;


CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    email VARCHAR(255) NOT NULL,

    nombres VARCHAR(100) NOT NULL,

    apellidos VARCHAR(100) NOT NULL,

    dni VARCHAR(8) NULL,

    nombre_usuario VARCHAR(100) NOT NULL,

    password_hash VARCHAR(255) NOT NULL,

    perfil ENUM('usuario', 'admin')
        NOT NULL DEFAULT 'usuario',

    fecha_nacimiento DATE NULL,

    fecha_registro DATETIME
        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    estado ENUM('activo', 'inactivo')
        NOT NULL DEFAULT 'activo',

    CONSTRAINT uq_usuarios_email
        UNIQUE (email),

    CONSTRAINT uq_usuarios_nombre_usuario
        UNIQUE (nombre_usuario),

    CONSTRAINT uq_usuarios_dni
        UNIQUE (dni),
    CONSTRAINT chk_usuarios_dni
        CHECK (dni IS NULL OR dni REGEXP '^[0-9]{7,8}$')
) ENGINE=InnoDB;



CREATE TABLE IF NOT EXISTS log_accesos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    usuario_id INT UNSIGNED NULL,

    email_intentado VARCHAR(255) NULL,

    fecha_hora DATETIME
        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    resultado ENUM('exitoso', 'fallido')
        NOT NULL,

    ip VARCHAR(45) NULL,

    INDEX idx_log_accesos_usuario (usuario_id),

    INDEX idx_log_accesos_fecha (fecha_hora),

    CONSTRAINT fk_log_accesos_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;



CREATE TABLE IF NOT EXISTS recuperacion_password (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    usuario_id INT UNSIGNED NOT NULL,

    token VARCHAR(64) NOT NULL,

    expira DATETIME NOT NULL,

    usado TINYINT(1) NOT NULL DEFAULT 0,

    fecha_creacion DATETIME
        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_recuperacion_password_token
        UNIQUE (token),

    INDEX idx_recuperacion_usuario (usuario_id),

    INDEX idx_recuperacion_expira (expira),

    CONSTRAINT fk_recuperacion_password_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT chk_recuperacion_usado
        CHECK (usado IN (0, 1))
) ENGINE=InnoDB;



CREATE TABLE IF NOT EXISTS credenciales (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    usuario_id INT UNSIGNED NOT NULL,

    numero_credencial VARCHAR(64) NOT NULL,

    fecha_emision DATETIME
        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    estado ENUM('activa', 'inactiva')
        NOT NULL DEFAULT 'activa',

    CONSTRAINT uq_credenciales_usuario
        UNIQUE (usuario_id),

    CONSTRAINT uq_credenciales_numero
        UNIQUE (numero_credencial),

    CONSTRAINT uq_credenciales_id_usuario
        UNIQUE (id, usuario_id),

    CONSTRAINT fk_credenciales_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;



CREATE TABLE IF NOT EXISTS vehiculos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    usuario_id INT UNSIGNED NOT NULL,

    patente VARCHAR(10) NOT NULL,

    marca VARCHAR(50) NOT NULL,

    modelo VARCHAR(50) NOT NULL,

    anio SMALLINT UNSIGNED NULL,

    chasis VARCHAR(50) NOT NULL,
    motor VARCHAR(50) NOT NULL,

    estado ENUM('activo', 'inactivo')
        NOT NULL DEFAULT 'activo',

    fecha_alta DATETIME
        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_vehiculos_patente
        UNIQUE (patente),

    CONSTRAINT uq_vehiculos_id_usuario
        UNIQUE (id, usuario_id),

    INDEX idx_vehiculos_usuario (usuario_id),

    CONSTRAINT fk_vehiculos_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT chk_vehiculos_anio
        CHECK (anio IS NULL OR anio BETWEEN 1900 AND 2155),
    CONSTRAINT chk_vehiculos_chasis
        CHECK (chasis IS NULL OR CHAR_LENGTH(TRIM(chasis)) > 0),
    CONSTRAINT chk_vehiculos_motor
        CHECK (motor IS NULL OR CHAR_LENGTH(TRIM(motor)) > 0)
) ENGINE=InnoDB;



CREATE TABLE IF NOT EXISTS aseguradoras (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(100) NOT NULL,

    cuit VARCHAR(13) NULL,

    estado ENUM('activa', 'inactiva')
        NOT NULL DEFAULT 'activa',

    fecha_alta DATETIME
        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_aseguradoras_nombre
        UNIQUE (nombre),

    CONSTRAINT uq_aseguradoras_cuit
        UNIQUE (cuit)
) ENGINE=InnoDB;



CREATE TABLE IF NOT EXISTS seguros (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    usuario_id INT UNSIGNED NOT NULL,

    credencial_id INT UNSIGNED NOT NULL,

    vehiculo_id INT UNSIGNED NOT NULL,

    aseguradora_id INT UNSIGNED NOT NULL,

    nro_poliza VARCHAR(50) NOT NULL,

    cobertura VARCHAR(100) NOT NULL,

    vigencia_desde DATE NOT NULL,

    vigencia_hasta DATE NOT NULL,

    estado ENUM(
        'pendiente',
        'vigente',
        'vencido',
        'cancelado'
    ) NOT NULL DEFAULT 'vigente',

    fecha_alta DATETIME
        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_seguros_aseguradora_poliza
        UNIQUE (aseguradora_id, nro_poliza),

    INDEX idx_seguros_usuario (usuario_id),

    INDEX idx_seguros_credencial (credencial_id),

    INDEX idx_seguros_vehiculo (vehiculo_id),

    INDEX idx_seguros_aseguradora (aseguradora_id),

    INDEX idx_seguros_vigencia
        (vigencia_desde, vigencia_hasta),

    CONSTRAINT fk_seguros_credencial_usuario
        FOREIGN KEY (credencial_id, usuario_id)
        REFERENCES credenciales(id, usuario_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_seguros_vehiculo_usuario
        FOREIGN KEY (vehiculo_id, usuario_id)
        REFERENCES vehiculos(id, usuario_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_seguros_aseguradora
        FOREIGN KEY (aseguradora_id)
        REFERENCES aseguradoras(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_seguros_vigencia
        CHECK (vigencia_hasta >= vigencia_desde)
) ENGINE=InnoDB;

-- Cuentas de acceso: admin/Admin123! y usuario_prueba/Usuario123!
START TRANSACTION;
INSERT INTO usuarios (nombres,apellidos,nombre_usuario,email,password_hash,perfil,dni,fecha_nacimiento) VALUES
('Admin','Del Sistema','admin','admin@tp.com','$2y$10$ZEdo78PrexNAvp77sfTugeWF2D70zhs9NpKucX1WCIB38ZdC3PfGm','admin',NULL,'1990-01-01'),
('Usuario','De Prueba','usuario_prueba','usuario.prueba@tp.com','$2y$10$okyKdH/ATQ2jYXuEFVDMmu7iYniHhDgUt3rKKDVzH3/Wbp8tJv0ye','usuario','30123456','1995-05-20');
SET @usuario_evaluacion_id := (SELECT id FROM usuarios WHERE nombre_usuario='usuario_prueba');
INSERT INTO credenciales (usuario_id,numero_credencial) VALUES (@usuario_evaluacion_id,'CRED-PRUEBA-0001');
SET @credencial_evaluacion_id := LAST_INSERT_ID();
INSERT INTO vehiculos (usuario_id,patente,marca,modelo,anio,chasis,motor)
VALUES (@usuario_evaluacion_id,'AB123CD','Volkswagen','Gol Trend',2018,'CHASIS-DEMO-0001','MOTOR-DEMO-0001');
SET @vehiculo_evaluacion_id := LAST_INSERT_ID();
INSERT INTO aseguradoras (nombre) VALUES ('Aseguradora Demo');
SET @aseguradora_evaluacion_id := LAST_INSERT_ID();
INSERT INTO seguros (usuario_id,credencial_id,vehiculo_id,aseguradora_id,nro_poliza,cobertura,vigencia_desde,vigencia_hasta)
VALUES (@usuario_evaluacion_id,@credencial_evaluacion_id,@vehiculo_evaluacion_id,@aseguradora_evaluacion_id,'POL-DEMO-0001','Terceros Completo','2026-01-01','2026-12-31');
COMMIT;
