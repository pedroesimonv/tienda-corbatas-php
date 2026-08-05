<?php
// Cargar el gestor de seguridad global (CSRF + Sanitización + Sesión)
require_once __DIR__ . '/helpers/autoload.php';
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atelier Élie - Tienda de Corbatas de Alta Costura</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif: ['Georgia', 'serif'],
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="flex flex-col h-full bg-slate-950 text-slate-100 font-sans">

    <!-- HEADER PRINCIPAL -->
    <header class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Logo de la Marca -->
            <div class="flex items-center space-x-2">
                <a href="index.php" class="text-2xl font-serif font-bold tracking-widest text-amber-500 hover:text-amber-400 transition">
                    ATELIER ÉLIE
                </a>
            </div>

            <!-- Menú de Navegación -->
            <nav class="hidden md:flex space-x-8 font-medium">
                <a href="index.php" class="text-amber-500 hover:text-amber-400 transition">Inicio</a>
                <a href="catalogo.php" class="text-slate-300 hover:text-amber-500 transition">Corbatas</a>
                <a href="nosotros.php" class="text-slate-300 hover:text-amber-500 transition">Nosotros</a>
                <a href="contacto.php" class="text-slate-300 hover:text-amber-500 transition">Contacto</a>
            </nav>

            <!-- Acciones de Usuario / Perfil Dinámico -->
            <div class="flex items-center space-x-6">
                <a href="cuenta.php?tab=favoritos" class="text-slate-400 hover:text-rose-500 transition" title="Mis Favoritos">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                    </svg>
                </a>

                <a href="carrito.php" class="text-slate-400 hover:text-amber-500 transition" title="Carrito de Compras">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                </a>

                <?php if (isset($_SESSION['usuario'])): ?>
                    <a href="cuenta.php" class="flex items-center space-x-2 text-slate-300 hover:text-amber-500 transition">
                        <span class="text-sm font-medium hidden sm:inline">Hola, <?= e($_SESSION['usuario']) ?></span>
                        <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-amber-500 font-bold text-sm">
                            <?= strtoupper(substr($_SESSION['usuario'], 0, 1)) ?>
                        </div>
                    </a>
                <?php else: ?>
                    <div class="flex items-center space-x-3">
                        <a href="login.php" class="text-sm font-semibold text-slate-300 hover:text-amber-500 transition px-3 py-2">
                            Entrar
                        </a>
                        <a href="registro.php" class="text-sm font-semibold bg-amber-600 hover:bg-amber-700 text-slate-950 px-4 py-2 rounded-md transition shadow-md shadow-amber-500/5">
                            Registrarse
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="flex-grow">