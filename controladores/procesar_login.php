<?php
session_start();

$redirectTour = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
$redirectGuide = isset($_POST['guide_id']) ? (int)$_POST['guide_id'] : 0;
$redirectCantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;

// Datos de ejemplo (en producción, esto vendría de la base de datos)
// Para esta demo, usaremos un archivo JSON o variables de sesión

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

// Simular verificación de usuario (en producción, esto consultaría la base de datos)
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $location = '../views/login.php?error=invalid';
    if ($redirectTour > 0) {
        $location .= '&tour_id=' . $redirectTour . '&guide_id=' . $redirectGuide . '&cantidad=' . $redirectCantidad;
    }
    header('Location: ' . $location);
    exit;
}

if (strlen($password) < 6) {
    $location = '../views/login.php?error=password_short';
    if ($redirectTour > 0) {
        $location .= '&tour_id=' . $redirectTour . '&guide_id=' . $redirectGuide . '&cantidad=' . $redirectCantidad;
    }
    header('Location: ' . $location);
    exit;
}

// Simular autenticación exitosa
$_SESSION['user_id'] = uniqid();
$_SESSION['user_email'] = $email;
$_SESSION['user_name'] = explode('@', $email)[0];

// Asignar rol Admin si el email contiene la palabra admin
$_SESSION['rol'] = stripos($email, 'admin') !== false ? 'Admin' : 'User';

$redirect = '';
if ($redirectTour > 0) {
    $redirect = '?tour_id=' . $redirectTour . '&guide_id=' . $redirectGuide . '&cantidad=' . $redirectCantidad;
}
header('Location: ../views/reservar.php' . $redirect);
exit;
?>
