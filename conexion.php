<?php
// Datos de conexión
$host = "localhost"; // Cambia si usas otro host
$usuario = "root"; // Usuario de tu base de datos
$contraseña = ""; // Contraseña del usuario
$base_datos = "water_bd_1"; // Nombre de la base de datos

// Crear conexión
$conexion = new mysqli($host, $usuario, $contraseña, $base_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
} else {
    // echo "Conexión exitosa a la base de datos.";
}
?>
