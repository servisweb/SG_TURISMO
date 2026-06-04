<?php
session_start();

$nombres          = isset($_POST['nombres'])          ? trim($_POST['nombres'])          : '';
$apellidos        = isset($_POST['apellidos'])        ? trim($_POST['apellidos'])        : '';
$email            = isset($_POST['email'])            ? trim($_POST['email'])            : '';
$telefono         = isset($_POST['telefono'])         ? trim($_POST['telefono'])         : '';
$inputPassword    = isset($_POST['password'])         ? $_POST['password']               : '';
$confirmPassword  = isset($_POST['confirm_password']) ? $_POST['confirm_password']       : '';
$returnUrl        = isset($_POST['return_url'])       ? $_POST['return_url']             : '';

$redirectTour     = isset($_POST['tour_id'])   ? (int)$_POST['tour_id']  : 0;
$redirectGuide    = isset($_POST['guide_id'])  ? (int)$_POST['guide_id'] : 0;
$redirectCantidad = isset($_POST['cantidad'])  ? (int)$_POST['cantidad'] : 1;

if (empty($nombres) || empty($apellidos) || empty($email) || empty($inputPassword)) {
    $back = '../views/login.php?error=empty';
    if (!empty($returnUrl)) $back .= '&return_url=' . urlencode($returnUrl);
    header('Location: ' . $back);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $back = '../views/login.php?error=invalid_email';
    if (!empty($returnUrl)) $back .= '&return_url=' . urlencode($returnUrl);
    header('Location: ' . $back);
    exit;
}

if ($inputPassword !== $confirmPassword) {
    $back = '../views/login.php?error=password_mismatch';
    if (!empty($returnUrl)) $back .= '&return_url=' . urlencode($returnUrl);
    header('Location: ' . $back);
    exit;
}

if (strlen($inputPassword) < 6) {
    $back = '../views/login.php?error=password_short';
    if (!empty($returnUrl)) $back .= '&return_url=' . urlencode($returnUrl);
    header('Location: ' . $back);
    exit;
}

require_once __DIR__ . '/../config/conexion.php';

$tipo_documento = 'DNI';
$numero_documento = strtoupper(uniqid('DOC'));
$rol = stripos($email, 'admin') !== false ? 'Admin' : 'Cliente';
$password_hash = password_hash($inputPassword, PASSWORD_DEFAULT);

$stmt = $conexion->prepare(
    'INSERT INTO usuarios (tipo_documento, numero_documento, nombres, apellidos, email, telefono, password_hash, rol)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);

if (!$stmt) {
    $back = '../views/login.php?error=register_failed';
    if (!empty($returnUrl)) $back .= '&return_url=' . urlencode($returnUrl);
    header('Location: ' . $back);
    exit;
}

$stmt->bind_param('ssssssss', $tipo_documento, $numero_documento, $nombres, $apellidos, $email, $telefono, $password_hash, $rol);
if (!$stmt->execute()) {
    $errorCode = $conexion->errno;
    $stmt->close();

    if ($errorCode === 1062) {
        $back = '../views/login.php?error=email_exists';
    } else {
        $back = '../views/login.php?error=register_failed';
    }
    if (!empty($returnUrl)) {
        $back .= '&return_url=' . urlencode($returnUrl);
    }
    header('Location: ' . $back);
    exit;
}
$stmt->close();

$back = '../views/login.php?success=registered';
if (!empty($returnUrl)) {
    $back .= '&return_url=' . urlencode($returnUrl);
}
header('Location: ' . $back);
exit;
?>
