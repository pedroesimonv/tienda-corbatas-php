<?php
require_once __DIR__ . '/helpers/autoload.php';

// Inicializar el carrito en la sesión si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Gestión de acciones del carrito (Modificar cantidad o Eliminar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf_or_die();
    
    $accion = $_POST['accion'] ?? '';
    $id_corbata = intval($_POST['id_corbata'] ?? 0);

    if ($id_corbata > 0 && isset($_SESSION['carrito'][$id_corbata])) {
        if ($accion === 'incrementar') {
            // Verificar stock en BD si fuera necesario, o simplemente sumar 1
            $_SESSION['carrito'][$id_corbata]['cantidad']++;
        } elseif ($accion === 'decrementar') {
            $_SESSION['carrito'][$id_corbata]['cantidad']--;
            if ($_SESSION['carrito'][$id_corbata]['cantidad'] <= 0) {
                unset($_SESSION['carrito'][$id_corbata]);
            }
        } elseif ($accion === 'eliminar') {
            unset($_SESSION['carrito'][$id_corbata]);
        }
    }

    header("Location: carrito.php");
    exit;
}

// Calcular Totales
$subtotal_general = 0;
foreach ($_SESSION['carrito'] as $item) {
    $subtotal_general += $item['precio'] * $item['cantidad'];
}
$iva = $subtotal_general * 0.21;
$total_final = $subtotal_general + $iva;

require_once 'header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <h1 class="text-3xl font-serif font-bold text-slate-100">Bolsa de Encargos</h1>
        <p class="text-xs text-slate-400 mt-1">Revisa las piezas seleccionadas antes de proceder al checkout en Oxenfurt.</p>
    </div>

    <?php if (empty($_SESSION['carrito'])): ?>
        <div class="text-center py-16 bg-slate-900 border border-slate-800 rounded-2xl max-w-2xl mx-auto space-y-4">
            <span class="text-5xl">🛍️</span>
            <h2 class="text-xl font-serif font-bold text-slate-100">Tu bolsa está vacía</h2>
            <p class="text-slate-400 text-xs max-w-md mx-auto">Explora el catálogo para añadir piezas de la sastrería artesanal.</p>
            <div class="pt-2">
                <a href="catalogo.php" class="bg-amber-600 hover:bg-amber-700 text-slate-950 font-bold px-6 py-2.5 rounded-xl text-xs transition inline-block">
                    Ver Catálogo de Corbatas
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- LISTADO DE ARTÍCULOS EN LA BOLSA -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl overflow-hidden">
                    <div class="hidden sm:grid grid-cols-12 text-[10px] font-bold uppercase tracking-wider text-slate-400 pb-4 border-b border-slate-800">
                        <div class="col-span-6">Prenda</div>
                        <div class="col-span-3 text-center">Cantidad</div>
                        <div class="col-span-3 text-right">Subtotal</div>
                    </div>

                    <div class="divide-y divide-slate-800/60">
                        <?php foreach ($_SESSION['carrito'] as $id => $item): ?>
                            <?php 
                                $img_src = !empty($item['imagen']) ? $item['imagen'] : 'assets/img/modelo_papucho.webp';
                                $subtotal_item = $item['precio'] * $item['cantidad'];
                            ?>
                            <div class="py-4 flex flex-col sm:grid sm:grid-cols-12 items-center gap-4">
                                
                                <!-- Detalle de Prenda -->
                                <div class="sm:col-span-6 flex items-center space-x-4 w-full">
                                    <img src="<?= e($img_src) ?>" alt="<?= e($item['nombre']) ?>" class="w-16 h-16 object-cover rounded-xl bg-slate-950 border border-slate-800 shrink-0">
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-sm font-serif font-bold text-slate-100 truncate"><?= e($item['nombre']) ?></h3>
                                        <p class="text-xs text-amber-500 font-semibold mt-0.5"><?= e(number_format($item['precio'], 2)) ?>€ <span class="text-[10px] text-slate-500 font-normal">/ ud.</span></p>
                                    </div>
                                </div>

                                <!-- Control de Cantidad (POST con CSRF) -->
                                <div class="sm:col-span-3 flex items-center justify-center space-x-2">
                                    <form action="carrito.php" method="POST" class="inline">
                                        <?= campo_csrf() ?>
                                        <input type="hidden" name="id_corbata" value="<?= e($id) ?>">
                                        <input type="hidden" name="accion" value="decrementar">
                                        <button type="submit" class="w-7 h-7 bg-slate-950 border border-slate-800 text-slate-300 hover:text-amber-500 hover:border-amber-500/50 rounded-lg flex items-center justify-center text-xs transition font-bold">-</button>
                                    </form>

                                    <span class="text-xs font-mono font-bold text-slate-200 w-6 text-center"><?= e($item['cantidad']) ?></span>

                                    <form action="carrito.php" method="POST" class="inline">
                                        <?= campo_csrf() ?>
                                        <input type="hidden" name="id_corbata" value="<?= e($id) ?>">
                                        <input type="hidden" name="accion" value="incrementar">
                                        <button type="submit" class="w-7 h-7 bg-slate-950 border border-slate-800 text-slate-300 hover:text-amber-500 hover:border-amber-500/50 rounded-lg flex items-center justify-center text-xs transition font-bold">+</button>
                                    </form>
                                </div>

                                <!-- Subtotal y Eliminar -->
                                <div class="sm:col-span-3 flex items-center justify-between sm:justify-end space-x-4 w-full">
                                    <span class="text-sm font-mono font-bold text-slate-100"><?= e(number_format($subtotal_item, 2)) ?>€</span>
                                    
                                    <form action="carrito.php" method="POST" class="inline">
                                        <?= campo_csrf() ?>
                                        <input type="hidden" name="id_corbata" value="<?= e($id) ?>">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <button type="submit" title="Quitar de la bolsa" class="text-slate-500 hover:text-rose-400 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- RESUMEN DEL PEDIDO -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 h-fit space-y-6 shadow-xl">
                <h2 class="text-lg font-serif font-bold text-slate-100 border-b border-slate-800 pb-3">Resumen del Pedido</h2>

                <div class="space-y-3 text-xs">
                    <div class="flex justify-between text-slate-400">
                        <span>Base Imponible</span>
                        <span class="font-mono text-slate-200"><?= e(number_format($subtotal_general, 2)) ?>€</span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>Impuestos (IVA 21%)</span>
                        <span class="font-mono text-slate-200"><?= e(number_format($iva, 2)) ?>€</span>
                    </div>
                    <div class="flex justify-between text-base font-bold text-amber-500 border-t border-slate-800 pt-4">
                        <span>Total estimado</span>
                        <span class="font-mono"><?= e(number_format($total_final, 2)) ?>€</span>
                    </div>
                </div>

                <a href="checkout.php" class="block w-full text-center bg-amber-600 hover:bg-amber-700 text-slate-950 font-bold py-3.5 rounded-xl text-sm transition tracking-wide shadow-lg shadow-amber-600/10">
                    Tramitar Pedido
                </a>
            </div>

        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>