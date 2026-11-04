<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "tea_system";

// Crear la conexión
$conn = new mysqli($host, $user, $password, $database);

// Verificar si hay errores de conexión
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Configurar caracteres a UTF-8 para evitar problemas con eñes o acentos
$conn->set_charset("utf8");
?>