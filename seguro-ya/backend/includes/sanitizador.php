<?php
/**
 * Sanitización centralizada de las entradas del usuario contra XSS
 * (inyección de HTML/JavaScript en campos de texto).
 *
 * La inyección SQL ya está cubierta en todo el backend porque se usan
 * exclusivamente consultas preparadas con PDO. El riesgo que cubre este
 * archivo es distinto: que alguien guarde algo como
 * '<script>...</script>' en un campo de texto (nombre, patente,
 * aseguradora, etc.) y que ese contenido se termine ejecutando después,
 * al mostrarse en "Mi credencial", "Mis datos" o algún panel de admin.
 *
 * Se engancha automáticamente desde:
 *  - includes/bootstrap.php, que sanitiza $_GET y $_POST completos
 *    apenas arranca cada request (cubre, por ejemplo, admin_search_user.php
 *    que lee $_GET['email'] directamente).
 *  - includes/response.php -> leer_body(), que sanitiza el body JSON
 *    antes de devolverlo (cubre register.php, login.php,
 *    recover_password.php, reset_password.php, verify_recovery_token.php).
 *
 * Por eso ningún endpoint necesita llamar nada de este archivo a mano:
 * para cuando el código de cada endpoint recibe los datos, ya llegan
 * sanitizados.
 */

/**
 * Campos que NUNCA se tocan. Si se les aplicara strip_tags/trim se
 * modificaría la contraseña real que el usuario escribió, sin que se
 * entere: el hash que se guarda ya no coincidiría con lo que tipeó, o
 * (peor) coincidiría con una versión "recortada" sin que eso sea evidente.
 * Las contraseñas tampoco se muestran nunca como HTML, así que no hay
 * riesgo de XSS que mitigar ahí.
 */
const SANITIZADOR_CAMPOS_EXCLUIDOS = [
    'password',
    'nueva_password',
    'password_actual',
    'password_confirmacion',
    'contrasena',
    'contraseña',
];

/**
 * Limpia un string de uso general: recorta espacios, quita bytes nulos y
 * caracteres de control invisibles, y elimina cualquier etiqueta HTML o
 * de script. No se usa htmlspecialchars() a propósito: esto sanitiza lo
 * que se GUARDA en la base, no lo que se imprime en una página HTML
 * tradicional (el frontend consume JSON), así que lo que interesa es que
 * no pueda quedar markup ejecutable en el dato, no escaparlo para un
 * contexto HTML puntual.
 */
function sanitizar_texto(string $valor): string
{
    // Bytes nulos y caracteres de control (se preservan salto de línea y tab).
    $valor = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $valor);
    // Quita <script>, <img onerror=...>, <a href="javascript:...">, etc.
    $valor = strip_tags($valor);
    return trim($valor);
}

/**
 * Aplica sanitizar_texto recursivamente sobre strings, recorriendo
 * arrays anidados (como "vehiculo" y "seguro" en el registro), sin tocar
 * los campos listados en SANITIZADOR_CAMPOS_EXCLUIDOS en ningún nivel.
 */
function sanitizar_valor($valor, ?string $clave = null)
{
    if ($clave !== null && in_array($clave, SANITIZADOR_CAMPOS_EXCLUIDOS, true)) {
        return $valor;
    }
    if (is_string($valor)) {
        return sanitizar_texto($valor);
    }
    if (is_array($valor)) {
        $resultado = [];
        foreach ($valor as $k => $v) {
            $resultado[$k] = sanitizar_valor($v, is_string($k) ? $k : null);
        }
        return $resultado;
    }
    // Números, booleanos, null: no hay nada que sanitizar.
    return $valor;
}

/**
 * Punto de entrada para sanitizar un array completo de datos de entrada
 * (el body ya decodificado, $_POST o $_GET).
 */
function sanitizar_datos(array $datos): array
{
    return sanitizar_valor($datos);
}
