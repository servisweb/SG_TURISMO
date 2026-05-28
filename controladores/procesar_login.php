<?php
session_start();

// Datos de ejemplo (en producción, esto vendría de la base de datos)
// Para esta demo, usaremos un archivo JSON o variables de sesión

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validar campos
if (empty($email) || empty($password)) {
    header('Location: ../views/login.php?error=empty');
    exit;
}

// Simular verificación de usuario (en producción, esto consultaría la base de datos)
// Para esta demo, aceptaremos cualquier email válido
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../views/login.php?error=invalid');
    exit;
}

// Simular autenticación exitosa
$_SESSION['user_id'] = uniqid();
$_SESSION['user_email'] = $email;
$_SESSION['user_name'] = explode('@', $email)[0];

// Redirigir a reserva o al tour específico
$redirect = isset($_GET['redirect']) ? '?tour_id=' . $_GET['redirect'] : '';
header('Location: ../views/reservar.php' . $redirect);
exit;
?>
