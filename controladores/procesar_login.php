<?php
session_start();

$email    = isset($_POST['email'])    ? mb_strtolower(trim($_POST['email']))    : '';
$inputPassword = isset($_POST['password']) ? trim($_POST['password']) : '';
$returnUrl= isset($_POST['return_url'])? $_POST['return_url']   : '';

$redirectTour   = isset($_POST['tour_id'])   ? (int)$_POST['tour_id']  : 0;
$redirectGuide  = isset($_POST['guide_id'])  ? (int)$_POST['guide_id'] : 0;
$redirectCantidad = isset($_POST['cantidad'])? (int)$_POST['cantidad'] : 1;

if (empty($email) || empty($inputPassword)) {
    $back = '../views/login.php?error=empty';
    if (!empty($returnUrl)) $back .= '&return_url=' . urlencode($returnUrl);
    if ($redirectTour > 0) {
        $back .= '&tour_id=' . $redirectTour . '&guide_id=' . $redirectGuide . '&cantidad=' . $redirectCantidad;
    }
    header('Location: ' . $back);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $back = '../views/login.php?error=invalid_email';
    if (!empty($returnUrl)) $back .= '&return_url=' . urlencode($returnUrl);
    header('Location: ' . $back);
    exit;
}

require_once __DIR__ . '/../config/conexion.php';

$stmt = $conexion->prepare('SELECT id_usuario, nombres, apellidos, email, telefono, password_hash, rol, estado FROM usuarios WHERE email = ? LIMIT 1');
if (!$stmt) {
    header('Location: ../views/login.php?error=invalid');
    exit;
}
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// --- Debug ligero: registrar intento sin guardar la contraseña en claro ---
function login_debug($email, $found, $hashLen, $pvResult) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    $logFile = $logDir . '/login_debug.log';
    $line = date('Y-m-d H:i:s') . " | email=" . $email . " | found=" . ($found ? '1' : '0') . " | hash_len=" . $hashLen . " | password_verify=" . ($pvResult === null ? 'N/A' : ($pvResult ? '1' : '0')) . " | pass_len=" . (isset($_POST['password']) ? strlen(trim($_POST['password'])) : 0) . "\n";
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

// registrar intento después de la consulta (aún no verificamos contraseña)
login_debug($email, $user ? true : false, isset($user['password_hash']) ? strlen($user['password_hash']) : 0, null);

if (!$user) {
    $back = '../views/login.php?error=not_found';
    if (!empty($returnUrl)) $back .= '&return_url=' . urlencode($returnUrl);
    header('Location: ' . $back);
    exit;
}

if ($user['estado'] !== 'Activo') {
    $back = '../views/login.php?error=inactive';
    if (!empty($returnUrl)) $back .= '&return_url=' . urlencode($returnUrl);
    header('Location: ' . $back);
    exit;
}

if (!password_verify($inputPassword, $user['password_hash'])) {
    // log resultado falla
    login_debug($email, true, strlen($user['password_hash']), false);
    $back = '../views/login.php?error=invalid';
    if (!empty($returnUrl)) $back .= '&return_url=' . urlencode($returnUrl);
    header('Location: ' . $back);
    exit;
}

// log resultado exitoso
login_debug($email, true, strlen($user['password_hash']), true);

$_SESSION['user_id']    = (int)$user['id_usuario'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name']  = trim($user['nombres'] . ' ' . $user['apellidos']);
$_SESSION['user_phone'] = $user['telefono'];
$_SESSION['rol']        = $user['rol'] === 'Admin' ? 'Admin' : 'User';

if (!empty($returnUrl)) {
    header('Location: ' . $returnUrl);
    exit;
}

if ($_SESSION['rol'] === 'Admin') {
    header('Location: ../views/admin/panel_admin.php');
    exit;
}

header('Location: ../views/user/perfil_user.php');
exit;
?>
