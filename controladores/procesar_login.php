<?php
session_start();

$email    = isset($_POST['email'])    ? trim($_POST['email'])    : '';
$password = isset($_POST['password']) ? $_POST['password']       : '';
$returnUrl= isset($_POST['return_url'])? $_POST['return_url']   : '';

$redirectTour   = isset($_POST['tour_id'])   ? (int)$_POST['tour_id']  : 0;
$redirectGuide  = isset($_POST['guide_id'])  ? (int)$_POST['guide_id'] : 0;
$redirectCantidad = isset($_POST['cantidad'])? (int)$_POST['cantidad'] : 1;

if (empty($email) || empty($password)) {
    $back = '../views/login.php?error=empty';
    if (!empty($returnUrl)) $back .= '&return_url=' . urlencode($returnUrl);
    header('Location: ' . $back);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
    $back = '../views/login.php?error=invalid';
    if (!empty($returnUrl)) $back .= '&return_url=' . urlencode($returnUrl);
    header('Location: ' . $back);
    exit;
}

$_SESSION['user_id']    = uniqid();
$_SESSION['user_email'] = $email;
$_SESSION['user_name']  = explode('@', $email)[0];
$_SESSION['rol']        = stripos($email, 'admin') !== false ? 'Admin' : 'User';

if (!empty($returnUrl)) {
    header('Location: ' . $returnUrl);
} elseif ($redirectTour > 0) {
    header('Location: ../views/reservar.php?tour_id=' . $redirectTour . '&guide_id=' . $redirectGuide . '&cantidad=' . $redirectCantidad);
} else {
    header('Location: ../index.php');
}
exit;
?>
