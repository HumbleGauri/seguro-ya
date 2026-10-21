<?php
/**
 * POST /api/logout.php
 */
require __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método no permitido.', 405);
}

cerrar_sesion_usuario();

json_ok(['mensaje' => 'Sesión cerrada.']);
