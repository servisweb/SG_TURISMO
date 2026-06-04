<?php

require_once 'config/mail.php';

try {

    $mail = configurarCorreo();

    $mail->addAddress('destino@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'Correo de prueba';

    $mail->Body = '
        <h2>Hola</h2>
        <p>Este correo fue enviado desde el sistema de tours.</p>
    ';

    $mail->send();

    echo "Correo enviado correctamente";

} catch (Exception $e) {

    echo "Error: " . $mail->ErrorInfo;
}