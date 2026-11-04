<?php
// Incluimos la configuración inicial (que arranca las sesiones)
require_once 'config.php';

// Si el usuario ya estaba logueado, lo mandamos directo al Dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

// PROCESAR EL FORMULARIO CUANDO EL USUARIO HACE CLIC EN "INICIAR SESIÓN"
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {

        // Buscar el usuario
        $stmt = $conn->prepare("SELECT id_user, username, password, role FROM Users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {

            $user = $result->fetch_assoc();

            // Verificar contraseña encriptada
            if (password_verify($password, $user['password'])) {

                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                header("Location: dashboard.php");
                exit;

            } else {
                $error = "Contraseña incorrecta. Intente de nuevo.";
            }

        } else {
            $error = "El usuario no existe en el sistema.";
        }

    } else {
        $error = "Por favor, complete todos los campos.";
    }
}

// Incluimos el encabezado visual
include 'includes/header.php';
?>

<div class="login-wrapper">
    <div class="login-container">
        <div class="login-brand">
            <h2>🍵 Té Devoto</h2>
            <p>Sistema de Gestión Interna</p>
        </div>

        <h3>Iniciar Sesión</h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                ⚠️ <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="index.php" method="POST" class="login-form">

            <div class="form-group">
                <label>Nombre de Usuario:</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
    <label>Contraseña:</label>

    <input type="password"
           id="password"
           name="password"
           required>

    <div style="margin-top:8px;">
        <input type="checkbox"
               id="showPassword"
               onclick="mostrarPassword()">

        <label for="showPassword" style="font-size:14px;">
            Mostrar contraseña
        </label>
    </div>
</div>

            <button type="submit" name="login" class="btn-login">
                Ingresar al Sistema
            </button>

        </form>

       <div class="login-footer">
    <p><a href="forgot_password.php">¿Olvidaste tu contraseña?</a></p>
    <br>
    <p>¿No tienes cuenta? <a href="register.php">Registrate acá</a></p>
</div>

    </div>
</div>

<script>
function mostrarPassword() {

    var x = document.getElementById("password");

    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }

}
</script>

<?php
include 'includes/footer.php';
?>