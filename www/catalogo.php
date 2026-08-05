<?php
// Cargar el gestor de seguridad global (CSRF + Sanitización)
require_once __DIR__ . '/helpers/autoload.php';

// Obtener la conexiónPDO centralizada
$pdo = obtener_conexion_db();

// Definir variables de entorno de usuario y estado
$id_user = $_SESSION['id_usuario'] ?? 0;
$corbatas = [];
$mis_favoritos = [];

//Obtener lista de IDs favoritos del usuario logueado
if ($id_user > 0) {
    $stmtFav = $pdo->prepare("SELECT id_corbata FROM FAVORITO WHERE id_user = :id_user");
    $stmtFav->execute(['id_user' => $id_user]);
    $mis_favoritos = $stmtFav->fetchAll(PDO::FETCH_COLUMN);
}

//Consultas "DISTINCT" para los filtros automáticos
$materiales_db = $pdo->query("SELECT DISTINCT material FROM CORBATA WHERE material IS NOT NULL AND material != ''")->fetchAll(PDO::FETCH_COLUMN);
$colores_db    = $pdo->query("SELECT DISTINCT color FROM CORBATA WHERE color IS NOT NULL AND color != ''")->fetchAll(PDO::FETCH_COLUMN);
$marcas_db     = $pdo->query("SELECT DISTINCT marca FROM CORBATA WHERE marca IS NOT NULL AND marca != ''")->fetchAll(PDO::FETCH_COLUMN);
$tallas_db     = $pdo->query("SELECT DISTINCT talla FROM CORBATA WHERE talla IS NOT NULL AND talla != ''")->fetchAll(PDO::FETCH_COLUMN);

$precio_max_db = $pdo->query("SELECT MAX(precio) FROM CORBATA")->fetchColumn();
$precio_max_limite = $precio_max_db ? ceil($precio_max_db) : 100;

//Captura y SANITIZACIÓN ESTRICTA de filtros desde la URL (GET)
$buscar     = isset($_GET['buscar']) ? sanear_string($_GET['buscar']) : '';
$material   = isset($_GET['material']) && is_array($_GET['material']) ? sanear_array($_GET['material']) : [];
$color      = isset($_GET['color']) && is_array($_GET['color']) ? sanear_array($_GET['color']) : [];
$marca      = isset($_GET['marca']) && is_array($_GET['marca']) ? sanear_array($_GET['marca']) : [];
$talla      = isset($_GET['talla']) && is_array($_GET['talla']) ? sanear_array($_GET['talla']) : [];
$precio_max = isset($_GET['precio_max']) ? floatval($_GET['precio_max']) : $precio_max_limite;

//CONSTRUCCIÓN DE LA CONSULTA DINÁMICA
$query = "SELECT * FROM CORBATA WHERE 1=1";
$params = [];

if (!empty($buscar)) {
    $query .= " AND (color LIKE :buscar OR material LIKE :buscar OR marca LIKE :buscar)";
    $params['buscar'] = '%' . $buscar . '%';
}

if (!empty($material)) {
    $in_parts = [];
    foreach ($material as $index => $val) {
        $key = "mat" . $index;
        $in_parts[] = ":" . $key;
        $params[$key] = $val;
    }
    $query .= " AND material IN (" . implode(',', $in_parts) . ")";
}

if (!empty($color)) {
    $in_parts = [];
    foreach ($color as $index => $val) {
        $key = "col" . $index;
        $in_parts[] = ":" . $key;
        $params[$key] = $val;
    }
    $query .= " AND color IN (" . implode(',', $in_parts) . ")";
}

if (!empty($marca)) {
    $in_parts = [];
    foreach ($marca as $index => $val) {
        $key = "mar" . $index;
        $in_parts[] = ":" . $key;
        $params[$key] = $val;
    }
    $query .= " AND marca IN (" . implode(',', $in_parts) . ")";
}

if (!empty($talla)) {
    $in_parts = [];
    foreach ($talla as $index => $val) {
        $key = "tal" . $index;
        $in_parts[] = ":" . $key;
        $params[$key] = $val;
    }
    $query .= " AND talla IN (" . implode(',', $in_parts) . ")";
}

