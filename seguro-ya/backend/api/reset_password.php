<?php

require __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/password_recovery.php';
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Método no permitido.', 405);

try {
    $body = leer_body();
    $email = recovery_email($body);
    $token = recovery_token($body);
    $password = $body['nueva_password'] ?? null;
    if (!is_string($password) || !validar_password_fuerte($password)) {
        throw new InvalidArgumentException('La nueva contraseña debe tener mínimo 8 caracteres, una mayúscula, un número y un símbolo.');
    }
    reset_recovered_password($pdo, $email, $token, $password);
    json_ok(['mensaje' => 'Contraseña actualizada correctamente.']);
} catch (InvalidArgumentException $error) {
    json_error($error->getMessage(), 400);
} catch (Throwable $error) {
    json_error('No se pudo actualizar la contraseña.', 500);
}
