<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_corbata = isset($_GET['id']) ? intval($_GET['id']) : 0;
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';

if ($id_corbata > 0 && isset($_SESSION['carrito'][$id_corbata])) {
    
    // Conexión para verificar el stock máximo en caliente
    $host = "db"; $db = "mydatabase"; $user_db = "myuser"; $password_db = "mypassword";
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user_db, $password_db);
        $stmt = $pdo->prepare("SELECT stock FROM CORBATA WHERE id_corbata = :id");
        $stmt->execute(['id' => $id_corbata]);
        $stock_disponible = $stmt->fetchColumn();

        if ($accion === 'sumar') {
            if ($_SESSION['carrito'][$id_corbata]['cantidad'] < $stock_disponible) {
                $_SESSION['carrito'][$id_corbata]['cantidad']++;
            }
        } elseif ($accion === 'restar') {
            $_SESSION['carrito'][$id_corbata]['cantidad']--;
            // Si baja de 1 unidad, eliminamos la línea del carrito
            if ($_SESSION['carrito'][$id_corbata]['cantidad'] <= 0) {
                unset($_SESSION['carrito'][$id_corbata]);
            }
        } elseif ($accion === 'fijar' && isset($_GET['cantidad'])) {
            $nueva_cant = intval($_GET['cantidad']);
            if ($nueva_cant <= 0) {
                unset($_SESSION['carrito'][$id_corbata]);
            } else {
                // Limitamos la cantidad asignada al stock real
                $_SESSION['carrito'][$id_corbata]['cantidad'] = min($nueva_cant, $stock_disponible);
            }
        }

    } catch (PDOException $e) {
        // En caso de fallo de BD, permitimos restar pero congelamos sumar
        if ($accion === 'restar') {
            $_SESSION['carrito'][$id_corbata]['cantidad']--;
            if ($_SESSION['carrito'][$id_corbata]['cantidad'] <= 0) {
                unset($_SESSION['carrito'][$id_corbata]);
            }
        }
    }
}

header("Location: carrito.php");
exit;