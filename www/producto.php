<?php
require_once __DIR__ . '/helpers/autoload.php';

// Inicializamos variables de control
$producto_encontrado = false;
$corbata = null;

// Capturamos el ID de la URL de forma segura (intval para evitar inyecciones de SQL)
$id_producto = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_producto > 0) {
    try {
        //Obtenemos la conexión PDO centralizada
        $pdo = obtener_conexion_db();
        
        //Preparamos la consulta segura usando el marcador ':id'
        $stmt = $pdo->prepare("SELECT * FROM CORBATA WHERE id_corbata = :id");
        
        //Ejecutamos la consulta asociando el marcador ':id' con la variable real
        $stmt->execute(['id' => $id_producto]);
        
        //fetch() extrae una única fila en lugar de un array de filas
        $corbata = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($corbata) {
            $producto_encontrado = true;
        }
    } catch (PDOException $e) {
        $error_msg = $e->getMessage();
    }
}

require_once 'header.php'; 
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="mb-8">
        <a href="catalogo.php" class="inline-flex items-center space-x-2 text-sm text-slate-400 hover:text-amber-500 transition">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Volver al Catálogo de Sastrería</span>
        </a>
    </div>

    <?php if ($producto_encontrado): ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
            
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden p-4 shadow-2xl">
                <div class="aspect-square bg-slate-950 rounded-xl overflow-hidden relative">
                    <img src="<?= e($corbata['imagen']) ?>" alt="Corbata <?= e($corbata['color']) ?>" class="w-full h-full object-cover">
                    <span class="absolute bottom-4 left-4 bg-slate-900/90 text-amber-500 text-xs font-bold px-3 py-1.5 rounded-md border border-slate-800 tracking-wider uppercase">
                        Colección Atelier Élie
                    </span>
                </div>
            </div>

            <div class="space-y-6">
                <div>
                    <span class="text-xs font-semibold tracking-widest text-amber-500 uppercase"><?= e($corbata['material']) ?> Premium</span>
                    <h1 class="text-3xl md:text-4xl font-serif font-bold text-slate-100 mt-2">Corbata <?= e($corbata['color']) ?></h1>
                    <p class="text-2xl font-semibold text-slate-200 mt-2"><?= e(number_format($corbata['precio'], 2)) ?>€</p>
                </div>

                <div class="border-t border-slate-850"></div>

                <div class="space-y-3">
                    <h3 class="text-xs font-bold tracking-wider text-slate-400 uppercase">Descripción de la Pieza</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        <?php 
                        if (strpos(strtolower($corbata['color']), 'lobo') !== false || strpos(strtolower($corbata['color']), 'plateado') !== false) {
                            echo "Inspirada en el porte sobrio de Kaer Morhen. Esta pieza de seda Jacquard presenta un relieve texturizado que evoca las cotas de malla de los brujos del Lobo. Ideal para recepciones que exigen elegancia y un carácter inquebrantable.";
                        } elseif (strpos(strtolower($corbata['color']), 'vibora') !== false || strpos(strtolower($corbata['color']), 'verde') !== false) {
                            echo "Diseñada con un corte moderno y ajustado (Slim). Su tejido satinado de color verde veneno imita las escamas de una serpiente y las armaduras ligeras de la Escuela de la Víbora. Una corbata audaz para aquellos que prefieren actuar desde las sombras.";
                        } elseif (strpos(strtolower($corbata['color']), 'yennefer') !== false || strpos(strtolower($corbata['color']), 'obsidiana') !== false) {
                            echo "Nacida del misterio y la alta magia de Vengerberg. Confeccionada sobre una base de terciopelo negro profundo con sutiles bordados en seda violeta lilac. Cada pieza de esta edición limitada es impregnada sutilmente con esencias de lila y grosella antes del embalaje.";
                        } else {
                            echo "Una pieza exclusiva del Atelier Élie de Oxenfurt, elaborada artesanalmente con costuras reinforced e hilos de seda natural seleccionados a mano para garantizar un nudo de caída impecable y estructura firme.";
                        }
                        ?>
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4 bg-slate-900/50 p-4 rounded-xl border border-slate-800 text-xs">
                    <div>
                        <span class="text-slate-500 block uppercase font-bold">Sello / Diseñador</span>
                        <span class="text-slate-200 font-semibold text-sm"><?= e($corbata['marca']) ?></span>
                    </div>
                    <div>
                        <span class="text-slate-500 block uppercase font-bold">Corte / Talla</span>
                        <span class="text-slate-200 font-semibold text-sm"><?= e($corbata['talla']) ?></span>
                    </div>
                    <div>
                        <span class="text-slate-500 block uppercase font-bold">Material Principal</span>
                        <span class="text-slate-200 font-semibold text-sm"><?= e($corbata['material']) ?></span>
                    </div>
                    <div>
                        <span class="text-slate-500 block uppercase font-bold">Disponibilidad</span>
                        <?php if ($corbata['stock'] > 0): ?>
                            <span class="text-emerald-400 font-semibold text-sm">En Stock (<?= e($corbata['stock']) ?> u.)</span>
                        <?php else: ?>
                            <span class="text-rose-500 font-semibold text-sm">Agotado</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 pt-4">
                    <?php if ($corbata['stock'] > 0): ?>
                        <!-- BOTÓN DE AÑADIR AL CARRITO -->
                        <a href="agregar_carrito.php?id=<?= e($corbata['id_corbata']) ?>" 
                           class="flex-1 bg-amber-600 hover:bg-amber-700 text-slate-950 font-bold py-3 px-6 rounded-xl transition duration-300 text-center shadow-lg shadow-amber-500/10 flex items-center justify-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                            <span>Añadir al Carrito</span>
                        </a>
                    <?php else: ?>
                        <!-- Estado deshabilitado si no hay stock disponible -->
                        <button disabled 
                                class="flex-1 bg-slate-800 text-slate-500 font-bold py-3 px-6 rounded-xl text-center cursor-not-allowed flex items-center justify-center space-x-2 border border-slate-750">
                            <span>Agotado temporalmente</span>
                        </button>
                    <?php endif; ?>
                    
                    <!-- Botón de Favoritos -->
                    <a href="gestionar_favorito.php?id=<?= e($corbata['id_corbata']) ?>" 
                       class="bg-slate-900 border border-slate-800 hover:border-rose-500 text-slate-400 hover:text-rose-500 px-6 py-3 rounded-xl transition duration-300 flex items-center justify-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                        <span class="hidden sm:inline">Añadir a Favoritos</span>
                    </a>
                </div>

            </div>

        </div>

    <?php else: ?>
        <div class="text-center py-16 bg-slate-900 border border-slate-800 rounded-2xl max-w-2xl mx-auto space-y-4">
            <span class="text-5xl">🔮</span>
            <h2 class="text-2xl font-serif font-bold text-slate-100">La magia no ha funcionado</h2>
            <p class="text-slate-400 text-sm max-w-md mx-auto">Vaya, parece que los brujos se han llevado todo el stock o la pieza que buscas no existe en los registros del Atelier Élie.</p>
            <div class="pt-4">
                <a href="catalogo.php" class="bg-amber-600 hover:bg-amber-700 text-slate-950 font-bold px-6 py-2 rounded-lg text-sm transition">
                    Volver al Catálogo
                </a>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php 
require_once 'footer.php'; 
?>