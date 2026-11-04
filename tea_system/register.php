<?php
require_once 'config.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_user'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $age = intval($_POST['age']);
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $role = $_POST['role'];

    if (!empty($username) && !empty($password) && !empty($first_name) && !empty($last_name) && $age > 0) {
        $stmt_check = $conn->prepare("SELECT id_user FROM Users WHERE username = ?");
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $mensaje = "<div class='alert alert-error' style='background:#FFEBEB; color:#8A0000; padding:12px; border-radius:4px; margin-bottom:15px; border-left:4px solid #FF4D4D;'>⚠️ El nombre de usuario ya está registrado.</div>";
        } else {
            $stmt = $conn->prepare("INSERT INTO Users (first_name, last_name, age, username, password, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisss", $first_name, $last_name, $age, $username, $password, $role);
            if ($stmt->execute()) {
                $mensaje = "<div class='alert alert-success' style='background:#E8F4FF; color:#0A192F; padding:12px; border-radius:4px; margin-bottom:15px; border-left:4px solid var(--celeste);'>✅ Usuario registrado con éxito. <a href='index.php' style='color:var(--azul-marino); font-weight:bold;'>Iniciar Sesión aquí</a></div>";
            } else {
                $mensaje = "<div class='alert alert-error' style='background:#FFEBEB; color:#8A0000; padding:12px; border-radius:4px; margin-bottom:15px; border-left:4px solid #FF4D4D;'>❌ Error al registrar el usuario.</div>";
            }
        }
    } else {
        $mensaje = "<div class='alert alert-error' style='background:#FFEBEB; color:#8A0000; padding:12px; border-radius:4px; margin-bottom:15px; border-left:4px solid #FF4D4D;'>⚠️ Por favor completa todos los campos correctamente.</div>";
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="register-container" style="max-width: 500px; margin: 50px auto; padding: 0 20px;">
    <div class="crud-card" style="background: white; padding: 30px; border-radius: 8px; border: 1px solid #E2E8F0; border-top: 4px solid var(--azul-marino); box-shadow: 0 4px 10px rgba(0,0,0,0.02);">
        <h2 style="color: var(--azul-marino); margin-bottom: 10px;">Registrar Nuevo Personal</h2>
        <p style="color: #718096; font-size: 14px; margin-bottom: 20px;">Alta de nuevas credenciales para el personal de la distribuidora de té.</p>
        
        <?php echo $mensaje; ?>

        <form action="register.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
            <div>
                <label style="display:block; margin-bottom:5px; font-weight:600; color: var(--azul-medio);">Nombre:</label>
                <input type="text" name="first_name" required placeholder="ej: Germán" style="width:100%; padding:10px; border:1px solid #CBD5E0; border-radius:4px;">
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; font-weight:600; color: var(--azul-medio);">Apellido:</label>
                <input type="text" name="last_name" required placeholder="ej: Paravizzini" style="width:100%; padding:10px; border:1px solid #CBD5E0; border-radius:4px;">
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; font-weight:600; color: var(--azul-medio);">Edad:</label>
                <input type="number" name="age" min="16" max="100" required placeholder="ej: 25" style="width:100%; padding:10px; border:1px solid #CBD5E0; border-radius:4px;">
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; font-weight:600; color: var(--azul-medio);">Nombre de Usuario:</label>
                <input type="text" name="username" required placeholder="ej: german_devoto" style="width:100%; padding:10px; border:1px solid #CBD5E0; border-radius:4px;">
            </div>
            
            <div>
                <label style="display:block; margin-bottom:5px; font-weight:600; color: var(--azul-medio);">Contraseña de Acceso:</label>
                <input type="password" name="password" required placeholder="••••••••" style="width:100%; padding:10px; border:1px solid #CBD5E0; border-radius:4px;">
            </div>
            
            <div>
                <label style="display:block; margin-bottom:5px; font-weight:600; color: var(--azul-medio);">Rol del Usuario:</label>
                <select name="role" required style="width:100%; padding:10px; border:1px solid #CBD5E0; border-radius:4px; background: white;">
                    <option value="Admin">Administrador (Acceso Total)</option>
                    <option value="Empleado">Empleado (Solo Ventas/Stock)</option>
                </select>
            </div>
            
            <button type="submit" name="register_user" style="background: var(--azul-marino); color: white; border:none; padding:12px; font-weight:bold; border-radius:4px; cursor:pointer; margin-top:10px; transition: background 0.2s;">
                Crear Usuario
            </button>

            <!-- Enlace para ir directamente al Iniciar Sesión (Login) -->
            <div style="text-align: center; margin-top: 15px;">
                <p style="font-size: 14px; color: #718096; margin: 0;">
                    ¿Ya tienes cuenta? 
                    <a href="index.php" style="color: var(--azul-marino); font-weight: bold; text-decoration: none;">Iniciar Sesión</a>
                </p>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>