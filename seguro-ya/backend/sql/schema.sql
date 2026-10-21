-- =========================================================
-- TP Programación de Sistemas 2025 - Script de Base de Datos
-- =========================================================
-- Incluye el alta conjunta de usuario, vehículo y seguro.
-- Los datos de prueba están aparte, en seed_test_data.sql.

CREATE DATABASE IF NOT EXISTS tp_programacion_sistemas
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE tp_programacion_sistemas;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    dni VARCHAR(8) NULL,

    email VARCHAR(255) NOT NULL UNIQUE,

    password_hash VARCHAR(255) NOT NULL,

    perfil ENUM('usuario', 'admin')
        NOT NULL DEFAULT 'usuario',

    fecha_nacimiento DATE NULL,

    fecha_registro DATETIME
        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_usuarios_dni UNIQUE (dni)
) ENGINE=InnoDB;

-- DNI nullable para cuentas anteriores; register.php lo exige en altas nuevas.
CREATE TABLE IF NOT EXISTS vehiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    anio SMALLINT UNSIGNED NOT NULL,
    patente VARCHAR(10) NOT NULL,
    chasis VARCHAR(50) NOT NULL,
    motor VARCHAR(50) NOT NULL,
    fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_vehiculos_patente UNIQUE (patente),
    CONSTRAINT uq_vehiculos_id_usuario UNIQUE (id, usuario_id),
    CONSTRAINT fk_vehiculos_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT chk_vehiculos_anio CHECK (anio BETWEEN 1900 AND 2155)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS log_accesos (
    id INT AUTO_INCREMENT PRIMARY KEY,

    usuario_id INT NULL,

    email_intentado VARCHAR(255) NULL,

    fecha_hora DATETIME
        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    resultado ENUM('exitoso', 'fallido')
        NOT NULL,

    ip VARCHAR(45) NULL,

    CONSTRAINT fk_log_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS recuperacion_password (
    id INT AUTO_INCREMENT PRIMARY KEY,

    usuario_id INT NOT NULL,

    token VARCHAR(64) NOT NULL UNIQUE,

    expira DATETIME NOT NULL,

    usado TINYINT(1) NOT NULL DEFAULT 0,

    CONSTRAINT fk_recuperacion_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS credenciales_seguro (
    id INT AUTO_INCREMENT PRIMARY KEY,

    usuario_id INT NOT NULL UNIQUE,
    vehiculo_id INT NULL,

    aseguradora VARCHAR(100) NOT NULL,

    nro_poliza VARCHAR(50) NOT NULL,

    asegurado_nombre VARCHAR(201) NOT NULL,

    asegurado_dni VARCHAR(20) NOT NULL,

    vehiculo_patente VARCHAR(10) NOT NULL,

    vehiculo_marca VARCHAR(50) NOT NULL,

    vehiculo_modelo VARCHAR(50) NOT NULL,

    cobertura VARCHAR(100) NOT NULL,

    vigencia_desde DATE NOT NULL,

    vigencia_hasta DATE NOT NULL,

    CONSTRAINT chk_credencial_vigencia CHECK (vigencia_hasta >= vigencia_desde),
    CONSTRAINT fk_credencial_vehiculo_usuario
        FOREIGN KEY (vehiculo_id, usuario_id)
        REFERENCES vehiculos(id, usuario_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_credencial_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;
