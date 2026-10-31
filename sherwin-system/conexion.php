<?php
// CONEXIÓN LOCAL: Conecta con la base de datos en tu computadora (XAMPP/WAMP).
$conexion = new mysqli("localhost", "root", "", "sherwin_db");

// CODIFICACIÓN: Configura UTF-8 para que los acentos y la letra Ñ se vean bien.
$conexion->set_charset("utf8mb4");

// CONTROL DE ERROR: Si falla, frena el sistema y muestra el error.
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>