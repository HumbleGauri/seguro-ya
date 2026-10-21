<?php
/**
 * Helpers de autenticación y sesión.
 */

function hashear_password(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function verificar_password(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

function iniciar_sesion_usuario(array $usuario): void
{
    // Regenerar el ID de sesión al loguear evita "session fixation".
    session_regenerate_id(true);
    $_SESSION['usuario_id']     = $usuario['id'];
    $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'] ?? null;
    $_SESSION['email']         = $usuario['email'];
    $_SESSION['perfil']        = $usuario['perfil'];
}

function cerrar_sesion_usuario(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function sesion_activa(): bool
{
    return isset($_SESSION['usuario_id']);
}

function es_admin(): bool
{
    return sesion_activa() && ($_SESSION['perfil'] ?? '') === 'admin';
}

/**
 * Corta la ejecución con 401 si no hay sesión activa.
 * Usar al principio de cualquier endpoint protegido.
 */
function requiere_login(): void
{
    if (!sesion_activa()) {
        json_error('Debe iniciar sesión.', 401);
    }
}

/**
 * Corta la ejecución con 403 si el usuario no es admin.
 * OJO: esto se valida siempre en el backend, nunca alcanza con
 * ocultar el botón en el frontend.
 */
function requiere_admin(): void
{
    requiere_login();
    if (!es_admin()) {
        json_error('Acceso restringido a administradores.', 403);
    }
}
