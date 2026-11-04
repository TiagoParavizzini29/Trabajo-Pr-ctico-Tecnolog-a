<?php
require_once 'config.php';

$mensaje = "";
$tipo_mensaje = "";
$mostrar_confirmacion = false;

$username = "";
$new_password = "";
$confirm_password = "";

// PRIMER PASO: comprobar los datos
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['continuar'])) {

    $username = trim($_POST['username']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($new_password) || empty($confirm_password)) {

        $mensaje = "Por favor, complete todos los campos.";
        $tipo_mensaje = "error";

    } elseif ($new_password !== $confirm_password) {

        $mensaje = "Las contraseñas no coinciden.";
        $tipo_mensaje = "error";

    } elseif (strlen($new_password) < 6) {

        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
        $tipo_mensaje = "error";

    } else {

        // Comprobar que el usuario existe
        $stmt = $conn->prepare("SELECT id_user FROM Users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {

            // Todo está correcto: mostramos la confirmación
            $mostrar_confirmacion = true;

        } else {

            $mensaje = "No existe ningún usuario con ese nombre.";
            $tipo_mensaje = "error";
        }
    }
}


// SEGUNDO PASO: confirmar el cambio
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirmar_cambio'])) {

    $username = trim($_POST['username']);
    $new_password = $_POST['new_password'];

    // Volvemos a buscar el usuario
    $stmt = $conn->prepare("SELECT id_user FROM Users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {

        $user = $result->fetch_assoc();

        // Crear hash seguro
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // Actualizar contraseña
        $stmt_update = $conn->prepare(
            "UPDATE Users SET password = ? WHERE id_user = ?"
        );

        $stmt_update->bind_param(
            "si",
            $password_hash,
            $user['id_user']
        );

        if ($stmt_update->execute()) {

            $mensaje = "✅ Contraseña cambiada correctamente. Ya podés iniciar sesión con tu nueva contraseña.";
            $tipo_mensaje = "success";

        } else {

            $mensaje = "❌ Ocurrió un error al cambiar la contraseña.";
            $tipo_mensaje = "error";
        }

    } else {

        $mensaje = "No existe ningún usuario con ese nombre.";
        $tipo_mensaje = "error";
    }
}

include 'includes/header.php';
?>

<div class="login-wrapper">
    <div class="login-container">

        <div class="login-brand">
            <h2>🍵 Té Devoto</h2>
            <p>Sistema de Gestión Interna</p>
        </div>

        <h3>Recuperar contraseña</h3>

        <?php if (!empty($mensaje)): ?>

            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>

        <?php endif; ?>


        <?php if ($mostrar_confirmacion): ?>

            <!-- CONFIRMACIÓN DEL CAMBIO -->

            <div class="form-group" style="text-align:center; margin-top:20px;">

                <p>
                    ¿Deseás establecer esta como tu nueva contraseña?
                </p>

                <form action="forgot_password.php" method="POST">

                    <input
                        type="hidden"
                        name="username"
                        value="<?php echo htmlspecialchars($username); ?>"
                    >

                    <input
                        type="hidden"
                        name="new_password"
                        value="<?php echo htmlspecialchars($new_password); ?>"
                    >

                    <button
                        type="submit"
                        name="confirmar_cambio"
                        class="btn-login"
                    >
                        Sí, cambiar contraseña
                    </button>

                </form>

                <br>

                <a href="forgot_password.php">
                    Cancelar
                </a>

            </div>


        <?php else: ?>


            <!-- FORMULARIO PARA NUEVA CONTRASEÑA -->

            <form action="forgot_password.php" method="POST" class="login-form">

                <div class="form-group">

                    <label for="username">
                        Nombre de Usuario:
                    </label>

                    <input
                        type="text"
                        name="username"
                        id="username"
                        placeholder="Ej: german_devoto"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="new_password">
                        Nueva Contraseña:
                    </label>

                    <input
                        type="password"
                        name="new_password"
                        id="new_password"
                        placeholder="Mínimo 6 caracteres"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="confirm_password">
                        Confirmar Nueva Contraseña:
                    </label>

                    <input
                        type="password"
                        name="confirm_password"
                        id="confirm_password"
                        placeholder="Repita su contraseña"
                        required
                    >

                </div>


                <button
                    type="submit"
                    name="continuar"
                    class="btn-login"
                >
                    Continuar
                </button>

            </form>


        <?php endif; ?>


        <div class="login-footer">

            <p>
                <a href="index.php">
                    ← Volver al inicio de sesión
                </a>
            </p>

        </div>

    </div>
</div>

<?php
include 'includes/footer.php';
?>