<?php
// 1. Cargar el autoloader global (inicia la sesión de forma segura)
require_once __DIR__ . '/helpers/autoload.php';

// Capturar e higienizar el ID del producto
$id_corbata = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_corbata <= 0) {
    header("Location: catalogo.php");
    exit;
}

try {
    $pdo = obtener_conexion_db();

    // Consultar el producto en la base de datos para verificar su existencia y stock
    $stmt = $pdo->prepare("SELECT id_corbata, color, material, marca, precio, stock, imagen FROM CORBATA WHERE id_corbata = :id");
    $stmt->execute(['id' => $id_corbata]);
    $corbata = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($corbata && $corbata['stock'] > 0) {
        // Inicializar el carrito en la sesión si no existe
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        // Si el producto ya está en el carrito, incrementamos la cantidad (sin superar el stock)
        if (isset($_SESSION['carrito'][$id_corbata])) {
            if ($_SESSION['carrito'][$id_corbata]['cantidad'] < $corbata['stock']) {
                $_SESSION['carrito'][$id_corbata]['cantidad']++;
            }
        } else {
            // Añadir nuevo producto al carrito
            $nombre_producto = "Corbata de " . ($corbata['material'] ?? '') . ' ' . ($corbata['color'] ?? '');
            $_SESSION['carrito'][$id_corbata] = [
                'id_corbata' => $corbata['id_corbata'],
                'nombre'     => $nombre_producto,
                'precio'     => $corbata['precio'],
                'imagen'     => $corbata['imagen'] ?? '',
                'cantidad'   => 1
            ];
        }
    }
} catch (PDOException $e) {
    registrar_error_log("Error al añadir al carrito: " . $e->getMessage(), "CARRITO_ERROR");
}

header("Location: carrito.php");
exit;