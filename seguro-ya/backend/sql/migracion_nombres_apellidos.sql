-- =========================================================
-- Migración: agregar nombres y apellidos a usuarios
-- =========================================================
-- El formulario de registro del frontend (Marcos) pide nombres y
-- apellidos, pero el script original de Juanpi no tenía esas columnas.
-- Correr esto UNA VEZ sobre una base ya creada con schema.sql,
-- y avisarle a Juanpi para que lo sume a su script oficial.

USE tp_programacion_sistemas;

ALTER TABLE usuarios
    ADD COLUMN nombres VARCHAR(100) NULL AFTER email,
    ADD COLUMN apellidos VARCHAR(100) NULL AFTER nombres;
