<?php

session_start();

require_once __DIR__ . '/../config/google.php';
require_once __DIR__ . '/../config/conexion.php';

if (!isset($_GET['code'])) {
    header('Location: ../views/login.php');
    exit;
}

$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

if (isset($token['error'])) {
    die('Error Google Login');
}

$client->setAccessToken($token['access_token']);

$google_service = new Google\Service\Oauth2($client);

$data = $google_service->userinfo->get();

$google_id = $data->id;
$email = $data->email;
$nombres = $data->givenName;
$apellidos = $data->familyName ?? '';
$avatar = $data->picture ?? '';

$stmt = $conexion->prepare("
    SELECT *
    FROM usuarios
    WHERE email = ?
");

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $usuario = $result->fetch_assoc();

    $update = $conexion->prepare("
        UPDATE usuarios
        SET google_id = ?, avatar = ?
        WHERE id_usuario = ?
    ");

    $update->bind_param(
        "ssi",
        $google_id,
        $avatar,
        $usuario['id_usuario']
    );

    $update->execute();

    $_SESSION['user_id'] = $usuario['id_usuario'];
    $_SESSION['user_email'] = $usuario['email'];
    $_SESSION['user_name'] = $usuario['nombres'] . ' ' . $usuario['apellidos'];
    $_SESSION['user_rol'] = $usuario['rol'];

} else {

    $tipo_doc = 'DNI';
    $numero_documento = 'GOOGLE' . rand(100000,999999);

    $password_hash = password_hash(
        bin2hex(random_bytes(16)),
        PASSWORD_DEFAULT
    );

    $rol = 'Cliente';
    $estado = 'Activo';

    $insert = $conexion->prepare("
        INSERT INTO usuarios
        (
            tipo_documento,
            numero_documento,
            nombres,
            apellidos,
            email,
            google_id,
            avatar,
            password_hash,
            rol,
            estado
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ");

    $insert->bind_param(
        "ssssssssss",
        $tipo_doc,
        $numero_documento,
        $nombres,
        $apellidos,
        $email,
        $google_id,
        $avatar,
        $password_hash,
        $rol,
        $estado
    );

    $insert->execute();

    $nuevo_id = $insert->insert_id;

    $_SESSION['user_id'] = $nuevo_id;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $nombres . ' ' . $apellidos;
    $_SESSION['user_rol'] = 'Cliente';
}

header('Location: ../index.php');
exit;