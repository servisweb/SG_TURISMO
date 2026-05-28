<?php
// =============================================
// CONTROLADOR - Detalles de Tours Individuales
// =============================================

$tours = [
    [
        "id"             => 1,
        "categoria"      => "naturaleza tumbes",
        "titulo"         => "Malecón - Puerto Pizarro",
        "imagen"         => "../assets/puerto-pizarro.jpg.jpg",
        "descripcion"    => "Explora los manglares, la Isla de los Pájaros y el zoocriadero de cocodrilos.",
        "descripcion_completa" => "Puerto Pizarro es uno de los principales atractivos turísticos de Tumbes. Disfruta de un paseo en bote por los manglares, visita la Isla de los Pájaros donde verás aves exóticas, y conoce el zoocriadero de cocodrilos. Es una experiencia inolvidable en contacto con la naturaleza.",
        "ubicacion"      => "Puerto Pizarro - Tumbes",
        "duracion"       => "1 día completo (8 horas)",
        "grupo"          => "Grupo de hasta 4 personas",
        "precio_persona" => 65,
        "precio_grupo"   => 260,
        "historia"       => "Puerto Pizarro fue fundado como un pequeño puerto de pescadores que con el tiempo se convirtió en un destino turístico gracias a sus manglares y su fauna marina. Aquí se desarrolla la historia natural de la región y las tradiciones pesqueras locales.",
        "destino"        => [ 'lat' => -3.5678, 'lng' => -80.4512 ],
        "rating"         => 4,
        "incluye"        => ["Paseo en bote", "Guía especializado", "Almuerzo", "Agua embotellada"],
        "horario_salida" => "08:00 AM",
        "horario_retorno" => "05:00 PM"
    ],

    [
        "id"             => 2,
        "categoria"      => "playa",
        "titulo"         => "Balneario de Zorritos",
        "imagen"         => "../assets/zorritos.jpg",
        "descripcion"    => "Relájate en las playas más cálidas del norte peruano y disfruta de su gastronomía.",
        "descripcion_completa" => "Zorritos es el balneario más visitado de Tumbes. Sus aguas cálidas durante todo el año la hacen perfecta para bañarse y disfrutar de actividades acuáticas. Disfruta de mariscos frescos en los restaurantes locales, camina por la playa y relájate en la arena blanca.",
        "ubicacion"      => "Zorritos, Tumbes",
        "duracion"       => "Día completo",
        "grupo"          => "Grupo de hasta 4 personas",
        "precio_persona" => 120,
        "precio_grupo"   => 480,
        "historia"       => "Zorritos ha sido un refugio costero tradicional por décadas: su historia está marcada por la pesca artesanal y la hospitalidad de su gente, con un crecimiento paralelo al turismo en la región.",
        "destino"        => [ 'lat' => -3.7023, 'lng' => -80.6354 ],
        "rating"         => 4,
        "incluye"        => ["Entrada a la playa", "Almuerzo en restaurante local", "Guía turístico", "Transporte"],
        "horario_salida" => "09:00 AM",
        "horario_retorno" => "06:00 PM"
    ],

    [
        "id"             => 3,
        "categoria"      => "cultura",
        "titulo"         => "Huaca del Sol – Cabeza de Vaca",
        "imagen"         => "../assets/huacas_del_sol.jpg",
        "descripcion"    => "Descubre la historia preínca de Tumbes en un fascinante recorrido arqueológico.",
        "descripcion_completa" => "Las Huacas del Sol y la Cabeza de Vaca son restos arqueológicos de la cultura Tumbesina. Acompañado por un guía especializado, descubrirás los misterios de la antigua civilización que habitó estas tierras. Un viaje fascinante por la historia del norte peruano.",
        "ubicacion"      => "Corrales, Tumbes",
        "duracion"       => "8 horas (aprox)",
        "grupo"          => "Grupo de hasta 4 personas",
        "precio_persona" => 120,
        "precio_grupo"   => 480,
        "historia"       => "Las Huacas del Sol y la Cabeza de Vaca guardan vestigios de antiguas civilizaciones que ocuparon el litoral norte. Este sitio conecta visitantes con el pasado precolombino del área.",
        "destino"        => [ 'lat' => -3.3987, 'lng' => -80.5021 ],
        "rating"         => 4,
        "incluye"        => ["Entrada arqueológica", "Guía especializado en historia", "Almuerzo", "Material informativo"],
        "horario_salida" => "08:00 AM",
        "horario_retorno" => "04:00 PM"
    ],

    [
        "id"             => 4,
        "categoria"      => "naturaleza",
        "titulo"         => "Punta Sal",
        "imagen"         => "../assets/punta-sal.jpg",
        "descripcion"    => "Playas de arena blanca con acantilados impresionantes y bosque seco.",
        "descripcion_completa" => "Punta Sal es un paraíso natural con playas vírgenes, acantilados dramáticos y una pequeña bahía perfecta para nadar. Ideal para fotógrafía, senderismo y relajación. Las aguas son cristalinas y el ambiente natural muy bien conservado.",
        "ubicacion"      => "Punta Sal, Tumbes",
        "duracion"       => "Full Day (10 horas)",
        "grupo"          => "Grupo de hasta 4 personas",
        "precio_persona" => 150,
        "precio_grupo"   => 600,
        "historia"       => "Punta Sal, con sus playas extensas y aguas cálidas, nació como una zona pesquera que hoy acoge actividades turísticas y deportivas acuáticas, manteniendo su carácter natural.",
        "destino"        => [ 'lat' => -3.9234, 'lng' => -80.6123 ],
        "rating"         => 5,
        "incluye"        => ["Transporte", "Snorkel", "Almuerzo", "Guía naturalista", "Equipo de buceo"],
        "horario_salida" => "07:00 AM",
        "horario_retorno" => "05:00 PM"
    ]
];

