<?php
// Cargar el gestor de seguridad global
require_once __DIR__ . '/helpers/autoload.php';

// Bloqueo si no está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_corbata = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id_user    = $_SESSION['id_usuario'];

if ($id_corbata <= 0) {
    header("Location: catalogo.php");
    exit;
}

try {
    $pdo = obtener_conexion_db();

    //Comprobar si ya existe en favoritos
    $stmtCheck = $pdo->prepare("SELECT 1 FROM FAVORITO WHERE id_user = :id_user AND id_corbata = :id_corbata");
    $stmtCheck->execute(['id_user' => $id_user, 'id_corbata' => $id_corbata]);
    
    if ($stmtCheck->fetch()) {
        // Ya existe -> Lo quitamos
        $stmtDel = $pdo->prepare("DELETE FROM FAVORITO WHERE id_user = :id_user AND id_corbata = :id_corbata");
        $stmtDel->execute(['id_user' => $id_user, 'id_corbata' => $id_corbata]);
    } else {
        // No existe -> Lo añadimos
        $stmtIns = $pdo->prepare("INSERT INTO FAVORITO (id_user, id_corbata) VALUES (:id_user, :id_corbata)");
        $stmtIns->execute(['id_user' => $id_user, 'id_corbata' => $id_corbata]);
    }

} catch (PDOException $e) {
    // Error silencioso en producción (ya lo gestiona la función centralizada)
}

//Redirección segura evitando Header Injection
$redirect = 'cuenta.php?tab=favoritos';
if (isset($_SERVER['HTTP_REFERER'])) {
    // Sanitizamos la cabecera limpiando saltos de línea y caracteres nocivos
    $referer_limpio = filter_var($_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL);
    if ($referer_limpio) {
        $redirect = $referer_limpio;
    }
}

header("Location: " . $redirect);
exit;