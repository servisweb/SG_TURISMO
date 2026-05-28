<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    $redirectQuery = [];
    if (isset($_GET['tour_id'])) {
        $redirectQuery[] = 'tour_id=' . (int)$_GET['tour_id'];
    }
    if (isset($_GET['guide_id'])) {
        $redirectQuery[] = 'guide_id=' . (int)$_GET['guide_id'];
    }
    if (isset($_GET['cantidad'])) {
        $redirectQuery[] = 'cantidad=' . (int)$_GET['cantidad'];
    }
    $queryString = $redirectQuery ? '?' . implode('&', $redirectQuery) : '';
    header('Location: login.php' . $queryString);
    exit;
}

// Obtener datos de los tours
include __DIR__ . '/../controladores/detalles_tour.php';

// Obtener el tour_id si fue enviado
$tour_id = isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : 0;
$tour_seleccionado = null;

if ($tour_id > 0) {
    $todos_tours = [
        [
            "id"             => 1,
            "categoria"      => "naturaleza tumbes",
            "titulo"         => "Malecón - Puerto Pizarro",
            "imagen"         => "../assets/puerto-pizarro.jpg",
            "precio_persona" => 65,
            "precio_grupo"   => 260
        ],
        [
            "id"             => 2,
            "categoria"      => "playa",
            "titulo"         => "Balneario de Zorritos",
            "imagen"         => "../assets/zorritos.jpg",
            "precio_persona" => 120,
            "precio_grupo"   => 480
        ],
        [
            "id"             => 3,
            "categoria"      => "cultura",
            "titulo"         => "Huaca del Sol – Cabeza de Vaca",
            "imagen"         => "../assets/huacas_del_sol.jpg",
            "precio_persona" => 120,
            "precio_grupo"   => 480
        ],
        [
            "id"             => 4,
            "categoria"      => "naturaleza",
            "titulo"         => "Punta Sal",
            "imagen"         => "../assets/punta-sal.jpg",
            "precio_persona" => 150,
            "precio_grupo"   => 600
        ]
    ];
    
    foreach ($todos_tours as $t) {
        if ($t['id'] == $tour_id) {
            $tour_seleccionado = $t;
            break;
        }
    }
}

$tourGuides = [];
$selectedGuideId = isset($_GET['guide_id']) ? (int)$_GET['guide_id'] : 0;
if ($tour_seleccionado && isset($tourGuidesMap[$tour_id])) {
    foreach ($tourGuidesMap[$tour_id] as $guideId) {
        if (isset($guias[$guideId])) {
            $tourGuides[] = $guias[$guideId];
        }
    }
}

$selectedGuide = null;
if ($selectedGuideId && isset($guias[$selectedGuideId])) {
    $selectedGuide = $guias[$selectedGuideId];
}

