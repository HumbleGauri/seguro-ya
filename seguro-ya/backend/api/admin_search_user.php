<?php
/**
 * GET /api/admin_search_user.php?email=...
 * Buscador de cuentas por email. Solo perfil admin. (Mejora opcional)
 */
require __DIR__ . '/../includes/bootstrap.php';

requiere_admin();

$email = trim($_GET['email'] ?? '');

if ($email === '') {
    json_error('Falta el parámetro email.');
}

$stmt = $pdo->prepare(
    'SELECT id, nombre_usuario, email, nombres, apellidos, perfil, fecha_nacimiento, fecha_registro
     FROM usuarios
     WHERE email LIKE :email
     ORDER BY fecha_registro DESC'
);
$stmt->execute(['email' => '%' . $email . '%']);
$usuarios = $stmt->fetchAll();

json_ok(['usuarios' => $usuarios]);
