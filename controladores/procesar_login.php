<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

$redirectTour = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
$redirectGuide = isset($_POST['guide_id']) ? (int)$_POST['guide_id'] : 0;
$redirectCantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validar campos
if (empty($email) || empty($password)) {
    $location = '../views/login.php?error=empty';
    if ($redirectTour > 0) {
        $location .= '&tour_id=' . $redirectTour . '&guide_id=' . $redirectGuide . '&cantidad=' . $redirectCantidad;
    }
    header('Location: ' . $location);
    exit;
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $location = '../views/login.php?error=invalid';
    if ($redirectTour > 0) {
        $location .= '&tour_id=' . $redirectTour . '&guide_id=' . $redirectGuide . '&cantidad=' . $redirectCantidad;
    }
    header('Location: ' . $location);
    exit;
}

// Buscar usuario en la base de datos
$stmt = $conexion->prepare("SELECT id_usuario, nombres, apellidos, email, password_hash, rol, estado FROM usuarios WHERE email = ? AND estado = 'Activo'");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $location = '../views/login.php?error=not_found';
    if ($redirectTour > 0) {
        $location .= '&tour_id=' . $redirectTour . '&guide_id=' . $redirectGuide . '&cantidad=' . $redirectCantidad;
    }
    header('Location: ' . $location);
    exit;
}

$usuario = $result->fetch_assoc();

// Verificar contraseña
if (!password_verify($password, $usuario['password_hash'])) {
    $location = '../views/login.php?error=invalid';
    if ($redirectTour > 0) {
        $location .= '&tour_id=' . $redirectTour . '&guide_id=' . $redirectGuide . '&cantidad=' . $redirectCantidad;
    }
    header('Location: ' . $location);
    exit;
}

// Login exitoso - Guardar datos en sesión
$_SESSION['user_id'] = $usuario['id_usuario'];
$_SESSION['user_email'] = $usuario['email'];
$_SESSION['user_name'] = $usuario['nombres'] . ' ' . $usuario['apellidos'];
$_SESSION['user_rol'] = $usuario['rol'];

$stmt->close();
$conexion->close();

// Redirigir según el rol
if ($usuario['rol'] === 'Admin') {
    header('Location: ../views/admin/dashboard.php');
    exit;
}

// Cliente - redirigir a reserva o página principal
$redirect = '';
if ($redirectTour > 0) {
    $redirect = '?tour_id=' . $redirectTour . '&guide_id=' . $redirectGuide . '&cantidad=' . $redirectCantidad;
    header('Location: ../views/reservar.php' . $redirect);
} else {
    header('Location: ../index.php');
}
exit;
?>
