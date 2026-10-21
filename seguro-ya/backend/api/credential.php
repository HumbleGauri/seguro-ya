<?php

require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/credential.php';

requiere_login();

$credencial = obtener_credencial($pdo, (int)$_SESSION['usuario_id']);

if (!$credencial) {
    json_error('Todavía no tenés una credencial de seguro cargada.', 404);
}

json_ok(['credencial' => $credencial]);
