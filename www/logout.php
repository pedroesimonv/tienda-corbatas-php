<?php
// Cargar el autoloader para inicializar la sesión actual con sus parámetros de seguridad
require_once __DIR__ . '/helpers/autoload.php';

//Vaciar el array global de sesión
$_SESSION = array();

//Si se utilizó una cookie para la sesión, borrarla también del navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000, // Fecha en el pasado para obligar al navegador a eliminarla
        $params["path"], 
        $params["domain"], 
        $params["secure"], 
        $params["httponly"]
    );
}

//Destruir la sesión en el servidor
session_destroy();

//Redirigir limpiamente a la página principal
header("Location: index.php");
exit;