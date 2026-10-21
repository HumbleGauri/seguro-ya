<?php
/**
 * POST /api/login.php
 * Body JSON: { "nombre_usuario": "...", "password": "..." }
 * (también acepta { "email": "...", "password": "..." } por compatibilidad)
 */
require __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método no permitido.', 405);
}

$body = leer_body();
$nombreUsuario = trim($body['nombre_usuario'] ?? '');
$email = trim($body['email'] ?? '');
$password = (string)($body['password'] ?? '');

if (($nombreUsuario === '' && $email === '') || $password === '') {
    json_error('Nombre de usuario y contraseña son obligatorios.');
}

// Se prioriza nombre_usuario (lo que pide la consigna); email queda como
// alternativa por si algún cliente viejo todavía lo manda.
if ($nombreUsuario !== '') {
    $identificador = $nombreUsuario;
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE nombre_usuario = :identificador LIMIT 1');
} else {
    $identificador = $email;
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = :identificador LIMIT 1');
}
$stmt->execute(['identificador' => $identificador]);
$usuario = $stmt->fetch();

if (!$usuario || !verificar_password($password, $usuario['password_hash'])) {
    // Registrar el intento fallido en el log de auditoría (guarda lo que
    // se haya escrito, sea nombre de usuario o email).
    registrar_acceso($pdo, $usuario['id'] ?? null, 'fallido', $identificador);
    json_error('Usuario o contraseña incorrectos.', 401);
}

iniciar_sesion_usuario($usuario);
registrar_acceso($pdo, $usuario['id'], 'exitoso', $identificador);

json_ok([
    'perfil'         => $usuario['perfil'],
    'nombre_usuario' => $usuario['nombre_usuario'],
    'email'          => $usuario['email'],
]);
