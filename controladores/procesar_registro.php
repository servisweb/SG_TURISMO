<?php
session_start();

$nombres = isset($_POST['nombres']) ? trim($_POST['nombres']) : '';
$apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// Validar campos
if (empty($nombres) || empty($apellidos) || empty($email) || empty($password)) {
    header('Location: ../views/login.php?error=empty');
    exit;
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../views/login.php?error=invalid_email');
    exit;
}

// Validar que las contraseñas coincidan
if ($password !== $confirm_password) {
    header('Location: ../views/login.php?error=password_mismatch');
    exit;
}

// Validar longitud de contraseña
if (strlen($password) < 6) {
    header('Location: ../views/login.php?error=password_short');
    exit;
}

// En producción, aquí se insertaría en la base de datos
// Por ahora, simulamos que se guardó correctamente
// y redirigimos al login con mensaje de éxito

header('Location: ../views/login.php?success=registered');
exit;
?>