$query .= " AND precio <= :precio_max";
$params['precio_max'] = $precio_max;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$corbatas = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php'; 
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <!--Barra de búsqueda superior -->
    <div class="flex justify-center">
        <form action="catalogo.php" method="GET" class="w-full max-w-3xl relative">
            <?php foreach ($material as $val): ?><input type="hidden" name="material[]" value="<?= e($val) ?>"><?php endforeach; ?>
            <?php foreach ($color as $val): ?><input type="hidden" name="color[]" value="<?= e($val) ?>"><?php endforeach; ?>
            <?php foreach ($marca as $val): ?><input type="hidden" name="marca[]" value="<?= e($val) ?>"><?php endforeach; ?>
            <?php foreach ($talla as $val): ?><input type="hidden" name="talla[]" value="<?= e($val) ?>"><?php endforeach; ?>
            <input type="hidden" name="precio_max" value="<?= e($precio_max) ?>">

            <input type="text" name="buscar" value="<?= e($buscar) ?>" placeholder="¿Buscas el Lobo, la Víbora o Lilas y Grosellas...?" 
                   class="w-full bg-slate-900 text-slate-100 placeholder-slate-500 pl-12 pr-4 py-3 rounded-full border border-slate-800 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.603 10.602Z" />
                </svg>
            </div>
        </form>
    </div>
    <!-- 2. Grid de Filtros + Catálogo -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- BARRA LATERAL DE FILTROS -->
        <aside class="lg:col-span-1 bg-slate-900 p-6 rounded-xl border border-slate-800 h-fit">
            <form action="catalogo.php" method="GET" class="space-y-6">
                <?php if (!empty($buscar)): ?>
                    <input type="hidden" name="buscar" value="<?= e($buscar) ?>">
                <?php endif; ?>

                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <h2 class="text-lg font-serif font-bold text-amber-500 tracking-wide">Filtros</h2>
                    <a href="catalogo.php" class="text-xs text-slate-500 hover:text-amber-400 transition">Limpiar todo</a>
                </div>

                <!-- Filtro Dinámico: Material -->
                <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Material</h3>
                    <div class="space-y-2 max-h-40 overflow-y-auto pr-2">
                        <?php foreach ($materiales_db as $mat_opcion): ?>
                            <label class="flex items-center text-sm text-slate-400 hover:text-slate-200 cursor-pointer">
                                <input type="checkbox" name="material[]" value="<?= e($mat_opcion) ?>" 
                                       <?= in_array($mat_opcion, $material) ? 'checked' : '' ?>
                                       class="rounded bg-slate-950 border-slate-800 text-amber-500 focus:ring-0 mr-3 h-4 w-4">
                                <?= e($mat_opcion) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Filtro Dinámico: Temáticas (Color) -->
                <div class="space-y-3 border-t border-slate-800 pt-4">
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Temáticas (Color)</h3>
                    <div class="space-y-2 max-h-40 overflow-y-auto pr-2">
                        <?php foreach ($colores_db as $col_opcion): ?>
                            <label class="flex items-center text-sm text-slate-400 hover:text-slate-200 cursor-pointer">
                                <input type="checkbox" name="color[]" value="<?= e($col_opcion) ?>" 
                                       <?= in_array($col_opcion, $color) ? 'checked' : '' ?>
                                       class="rounded bg-slate-950 border-slate-800 text-amber-500 focus:ring-0 mr-3 h-4 w-4">
                                <?= e($col_opcion) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Filtro Dinámico: Marcas -->
                <div class="space-y-3 border-t border-slate-800 pt-4">
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Sello</h3>
                    <div class="space-y-2">
                        <?php foreach ($marcas_db as $mar_opcion): ?>
                            <label class="flex items-center text-sm text-slate-400 hover:text-slate-200 cursor-pointer">
                                <input type="checkbox" name="marca[]" value="<?= e($mar_opcion) ?>" 
                                       <?= in_array($mar_opcion, $marca) ? 'checked' : '' ?>
                                       class="rounded bg-slate-950 border-slate-800 text-amber-500 focus:ring-0 mr-3 h-4 w-4">
                                <?= e($mar_opcion) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Filtro Dinámico: Talla -->
                <div class="space-y-3 border-t border-slate-800 pt-4">
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Corte / Talla</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($tallas_db as $tal_opcion): ?>
                            <label class="cursor-pointer">
                                <input type="checkbox" name="talla[]" value="<?= e($tal_opcion) ?>" 
                                       <?= in_array($tal_opcion, $talla) ? 'checked' : 'hidden' ?> class="sr-only peer">
                                <span class="px-3 py-1 bg-slate-950 border border-slate-800 peer-checked:border-amber-500 peer-checked:text-amber-500 text-xs font-semibold rounded-md transition text-slate-400 block">
                                    <?= e($tal_opcion) ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Filtro Deslizante: Rango de Precio -->
                <div class="space-y-3 border-t border-slate-800 pt-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Precio Máximo</h3>
                        <span class="text-xs text-amber-500 font-bold" id="precio_valor"><?= e($precio_max) ?>€</span>
                    </div>
                    <input type="range" name="precio_max" min="0" max="<?= e($precio_max_limite) ?>" value="<?= e($precio_max) ?>" 
                           oninput="document.getElementById('precio_valor').innerText = this.value + '€'"
                           class="w-full accent-amber-500 bg-slate-950 h-1.5 rounded-lg appearance-none cursor-pointer">
                </div>

                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-slate-950 font-bold py-2.5 rounded-lg transition duration-300 text-sm mt-4 shadow-lg shadow-amber-500/10">
                    Aplicar Selección
                </button>
            </form>
        </aside>

        <!-- SECCIÓN DE PRODUCTOS -->
        <main class="lg:col-span-3 space-y-6">
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500">Mostrando <span class="text-slate-200 font-semibold"><?= count($corbatas) ?></span> diseños encontrados</p>
            </div>

            <?php if (empty($corbatas)): ?>
                <div class="text-center py-16 bg-slate-900 border border-slate-800 rounded-2xl space-y-4">
                    <span class="text-5xl">🔮</span>
                    <h3 class="text-lg font-serif font-bold text-slate-100">Sin resultados mágicos</h3>
                    <p class="text-slate-400 text-sm max-w-md mx-auto">No hay piezas que coincidan con tus filtros. Intenta restablecer los parámetros o buscar otro término.</p>
                    <div class="pt-2">
                        <a href="catalogo.php" class="bg-amber-600 hover:bg-amber-700 text-slate-950 font-bold px-6 py-2 rounded-lg text-sm transition inline-block">
                            Ver Todo el Catálogo
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Grid de Tarjetas -->
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php foreach ($corbatas as $corbata): ?>
                        <?php $es_favorito = in_array($corbata['id_corbata'], $mis_favoritos); ?>
                        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg hover:border-amber-500/50 hover:shadow-amber-500/5 transition duration-300 flex flex-col group relative">
                            
                            <!-- BOTÓN DE FAVORITOS EN LA PARRILLA -->
                            <a href="gestionar_favorito.php?id=<?= e($corbata['id_corbata']) ?>" 
                               title="<?= $es_favorito ? 'Quitar de favoritos' : 'Añadir a favoritos' ?>"
                               class="absolute top-3 right-3 z-10 w-9 h-9 rounded-full bg-slate-950/80 backdrop-blur-sm border border-slate-800 flex items-center justify-center transition duration-200 hover:scale-110 <?= $es_favorito ? 'text-rose-500 border-rose-500/40' : 'text-slate-400 hover:text-rose-500' ?>">
                                
                                <svg xmlns="http://www.w3.org/2000/svg" 
                                     viewBox="0 0 24 24" 
                                     stroke-width="2" 
                                     stroke="currentColor" 
                                     class="w-5 h-5 <?= $es_favorito ? 'fill-rose-500' : 'fill-none' ?>">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                </svg>
                            </a>

                            <!-- Imagen del producto -->
                            <div class="relative aspect-square bg-slate-950 overflow-hidden">
                                <img src="<?= e($corbata['imagen']) ?>" alt="<?= e($corbata['color']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500 opacity-90 group-hover:opacity-100">
                            </div>
                            
                            <!-- Información de la tarjeta -->
                            <div class="p-5 flex-grow flex flex-col justify-between space-y-4">
                                <div>
                                    <span class="text-xs font-semibold tracking-wider text-amber-500 uppercase"><?= e($corbata['material']) ?> · <?= e($corbata['marca']) ?></span>
                                    <h3 class="text-base font-serif font-bold text-slate-100 mt-1">Corbata <?= e($corbata['color']) ?></h3>
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-lg font-semibold text-slate-200"><?= e(number_format($corbata['precio'], 2)) ?>€</span>
                                        <span class="text-xs text-slate-500">Talla: <?= e($corbata['talla']) ?></span>
                                    </div>
                                </div>

                                <div class="pt-1">
                                    <a href="producto.php?id=<?= e($corbata['id_corbata']) ?>" class="block w-full text-center bg-slate-800 hover:bg-amber-600 hover:text-slate-950 border border-slate-700 hover:border-transparent text-slate-300 font-semibold py-2 px-4 rounded-lg transition duration-300 text-sm">
                                        Ver Detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>

    </div>
</div>

<?php 
require_once 'footer.php'; 
?>