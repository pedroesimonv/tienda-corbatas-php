<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Genera un token CSRF único por sesión si no existe aún.
 * Utiliza random_bytes() para máxima seguridad criptográfica.
 */
function obtener_token_csrf(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Retorna un campo HTML hidden pre-maquetado para incluir directamente en los formularios.
 */
function campo_csrf(): string {
    $token = obtener_token_csrf();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Comprueba que el token enviado por POST coincida de forma estricta con el guardado en la sesión.
 * Utiliza hash_equals() para prevenir ataques de temporización (timing attacks).
 */
function validar_token_csrf(): bool {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token_recibido = $_POST['csrf_token'] ?? '';
        $token_sesion   = $_SESSION['csrf_token'] ?? '';

        if (empty($token_recibido) || empty($token_sesion) || !hash_equals($token_sesion, $token_recibido)) {
            return false;
        }
    }
    return true;
}

/**
 * Cancela inmediatamente la ejecución si la validación CSRF falla.
 */
function verificar_csrf_or_die(): void {
    if (!validar_token_csrf()) {
        http_response_code(403);
        die("❌ Error de Seguridad (CSRF): Petición no autorizada o sesión caducada. Por favor, vuelve atrás y recarga la página.");
    }
}