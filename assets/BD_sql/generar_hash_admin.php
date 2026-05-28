<?php
/**
 * Script para generar hash de contraseña del administrador
 * Ejecutar este archivo y copiar el hash generado al SQL
 */

$password = 'Admin2024!';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "==============================================\n";
echo "CREDENCIALES DE ADMINISTRADOR\n";
echo "==============================================\n";
echo "Email: admin@tumbestours.com\n";
echo "Password: $password\n";
echo "Hash: $hash\n";
echo "==============================================\n\n";

echo "SQL para insertar/actualizar:\n\n";
echo "UPDATE usuarios SET password_hash = '$hash' WHERE email = 'admin@tumbestours.com';\n\n";
echo "O si no existe:\n\n";
echo "INSERT INTO usuarios (tipo_documento, numero_documento, nombres, apellidos, email, telefono, password_hash, rol, estado)\n";
echo "VALUES ('DNI', '12345678', 'Administrador', 'Sistema', 'admin@tumbestours.com', '942123456', '$hash', 'Admin', 'Activo');\n";
?>
