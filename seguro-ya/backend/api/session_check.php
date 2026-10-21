<?php
/**
 * GET /api/session_check.php
 * Le permite al frontend (Marcos) saber si hay una sesión activa
 * y de qué perfil, por ejemplo al recargar la página.
 */
require __DIR__ . '/../includes/bootstrap.php';

if (!sesion_activa()) {
    json_ok(['logueado' => false]);
}

json_ok([
    'logueado'       => true,
    'nombre_usuario' => $_SESSION['nombre_usuario'] ?? null,
    'email'          => $_SESSION['email'],
    'perfil'         => $_SESSION['perfil'],
]);
