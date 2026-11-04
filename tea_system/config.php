<?php
// Iniciar la sesión de forma segura si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir la conexión a la base de datos
require_once 'db.php';

// Definir una constante para la URL base si fuera necesario en el futuro
define('BASE_URL', 'http://localhost/tea_system/');
?>