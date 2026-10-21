<?php

function json_response($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error(string $mensaje, int $status = 400): void
{
    json_response(['ok' => false, 'error' => $mensaje], $status);
}

function json_ok(array $data = []): void
{
    json_response(array_merge(['ok' => true], $data), 200);
}

function leer_body(): array
{
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);

    $datos = is_array($json) ? $json : $_POST;

    // Sanitiza contra XSS acá, en el único punto por el que pasan todos
    // los endpoints que reciben JSON (register.php, login.php,
    // recover_password.php, reset_password.php, verify_recovery_token.php).
    return sanitizar_datos($datos);
}
