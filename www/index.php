<?php
// Cargar autoloader global (Sesión + Helper DB + Sanitización + CSRF)
require_once __DIR__ . '/helpers/autoload.php';

// Importamos el Header modular
require_once 'header.php'; 

$id_user = $_SESSION['id_usuario'] ?? 0;
$corbatas_destacadas = [];
$mis_favoritos = [];
$conexion_ok = false;
$error_msg = '';

try {
    //Conexión centralizada a la Base de Datos
    $pdo = obtener_conexion_db();
    $conexion_ok = true; 

    //Traer los productos destacados guardados en la BD
    $stmt = $pdo->query("SELECT id_corbata, color, material, marca, precio, stock, imagen FROM CORBATA LIMIT 6");
    $corbatas_destacadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //Comprobar favoritos del usuario si ha iniciado sesión
    if ($id_user > 0) {
        $stmtFav = $pdo->prepare("SELECT id_corbata FROM FAVORITO WHERE id_user = :id_user");
        $stmtFav->execute(['id_user' => $id_user]);
        $mis_favoritos = $stmtFav->fetchAll(PDO::FETCH_COLUMN);
    }

} catch (PDOException $e) {
    $conexion_ok = false;
    $error_msg = $e->getMessage();
}
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">

    <!-- Estado de Conexión a Base de Datos 
    <div class="flex justify-end">
        <?php if ($conexion_ok): ?>
            <span class="inline-flex items-center gap-x-1.5 rounded-md bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-400 ring-1 ring-inset ring-emerald-500/20">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                Atelier Conectado
            </span>
        <?php else: ?>
            <span class="inline-flex items-center gap-x-1.5 rounded-md bg-rose-500/10 px-2.5 py-1 text-xs font-medium text-rose-400 ring-1 ring-inset ring-rose-500/20" title="<?= e($error_msg) ?>">
                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                Error de Conexión
            </span>
        <?php endif; ?>
    </div>-->

    <!-- Buscador Principal -->
    <div class="flex justify-center">
        <form action="catalogo.php" method="GET" class="w-full max-w-2xl relative">
            <input type="text" name="buscar" placeholder="Busca por temática (Lobo, Víbora, Lilas...), material o color..." 
                   class="w-full bg-slate-900 text-slate-100 placeholder-slate-500 pl-12 pr-4 py-3.5 rounded-full border border-slate-800 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition duration-300 text-sm shadow-xl">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.603 10.602Z" />
                </svg>
            </div>
        </form>
    </div>

    <!-- Encabezado de Colección Adaptado -->
    <div class="text-center py-4 space-y-2">
        <span class="text-xs font-semibold tracking-widest text-amber-500 uppercase">Sastrería Artesanal de Oxenfurt</span>
        <h1 class="text-4xl md:text-5xl font-serif font-bold text-slate-100 tracking-tight">Atelier Élie</h1>
        <p class="text-slate-400 text-sm max-w-xl mx-auto">
            Piezas elaboradas con telas nobles e inspiradas en las viejas tradiciones de los reinos del norte.
        </p>
    </div>

    <!-- Grid de Productos desde MySQL -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        
        <?php if (!empty($corbatas_destacadas)): ?>
            <?php foreach ($corbatas_destacadas as $corbata): ?>
                <?php 
                    $es_favorito = in_array($corbata['id_corbata'], $mis_favoritos);
                    $nombre_producto = "Corbata de " . e($corbata['material'] . ' ' . $corbata['color']);
                ?>
                <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-xl hover:border-amber-500/50 hover:shadow-amber-500/5 transition duration-300 flex flex-col group relative">
                    
                    <!-- Botón de Favoritos vinculado al controlador -->
                    <a href="gestionar_favorito.php?id=<?= e($corbata['id_corbata']) ?>" 
                       title="<?= $es_favorito ? 'Quitar de favoritos' : 'Añadir a favoritos' ?>"
                       class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-slate-950/80 backdrop-blur-sm border border-slate-800 flex items-center justify-center transition duration-200 hover:scale-110 <?= $es_favorito ? 'text-rose-500 border-rose-500/40' : 'text-slate-400 hover:text-rose-500' ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" 
                             viewBox="0 0 24 24" 
                             stroke-width="2" 
                             stroke="currentColor" 
                             class="w-5 h-5 <?= $es_favorito ? 'fill-rose-500' : 'fill-none' ?>">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                    </a>

                    <!-- Imagen recuperada dinámicamente -->
                    <div class="relative aspect-square bg-slate-950 overflow-hidden">
                        <img src="<?= e($corbata['imagen']) ?>" 
                             alt="<?= $nombre_producto ?>" 
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500 opacity-90 group-hover:opacity-100">
                    </div>
                    
                    <!-- Información y enlace al detalle -->
                    <div class="p-6 flex-grow flex flex-col justify-between space-y-4">
                        <div>
                            <span class="text-xs font-semibold tracking-wider text-amber-500 uppercase">
                                <?= e($corbata['material']) ?> · <?= e($corbata['marca'] ?? 'Atelier Élie') ?>
                            </span>
                            <h3 class="text-lg font-serif font-bold text-slate-100 mt-1"><?= $nombre_producto ?></h3>
                            <p class="text-xl font-semibold text-slate-200 mt-2"><?= e(number_format($corbata['precio'], 2)) ?>€</p>
                        </div>

                        <div class="pt-2">
                            <a href="producto.php?id=<?= e($corbata['id_corbata']) ?>" class="block w-full text-center bg-slate-800 hover:bg-amber-600 hover:text-slate-950 border border-slate-700 hover:border-transparent text-slate-300 font-semibold py-2.5 px-4 rounded-lg transition duration-300 text-sm">
                                Ver Detalles de la Pieza
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-12 text-slate-500 text-sm">
                No hay piezas destacadas registradas en el atelier actualmente.
            </div>
        <?php endif; ?>
    </div>

    <!-- SECCIÓN: GALERÍA EDITORIAL CON MODELOS (Atelier Élie) -->
    <section class="py-10 border-t border-slate-800/80 space-y-6">
        <div class="text-center space-y-2">
            <span class="text-xs font-semibold tracking-widest text-amber-500 uppercase">Colección en Contexto</span>
            <h2 class="text-2xl md:text-3xl font-serif font-bold text-slate-100">El Arte del Buen Vestir en Oxenfurt</h2>
            <p class="text-slate-400 text-xs max-w-lg mx-auto">
                Descubre cómo lucen las piezas artesanales del Atelier en la aristocracia, académicos y diplomáticos de los Reinos del Norte.
            </p>
        </div>

        <!-- Grid de Fotos Editoriales / Modelos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 pt-4">
            
            <!-- Modelo 1: Caballero / Beauclair -->
            <div class="group relative rounded-xl overflow-hidden border border-slate-800 bg-slate-950 aspect-[4/5] shadow-lg">
                <img src="assets/img/modelo_papucho.webp" alt="Estilo Beauclair" class="w-full h-full object-cover group-hover:scale-105 transition duration-500 opacity-85 group-hover:opacity-100">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-transparent to-transparent opacity-80 group-hover:opacity-100 transition"></div>
                <div class="absolute bottom-3 left-3 right-3 text-left">
                    <span class="text-[10px] font-bold text-amber-500 uppercase tracking-wider block">Corte de Toussaint</span>
                    <p class="text-xs font-serif text-slate-200 font-semibold">Seda Ámbar & Terciopelo</p>
                </div>
            </div>

            <!-- Modelo 2: Académico de Oxenfurt -->
            <div class="group relative rounded-xl overflow-hidden border border-slate-800 bg-slate-950 aspect-[4/5] shadow-lg">
                <img src="assets/img/modelo_grifo.webp" alt="Académico de Oxenfurt" class="w-full h-full object-cover group-hover:scale-105 transition duration-500 opacity-85 group-hover:opacity-100">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-transparent to-transparent opacity-80 group-hover:opacity-100 transition"></div>
                <div class="absolute bottom-3 left-3 right-3 text-left">
                    <span class="text-[10px] font-bold text-amber-500 uppercase tracking-wider block">Academia de Oxenfurt</span>
                    <p class="text-xs font-serif text-slate-200 font-semibold">Corte Gris Piedra</p>
                </div>
            </div>

            <!-- Modelo 3: Aristócrata de Novigrado -->
            <div class="group relative rounded-xl overflow-hidden border border-slate-800 bg-slate-950 aspect-[4/5] shadow-lg">
                <img src="assets/img/modelo_grifo.webp" alt="Aristócrata de Novigrado" class="w-full h-full object-cover group-hover:scale-105 transition duration-500 opacity-85 group-hover:opacity-100">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-transparent to-transparent opacity-80 group-hover:opacity-100 transition"></div>
                <div class="absolute bottom-3 left-3 right-3 text-left">
                    <span class="text-[10px] font-bold text-amber-500 uppercase tracking-wider block">Gildorf & Murallas</span>
                    <p class="text-xs font-serif text-slate-200 font-semibold">Seda Verde Esmeralda</p>
                </div>
            </div>

            <!-- Modelo 4: Corte de Wyzima -->
            <div class="group relative rounded-xl overflow-hidden border border-slate-800 bg-slate-950 aspect-[4/5] shadow-lg">
                <img src="assets/img/modelo_grifo.webp" alt="Corte de Wyzima" class="w-full h-full object-cover group-hover:scale-105 transition duration-500 opacity-85 group-hover:opacity-100">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-transparent to-transparent opacity-80 group-hover:opacity-100 transition"></div>
                <div class="absolute bottom-3 left-3 right-3 text-left">
                    <span class="text-[10px] font-bold text-amber-500 uppercase tracking-wider block">Corte Real de Temeria</span>
                    <p class="text-xs font-serif text-slate-200 font-semibold">Seda Marfil Cacería</p>
                </div>
            </div>

            <!-- Modelo 5: Mercaderes de la Puerta del Sur -->
            <div class="group relative rounded-xl overflow-hidden border border-slate-800 bg-slate-950 aspect-[4/5] shadow-lg">
                <img src="assets/img/modelo_grifo.webp" alt="Taller de Oxenfurt" class="w-full h-full object-cover group-hover:scale-105 transition duration-500 opacity-85 group-hover:opacity-100">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-transparent to-transparent opacity-80 group-hover:opacity-100 transition"></div>
                <div class="absolute bottom-3 left-3 right-3 text-left">
                    <span class="text-[10px] font-bold text-amber-500 uppercase tracking-wider block">Atelier Élie</span>
                    <p class="text-xs font-serif text-slate-200 font-semibold">Confección a Medida</p>
                </div>
            </div>

        </div>
    </section>

    <!-- Botón directo al Catálogo Completo -->
    <div class="text-center pt-4">
        <a href="catalogo.php" class="inline-block bg-amber-600 hover:bg-amber-700 text-slate-950 font-bold px-8 py-3 rounded-xl transition text-sm tracking-wide shadow-lg shadow-amber-600/10">
            Explorar Toda la Colección de Oxenfurt
        </a>
    </div>
</div>

<?php 
require_once 'footer.php'; 
?>