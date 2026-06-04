<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function enviarCorreo($destino, $nombre, $asunto, $mensaje)
{
    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;

        $mail->Username   = 'tucorreo@gmail.com';
        $mail->Password   = 'pvwj sdtt jbgx hzmi';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('tucorreo@gmail.com', 'Sistema Turismo');
        $mail->addAddress($destino, $nombre);

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $mensaje;

        return $mail->send();

    } catch (Exception $e) {
        return false;
    }
}