<?php
// Cargar autoloader para validar POST y sesión antes de generar respuesta HTML
require_once __DIR__ . '/helpers/autoload.php';

// Control de acceso: requiere inicio de sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

// Verificar que el carrito contenga productos
if (empty($_SESSION['carrito'])) {
    header("Location: carrito.php");
    exit;
}

$id_user = $_SESSION['id_usuario'];
$error = '';
$exito = '';

// 1. Recálculo del resumen para la interfaz (Siempre disponible aunque falle la BD)
$subtotal_general = 0;
foreach ($_SESSION['carrito'] as $item) {
    $subtotal_general += $item['precio'] * $item['cantidad'];
}
$iva = $subtotal_general * 0.21;
$total_final = $subtotal_general + $iva;

try {
    // Obtener la conexión a la Base de Datos centralizada
    $pdo = obtener_conexion_db();

    // Obtener la dirección registrada del usuario
    $stmtDir = $pdo->prepare("SELECT id_direccion, calle, ciudad, codigo_postal FROM DIRECCION WHERE id_user = :id_user LIMIT 1");
    $stmtDir->execute(['id_user' => $id_user]);
    $direccion = $stmtDir->fetch(PDO::FETCH_ASSOC);

    // PROCESAR FORMULARIOS (POST) CON VALIDACIÓN CSRF Y SANITIZACIÓN
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verificar_csrf_or_die();

        // ACCIÓN A: Guardar dirección si el usuario no tenía una registrada
        if (isset($_POST['accion_checkout']) && $_POST['accion_checkout'] === 'guardar_direccion') {
            $calle  = sanear_string($_POST['calle'] ?? '');
            $ciudad = sanear_string($_POST['ciudad'] ?? '');
            $cp     = sanear_string($_POST['codigo_postal'] ?? '');

            if (!empty($calle) && !empty($ciudad) && !empty($cp)) {
                $stmtInsDir = $pdo->prepare("INSERT INTO DIRECCION (id_user, calle, ciudad, codigo_postal) VALUES (:id_user, :calle, :ciudad, :cp)");
                $stmtInsDir->execute([
                    'id_user' => $id_user,
                    'calle'   => $calle,
                    'ciudad'  => $ciudad,
                    'cp'      => $cp
                ]);

                // Recargar información de dirección actualizada
                $stmtDir->execute(['id_user' => $id_user]);
                $direccion = $stmtDir->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = "Por favor, completa todos los campos de la dirección de entrega.";
            }
        }

        // ACCIÓN B: Confirmar compra e iniciar Transacción SQL
        if (isset($_POST['accion_checkout']) && $_POST['accion_checkout'] === 'confirmar_pedido' && $direccion) {
            
            // Iniciar Transacción Atómica
            $pdo->beginTransaction();

            $total_pedido = 0;
            $items_pedido = [];

            // Verificar stock en caliente con bloqueo de lectura (FOR UPDATE)
            foreach ($_SESSION['carrito'] as $id_corbata => $item) {
                $stmtStock = $pdo->prepare("SELECT stock, precio, color, material FROM CORBATA WHERE id_corbata = :id FOR UPDATE");
                $stmtStock->execute(['id' => $id_corbata]);
                $corbata_db = $stmtStock->fetch(PDO::FETCH_ASSOC);

                if (!$corbata_db || $corbata_db['stock'] < $item['cantidad']) {
                    $pdo->rollBack();
                    $error = "Stock insuficiente para la corbata de " . e(($corbata_db['material'] ?? '') . ' ' . ($corbata_db['color'] ?? ''));
                    break;
                }

                $subtotal = $corbata_db['precio'] * $item['cantidad'];
                $total_pedido += $subtotal;

                $items_pedido[] = [
                    'id_corbata'      => $id_corbata,
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $corbata_db['precio']
                ];
            }

            // Si no hay errores de stock, insertar PEDIDO y LINEAS
            if (empty($error)) {
                // 2. Insertar Cabecera de Pedido con la dirección asociada
                $stmtPed = $pdo->prepare("INSERT INTO PEDIDO (id_user, id_direccion, total, estado, fecha_pedido) VALUES (:id_user, :id_direccion, :total, 'Procesado', NOW())");
                $stmtPed->execute([
                    'id_user'      => $id_user,
                    'id_direccion' => $direccion['id_direccion'],
                    'total'        => $total_pedido
                ]);
                $id_pedido_generado = $pdo->lastInsertId();

                // Insertar Líneas de Pedido y Descontar Stock
                $stmtLin = $pdo->prepare("INSERT INTO LINEA_PEDIDO (id_pedido, id_corbata, cantidad, precio_unitario) VALUES (:id_pedido, :id_corbata, :cantidad, :precio)");
                $stmtDec = $pdo->prepare("UPDATE CORBATA SET stock = stock - :cantidad WHERE id_corbata = :id_corbata");

                foreach ($items_pedido as $linea) {
                    $stmtLin->execute([
                        'id_pedido'  => $id_pedido_generado,
                        'id_corbata' => $linea['id_corbata'],
                        'cantidad'   => $linea['cantidad'],
                        'precio'     => $linea['precio_unitario']
                    ]);

                    $stmtDec->execute([
                        'cantidad'   => $linea['cantidad'],
                        'id_corbata' => $linea['id_corbata']
                    ]);
                }

                // Confirmar Transacción
                $pdo->commit();

                // Vaciar Carrito de la Sesión
                unset($_SESSION['carrito']);

                // Redirigir al panel de cuenta con pestaña de pedidos
                header("Location: cuenta.php?tab=pedidos&confirmado=1");
                exit;
            }
        }
    }

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $error = "Error al procesar el encargo: " . $e->getMessage();
}

