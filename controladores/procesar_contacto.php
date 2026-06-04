<?php
header('Content-Type: application/json');

$nombre  = trim($_POST['nombre'] ?? '');
$email   = trim($_POST['email'] ?? '');
$telefono= trim($_POST['telefono'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

if (empty($nombre) || empty($email) || empty($mensaje)) {
    echo json_encode(['ok' => false, 'msg' => 'Por favor completa todos los campos obligatorios.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'msg' => 'El correo electrónico no es válido.']);
    exit;
}

require_once __DIR__ . '/../config/conexion.php';

// Guardar en BD
$conexion->query("
    CREATE TABLE IF NOT EXISTS contactos (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(150) NOT NULL,
        email VARCHAR(150) NOT NULL,
        telefono VARCHAR(30),
        mensaje TEXT NOT NULL,
        leido TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
");

$stmt = $conexion->prepare('INSERT INTO contactos (nombre, email, telefono, mensaje) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $nombre, $email, $telefono, $mensaje);

if ($stmt->execute()) {
    echo json_encode(['ok' => true, 'msg' => '¡Mensaje enviado! Te contactaremos pronto.']);
} else {
    echo json_encode(['ok' => false, 'msg' => 'No se pudo enviar el mensaje. Intenta de nuevo.']);
}
$stmt->close();
?>
