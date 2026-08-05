<?php
$mensaje_enviado = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aquí es donde en el futuro validaríamos y guardaríamos en la BD o enviaríamos un correo
    $nombre = htmlspecialchars($_POST['nombre']);
    $correo = htmlspecialchars($_POST['correo']);
    $asunto = htmlspecialchars($_POST['asunto']);
    $mensaje = htmlspecialchars($_POST['mensaje']);
    
    // Activamos el flag de éxito
    $mensaje_enviado = true;
}


require_once 'header.php'; 
?>

<!-- CONTENEDOR PRINCIPAL: CONTACTO -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">

    <!-- Encabezado de la página -->
    <div class="text-center space-y-4">
        <span class="text-xs font-semibold tracking-widest text-amber-500 uppercase">Atención al Cliente</span>
        <h1 class="text-4xl md:text-5xl font-serif font-bold text-slate-100 tracking-tight">Contacta con Nosotros</h1>
        <div class="h-1 w-12 bg-amber-500 mx-auto mt-4"></div>
    </div>

    <!-- Alerta de mensaje enviado con éxito -->
    <?php if ($mensaje_enviado): ?>
        <div class="max-w-4xl mx-auto bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-xl flex items-center space-x-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <div>
                <p class="font-semibold">¡Mensaje recibido con éxito, <?= $nombre ?>!</p>
                <p class="text-xs text-emerald-500/80">Nos pondremos en contacto contigo en tu dirección (<?= $correo ?>) lo antes posible.</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Grid Principal (Información de contacto + Formulario) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 max-w-5xl mx-auto items-start">
        
        <!-- COLUMNA IZQUIERDA: INFORMACIÓN DE CONTACTO (1/3) -->
        <div class="lg:col-span-1 space-y-8 bg-slate-900 p-8 rounded-2xl border border-slate-800">
            <div>
                <h2 class="text-xl font-serif font-bold text-slate-100">Sartorial Atellier</h2>
                <p class="text-sm text-slate-500 mt-1">¿Tienes alguna consulta sobre tejidos, envíos o medidas personalizadas? Estamos para ayudarte.</p>
            </div>

            <div class="space-y-6">
                <!-- Teléfono -->
                <div class="flex items-start space-x-4">
                    <div class="p-2 rounded-lg bg-slate-950 border border-slate-800 text-amber-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.824-1.802-5.18-4.156-6.98-6.979l1.302-.97a1.025 1.025 0 0 0 .375-1.158L4.29 4.316A1.025 1.025 0 0 0 3.11 4.05L2.25 4.878A2.25 2.25 0 0 0 2.25 6.75Z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-450">Llámanos</h4>
                        <p class="text-sm text-slate-300 mt-0.5">+34 965 123 456</p>
                    </div>
                </div>

                <!-- Correo -->
                <div class="flex items-start space-x-4">
                    <div class="p-2 rounded-lg bg-slate-950 border border-slate-800 text-amber-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0l-7.5-4.615a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-450">Escríbenos</h4>
                        <p class="text-sm text-slate-300 mt-0.5">contacto@sartorial.es</p>
                    </div>
                </div>

                <!-- Horario -->
                <div class="flex items-start space-x-4">
                    <div class="p-2 rounded-lg bg-slate-950 border border-slate-800 text-amber-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-450">Atención</h4>
                        <p class="text-sm text-slate-300 mt-0.5">Lunes a Viernes</p>
                        <p class="text-xs text-slate-500">09:00 - 18:00 (CET)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: FORMULARIO (2/3) -->
        <div class="lg:col-span-2 bg-slate-900 p-8 rounded-2xl border border-slate-800">
            <form action="contacto.php" method="POST" class="space-y-6">
                
                <!-- Fila 1: Nombre y Correo -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="nombre" class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tu Nombre</label>
                        <input type="text" id="nombre" name="nombre" required 
                               class="w-full bg-slate-950 border border-slate-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-lg px-4 py-2.5 text-sm text-slate-100 outline-none transition">
                    </div>
                    <div class="space-y-2">
                        <label for="correo" class="text-xs font-semibold uppercase tracking-wider text-slate-400">Correo Electrónico</label>
                        <input type="email" id="correo" name="correo" required 
                               class="w-full bg-slate-950 border border-slate-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-lg px-4 py-2.5 text-sm text-slate-100 outline-none transition">
                    </div>
                </div>

                <!-- Asunto -->
                <div class="space-y-2">
                    <label for="asunto" class="text-xs font-semibold uppercase tracking-wider text-slate-400">Asunto</label>
                    <input type="text" id="asunto" name="asunto" required 
                           class="w-full bg-slate-950 border border-slate-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-lg px-4 py-2.5 text-sm text-slate-100 outline-none transition">
                </div>

                <!-- Mensaje -->
                <div class="space-y-2">
                    <label for="mensaje" class="text-xs font-semibold uppercase tracking-wider text-slate-400">Mensaje</label>
                    <textarea id="mensaje" name="mensaje" rows="6" required 
                              class="w-full bg-slate-950 border border-slate-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-lg px-4 py-2.5 text-sm text-slate-100 outline-none transition resize-none"></textarea>
                </div>

                <!-- Botón de Envío -->
                <div class="flex justify-end">
                    <button type="submit" 
                            class="bg-amber-600 hover:bg-amber-700 text-slate-950 font-bold px-8 py-3 rounded-lg transition duration-300 text-sm shadow-lg shadow-amber-500/10">
                        Enviar Mensaje
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>

<?php 
require_once 'footer.php'; 
?>