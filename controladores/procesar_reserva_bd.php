<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    $tour_id = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
    $guide_id = isset($_POST['guide_id']) ? (int)$_POST['guide_id'] : 0;
    $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
    $query = '';
    if ($tour_id > 0) {
        $query = '?tour_id=' . $tour_id . '&guide_id=' . $guide_id . '&cantidad=' . $cantidad;
    }
    header('Location: ../views/login.php' . $query);
    exit;
}

// Obtener datos del formulario
$tour_id = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
$cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0;
$fecha_salida = isset($_POST['fecha_salida']) ? $_POST['fecha_salida'] : '';
$nombre_contacto = isset($_POST['nombre_contacto']) ? trim($_POST['nombre_contacto']) : '';
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$comentarios = isset($_POST['comentarios']) ? trim($_POST['comentarios']) : '';
$guide_id = isset($_POST['guide_id']) ? (int)$_POST['guide_id'] : 0;

// Validar datos básicos
if ($tour_id <= 0 || $cantidad <= 0 || empty($fecha_salida) || empty($nombre_contacto) || empty($telefono)) {
    $_SESSION['error'] = 'Por favor completa todos los campos requeridos';
    header('Location: ../views/reservar.php');
    exit;
}

// Obtener información del paquete desde la BD
$query_paquete = "SELECT p.*, d.nombre_destino 
                  FROM paquetes p 
                  INNER JOIN destinos d ON p.id_destino = d.id_destino 
                  WHERE p.id_paquete = ? AND p.estado = 'Activo'";
$stmt = $conexion->prepare($query_paquete);
$stmt->bind_param("i", $tour_id);
$stmt->execute();
$result = $stmt->get_result();
$paquete = $result->fetch_assoc();

if (!$paquete) {
    $_SESSION['error'] = 'El paquete seleccionado no está disponible';
    header('Location: ../index.php');
    exit;
}

// Calcular precio
$precio_base = $paquete['precio_base'];
$precio_total = $precio_base * $cantidad;

// Buscar o crear salida operativa
$fecha_salida_dt = new DateTime($fecha_salida);
$fecha_retorno = clone $fecha_salida_dt;
$fecha_retorno->modify('+1 day'); // Asumiendo tours de 1 día

// Buscar salida existente o crear una nueva
$query_salida = "SELECT id_salida FROM salidas_operativas 
                 WHERE id_paquete = ? AND DATE(fecha_hora_salida) = ? 
                 AND estado IN ('Programada', 'Confirmada')
                 LIMIT 1";
$stmt_salida = $conexion->prepare($query_salida);
$fecha_salida_date = $fecha_salida_dt->format('Y-m-d');
$stmt_salida->bind_param("is", $tour_id, $fecha_salida_date);
$stmt_salida->execute();
$result_salida = $stmt_salida->get_result();

if ($result_salida->num_rows > 0) {
    $salida = $result_salida->fetch_assoc();
    $id_salida = $salida['id_salida'];
} else {
    // Crear nueva salida operativa (requiere guía y movilidad - usar valores por defecto)
    $query_insert_salida = "INSERT INTO salidas_operativas 
                           (id_paquete, fecha_hora_salida, fecha_hora_retorno, id_guia, id_movilidad, cupos_totales, cupos_reservados, estado)
                           VALUES (?, ?, ?, 1, 1, 50, 0, 'Programada')";
    $stmt_insert = $conexion->prepare($query_insert_salida);
    $fecha_salida_full = $fecha_salida_dt->format('Y-m-d 08:00:00');
    $fecha_retorno_full = $fecha_retorno->format('Y-m-d 18:00:00');
    $stmt_insert->bind_param("iss", $tour_id, $fecha_salida_full, $fecha_retorno_full);
    $stmt_insert->execute();
    $id_salida = $conexion->insert_id;
}

// Generar código único de reserva
$codigo_reserva = 'RES-' . date('Ymd') . '-' . str_pad($id_salida, 4, '0', STR_PAD_LEFT) . '-' . rand(1000, 9999);

// Insertar reserva en la base de datos
$query_reserva = "INSERT INTO reservas 
                  (codigo_reserva, id_usuario_titular, id_salida, cantidad_pasajeros, precio_total, estado_reserva)
                  VALUES (?, ?, ?, ?, ?, 'Pendiente')";
$stmt_reserva = $conexion->prepare($query_reserva);
$user_id = $_SESSION['user_id'];
$stmt_reserva->bind_param("siiid", $codigo_reserva, $user_id, $id_salida, $cantidad, $precio_total);

if ($stmt_reserva->execute()) {
    $id_reserva = $conexion->insert_id;
    
    // Actualizar cupos reservados en salida operativa
    $query_update_cupos = "UPDATE salidas_operativas 
                          SET cupos_reservados = cupos_reservados + ? 
                          WHERE id_salida = ?";
    $stmt_update = $conexion->prepare($query_update_cupos);
    $stmt_update->bind_param("ii", $cantidad, $id_salida);
    $stmt_update->execute();
    
    // Guardar datos de la reserva en sesión para mostrar en confirmación
    $_SESSION['ultima_reserva'] = [
        'reserva_id' => $codigo_reserva,
        'id_reserva' => $id_reserva,
        'tour_nombre' => $paquete['titulo'],
        'destino' => $paquete['nombre_destino'],
        'cantidad' => $cantidad,
        'fecha_salida' => $fecha_salida,
        'nombre_contacto' => $nombre_contacto,
        'telefono' => $telefono,
        'comentarios' => $comentarios,
        'precio_total' => $precio_total,
        'fecha_reserva' => date('Y-m-d H:i:s'),
        'estado' => 'Pendiente'
    ];
    
    $_SESSION['success'] = '¡Reserva creada exitosamente!';
    header('Location: ../views/confirmacion.php');
    exit;
} else {
    $_SESSION['error'] = 'Error al procesar la reserva. Por favor intenta nuevamente.';
    header('Location: ../views/reservar.php?tour_id=' . $tour_id);
    exit;
}
?>
