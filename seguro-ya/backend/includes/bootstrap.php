<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Preflight de CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// --- Sesión ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/sanitizador.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/validators.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/log.php';

// Sanitiza contra XSS apenas arranca el request: cubre los endpoints que
// leen directamente de $_GET (admin_search_user.php) o de $_POST como
// fallback de leer_body() (ver response.php para el body JSON).
if (!empty($_GET)) {
    $_GET = sanitizar_datos($_GET);
}
if (!empty($_POST)) {
    $_POST = sanitizar_datos($_POST);
}
