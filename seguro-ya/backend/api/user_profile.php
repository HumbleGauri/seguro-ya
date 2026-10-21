<?php

require __DIR__ . '/../includes/bootstrap.php';

requiere_login();

$stmt = $pdo->prepare(
    'SELECT nombre_usuario, nombres, apellidos, email,
            fecha_nacimiento, fecha_registro, estado
     FROM usuarios
     WHERE id = :id
     LIMIT 1'
);
$stmt->execute(['id' => $_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

if (!$usuario) {
    json_error('No se encontraron los datos del usuario.', 404);
}

json_ok(['usuario' => $usuario]);
