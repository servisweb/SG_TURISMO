<?php
session_start();

$nombres          = isset($_POST['nombres'])          ? trim($_POST['nombres'])          : '';
$apellidos        = isset($_POST['apellidos'])        ? trim($_POST['apellidos'])        : '';
$email            = isset($_POST['email'])            ? trim($_POST['email'])            : '';
$telefono         = isset($_POST['telefono'])         ? trim($_POST['telefono'])         : '';
$password         = isset($_POST['password'])         ? $_POST['password']               : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password']       : '';
$returnUrl        = isset($_POST['return_url'])       ? $_POST['return_url']             : '';

$redirectTour     = isset($_POST['tour_id'])   ? (int)$_POST['tour_id']  : 0;
$redirectGuide    = isset($_POST['guide_id'])  ? (int)$_POST['guide_id'] : 0;
$redirectCantidad = isset($_POST['cantidad'])  ? (int)$_POST['cantidad'] : 1;

if (empty($nombres) || empty($apellidos) || empty($email) || empty($password)) {
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

if ($password !== $confirm_password) {
    $back = '../views/login.php?error=password_mismatch';
    if (!empty($returnUrl)) $back .= '&return_url=' . urlencode($returnUrl);
    header('Location: ' . $back);
    exit;
}

if (strlen($password) < 6) {
    $back = '../views/login.php?error=password_short';
    if (!empty($returnUrl)) $back .= '&return_url=' . urlencode($returnUrl);
    header('Location: ' . $back);
    exit;
}

$_SESSION['user_id']    = uniqid();
$_SESSION['user_email'] = $email;
$_SESSION['user_name']  = $nombres;

if (!empty($returnUrl)) {
    header('Location: ' . $returnUrl);
} elseif ($redirectTour > 0) {
    header('Location: ../views/reservar.php?tour_id=' . $redirectTour . '&guide_id=' . $redirectGuide . '&cantidad=' . $redirectCantidad);
} else {
    header('Location: ../views/login.php?success=registered');
}
exit;
?>
