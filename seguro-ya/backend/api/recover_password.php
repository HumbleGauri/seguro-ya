<?php

require __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/password_recovery.php';
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Método no permitido.', 405);

try {
    $email = recovery_email(leer_body());
    request_password_recovery($pdo, $email);
    json_ok(['mensaje' => RECOVERY_REQUEST_MESSAGE]);
} catch (InvalidArgumentException $error) {
    json_error($error->getMessage(), 400);
} catch (Throwable $error) {
    json_error('No se pudo iniciar la recuperación.', 500);
}
