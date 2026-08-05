<?php
// Configuración de Seguridad para Cookies de Sesión (Session Cookie Hardening)
if (session_status() === PHP_SESSION_NONE) {
    $es_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Strict');
    if ($es_https) {
        ini_set('session.cookie_secure', 1);
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $es_https,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    session_start();
}

// Cargar e inyectar cabeceras HTTP de seguridad
require_once __DIR__ . '/headers.php';
aplicar_cabeceras_seguridad();

// Cargar gestión de logs y excepciones
require_once __DIR__ . '/logger.php';

// Cargar el resto de helpers globales
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/sanitizer.php';