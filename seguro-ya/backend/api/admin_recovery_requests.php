<?php

require __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/password_recovery.php';
header('Cache-Control: no-store');
requiere_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_error('Método no permitido.', 405);

try {
    json_ok(['solicitudes' => list_recovery_requests($pdo)]);
} catch (Throwable $error) {
    json_error('No se pudieron consultar las solicitudes de recupero.', 500);
}
