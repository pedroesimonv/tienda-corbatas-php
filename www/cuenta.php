<?php
//Cargar autoloader antes del HTML para gestionar redirecciones de sesión
require_once __DIR__ . '/helpers/autoload.php';

//Control de accesos: Si no está logueado, directo al login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_usuario'];
$tab = isset($_GET['tab']) ? sanear_string($_GET['tab']) : 'pedidos';

$mensaje_exito_datos = '';
$error_datos = '';

try {
    // Obtenemos la conexión PDO centralizada
    $pdo = obtener_conexion_db();

    //PESTAÑA PEDIDOS
    $pedidos = [];
    if ($tab === 'pedidos') {
        $stmtP = $pdo->prepare("SELECT id_pedido, total, fecha_pedido, estado FROM PEDIDO WHERE id_user = :id_user ORDER BY fecha_pedido DESC");
        $stmtP->execute(['id_user' => $id_user]);
        $pedidos = $stmtP->fetchAll(PDO::FETCH_ASSOC);
    }

    //PESTAÑA DATOS (PROCESAMIENTO Y LECTURA)
    $usuario_info = null;
    $direccion_info = null;

    if ($tab === 'datos') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar Token CSRF obligatorio
            verificar_csrf_or_die();
            
            //Actualizar Perfil y Dirección
            if (isset($_POST['accion_datos']) && $_POST['accion_datos'] === 'actualizar_perfil') {
                $nuevo_correo   = filter_var(sanear_string($_POST['correo'] ?? ''), FILTER_VALIDATE_EMAIL);
                $nuevo_telefono = sanear_string($_POST['telefono'] ?? '');
                $nueva_calle    = sanear_string($_POST['calle'] ?? '');
                $nueva_ciudad   = sanear_string($_POST['ciudad'] ?? '');
                $nuevo_cp       = sanear_string($_POST['codigo_postal'] ?? '');

                if ($nuevo_correo && !empty($nuevo_telefono)) {
                    // Actualizar USUARIO
                    $stmtUpUser = $pdo->prepare("UPDATE USUARIO SET correo = :correo, telefono = :telefono WHERE id_user = :id_user");
                    $stmtUpUser->execute([
                        'correo'   => $nuevo_correo,
                        'telefono' => $nuevo_telefono,
                        'id_user'  => $id_user
                    ]);

                    // Actualizar o Insertar DIRECCION
                    $stmtChkDir = $pdo->prepare("SELECT id_direccion FROM DIRECCION WHERE id_user = :id_user");
                    $stmtChkDir->execute(['id_user' => $id_user]);
                    
                    if ($stmtChkDir->fetch()) {
                        $stmtUpDir = $pdo->prepare("UPDATE DIRECCION SET calle = :calle, ciudad = :ciudad, codigo_postal = :cp WHERE id_user = :id_user");
                        $stmtUpDir->execute([
                            'calle'   => $nueva_calle,
                            'ciudad'  => $nueva_ciudad,
                            'cp'      => $nuevo_cp,
                            'id_user' => $id_user
                        ]);
                    } else {
                        $stmtInsDir = $pdo->prepare("INSERT INTO DIRECCION (id_user, calle, ciudad, codigo_postal) VALUES (:id_user, :calle, :ciudad, :cp)");
                        $stmtInsDir->execute([
                            'id_user' => $id_user,
                            'calle'   => $nueva_calle,
                            'ciudad'  => $nueva_ciudad,
                            'cp'      => $nuevo_cp
                        ]);
                    }

                    $mensaje_exito_datos = "Perfil y datos de entrega actualizados correctamente.";
                } else {
                    $error_datos = "El correo y el teléfono deben tener un formato válido.";
                }
            }

            //Cambiar Contraseña
            if (isset($_POST['accion_datos']) && $_POST['accion_datos'] === 'cambiar_password') {
                $pass_actual    = $_POST['pass_actual'] ?? '';
                $pass_nueva     = $_POST['pass_nueva'] ?? '';
                $pass_confirmar = $_POST['pass_confirmar'] ?? '';

                if (!empty($pass_actual) && !empty($pass_nueva) && !empty($pass_confirmar)) {
                    if ($pass_nueva === $pass_confirmar) {
                        $stmtPass = $pdo->prepare("SELECT contrasenia FROM USUARIO WHERE id_user = :id_user");
                        $stmtPass->execute(['id_user' => $id_user]);
                        $user_db_res = $stmtPass->fetch(PDO::FETCH_ASSOC);

                        if ($user_db_res && password_verify($pass_actual, $user_db_res['contrasenia'])) {
                            $hash_nueva = password_hash($pass_nueva, PASSWORD_BCRYPT);
                            
                            $stmtUpPass = $pdo->prepare("UPDATE USUARIO SET contrasenia = :pass WHERE id_user = :id_user");
                            $stmtUpPass->execute([
                                'pass'    => $hash_nueva,
                                'id_user' => $id_user
                            ]);

                            $mensaje_exito_datos = "Contraseña actualizada con éxito.";
                        } else {
                            $error_datos = "La contraseña actual introducida no es correcta.";
                        }
                    } else {
                        $error_datos = "La nueva contraseña y su confirmación no coinciden.";
                    }
                } else {
                    $error_datos = "Por favor, completa todos los campos de contraseña.";
                }
            }
        }

        // Cargar datos tras procesamiento
        $stmtU = $pdo->prepare("SELECT nombres, apellidos, dni, correo, telefono FROM USUARIO WHERE id_user = :id_user");
        $stmtU->execute(['id_user' => $id_user]);
        $usuario_info = $stmtU->fetch(PDO::FETCH_ASSOC);

        $stmtD = $pdo->prepare("SELECT calle, ciudad, codigo_postal FROM DIRECCION WHERE id_user = :id_user LIMIT 1");
        $stmtD->execute(['id_user' => $id_user]);
        $direccion_info = $stmtD->fetch(PDO::FETCH_ASSOC);
    }

    //PESTAÑA FAVORITOS
    $favoritos = [];
    if ($tab === 'favoritos') {
        $stmtF = $pdo->prepare("
            SELECT c.id_corbata, c.color, c.material, c.precio, c.imagen 
            FROM FAVORITO f
            JOIN CORBATA c ON f.id_corbata = c.id_corbata
            WHERE f.id_user = :id_user
        ");
        $stmtF->execute(['id_user' => $id_user]);
        $favoritos = $stmtF->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $error_datos = "Error en el panel: " . $e->getMessage();
}

require_once 'header.php';
?>

<div class="max-w-6xl mx-auto my-12 px-4">
    <div class="flex flex-col md:flex-row gap-8">
        
        <!-- BARRA LATERAL -->
        <div class="w-full md:w-1/4 space-y-2">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 text-center space-y-2">
                <div class="w-16 h-16 bg-amber-600/10 border border-amber-500/20 text-amber-500 rounded-full flex items-center justify-center mx-auto text-2xl font-serif font-bold uppercase">
                    <?= e(mb_substr($_SESSION['usuario'], 0, 1, 'UTF-8')) ?>
                </div>
                <div>
                    <h2 class="text-slate-100 font-bold font-serif"><?= e($_SESSION['usuario']) ?></h2>
                    <p class="text-xs text-slate-500">Miembro del Atelier</p>
                </div>
            </div>

            <nav class="bg-slate-900 border border-slate-800 rounded-2xl p-3 flex flex-row md:flex-col gap-1 overflow-x-auto">
                <a href="cuenta.php?tab=pedidos" class="flex-1 md:flex-none text-center md:text-left px-4 py-2.5 rounded-xl text-sm font-medium transition <?= $tab === 'pedidos' ? 'bg-amber-600 text-slate-950 font-bold' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' ?>">
                    📦 Mis Encargos
                </a>
                <a href="cuenta.php?tab=datos" class="flex-1 md:flex-none text-center md:text-left px-4 py-2.5 rounded-xl text-sm font-medium transition <?= $tab === 'datos' ? 'bg-amber-600 text-slate-950 font-bold' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' ?>">
                    🔑 Datos e Historial
                </a>
                <a href="cuenta.php?tab=favoritos" class="flex-1 md:flex-none text-center md:text-left px-4 py-2.5 rounded-xl text-sm font-medium transition <?= $tab === 'favoritos' ? 'bg-amber-600 text-slate-950 font-bold' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' ?>">
                    ⭐ Lista de Deseos
                </a>
                <div class="border-t border-slate-800/60 my-2 hidden md:block"></div>
                <a href="logout.php" class="text-center md:text-left px-4 py-2.5 rounded-xl text-sm font-medium text-rose-500 hover:bg-rose-500/10 transition">
                    🚪 Cerrar Sesión
                </a>
            </nav>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="flex-1 bg-slate-900 border border-slate-800 rounded-2xl p-6 md:p-8 shadow-2xl">
            
            <!-- PESTAÑA 1: PEDIDOS -->
            <?php if ($tab === 'pedidos'): ?>
                <div class="space-y-6">
                    <div>
                        <h1 class="text-2xl font-serif font-bold text-slate-100">Historial de Encargos</h1>
                        <p class="text-xs text-slate-400 mt-1">Registros de compras y estados de envíos en Oxenfurt.</p>
                    </div>

                    <?php if (empty($pedidos)): ?>
                        <div class="border border-dashed border-slate-800 rounded-xl p-12 text-center text-slate-500 text-sm">
                            Todavía no has realizado ningún encargo en la colección del Atelier Élie.
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($pedidos as $pedido): ?>
                                <div class="border border-slate-800 bg-slate-950/40 rounded-xl p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm font-mono font-bold text-slate-200">Pedido #<?= e($pedido['id_pedido']) ?></span>
                                            <span class="text-[10px] px-2 py-0.5 rounded font-semibold tracking-wider uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                <?= e($pedido['estado'] ?? 'Procesado') ?>
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500">Realizado el: <?= e(date('d/m/Y H:i', strtotime($pedido['fecha_pedido']))) ?></p>
                                    </div>
                                    <div class="text-right w-full sm:w-auto flex sm:flex-col justify-between sm:justify-center items-center sm:items-end">
                                        <span class="text-xs text-slate-400 sm:hidden">Total cobrado:</span>
                                        <span class="text-base font-bold text-amber-500"><?= e(number_format($pedido['total'], 2)) ?>€</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            <!-- PESTAÑA 2: DATOS PERSONALES Y EDICIÓN -->
            <?php elseif ($tab === 'datos'): ?>
                <div class="space-y-8">
                    <div>
                        <h1 class="text-2xl font-serif font-bold text-slate-100">Gestión de Perfil y Seguridad</h1>
                        <p class="text-xs text-slate-400 mt-1">Modifica tus datos de contacto, dirección de entrega o renueva tu contraseña.</p>
                    </div>

                    <?php if ($mensaje_exito_datos): ?>
                        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-3 rounded-lg text-sm text-center">
                            <?= e($mensaje_exito_datos) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_datos): ?>
                        <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3 rounded-lg text-sm text-center">
                            <?= e($error_datos) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario 1: Perfil y Dirección -->
                    <form action="cuenta.php?tab=datos" method="POST" class="space-y-6">
                        <?= campo_csrf() ?>
                        <input type="hidden" name="accion_datos" value="actualizar_perfil">

                        <div class="border-b border-slate-800 pb-3">
                            <h3 class="text-sm font-bold text-amber-500 uppercase tracking-wider">Información Personal y Logística</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="space-y-1">
                                <label class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Nombre Completo</label>
                                <input type="text" disabled value="<?= e(($usuario_info['nombres'] ?? '') . ' ' . ($usuario_info['apellidos'] ?? '')) ?>" class="w-full bg-slate-950/60 border border-slate-850 rounded-lg px-3 py-2 text-slate-400 cursor-not-allowed">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">DNI / Identificación</label>
                                <input type="text" disabled value="<?= e($usuario_info['dni'] ?? '') ?>" class="w-full bg-slate-950/60 border border-slate-850 rounded-lg px-3 py-2 text-slate-400 font-mono cursor-not-allowed">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="space-y-1">
                                <label class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Correo Electrónico</label>
                                <input type="email" name="correo" required value="<?= e($usuario_info['correo'] ?? '') ?>" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-200 outline-none focus:border-amber-500 transition">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Teléfono de Contacto</label>
                                <input type="text" name="telefono" required value="<?= e($usuario_info['telefono'] ?? '') ?>" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-200 outline-none focus:border-amber-500 transition">
                            </div>
                        </div>

                        <div class="space-y-4 pt-2">
                            <div class="space-y-1 text-sm">
                                <label class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Dirección de Entrega (Calle, Número, Piso)</label>
                                <input type="text" name="calle" value="<?= e($direccion_info['calle'] ?? '') ?>" placeholder="Ej: Calle de las Puertas del Sur 12" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-200 outline-none focus:border-amber-500 transition">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div class="space-y-1">
                                    <label class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Ciudad</label>
                                    <input type="text" name="ciudad" value="<?= e($direccion_info['ciudad'] ?? '') ?>" placeholder="Ej: Oxenfurt" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-200 outline-none focus:border-amber-500 transition">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Código Postal</label>
                                    <input type="text" name="codigo_postal" value="<?= e($direccion_info['codigo_postal'] ?? '') ?>" placeholder="Ej: 03008" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-200 outline-none focus:border-amber-500 transition">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-slate-950 font-bold px-5 py-2.5 rounded-lg text-sm transition">
                            Guardar Cambios de Perfil
                        </button>
                    </form>

                    <div class="border-t border-slate-800 my-6"></div>

                    <!-- Formulario 2: Cambio de Contraseña -->
                    <form action="cuenta.php?tab=datos" method="POST" class="space-y-4">
                        <?= campo_csrf() ?>
                        <input type="hidden" name="accion_datos" value="cambiar_password">

                        <div class="border-b border-slate-800 pb-3">
                            <h3 class="text-sm font-bold text-amber-500 uppercase tracking-wider">Seguridad y Renovación de Clave</h3>
                        </div>

                        <div class="space-y-1 text-sm">
                            <label class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Contraseña Actual</label>
                            <input type="password" name="pass_actual" required placeholder="••••••••" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-200 outline-none focus:border-amber-500 transition">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="space-y-1">
                                <label class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Nueva Contraseña</label>
                                <input type="password" name="pass_nueva" required placeholder="••••••••" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-200 outline-none focus:border-amber-500 transition">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Confirmar Nueva Contraseña</label>
                                <input type="password" name="pass_confirmar" required placeholder="••••••••" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-200 outline-none focus:border-amber-500 transition">
                            </div>
                        </div>

                        <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold px-5 py-2.5 rounded-lg text-sm transition border border-slate-700">
                            Actualizar Contraseña
                        </button>
                    </form>
                </div>

            <!-- PESTAÑA 3: FAVORITOS -->
            <?php elseif ($tab === 'favoritos'): ?>
                <div class="space-y-6">
                    <div>
                        <h1 class="text-2xl font-serif font-bold text-slate-100">Lista de Deseos</h1>
                        <p class="text-xs text-slate-400 mt-1">Tus piezas exclusivas reservadas para futuras adquisiciones.</p>
                    </div>
                    
                    <?php if (empty($favoritos)): ?>
                        <div class="border border-dashed border-slate-800 rounded-xl p-12 text-center text-slate-500 text-sm">
                            Tu lista de deseos está vacía. Explora el catálogo para añadir tus piezas preferidas.
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php foreach ($favoritos as $fav): ?>
                                <div class="border border-slate-800 bg-slate-950/40 rounded-xl p-4 flex items-center gap-4">
                                    <img src="<?= e($fav['imagen']) ?>" alt="Corbata" class="w-16 h-16 object-cover rounded-lg bg-slate-900 border border-slate-800">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-serif font-bold text-slate-200 truncate">Corbata de <?= e($fav['material'] . ' ' . $fav['color']) ?></h4>
                                        <p class="text-xs text-amber-500 font-semibold mt-0.5"><?= e(number_format($fav['precio'], 2)) ?>€</p>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <a href="producto.php?id=<?= e($fav['id_corbata']) ?>" class="text-center bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold px-3 py-1.5 rounded-md transition">
                                            Ver
                                        </a>
                                        <a href="gestionar_favorito.php?id=<?= e($fav['id_corbata']) ?>" class="text-center text-rose-500 hover:text-rose-400 text-[11px] transition underline">
                                            Quitar
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>