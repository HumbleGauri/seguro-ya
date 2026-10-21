<?php

require __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/password_recovery.php';
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Método no permitido.', 405);

try {
    $body = leer_body();
    verify_password_recovery($pdo, recovery_email($body), recovery_token($body));
    json_ok(['mensaje' => 'Token validado. Podés ingresar tu nueva contraseña.']);
} catch (InvalidArgumentException $error) {
    json_error($error->getMessage(), 400);
} catch (Throwable $error) {
    json_error('No se pudo validar la solicitud.', 500);
}