require_once 'header.php';
?>

<div class="max-w-5xl mx-auto px-4 py-12">
    
    <div class="text-center space-y-2 mb-10">
        <span class="text-xs font-semibold tracking-widest text-amber-500 uppercase">Finalizar Encargo</span>
        <h1 class="text-3xl font-serif font-bold text-slate-100">Atelier Élie – Checkout</h1>
        <p class="text-xs text-slate-400">Revisa el desglose y confirma tu entrega en los Reinos del Norte.</p>
    </div>

    <?php if ($error): ?>
        <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-xl text-center text-sm mb-8">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- COLUMNA IZQUIERDA: DIRECCIÓN Y CONFIRMACIÓN -->
        <div class="lg:col-span-2 space-y-6">
            
            <?php if (!$direccion): ?>
                <!-- Formulario de Dirección si no existe -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                    <div class="border-b border-slate-800 pb-3">
                        <h2 class="text-lg font-serif font-bold text-amber-500">Dirección de Entrega Requerida</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Indica dónde debemos enviar las piezas confeccionadas.</p>
                    </div>

                    <form action="checkout.php" method="POST" class="space-y-4">
                        <?= campo_csrf() ?>
                        <input type="hidden" name="accion_checkout" value="guardar_direccion">

                        <div class="space-y-1 text-sm">
                            <label class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Calle y Número</label>
                            <input type="text" name="calle" required placeholder="Ej: Calle de las Puertas del Sur 12" 
                                   class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 outline-none focus:border-amber-500 transition">
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="space-y-1">
                                <label class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Ciudad / Villa</label>
                                <input type="text" name="ciudad" required placeholder="Ej: Oxenfurt" 
                                       class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 outline-none focus:border-amber-500 transition">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Código Postal</label>
                                <input type="text" name="codigo_postal" required placeholder="Ej: 03008" 
                                       class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 outline-none focus:border-amber-500 transition">
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-slate-950 font-bold py-2.5 rounded-lg text-sm transition">
                            Guardar Dirección y Continuar
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <!-- Resumen de Dirección Confirmada -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <h2 class="text-lg font-serif font-bold text-slate-100">Destino de Entrega</h2>
                        <a href="cuenta.php?tab=datos" class="text-xs text-amber-500 hover:underline">Modificar en perfil</a>
                    </div>
                    
                    <div class="text-sm space-y-1 text-slate-300">
                        <p class="font-semibold text-slate-100"><?= e($direccion['calle']) ?></p>
                        <p class="text-xs text-slate-400"><?= e($direccion['ciudad']) ?>, CP <?= e($direccion['codigo_postal']) ?></p>
                    </div>
                </div>

                <!-- Botón de Pago Transaccional -->
                <form action="checkout.php" method="POST">
                    <?= campo_csrf() ?>
                    <input type="hidden" name="accion_checkout" value="confirmar_pedido">

                    <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-slate-950 font-bold py-4 rounded-xl text-base transition tracking-wide shadow-xl shadow-amber-600/10">
                        Confirmar y Procesar Encargo (<?= e(number_format($total_final, 2)) ?>€)
                    </button>
                </form>
            <?php endif; ?>

        </div>

        <!-- COLUMNA DERECHA: DESGLOSE DE ARTÍCULOS -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 h-fit space-y-6 shadow-xl">
            <h2 class="text-lg font-serif font-bold text-slate-100 border-b border-slate-800 pb-3">Resumen de Piezas</h2>
            
            <div class="space-y-4 max-h-80 overflow-y-auto pr-1">
                <?php foreach ($_SESSION['carrito'] as $id => $item): ?>
                    <div class="flex items-center justify-between text-xs border-b border-slate-800/60 pb-3">
                        <div>
                            <p class="font-bold text-slate-200"><?= e($item['nombre']) ?></p>
                            <p class="text-slate-500 mt-0.5"><?= e($item['cantidad']) ?> x <?= e(number_format($item['precio'], 2)) ?>€</p>
                        </div>
                        <span class="font-mono text-slate-300 font-bold"><?= e(number_format($item['precio'] * $item['cantidad'], 2)) ?>€</span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="space-y-2 text-xs border-t border-slate-800 pt-4">
                <div class="flex justify-between text-slate-400">
                    <span>Base Imponible</span>
                    <span class="font-mono"><?= e(number_format($subtotal_general, 2)) ?>€</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>IVA (21%)</span>
                    <span class="font-mono"><?= e(number_format($iva, 2)) ?>€</span>
                </div>
                <div class="flex justify-between text-base font-bold text-amber-500 border-t border-slate-800/80 pt-3">
                    <span>Total General</span>
                    <span class="font-mono"><?= e(number_format($total_final, 2)) ?>€</span>
                </div>
            </div>
        </div>

    </div>

</div>

<?php 
require_once 'footer.php'; 
?>