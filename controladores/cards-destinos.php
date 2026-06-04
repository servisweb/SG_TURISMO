<?php
// =============================================
// DATOS DE LOS TOURS - Tumbes Tours
// Ahora se leen desde la BD tabla destinos
// =============================================

require_once __DIR__ . '/../config/conexion.php';

// Leer tours desde la BD tabla destinos
$result = $conexion->query('
    SELECT id_destino, nombre_destino, tipo_destino, foto_url, descripcion,
           provincia, distrito, precio_referencial, duracion
    FROM destinos
    WHERE estado = "Activo"
    ORDER BY nombre_destino ASC
');

$tours = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $precio = (float)($row['precio_referencial'] ?? 0);
        $tours[] = [
            "id"             => (int)$row['id_destino'],
            "categoria"      => $row['tipo_destino'] ?? 'Mixto',
            "titulo"         => $row['nombre_destino'],
            "imagen"         => $row['foto_url'] ?? '',
            "descripcion"    => $row['descripcion'] ?? '',
            "ubicacion"      => trim(($row['provincia'] ?? 'Tumbes') . ' ' . ($row['distrito'] ?? '')),
            "duracion"       => $row['duracion'] ?: 'Día completo',
            "grupo"          => 'Grupo de hasta 4 personas',
            "precio_persona" => $precio,
            "precio_grupo"   => round($precio * 4 * 0.9, 2),
            "rating"         => 4
        ];
    }
}
?>
