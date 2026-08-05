<?php
function e(?string $cadena): string {
    if ($cadena === null) {
        return '';
    }
    return htmlspecialchars($cadena, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Limpia espacios innecesarios y remueve etiquetas HTML nocivas de un input de texto.
 */
function sanear_string(?string $cadena): string {
    if ($cadena === null) {
        return '';
    }
    return trim(strip_tags($cadena));
}

/**
 * Limpia recursivamente un array de datos, aplicando sanear_string a cada valor de tipo string.
 * Esto es útil para sanitizar datos de entrada, como $_POST o $_GET.
 */
function sanear_array(array $datos): array {
    $limpio = [];
    foreach ($datos as $clave => $valor) {
        if (is_array($valor)) {
            $limpio[$clave] = sanear_array($valor);
        } elseif (is_string($valor)) {
            $limpio[$clave] = sanear_string($valor);
        } else {
            $limpio[$clave] = $valor;
        }
    }
    return $limpio;
}