-- =========================================================
-- Datos de prueba para desarrollo (NO forma parte del script
-- oficial de Juanpi). Correr después de schema.sql,
-- migracion_nombres_apellidos.sql Y migracion_nombre_usuario.sql.
-- =========================================================

USE tp_programacion_sistemas;

-- Admin: usuario "admin" / email admin@tp.com / password Admin123!
INSERT INTO usuarios (nombre_usuario, email, password_hash, perfil, nombres, apellidos, fecha_nacimiento)
VALUES (
    'admin',
    'admin@tp.com',
    '$2y$12$mQWpQhwJBU.7roHsOPPtJe4mdCHdFu7HP3UXHQ1ata8srdb8E.sae',
    'admin',
    'Admin',
    'Del Sistema',
    '1990-01-01'
);
-- (el hash corresponde a la contraseña Admin123! generada con password_hash() de PHP)

-- Usuario de prueba con credencial de seguro cargada
-- usuario: usuario_prueba / email: usuario.prueba@tp.com / password: Usuario123!
INSERT INTO usuarios (nombre_usuario, email, password_hash, perfil, nombres, apellidos, fecha_nacimiento)
VALUES (
    'usuario_prueba',
    'usuario.prueba@tp.com',
    '$2y$12$Setd2QYhNfWbrxxtHDTuK.T3evZK8Vrh30ldg3biYWffCDSMkQZ5q',
    'usuario',
    'Usuario',
    'De Prueba',
    '1995-05-20'
);

-- Recordar: credenciales_seguro.usuario_id es UNIQUE (1 credencial por usuario)
INSERT INTO credenciales_seguro (
    usuario_id, aseguradora, nro_poliza, asegurado_nombre, asegurado_dni,
    vehiculo_patente, vehiculo_marca, vehiculo_modelo, cobertura,
    vigencia_desde, vigencia_hasta
) VALUES (
    (SELECT id FROM usuarios WHERE email = 'usuario.prueba@tp.com'),
    'La Segunda Seguros',
    'POL-2025-001234',
    'Usuario de Prueba',
    '30123456',
    'AB123CD',
    'Volkswagen',
    'Gol Trend',
    'Terceros Completo',
    '2025-01-01',
    '2026-01-01'
);
