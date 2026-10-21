<?php
/**
 * Funciones de validación reutilizadas por register.php y reset_password.php
 */

function validar_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Contraseña fuerte: mínimo 8 caracteres, al menos una mayúscula,
 * un número y un símbolo (según Criterios de Aprobación del TP).
 */
function validar_password_fuerte(string $password): bool
{
    if (strlen($password) < 8) {
        return false;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        return false; // al menos un símbolo
    }
    return true;
}

function existe_email(PDO $pdo, string $email): bool
{
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    return $stmt->fetch() !== false;
}

/**
 * Nombre de usuario ("ID" en la consigna): solo letras, números,
 * punto y guión bajo, entre 3 y 50 caracteres.
 */
function validar_nombre_usuario(string $nombreUsuario): bool
{
    return (bool) preg_match('/^[A-Za-z0-9_.]{3,50}$/', $nombreUsuario);
}

function existe_nombre_usuario(PDO $pdo, string $nombreUsuario): bool
{
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE nombre_usuario = :nombre_usuario LIMIT 1');
    $stmt->execute(['nombre_usuario' => $nombreUsuario]);
    return $stmt->fetch() !== false;
}

/**
 * Valida (opcionalmente) el formato de fecha de nacimiento YYYY-MM-DD.
 * Es un campo opcional (mejora), por eso permite null/"".
 */
function validar_fecha_nacimiento(?string $fecha): bool
{
    if ($fecha === null || $fecha === '') {
        return true;
    }
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
}
