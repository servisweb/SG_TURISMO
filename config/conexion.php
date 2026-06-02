<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'gestion_turismo_v4';

$conexion = new mysqli($host, $user, $password, $database);

if ($conexion->connect_error) {
    echo "<h2 style='color:red;'>TENEMOS DIFICULTADES PARA PROCESAR ESTO: " . $conexion->connect_error . "</h2>";
    exit;
}

$conexion->set_charset('utf8mb4');

?>