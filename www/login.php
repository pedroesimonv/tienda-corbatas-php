<?php
// Cargar el autoloader ANTES de cualquier HTML para procesar la redirección limpia
require_once __DIR__ . '/helpers/autoload.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf_or_die();

    $email = filter_var(sanear_string($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $pass  = $_POST['password'] ?? '';

    if (!$email || empty($pass)) {
        $error = "El correo electrónico o la contraseña son incorrectos.";
    } else {
        try {
            // Obtenemos la conexión PDO centralizada
            $pdo = obtener_conexion_db();

            $stmt = $pdo->prepare("SELECT * FROM USUARIO WHERE correo = :correo");
            $stmt->execute(['correo' => $email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                if ($usuario['bloqueado_hasta'] && strtotime($usuario['bloqueado_hasta']) > time()) {
                    $tiempo_restante = strtotime($usuario['bloqueado_hasta']) - time();
                    $minutos = ceil($tiempo_restante / 60);
                    $error = "Cuenta bloqueada temporalmente por seguridad. Inténtalo de nuevo en $minutos minutos.";
                } else {
                    if (password_verify($pass, $usuario['contrasenia'])) {
                        
                        // Prevenir fijación de sesión
                        session_regenerate_id(true);

                        $reset = $pdo->prepare("UPDATE USUARIO SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id_user = :id");
                        $reset->execute(['id' => $usuario['id_user']]);

                        $_SESSION['usuario'] = $usuario['nombres'];
                        $_SESSION['id_usuario'] = $usuario['id_user']; 

                        header("Location: index.php");
                        exit;
                    } else {
                        $nuevos_intentos = $usuario['intentos_fallidos'] + 1;
                        
                        if ($nuevos_intentos >= 5) {
                            $bloqueo = $pdo->prepare("UPDATE USUARIO SET intentos_fallidos = :intentos, bloqueado_hasta = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id_user = :id");
                            $bloqueo->execute(['intentos' => $nuevos_intentos, 'id' => $usuario['id_user']]);
                            $error = "Has superado el límite de intentos. Cuenta bloqueada por 15 minutos.";
                        } else {
                            $bloqueo = $pdo->prepare("UPDATE USUARIO SET intentos_fallidos = :intentos WHERE id_user = :id");
                            $bloqueo->execute(['intentos' => $nuevos_intentos, 'id' => $usuario['id_user']]);
                            $error = "El correo electrónico o la contraseña son incorrectos.";
                        }
                    }
                }
            } else {
                $error = "El correo electrónico o la contraseña son incorrectos.";
            }
        } catch (PDOException $e) {
            $error = "Error de conexión: " . $e->getMessage();
        }
    }
}

// Una vez procesada la lógica, cargamos la interfaz HTML
require_once 'header.php';
?>

<div class="max-w-md mx-auto my-12 bg-slate-900 border border-slate-800 p-8 rounded-2xl shadow-2xl space-y-6">
    <div class="text-center">
        <span class="text-xs font-semibold tracking-widest text-amber-500 uppercase">Acceso Restringido</span>
        <h2 class="text-2xl font-serif font-bold text-slate-100 mt-1">Iniciar Sesión</h2>
    </div>

    <?php if ($error): ?>
        <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3 rounded-lg text-sm text-center"><?= e($error) ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST" class="space-y-4">
        <!-- TOKEN CSRF OBLIGATORIO -->
        <?= campo_csrf() ?>

        <div>
            <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">Correo Electrónico</label>
            <input type="email" name="email" required class="w-full bg-slate-950 border border-slate-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-lg px-4 py-2 text-sm text-slate-100 outline-none transition mt-1">
        </div>
        <div>
            <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">Contraseña</label>
            <input type="password" name="password" required class="w-full bg-slate-950 border border-slate-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-lg px-4 py-2 text-sm text-slate-100 outline-none transition mt-1">
        </div>
        <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-slate-950 font-bold py-2.5 rounded-lg transition text-sm">Entrar al Atelier</button>
    </form>

    <p class="text-xs text-center text-slate-500">¿Eres nuevo? <a href="registro.php" class="text-amber-500 hover:underline">Regístrate en el gremio</a></p>
</div>

<?php require_once 'footer.php'; ?>