$guias = [
    1 => [
        'id' => 1,
        'nombre' => 'Carlos Rojas',
        'foto' => '../assets/guia_carlos.jpg',
        'especialidad' => 'Naturaleza y manglares',
        'experiencia' => '8 años de experiencia',
        'idiomas' => 'Español, Inglés',
        'descripcion' => 'Guía naturalista especializado en rutas por manglares y ecoturismo. Perfecto para aventuras en Puerto Pizarro.',
        'precio_extra' => 20,
        'disponibilidad' => 'Lun a Vie 08:00 - 17:00'
    ],
    2 => [
        'id' => 2,
        'nombre' => 'María Sánchez',
        'foto' => '../assets/guia_maria.jpg',
        'especialidad' => 'Playas y servicio al cliente',
        'experiencia' => '10 años de experiencia',
        'idiomas' => 'Español, Inglés, Portugués',
        'descripcion' => 'Guía experta en experiencias de playa, atención al turismo familiar y gastronomía local.',
        'precio_extra' => 30,
        'disponibilidad' => 'Mar a Dom 09:00 - 18:00'
    ],
    3 => [
        'id' => 3,
        'nombre' => 'Jorge Mejía',
        'foto' => '../assets/guia_jorge.jpg',
        'especialidad' => 'Historia y arqueología',
        'experiencia' => '12 años de experiencia',
        'idiomas' => 'Español, Inglés',
        'descripcion' => 'Guía cultural con profundo conocimiento en rutas arqueológicas y patrimonio histórico de Tumbes.',
        'precio_extra' => 40,
        'disponibilidad' => 'Lun a Sab 08:00 - 17:00'
    ],
    4 => [
        'id' => 4,
        'nombre' => 'Ana Torres',
        'foto' => '../assets/guia_ana.jpg',
        'especialidad' => 'Aventura y deportes',
        'experiencia' => '6 años de experiencia',
        'idiomas' => 'Español, Inglés',
        'descripcion' => 'Guía naturalista con experiencia en snorkel, senderismo y deportes acuáticos en Punta Sal.',
        'precio_extra' => 18,
        'disponibilidad' => 'Mié a Dom 07:00 - 19:00'
    ]
];

$tourGuidesMap = [
    1 => [1, 4],
    2 => [2, 4],
    3 => [3],
    4 => [4, 2]
];

// Obtener el tour específico por ID
$tour_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tour = null;

foreach ($tours as $t) {
    if ($t['id'] == $tour_id) {
        $tour = $t;
        break;
    }
}

$tourGuias = [];
if ($tour && isset($tourGuidesMap[$tour_id])) {
    foreach ($tourGuidesMap[$tour_id] as $guideId) {
        if (isset($guias[$guideId])) {
            $tourGuias[] = $guias[$guideId];
        }
    }
}
?>
