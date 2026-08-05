<?php
//Cargar el autoloader para que se active el manejador global de excepciones
require_once __DIR__ . '/helpers/autoload.php';

//Lanzar una excepción de prueba simulada
throw new Exception("Prueba de auditoría de errores: Conexión o consulta fallida en la tienda.");