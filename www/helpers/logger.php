<?php
/**
 * Escribe un mensaje de error en el archivo de log privado del servidor.
 */
function registrar_error_log(string $mensaje, string $origen = 'GENERAL'): void {
    $dir_logs = __DIR__ . '/../logs';
    
    // Crear la carpeta logs si no existe
    if (!is_dir($dir_logs)) {
        @mkdir($dir_logs, 0750, true);
    }

    $archivo_log = $dir_logs . '/app_errors.log';
    $fecha = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $uri = $_SERVER['REQUEST_URI'] ?? 'CLI';

    $linea_log = sprintf("[%s] [%s] [IP: %s] [URI: %s] - %s%s", $fecha, $origen, $ip, $uri, $mensaje, PHP_EOL);

    @file_put_contents($archivo_log, $linea_log, FILE_APPEND | LOCK_EX);
}

/**
 * Manejador global para excepciones no capturadas.
 */
set_exception_handler(function (Throwable $exception) {
    registrar_error_log($exception->getMessage() . " en " . $exception->getFile() . ":" . $exception->getLine(), 'CRITICAL_EXCEPTION');
    
    // Si la respuesta no ha enviado cabeceras, devolver HTTP 500
    if (!headers_sent()) {
        http_response_code(500);
    }
    
    // Si es una petición AJAX / API, devolver JSON
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['error' => 'Ocurrió un error interno en el servidor.']);
        exit;
    }

    //
    ?>
    <!DOCTYPE html>
    <html lang="es" class="dark">
    <head>
        <meta charset="UTF-8">
        <title>Atelier Élie - Error del Servidor</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center space-y-4 shadow-2xl">
            <span class="text-5xl">⚡</span>
            <h1 class="text-2xl font-serif font-bold text-amber-500">Inconveniente en el Atelier</h1>
            <p class="text-xs text-slate-400 leading-relaxed">
                Nuestros artesanos están revisando el pergamino de registros. La anomalía ha sido notificada y será resuelta a la brevedad.
            </p>
            <div class="pt-2">
                <a href="index.php" class="inline-block bg-amber-600 hover:bg-amber-700 text-slate-950 font-bold px-6 py-2.5 rounded-xl text-xs transition">
                    Volver a la Entrada Principal
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
});