-- =========================================================
-- Migración: agregar nombre_usuario (el "ID" que pide la consigna)
-- =========================================================
-- La consigna original pide un "ID (único)" en el registro, separado
-- del correo electrónico, y en Validaciones habla de "nombre de
-- usuario". Hoy la base solo tiene email como identificador único.
-- Esto agrega esa columna sin tocar el script oficial de Juanpi.
--
-- Correr DESPUÉS de schema.sql y migracion_nombres_apellidos.sql.
-- Avisarle a Juanpi para que la sume a su script oficial.

USE tp_programacion_sistemas;

-- 1) Se agrega nullable primero porque la tabla puede ya tener filas.
ALTER TABLE usuarios
    ADD COLUMN nombre_usuario VARCHAR(50) NULL AFTER id;

-- 2) Se completa con un valor temporal cualquier fila que ya exista
--    (por ejemplo si ya cargaste usuarios de prueba antes de esta migración).
UPDATE usuarios
SET nombre_usuario = CONCAT('usuario', id)
WHERE nombre_usuario IS NULL;

-- 3) Ahora sí se puede exigir único y obligatorio.
ALTER TABLE usuarios
    ADD UNIQUE KEY uq_usuarios_nombre_usuario (nombre_usuario);

ALTER TABLE usuarios
    MODIFY nombre_usuario VARCHAR(50) NOT NULL;
