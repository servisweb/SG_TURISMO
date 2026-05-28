<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

$redirectTour = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
$redirectGuide = isset($_POST['guide_id']) ? (int)$_POST['guide_id'] : 0;
$redirectCantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;

$nombres = isset($_POST['nombres']) ? trim($_POST['nombres']) : '';
$apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// Validar campos obligatorios
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

// Verificar si el email ya existe
$stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $location = '../views/login.php?error=email_exists';
    if ($redirectTour > 0) {
        $location .= '&tour_id=' . $redirectTour . '&guide_id=' . $redirectGuide . '&cantidad=' . $redirectCantidad;
    }
    header('Location: ' . $location);
    exit;
}
$stmt->close();

// Encriptar contraseña
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Generar número de documento temporal (DNI)
$numero_documento = '00000000'; // En producción, esto debería ser capturado en el formulario

// Insertar usuario en la base de datos
$stmt = $conexion->prepare("INSERT INTO usuarios (tipo_documento, numero_documento, nombres, apellidos, email, telefono, password_hash, rol, estado) VALUES (?, ?, ?, ?, ?, ?, ?, 'Cliente', 'Activo')");
$tipo_doc = 'DNI';
$stmt->bind_param("sssssss", $tipo_doc, $numero_documento, $nombres, $apellidos, $email, $telefono, $password_hash);

if (!$stmt->execute()) {
    $location = '../views/login.php?error=registration_failed';
    if ($redirectTour > 0) {
        $location .= '&tour_id=' . $redirectTour . '&guide_id=' . $redirectGuide . '&cantidad=' . $redirectCantidad;
    }
    header('Location: ' . $location);
    exit;
}

$user_id = $stmt->insert_id;
$stmt->close();
$conexion->close();

// Autenticación automática después del registro
$_SESSION['user_id'] = $user_id;
$_SESSION['user_email'] = $email;
$_SESSION['user_name'] = $nombres . ' ' . $apellidos;
$_SESSION['user_rol'] = 'Cliente';

// Redirigir
if ($redirectTour > 0) {
    header('Location: ../views/reservar.php?tour_id=' . $redirectTour . '&guide_id=' . $redirectGuide . '&cantidad=' . $redirectCantidad);
} else {
    header('Location: ../index.php');
}
exit;
?>
