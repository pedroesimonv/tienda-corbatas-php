<?php
/**
 * Retorna la conexión PDO centralizada a la Base de Datos.
 */
function obtener_conexion_db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $host = 'db';
        $dbname = 'mydatabase';
        $user = 'myuser';
        $pass = 'mypassword';
        $port = '3306';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            if (function_exists('registrar_error_log')) {
                registrar_error_log("Error PDO db.php: " . $e->getMessage(), "DB_CONNECT");
            }
            throw new Exception("Error de conexión con la base de datos.");
        }
    }

    return $pdo;
}