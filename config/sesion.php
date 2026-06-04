<?php
// Tiempo de expiración en segundos (30 minutos)
define('SESSION_TIMEOUT', 1800);

function verificar_sesion() {
    if (!isset($_SESSION['user_id'])) return;

    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        $returnUrl = $_SERVER['REQUEST_URI'] ?? '';
        session_unset();
        session_destroy();
        header('Location: /sistema_de_reserva/views/login.php?error=session_expired&return_url=' . urlencode($returnUrl));
        exit;
    }
    $_SESSION['last_activity'] = time();
}
