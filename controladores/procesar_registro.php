<?php
session_start();

$redirectTour = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
$redirectGuide = isset($_POST['guide_id']) ? (int)$_POST['guide_id'] : 0;
$redirectCantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;

$nombres = isset($_POST['nombres']) ? trim($_POST['nombres']) : '';
$apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// Validar campos
if (empty($nombres) || empty($apellidos) || empty($email) || empty($password)) {
    $location = '../views/login.php?error=empty';
    if ($redirectTour > 0) {
        $location .= '&tour_id=' . $redirectTour . '&guide_id=' . $redirectGuide . '&cantidad=' . $redirectCantidad;
    }
    header('Location: ' . $location);
    exit;
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $location = '../views/login.php?error=invalid_email';
    if ($redirectTour > 0) {
        $location .= '&tour_id=' . $redirectTour . '&guide_id=' . $redirectGuide . '&cantidad=' . $redirectCantidad;
    }
    header('Location: ' . $location);
    exit;
}

// Validar que las contraseñas coincidan
if ($password !== $confirm_password) {
    $location = '../views/login.php?error=password_mismatch';
    if ($redirectTour > 0) {
        $location .= '&tour_id=' . $redirectTour . '&guide_id=' . $redirectGuide . '&cantidad=' . $redirectCantidad;
    }
    header('Location: ' . $location);
    exit;
}

// Validar longitud de contraseña
if (strlen($password) < 6) {
    $location = '../views/login.php?error=password_short';
    if ($redirectTour > 0) {
        $location .= '&tour_id=' . $redirectTour . '&guide_id=' . $redirectGuide . '&cantidad=' . $redirectCantidad;
    }
    header('Location: ' . $location);
    exit;
}

// Simular registro y autenticación automática
$_SESSION['user_id'] = uniqid();
$_SESSION['user_email'] = $email;
$_SESSION['user_name'] = $nombres;

if ($redirectTour > 0) {
    header('Location: ../views/reservar.php?tour_id=' . $redirectTour . '&guide_id=' . $redirectGuide . '&cantidad=' . $redirectCantidad);
    exit;
}

header('Location: ../views/login.php?success=registered');
exit;
?>