$selectedQuantity = isset($_GET['cantidad']) ? (int) $_GET['cantidad'] : 1;
$selectedQuantity = max(1, min(10, $selectedQuantity));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Reserva | Tumbes Tours</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/estyle.css">
    <style>
        .reservation-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }

        .reservation-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .reservation-header h2 {
            font-size: 36px;
            margin-bottom: 10px;
            color: #111;
        }

        .reservation-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        .reservation-form h3,
        .reservation-summary h3 {
            font-size: 22px;
            margin-bottom: 20px;
            color: #111;
        }

        .form-section {
            margin-bottom: 25px;
        }

        .form-section label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }

        .form-section input,
        .form-section select,
        .form-section textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.3s;
        }

        .form-section input:focus,
        .form-section select:focus,
        .form-section textarea:focus {
            border-color: #31735a;
            box-shadow: 0 0 0 3px rgba(49, 115, 90, 0.1);
        }

        .form-section textarea {
            resize: vertical;
            min-height: 80px;
        }

        .reservation-summary {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-item-label {
            color: #666;
        }

        .summary-item-value {
            font-weight: 600;
            color: #111;
        }

        .total-price {
            display: flex;
            justify-content: space-between;
            font-size: 20px;
            font-weight: 700;
            color: #31735a;
            padding-top: 15px;
            border-top: 2px solid #31735a;
            margin-top: 15px;
        }

        .btn-reservation {
            width: 100%;
            padding: 15px;
            background-color: #31735a;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 20px;
        }

        .btn-reservation:hover {
            background-color: #236c5b;
        }

        .user-info {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-info-text {
            color: #2e7d32;
        }

        .logout-btn {
            padding: 8px 16px;
            background: #d32f2f;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .logout-btn:hover {
            background: #b71c1c;
        }

        @media (max-width: 768px) {
            .reservation-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <header class="site-header">
        <div class="site-header__brand">
            <img src="https://via.placeholder.com/50" alt="Logotipo de Tumbes Tours" class="site-header__logo">
            <div class="site-header__titles">
                <h1>Tumbes Tours</h1>
                <p>Descubre el paraíso del norte</p>
            </div>
        </div>
        
        <nav class="site-header__nav" aria-label="Navegación principal">
            <ul>
                <li><a href="../html/index.php#paquetes">Paquetes</a></li>
                <li><a href="../html/index.php#sobre-nosotros">Sobre Nosotros</a></li>
                <li><a href="../html/index.php#contacto">Contacto</a></li>
            </ul>
        </nav>
        
        <button class="btn btn--outline" onclick="logout()">
            <i class="fa-solid fa-sign-out-alt"></i> Cerrar Sesión
        </button>
    </header>

    <main>
        <div class="reservation-container">
            <div class="reservation-header">
                <h2>Formulario de Reserva</h2>
                <p>Complete el formulario para reservar su tour</p>
            </div>

            <div class="user-info">
                <div class="user-info-text">
                    <i class="fa-solid fa-check-circle"></i> Sesión iniciada como: <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>
                </div>
            </div>

            <div class="reservation-grid">
                <!-- FORMULARIO DE RESERVA -->
                <div class="reservation-form">
                    <h3>Datos de la Reserva</h3>
                    <form id="reservation-form" action="../controladores/procesar_reserva.php" method="POST">
                        <!-- Tour seleccionado -->
                        <div class="form-section">
                            <label for="tour">Tour Seleccionado</label>
                            <select id="tour" name="tour_id" required onchange="actualizarResumen()">
                                <option value="">-- Selecciona un tour --</option>
                                <option value="1" <?= $tour_seleccionado && $tour_seleccionado['id'] == 1 ? 'selected' : '' ?>>Malecón - Puerto Pizarro</option>
                                <option value="2" <?= $tour_seleccionado && $tour_seleccionado['id'] == 2 ? 'selected' : '' ?>>Balneario de Zorritos</option>
                                <option value="3" <?= $tour_seleccionado && $tour_seleccionado['id'] == 3 ? 'selected' : '' ?>>Huaca del Sol – Cabeza de Vaca</option>
                                <option value="4" <?= $tour_seleccionado && $tour_seleccionado['id'] == 4 ? 'selected' : '' ?>>Punta Sal</option>
                            </select>
                        </div>

                        <!-- Cantidad de personas -->
                        <div class="form-section">
                            <label for="cantidad">Cantidad de Personas</label>
                            <input type="number" id="cantidad" name="cantidad" min="1" max="10" value="<?= $selectedQuantity ?>" required onchange="actualizarResumen()">
                        </div>

                        <!-- Fecha de salida -->
                        <div class="form-section">
                            <label for="fecha_salida">Fecha de Salida</label>
                            <input type="date" id="fecha_salida" name="fecha_salida" required onchange="actualizarResumen()">
                        </div>

                        <!-- Nombre del contacto -->
                        <div class="form-section">
                            <label for="nombre_contacto">Nombre del Contacto Principal</label>
                            <input type="text" id="nombre_contacto" name="nombre_contacto" required>
                        </div>

                        <!-- Teléfono -->
                        <div class="form-section">
                            <label for="telefono">Teléfono de Contacto</label>
                            <input type="tel" id="telefono" name="telefono" required>
                        </div>

                        <div class="form-section">
                            <label for="guide">Selecciona tu guía</label>
                            <select id="guide" name="guide_id" required onchange="actualizarGuide()">
                                <option value="0" <?= !$selectedGuide ? 'selected' : '' ?>>Prefiero no elegir guía - Sin cargo adicional</option>
                                <?php foreach ($tourGuides as $guide): ?>
                                    <option value="<?= $guide['id'] ?>" <?= $selectedGuide && $selectedGuide['id'] === $guide['id'] ? 'selected' : '' ?>><?= htmlspecialchars($guide['nombre']) ?> - <?= htmlspecialchars($guide['especialidad']) ?> (S/. <?= number_format($guide['precio_extra'], 2) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-section" id="guide-summary" style="display: <?= $selectedGuide ? 'block' : 'none' ?>;">
                            <label>Detalles del guía seleccionado</label>
                            <div style="background:#f5f5f5;padding:15px;border-radius:10px;">
                                <strong id="guide-name"><?= $selectedGuide ? htmlspecialchars($selectedGuide['nombre']) : '' ?></strong><br>
                                <span id="guide-role"><?= $selectedGuide ? htmlspecialchars($selectedGuide['especialidad']) : '' ?></span><br>
                                <span id="guide-experience"><?= $selectedGuide ? htmlspecialchars($selectedGuide['experiencia']) : '' ?></span><br>
                                <span id="guide-languages"><?= $selectedGuide ? htmlspecialchars($selectedGuide['idiomas']) : '' ?></span>
                            </div>
                        </div>

                        <!-- Comentarios especiales -->
                        <div class="form-section">
                            <label for="comentarios">Comentarios o Requisitos Especiales</label>
                            <textarea id="comentarios" name="comentarios" placeholder="Cuéntanos si tienes alguna necesidad especial..."></textarea>
                        </div>
                    </form>
                </div>

                <!-- RESUMEN DE RESERVA -->
                <div class="reservation-summary">
                    <h3>Resumen de tu Reserva</h3>
                    
                    <div class="summary-item">
                        <span class="summary-item-label">Tour:</span>
                        <span class="summary-item-value" id="resumen-tour">Selecciona un tour</span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="summary-item-label">Personas:</span>
                        <span class="summary-item-value" id="resumen-cantidad">1</span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="summary-item-label">Fecha de Salida:</span>
                        <span class="summary-item-value" id="resumen-fecha">No seleccionada</span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="summary-item-label">Guía:</span>
                        <span class="summary-item-value" id="resumen-guia"><?= $selectedGuide ? htmlspecialchars($selectedGuide['nombre']) : 'Prefiero no elegir guía' ?></span>
                    </div>

                    <div class="summary-item">
                        <span class="summary-item-label">Recargo guía:</span>
                        <span class="summary-item-value" id="resumen-guide-price">S/. 0.00</span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="summary-item-label">Precio por Persona:</span>
                        <span class="summary-item-value" id="resumen-precio-persona">S/. 0.00</span>
                    </div>

                    <div class="total-price">
                        <span>Total a Pagar:</span>
                        <span id="resumen-total">S/. 0.00</span>
                    </div>

                    <button class="btn-reservation" onclick="enviarReserva()">
                        <i class="fa-solid fa-check-circle"></i> Confirmar Reserva
                    </button>

                    <p style="text-align: center; color: #666; font-size: 12px; margin-top: 15px;">
                        Al confirmar, aceptas nuestros términos y condiciones
                    </p>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Tumbes Tours. Todos los derechos reservados.</p>
    </footer>

    <script>
        // Datos de precios de los tours
        const tours = {
            1: { titulo: "Malecón - Puerto Pizarro", precio: 65 },
            2: { titulo: "Balneario de Zorritos", precio: 120 },
            3: { titulo: "Huaca del Sol – Cabeza de Vaca", precio: 120 },
            4: { titulo: "Punta Sal", precio: 150 }
        };

        const tourGuides = <?= json_encode($tourGuidesMap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const guides = <?= json_encode($guias, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

        document.addEventListener('DOMContentLoaded', function() {
            const fechaInput = document.getElementById('fecha_salida');
            const hoy = new Date().toISOString().split('T')[0];
            fechaInput.min = hoy;

            // Si hay tour_id en URL, seleccionarlo
            const urlParams = new URLSearchParams(window.location.search);
            const tour_id = urlParams.get('tour_id');
            const guide_id = urlParams.get('guide_id');
            if (tour_id) {
                document.getElementById('tour').value = tour_id;
                actualizarGuideOptions();
                if (guide_id) {
                    document.getElementById('guide').value = guide_id;
                }
                actualizarGuide();
                actualizarResumen();
            }

            document.getElementById('tour').addEventListener('change', function() {
                actualizarGuideOptions();
                actualizarResumen();
            });
        });

        function actualizarGuideOptions() {
            const tourSelect = document.getElementById('tour');
            const guideSelect = document.getElementById('guide');
            const tourId = tourSelect.value;

            guideSelect.innerHTML = '<option value="0">Prefiero no elegir guía - Sin cargo adicional</option>';
            document.getElementById('guide-summary').style.display = 'none';

            if (tourId && tourGuides[tourId]) {
                tourGuides[tourId].forEach(function(guideId) {
                    if (guides[guideId]) {
                        const option = document.createElement('option');
                        option.value = guideId;
                        option.textContent = guides[guideId].nombre + ' - ' + guides[guideId].especialidad + ' (S/. ' + guides[guideId].precio_extra.toFixed(2) + ')';
                        guideSelect.appendChild(option);
                    }
                });

                if (guideSelect.options.length > 0) {
                    guideSelect.selectedIndex = 0;
                    actualizarGuide();
                }
            }
        }

        function actualizarGuide() {
            const guideSelect = document.getElementById('guide');
            const guideId = guideSelect.value;
            const summary = document.getElementById('guide-summary');

            if (guideId && guides[guideId]) {
                const guide = guides[guideId];
                document.getElementById('guide-name').textContent = guide.nombre;
                document.getElementById('guide-role').textContent = guide.especialidad;
                document.getElementById('guide-experience').textContent = guide.experiencia;
                document.getElementById('guide-languages').textContent = guide.idiomas;
                document.getElementById('resumen-guia').textContent = guide.nombre;
                document.getElementById('resumen-guide-price').textContent = 'S/. ' + guide.precio_extra.toFixed(2);
                summary.style.display = 'block';
            } else {
                summary.style.display = 'none';
                document.getElementById('resumen-guia').textContent = 'Prefiero no elegir guía';
                document.getElementById('resumen-guide-price').textContent = 'S/. 0.00';
            }

            actualizarResumen();
        }

        function actualizarResumen() {
            const tourSelect = document.getElementById('tour');
            const cantidadInput = document.getElementById('cantidad');
            const fechaInput = document.getElementById('fecha_salida');
            const guideSelect = document.getElementById('guide');

            const tourId = tourSelect.value;
            const cantidad = parseInt(cantidadInput.value) || 1;
            const fecha = fechaInput.value;
            const guideId = guideSelect.value;
            const guidePrice = guideId && guides[guideId] ? guides[guideId].precio_extra : 0;

            if (tourId && tours[tourId]) {
                const tour = tours[tourId];
                const total = (tour.precio + guidePrice) * cantidad;

                document.getElementById('resumen-tour').textContent = tour.titulo;
                document.getElementById('resumen-cantidad').textContent = cantidad;
                document.getElementById('resumen-precio-persona').textContent = 'S/. ' + tour.precio.toFixed(2);
                document.getElementById('resumen-guide-price').textContent = 'S/. ' + guidePrice.toFixed(2);
                document.getElementById('resumen-total').textContent = 'S/. ' + total.toFixed(2);
                document.getElementById('resumen-fecha').textContent = fecha || 'No seleccionada';
            }
        }

        function enviarReserva() {
            const form = document.getElementById('reservation-form');

            if (!form.tour_id.value) {
                alert('Por favor selecciona un tour');
                return;
            }
            if (!form.fecha_salida.value) {
                alert('Por favor selecciona una fecha de salida');
                return;
            }
            if (!form.nombre_contacto.value) {
                alert('Por favor ingresa el nombre del contacto');
                return;
            }
            if (!form.telefono.value) {
                alert('Por favor ingresa tu teléfono');
                return;
            }
            if (form.guide_id.value === '') {
                alert('Por favor selecciona un guía o elige la opción de no seleccionar guía');
                return;
            }

            form.submit();
        }

        function logout() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../controladores/cerrar_sesion.php';
            }
        }
    </script>

</body>
</html>
