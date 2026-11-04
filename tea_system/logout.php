<?php
// Iniciar sesión para poder destruirla
session_start();

// Limpiar todas las variables de sesión
$_SESSION = array();

// Destruir la sesión por completo
session_destroy();

// Redirigir al usuario a la pantalla de Login (index.php)
header("Location: index.php");
exit;
?>