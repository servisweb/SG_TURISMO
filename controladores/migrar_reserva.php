<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
    exit;
}

$id_app_reserva = isset($_POST['id_app_reserva']) ? (int)$_POST['id_app_reserva'] : 0;
$id_salida      = isset($_POST['id_salida'])      ? (int)$_POST['id_salida']      : 0;

if ($id_app_reserva <= 0 || $id_salida <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Datos incompletos']);
    exit;
}

// Obtener la reserva de app_reservas
$stmt = $conexion->prepare('SELECT * FROM app_reservas WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id_app_reserva);
$stmt->execute();
$app = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$app) {
    echo json_encode(['ok' => false, 'msg' => 'Reserva no encontrada']);
    exit;
}

if ($app['estado'] === 'Migrada') {
    echo json_encode(['ok' => false, 'msg' => 'Esta reserva ya fue migrada']);
    exit;
}

$app['total'] = trim($app['total']);

// Buscar guía oficial por nombre
$id_guia_oficial = null;
if (!empty($app['guide_name'])) {
    $stmtG = $conexion->prepare('SELECT id_guia FROM guias WHERE nombres_completos LIKE ? LIMIT 1');
    $like = '%' . $app['guide_name'] . '%';
    $stmtG->bind_param('s', $like);
    $stmtG->execute();
    $rowG = $stmtG->get_result()->fetch_assoc();
    $stmtG->close();
    if ($rowG) $id_guia_oficial = $rowG['id_guia'];
}

// Insertar en reservas oficial
$codigo = $app['codigo_reserva'];
$stmtR = $conexion->prepare(
    'INSERT INTO reservas (codigo_reserva, id_usuario_titular, id_salida, id_guia_elegido, cantidad_pasajeros, precio_total, estado_reserva, comentarios)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);
$estado_reserva = 'Pendiente';
$id_guia_oficial = $id_guia_oficial ?? null;
$stmtR->bind_param(
    'siiisdss',
    $codigo,
    $app['id_usuario'],
    $id_salida,
    $id_guia_oficial,
    $app['cantidad'],
    $app['total'],
    $estado_reserva,
    $app['comentarios']
);

if (!$stmtR->execute()) {
    $errno = $conexion->errno;
    $stmtR->close();
    if ($errno === 1062) {
        // Ya migrada, solo limpiar app_reservas
        $stmtDel = $conexion->prepare('DELETE FROM app_reservas WHERE id = ?');
        $stmtDel->bind_param('i', $id_app_reserva);
        $stmtDel->execute();
        $stmtDel->close();
        echo json_encode(['ok' => true, 'msg' => 'Reserva ya existia, eliminada de pendientes', 'id_reserva' => 0]);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Error al migrar: ' . $conexion->error]);
    }
    exit;
}
$id_reserva_oficial = $stmtR->insert_id;
$stmtR->close();

// Migrar pasajeros de app_pasajeros a pasajeros oficial
$stmtP = $conexion->prepare('SELECT * FROM app_pasajeros WHERE id_reserva = ?');
$stmtP->bind_param('i', $id_app_reserva);
$stmtP->execute();
$pasajeros = $stmtP->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtP->close();

if (!empty($pasajeros)) {
    $stmtIP = $conexion->prepare(
        'INSERT INTO pasajeros (id_reserva, tipo_documento, numero_documento, nombres_completos, fecha_nacimiento, contacto_emergencia_nombre, contacto_emergencia_telefono, consentimiento_privacidad)
         VALUES (?, ?, ?, ?, ?, ?, ?, 1)'
    );
    foreach ($pasajeros as $p) {
        $stmtIP->bind_param(
            'issssss',
            $id_reserva_oficial,
            $p['tipo_documento'],
            $p['numero_documento'],
            $p['nombres_completos'],
            $p['fecha_nacimiento'],
            $p['contacto_emergencia_nombre'],
            $p['contacto_emergencia_telefono']
        );
        $stmtIP->execute();
    }
    $stmtIP->close();
}

// Eliminar de app_reservas una vez migrada
$stmtU = $conexion->prepare('DELETE FROM app_reservas WHERE id = ?');
$stmtU->bind_param('i', $id_app_reserva);
$stmtU->execute();
$stmtU->close();

// Eliminar pasajeros de app_pasajeros también
$stmtD = $conexion->prepare('DELETE FROM app_pasajeros WHERE id_reserva = ?');
$stmtD->bind_param('i', $id_app_reserva);
$stmtD->execute();
$stmtD->close();

echo json_encode(['ok' => true, 'msg' => 'Reserva migrada correctamente', 'id_reserva' => $id_reserva_oficial]);
?>
