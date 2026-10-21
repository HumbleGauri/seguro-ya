-- Ejecutar UNA VEZ en la base existente usada por backend/config/db.php.
-- Requiere nombres, apellidos y nombre_usuario (migraciones anteriores).
-- Corresponde al esquema backend/sql/schema.sql, con id INT.
-- Para una instalación nueva, ejecutar solamente schema.sql.
USE tp_programacion_sistemas;

ALTER TABLE usuarios ENGINE=InnoDB;
ALTER TABLE usuarios
    ADD COLUMN dni VARCHAR(8) NULL AFTER apellidos,
    ADD CONSTRAINT uq_usuarios_dni UNIQUE (dni);

CREATE TABLE vehiculos (
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

ALTER TABLE credenciales_seguro ENGINE=InnoDB;
ALTER TABLE credenciales_seguro
    ADD COLUMN vehiculo_id INT NULL AFTER usuario_id,
    MODIFY asegurado_nombre VARCHAR(201) NOT NULL,
    ADD CONSTRAINT fk_credencial_vehiculo_usuario
        FOREIGN KEY (vehiculo_id, usuario_id)
        REFERENCES vehiculos(id, usuario_id) ON DELETE CASCADE;

-- Las credenciales anteriores conservan vehiculo_id NULL; no se inventan
-- sus números de motor, chasis ni año. Cada alta nueva guarda los tres registros.
