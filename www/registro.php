<?php
//Cargar autoloader para validar el POST antes de cargar HTML
require_once __DIR__ . '/helpers/autoload.php';

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf_or_die();

    $nombres   = sanear_string($_POST['nombres'] ?? '');
    $apellidos = sanear_string($_POST['apellidos'] ?? '');
    $dni       = sanear_string($_POST['dni'] ?? '');
    $telefono  = sanear_string($_POST['telefono'] ?? '');
    $email     = filter_var(sanear_string($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $pass      = $_POST['password'] ?? '';

    if (!$nombres || !$email || empty($pass)) {
        $error = "Por favor, rellena todos los campos con un formato válido.";
    } elseif (strlen($pass) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres por seguridad.";
    } else {
        try {
            //Obtenemos la conexión PDO centralizada
            $pdo = obtener_conexion_db();
            
            $stmt = $pdo->prepare("SELECT id_user FROM USUARIO WHERE correo = :correo");
            $stmt->execute(['correo' => $email]);
            
            if ($stmt->fetch()) {
                $error = "Este correo electrónico ya está registrado en el Atelier.";
            } else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);

                $insert = $pdo->prepare("INSERT INTO USUARIO 
                    (nombres, apellidos, dni, correo, telefono, contrasenia) 
                    VALUES (:nombres, :apellidos, :dni, :correo, :telefono, :hash)");
                
                $insert->execute([
                    'nombres'   => $nombres,
                    'apellidos' => $apellidos,
                    'dni'       => $dni,
                    'correo'    => $email,
                    'telefono'  => $telefono,
                    'hash'      => $hash
                ]);

                $exito = "¡Registro completado con éxito! Bienvenido al Atelier Élie. Ya puedes iniciar sesión.";
            }
        } catch (PDOException $e) {
            $error = "Error en la base de datos: " . $e->getMessage();
        }
    }
}
require_once 'header.php';
?>

<div class="max-w-md mx-auto my-12 bg-slate-900 border border-slate-800 p-8 rounded-2xl shadow-2xl space-y-6">
    <div class="text-center">
        <span class="text-xs font-semibold tracking-widest text-amber-500 uppercase">Atelier Élie de Oxenfurt</span>
        <h2 class="text-2xl font-serif font-bold text-slate-100 mt-1">Crear una Cuenta</h2>
    </div>

    <?php if ($error): ?>
        <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3 rounded-lg text-sm"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($exito): ?>
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-3 rounded-lg text-sm"><?= e($exito) ?></div>
    <?php endif; ?>

    <form action="registro.php" method="POST" class="space-y-4">
        <?= campo_csrf() ?>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-semibold uppercase text-slate-400">Nombres</label>
                <input type="text" name="nombres" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2 text-sm text-slate-100 mt-1 focus:border-amber-500 outline-none">
            </div>
            <div>
                <label class="text-xs font-semibold uppercase text-slate-400">Apellidos</label>
                <input type="text" name="apellidos" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2 text-sm text-slate-100 mt-1 focus:border-amber-500 outline-none">
            </div>
        </div>
        <div>
            <label class="text-xs font-semibold uppercase text-slate-400">DNI</label>
            <input type="text" name="dni" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2 text-sm text-slate-100 mt-1 focus:border-amber-500 outline-none">
        </div>
        <div>
            <label class="text-xs font-semibold uppercase text-slate-400">Teléfono</label>
            <input type="text" name="telefono" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2 text-sm text-slate-100 mt-1 focus:border-amber-500 outline-none">
        </div>
        <div>
            <label class="text-xs font-semibold uppercase text-slate-400">Correo Electrónico</label>
            <input type="email" name="email" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2 text-sm text-slate-100 mt-1 focus:border-amber-500 outline-none">
        </div>
        <div>
            <label class="text-xs font-semibold uppercase text-slate-400">Contraseña</label>
            <input type="password" name="password" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2 text-sm text-slate-100 mt-1 focus:border-amber-500 outline-none">
        </div>
        <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-slate-950 font-bold py-2.5 rounded-lg transition text-sm">Registrarse en el Atelier</button>
    </form>

    <p class="text-xs text-center text-slate-500">¿Ya tienes cuenta? <a href="login.php" class="text-amber-500 hover:underline">Inicia sesión aquí</a></p>
</div>

<?php require_once 'footer.php'; ?>