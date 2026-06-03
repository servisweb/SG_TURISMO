<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php?return_url=' . urlencode('../conocer_mas.php'));
    exit;
}

include __DIR__ . '/detalles_tour.php';

$tour_id        = isset($_POST['tour_id'])        ? (int)$_POST['tour_id']          : 0;
$cantidad       = isset($_POST['cantidad'])       ? (int)$_POST['cantidad']         : 0;
$fecha_salida   = isset($_POST['fecha_salida'])   ? $_POST['fecha_salida']           : '';
$nombre_contacto= isset($_POST['nombre_contacto'])? trim($_POST['nombre_contacto']) : '';
$telefono       = isset($_POST['telefono'])       ? trim($_POST['telefono'])         : '';
$comentarios    = isset($_POST['comentarios'])    ? trim($_POST['comentarios'])      : '';
$guide_id       = isset($_POST['guide_id'])       ? (int)$_POST['guide_id']         : 0;

if ($tour_id <= 0 || $cantidad <= 0 || empty($fecha_salida) || empty($nombre_contacto) || empty($telefono)) {
    $_SESSION['error'] = 'Por favor completa todos los campos requeridos.';
    header('Location: ../conocer_mas.php');
    exit;
}

$precio_unitario = 0;
$precio_grupo    = 0;
$tour            = null;

foreach ($tours as $t) {
    if ($t['id'] === $tour_id) {
        $tour = $t;
        break;
    }
}

if ($tour) {
    $precio_unitario = $tour['precio_persona'];
    $precio_grupo    = $tour['precio_grupo'];
}

$precio_guia     = isset($guias[$guide_id]) ? $guias[$guide_id]['precio_extra'] : 0;
$groupPackages   = intdiv($cantidad, 4);
$individualPeople= $cantidad % 4;
$tourCost        = ($groupPackages * $precio_grupo) + ($individualPeople * $precio_unitario);
$total           = $tourCost + $precio_guia;

$reserva = [
    'reserva_id'      => uniqid('RES-'),
    'user_id'         => $_SESSION['user_id'],
    'tour_id'         => $tour_id,
    'guide_id'        => $guide_id,
    'guide_name'      => isset($guias[$guide_id]) ? $guias[$guide_id]['nombre'] : 'Sin guía',
    'precio_guia'     => $precio_guia,
    'cantidad'        => $cantidad,
    'fecha_salida'    => $fecha_salida,
    'nombre_contacto' => $nombre_contacto,
    'telefono'        => $telefono,
    'comentarios'     => $comentarios,
    'precio_unitario' => $precio_unitario,
    'total'           => $total,
    'fecha_reserva'   => date('Y-m-d H:i:s'),
    'estado'          => 'Confirmada'
];

if (!isset($_SESSION['reservas'])) {
    $_SESSION['reservas'] = [];
}
$_SESSION['reservas'][]   = $reserva;
$_SESSION['ultima_reserva'] = $reserva;

header('Location: ../views/confirmacion.php');
exit;
?>
