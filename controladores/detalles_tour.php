<?php
// =============================================
// CONTROLADOR - Detalles de Tours Individuales
// Lee desde la BD tabla destinos
// =============================================

require_once __DIR__ . '/../config/conexion.php';

$tour_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tour = null;
$tourGuias = [];

if ($tour_id > 0) {
    $stmt = $conexion->prepare('SELECT * FROM destinos WHERE id_destino = ? AND estado = "Activo"');
    $stmt->bind_param('i', $tour_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row) {
        $precio = (float)($row['precio_referencial'] ?? 0);
        $tour = [
            'id'                   => (int)$row['id_destino'],
            'titulo'               => $row['nombre_destino'] ?? '',
            'categoria'            => $row['tipo_destino'] ?? '',
            'imagen'               => ($row['foto_url'] ?? '') ? 'assets/uploads/' . $row['foto_url'] : 'assets/uploads/img/fondo.jpg',
            'descripcion'          => $row['descripcion'] ?? '',
            'descripcion_completa' => $row['descripcion_completa'] ?? ($row['descripcion'] ?? ''),
            'ubicacion'            => trim(($row['provincia'] ?? 'Tumbes') . ', ' . ($row['distrito'] ?? '')),
            'duracion'             => 'Día completo (8 horas)',
            'grupo'                => 'Grupo de hasta 4 personas',
            'precio_persona'       => $precio,
            'precio_grupo'         => round($precio * 4 * 0.9, 2),
            'rating'               => 4,
            'incluye'              => ['Transporte', 'Guía especializado', 'Agua embotellada'],
            'horario_salida'       => '08:00 AM',
            'horario_retorno'      => '05:00 PM',
            'destino'              => ['lat' => -3.5678, 'lng' => -80.4512],
        ];
    }
}

// Cargar guías desde la BD
if ($tour) {
    $result = $conexion->query('SELECT * FROM guias WHERE estado = "Activo" LIMIT 3');
    while ($g = $result->fetch_assoc()) {
        $tourGuias[] = [
            'id'           => (int)$g['id_guia'],
            'nombre'       => $g['nombres_completos'],
            'foto'         => $g['foto_url'] ? 'assets/uploads/' . $g['foto_url'] : 'assets/uploads/guias/guia_carlos.jpg',
            'especialidad' => $g['especialidad'],
            'experiencia'  => $g['experiencia_anios'] . ' años de experiencia',
            'idiomas'      => $g['idiomas'] ?? 'Español',
            'precio_extra' => (float)$g['precio_adicional'],
            'disponibilidad' => 'Lun a Dom 08:00 - 17:00',
        ];
    }
}
?>
