<?php
/**
 * Retorna una instancia única de PDO (Patrón Singleton básico).
 */
function obtener_conexion_db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $host     = "db";
        $db       = "mydatabase";
        $user     = "myuser";
        $password = "mypassword";

        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $password, $opciones);
        } catch (PDOException $e) {
            // En producción ocultamos detalles técnicos y registramos el error
            error_log("Error de conexión a la BD: " . $e->getMessage());
            die("❌ Error de conexión con la base de datos. Por favor, inténtalo más tarde.");
        }
    }

    return $pdo;
}