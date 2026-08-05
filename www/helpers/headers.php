<?php
/**
 * Inyecta las cabeceras HTTP de seguridad globales (OWASP Recommended Headers).
 */
function aplicar_cabeceras_seguridad(): void {
    // Evita enviar cabeceras duplicadas si la salida HTML ya comenzó
    if (headers_sent()) {
        return;
    }

    //Prevención contra Clickjacking
    header("X-Frame-Options: DENY");

    //Prevención contra MIME-type Sniffing
    header("X-Content-Type-Options: nosniff");

    //Control de fuga de información en Referrer
    header("Referrer-Policy: strict-origin-when-cross-origin");

    //Activación de filtro XSS para navegadores legacy
    header("X-XSS-Protection: 1; mode=block");

    //Content Security Policy (CSP) adaptado al Atelier (Tailwind + Scripts locales)
    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; " .
           "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
           "font-src 'self' https://fonts.gstatic.com; " .
           "img-src 'self' data: https:; " .
           "connect-src 'self'; " .
           "frame-ancestors 'none';";

    header("Content-Security-Policy: " . $csp);
}