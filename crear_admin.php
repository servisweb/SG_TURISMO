<?php
require_once 'config/conexion.php';

// Credenciales del administrador
$email = 'admin@tumbestours.com';
$password = 'Admin2024!';
$nombres = 'Administrador';
$apellidos = 'Sistema';
$tipo_documento = 'DNI';
$numero_documento = '99999999';
$telefono = '999999999';
$rol = 'Admin';

// Generar hash de la contraseña
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Verificar si ya existe el admin
$check = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo "❌ El usuario admin ya existe en la base de datos.\n";
    echo "Si deseas actualizar la contraseña, elimina el usuario primero.\n";
} else {
    // Insertar el usuario administrador
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombres, apellidos, tipo_documento, numero_documento, email, telefono, password_hash, rol, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssssss", $nombres, $apellidos, $tipo_documento, $numero_documento, $email, $telefono, $password_hash, $rol);
    
    if ($stmt->execute()) {
        echo "✅ Usuario administrador creado exitosamente!\n\n";
        echo "═══════════════════════════════════════\n";
        echo "📧 Email: " . $email . "\n";
        echo "🔑 Contraseña: " . $password . "\n";
        echo "═══════════════════════════════════════\n\n";
        echo "⚠️  IMPORTANTE: Guarda estas credenciales en un lugar seguro.\n";
        echo "💡 Puedes acceder al panel admin en: /views/admin/dashboard.php\n";
    } else {
        echo "❌ Error al crear el usuario: " . $stmt->error . "\n";
    }
    
    $stmt->close();
}

$check->close();
$conexion->close();
?>
