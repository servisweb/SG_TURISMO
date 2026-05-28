<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}

// Obtener datos de la reserva
include __DIR__ . '/detalles_tour.php';

$tour_id = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
$cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0;
$fecha_salida = isset($_POST['fecha_salida']) ? $_POST['fecha_salida'] : '';
$nombre_contacto = isset($_POST['nombre_contacto']) ? trim($_POST['nombre_contacto']) : '';
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$comentarios = isset($_POST['comentarios']) ? trim($_POST['comentarios']) : '';
$guide_id = isset($_POST['guide_id']) ? (int)$_POST['guide_id'] : 0;

// Validar datos
if ($tour_id <= 0 || $cantidad <= 0 || empty($fecha_salida) || empty($nombre_contacto) || empty($telefono)) {
    $_SESSION['error'] = 'Por favor completa todos los campos requeridos';
    header('Location: ../views/reservar.php');
    exit;
}

// Obtener los datos del tour y del guía desde los arrays simulados
$precio_unitario = 0;
$precio_grupo = 0;
$tour = null;

if (isset($tours) && is_array($tours)) {
    foreach ($tours as $t) {
        if ($t['id'] === $tour_id) {
            $tour = $t;
            break;
        }
    }
}

if ($tour) {
    $precio_unitario = isset($tour['precio_persona']) ? $tour['precio_persona'] : 0;
    $precio_grupo = isset($tour['precio_grupo']) ? $tour['precio_grupo'] : 0;
}

$precio_guia = isset($guias[$guide_id]) ? $guias[$guide_id]['precio_extra'] : 0;
$groupPackages = intdiv($cantidad, 4);
$individualPeople = $cantidad % 4;
$tourCost = ($groupPackages * $precio_grupo) + ($individualPeople * $precio_unitario);
$guideCost = $precio_guia;
$total = $tourCost + $guideCost;

// Datos de la reserva (en producción, se guardaría en la base de datos)
$reserva = [
    'reserva_id' => uniqid('RES-'),
    'user_id' => $_SESSION['user_id'],
    'tour_id' => $tour_id,
    'guide_id' => $guide_id,
    'guide_name' => isset($guias[$guide_id]) ? $guias[$guide_id]['nombre'] : 'Prefiero no elegir guía',
    'precio_guia' => $precio_guia,
    'cantidad' => $cantidad,
    'fecha_salida' => $fecha_salida,
    'nombre_contacto' => $nombre_contacto,
    'telefono' => $telefono,
    'comentarios' => $comentarios,
    'precio_unitario' => $precio_unitario,
    'total' => $total,
    'fecha_reserva' => date('Y-m-d H:i:s'),
    'estado' => 'Confirmada'
];

// Guardar en sesión (en producción, iría a la base de datos)
if (!isset($_SESSION['reservas'])) {
    $_SESSION['reservas'] = [];
}
$_SESSION['reservas'][] = $reserva;

// Redirigir a confirmación
$_SESSION['ultima_reserva'] = $reserva;
header('Location: ../views/confirmacion.php');
exit;
?>
