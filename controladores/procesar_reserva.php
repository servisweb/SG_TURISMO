<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php?return_url=' . urlencode('../conocer_mas.php'));
    exit;
}

require_once __DIR__ . '/../config/conexion.php';
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
    $precio_unitario = isset($tour['precio_persona']) ? $tour['precio_persona'] : 0;
    $precio_grupo    = isset($tour['precio_grupo']) ? $tour['precio_grupo'] : 0;
}

$precio_guia     = isset($guias[$guide_id]) ? $guias[$guide_id]['precio_extra'] : 0;
$groupPackages   = intdiv($cantidad, 4);
$individualPeople= $cantidad % 4;
$tourCost        = ($groupPackages * $precio_grupo) + ($individualPeople * $precio_unitario);
$total           = $tourCost + $precio_guia;

$codigo_reserva = uniqid('RES-');
$fecha_reserva = date('Y-m-d H:i:s');

// Crear tabla app_reservas si no existe
$conexion->query("
CREATE TABLE IF NOT EXISTS app_reservas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo_reserva VARCHAR(60) NOT NULL,
  id_usuario BIGINT UNSIGNED NOT NULL,
  tour_id INT NOT NULL,
  guide_id INT DEFAULT NULL,
  guide_name VARCHAR(200) DEFAULT NULL,
  precio_guia DECIMAL(10,2) DEFAULT 0.00,
  cantidad SMALLINT UNSIGNED NOT NULL,
  fecha_salida DATE DEFAULT NULL,
  nombre_contacto VARCHAR(200) DEFAULT NULL,
  telefono VARCHAR(60) DEFAULT NULL,
  comentarios TEXT DEFAULT NULL,
  precio_unitario DECIMAL(10,2) DEFAULT 0.00,
  total DECIMAL(10,2) DEFAULT 0.00,
  estado VARCHAR(40) DEFAULT 'Pendiente',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)");

// Crear tabla app_pasajeros si no existe
$conexion->query("
CREATE TABLE IF NOT EXISTS app_pasajeros (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_reserva BIGINT UNSIGNED NOT NULL,
  tipo_documento ENUM('DNI','CE','PASSPORT') NOT NULL DEFAULT 'DNI',
  numero_documento VARCHAR(20) NOT NULL,
  nombres_completos VARCHAR(200) NOT NULL,
  fecha_nacimiento DATE DEFAULT NULL,
  contacto_emergencia_nombre VARCHAR(150) DEFAULT NULL,
  contacto_emergencia_telefono VARCHAR(30) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)");

$insertSql = "INSERT INTO app_reservas (codigo_reserva, id_usuario, tour_id, guide_id, guide_name, precio_guia, cantidad, fecha_salida, nombre_contacto, telefono, comentarios, precio_unitario, total, estado, fecha_creacion)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conexion->prepare($insertSql);
if (!$stmt) {
    $_SESSION['error'] = 'No se pudo preparar la reserva.';
    header('Location: ../conocer_mas.php');
    exit;
}

$fecha_salida_db = date('Y-m-d', strtotime($fecha_salida));
$estado = 'Pendiente';
$guide_name = isset($guias[$guide_id]) ? $guias[$guide_id]['nombre'] : null;

$stmt->bind_param(
    'siiissiissssdss',
    $codigo_reserva,
    $_SESSION['user_id'],
    $tour_id,
    $guide_id,
    $guide_name,
    $precio_guia,
    $cantidad,
    $fecha_salida_db,
    $nombre_contacto,
    $telefono,
    $comentarios,
    $precio_unitario,
    $total,
    $estado,
    $fecha_reserva
);

if (!$stmt->execute()) {
    $_SESSION['error'] = 'No se pudo guardar la reserva en la base de datos.';
    $stmt->close();
    header('Location: ../conocer_mas.php');
    exit;
}

$insertId = $stmt->insert_id;
$stmt->close();

$reserva = [
    'id' => $insertId,
    'reserva_id' => $codigo_reserva,
    'user_id' => $_SESSION['user_id'],
    'tour_id' => $tour_id,
    'guide_id' => $guide_id,
    'guide_name' => $guide_name,
    'precio_guia' => $precio_guia,
    'cantidad' => $cantidad,
    'fecha_salida' => $fecha_salida_db,
    'nombre_contacto' => $nombre_contacto,
    'telefono' => $telefono,
    'comentarios' => $comentarios,
    'precio_unitario' => $precio_unitario,
    'total' => $total,
    'fecha_reserva' => $fecha_reserva,
    'estado' => $estado
];

$_SESSION['ultima_reserva'] = $reserva;

// Guardar pasajeros
$pasajerosPost = isset($_POST['pasajeros']) && is_array($_POST['pasajeros']) ? $_POST['pasajeros'] : [];
$pasajerosResumen = [];
if (!empty($pasajerosPost)) {
    $stmtP = $conexion->prepare(
        'INSERT INTO app_pasajeros (id_reserva, tipo_documento, numero_documento, nombres_completos, fecha_nacimiento, contacto_emergencia_nombre, contacto_emergencia_telefono)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    if ($stmtP) {
        foreach ($pasajerosPost as $p) {
            $tipo_doc  = trim($p['tipo_doc'] ?? 'DNI');
            $num_doc   = trim($p['num_doc'] ?? '');
            $nombres_p = trim($p['nombres'] ?? '');
            $fecha_nac = !empty($p['fecha_nac']) ? $p['fecha_nac'] : null;
            $em_nombre = trim($p['emergencia_nombre'] ?? '');
            $em_tel    = trim($p['emergencia_tel'] ?? '');
            if (empty($nombres_p) || empty($num_doc)) continue;
            $stmtP->bind_param('issssss', $insertId, $tipo_doc, $num_doc, $nombres_p, $fecha_nac, $em_nombre, $em_tel);
            $stmtP->execute();
            $pasajerosResumen[] = [
                'nombres'           => $nombres_p,
                'tipo_doc'          => $tipo_doc,
                'num_doc'           => $num_doc,
                'emergencia_nombre' => $em_nombre,
                'emergencia_tel'    => $em_tel,
            ];
        }
        $stmtP->close();
    }
}
$_SESSION['ultima_reserva']['pasajeros'] = $pasajerosResumen;

header('Location: ../views/confirmacion.php');
exit;
?>
