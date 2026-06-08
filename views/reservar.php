<?php
session_start();
require_once __DIR__ . '/../config/sesion.php';
verificar_sesion();

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

// Cargar conexión y datos maestros: destinos, guías y mapa guía-tour
require_once __DIR__ . '/../config/conexion.php';

// Destinos activos
$todos_destinos = $conexion->query('SELECT id_destino, nombre_destino, precio_referencial FROM destinos WHERE estado="Activo" ORDER BY nombre_destino ASC')->fetch_all(MYSQLI_ASSOC);

// Cargar guías desde la BD (array asociativo por id)
$guias = [];
$resG = $conexion->query('SELECT id_guia, nombres_completos, foto_url, especialidad, experiencia_anios, idiomas, precio_adicional FROM guias WHERE estado = "Activo"');
if ($resG) {
    while ($g = $resG->fetch_assoc()) {
        $guias[(int)$g['id_guia']] = [
            'id' => (int)$g['id_guia'],
            'nombre' => $g['nombres_completos'],
            'foto' => $g['foto_url'] ? 'assets/uploads/' . $g['foto_url'] : 'assets/uploads/guias/guia_carlos.jpg',
            'especialidad' => $g['especialidad'],
            'experiencia' => ($g['experiencia_anios'] ?? 0) . ' años de experiencia',
            'idiomas' => $g['idiomas'] ?? 'Español',
            'precio_extra' => (float)($g['precio_adicional'] ?? 0)
        ];
    }
}

// Cargar el mapeo tour -> guías desde data/tour_guides.json si existe
$tourGuidesMap = [];
$tgFile = __DIR__ . '/../data/tour_guides.json';
if (file_exists($tgFile)) {
    $json = file_get_contents($tgFile);
    $decoded = json_decode($json, true);
    if (is_array($decoded)) $tourGuidesMap = $decoded;
}

// Obtener el tour_id si fue enviado
$tour_id = isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : 0;
$tour_seleccionado = null;

if ($tour_id > 0) {
    foreach ($todos_destinos as $d) {
        if ((int)$d['id_destino'] === $tour_id) {
            $tour_seleccionado = [
                'id'           => (int)$d['id_destino'],
                'titulo'       => $d['nombre_destino'],
                'precio_persona' => (float)$d['precio_referencial'],
                'precio_grupo'   => round((float)$d['precio_referencial'] * 4 * 0.9, 2),
            ];
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
        body { background: #f4f7f6; }
        .reservation-container { max-width: 820px; margin: 40px auto; padding: 20px; }
        .reservation-header { text-align: center; margin-bottom: 30px; }
        .reservation-header h2 { font-size: 32px; margin-bottom: 6px; color: #111; }
        .reservation-header p { color: #666; }

        /* WIZARD STEPS */
        .wizard-steps {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 32px;
            gap: 0;
        }
        .wizard-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            position: relative;
        }
        .wizard-step .step-circle {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: #ddd;
            color: #888;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 15px;
            transition: all 0.3s;
            z-index: 1;
        }
        .wizard-step.active .step-circle { background: #31735a; color: #fff; }
        .wizard-step.done .step-circle { background: #a5d6a7; color: #fff; }
        .wizard-step .step-label { font-size: 12px; color: #888; font-weight: 600; white-space: nowrap; }
        .wizard-step.active .step-label { color: #31735a; }
        .wizard-connector {
            width: 80px; height: 3px;
            background: #ddd;
            margin-bottom: 18px;
            transition: background 0.3s;
        }
        .wizard-connector.done { background: #a5d6a7; }

        /* PANELS */
        .wizard-panel { display: none; }
        .wizard-panel.active { display: block; }

        .wizard-card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            padding: 32px;
        }
        .wizard-card h3 { font-size: 20px; color: #111; margin: 0 0 24px; }

        .form-section { margin-bottom: 20px; }
        .form-section label { display: block; margin-bottom: 7px; color: #333; font-weight: 600; font-size: 14px; }
        .form-section input,
        .form-section select,
        .form-section textarea {
            width: 100%; padding: 12px; border: 1px solid #ddd;
            border-radius: 8px; font-size: 15px; font-family: inherit;
            outline: none; transition: border-color 0.3s; box-sizing: border-box;
        }
        .form-section input:focus,
        .form-section select:focus,
        .form-section textarea:focus {
            border-color: #31735a;
            box-shadow: 0 0 0 3px rgba(49,115,90,0.1);
        }
        .form-section textarea { resize: vertical; min-height: 80px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        /* PASAJERO NAV */
        .pasajero-nav {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px;
        }
        .pasajero-nav span { font-weight: 700; color: #31735a; font-size: 16px; }
        .pasajero-dots { display: flex; gap: 8px; }
        .pasajero-dot {
            width: 10px; height: 10px; border-radius: 50%;
            background: #ddd; cursor: pointer; transition: background 0.2s;
        }
        .pasajero-dot.active { background: #31735a; }
        .pasajero-dot.done { background: #a5d6a7; }

        .pasajero-panel { display: none; }
        .pasajero-panel.active { display: block; }

        /* RESUMEN PASO 3 */
        .summary-item {
            display: flex; justify-content: space-between;
            padding: 12px 0; border-bottom: 1px solid #eee;
        }
        .summary-item:last-child { border-bottom: none; }
        .summary-item-label { color: #666; }
        .summary-item-value { font-weight: 600; color: #111; }
        .total-price {
            display: flex; justify-content: space-between;
            font-size: 20px; font-weight: 700; color: #31735a;
            padding-top: 16px; border-top: 2px solid #31735a; margin-top: 16px;
        }
        .pasajeros-resumen { margin-top: 16px; }
        .pasajero-resumen-item {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 14px; background: #f5f9f7;
            border-radius: 8px; margin-bottom: 8px;
            border: 1px solid #c8e6c9;
        }
        .pasajero-resumen-item i { color: #31735a; }

        /* BOTONES NAV */
        .wizard-nav {
            display: flex; justify-content: space-between;
            margin-top: 28px; gap: 12px;
        }
        .btn-prev, .btn-next, .btn-reservation {
            padding: 13px 28px; border: none; border-radius: 8px;
            font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-prev { background: #f0f0f0; color: #333; }
        .btn-prev:hover { background: #e0e0e0; }
        .btn-next { background: #31735a; color: white; margin-left: auto; }
        .btn-next:hover { background: #236c5b; }
        .btn-reservation { background: #31735a; color: white; width: 100%; justify-content: center; margin-top: 4px; }
        .btn-reservation:hover { background: #236c5b; }

        .user-info {
            background: #e8f5e9; padding: 13px 16px; border-radius: 8px;
            margin-bottom: 24px; color: #2e7d32; font-size: 14px;
        }
        .guide-detail-box { background:#f5f5f5; padding:14px; border-radius:8px; font-size:14px; line-height:1.6; }

        @media (max-width: 600px) {
            .form-row { grid-template-columns: 1fr; }
            .wizard-connector { width: 40px; }
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <header class="site-header">
        <div class="site-header__brand">
            <img src="../assets/uploads/img/pt.jpg" alt="Logotipo de Tumbes Tours" class="site-header__logo">
            <div class="site-header__titles">
                <h1>Tumbes Tours</h1>
                <p>Descubre el paraíso del norte</p>
            </div>
        </div>
        
        <nav class="site-header__nav" aria-label="Navegación principal">
            <ul>
                <li><a href="../index.php#paquetes">Paquetes</a></li>
                <li><a href="../index.php#sobre-nosotros">Sobre Nosotros</a></li>
                <li><a href="../index.php#contacto">Contacto</a></li>
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
                <i class="fa-solid fa-check-circle"></i> Sesión iniciada como: <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?></strong>
            </div>

            <!-- INDICADOR DE PASOS -->
            <div class="wizard-steps">
                <div class="wizard-step active" id="step-ind-1">
                    <div class="step-circle">1</div>
                    <span class="step-label">Tu reserva</span>
                </div>
                <div class="wizard-connector" id="conn-1"></div>
                <div class="wizard-step" id="step-ind-2">
                    <div class="step-circle">2</div>
                    <span class="step-label">Pasajeros</span>
                </div>
                <div class="wizard-connector" id="conn-2"></div>
                <div class="wizard-step" id="step-ind-3">
                    <div class="step-circle">3</div>
                    <span class="step-label">Confirmar</span>
                </div>
            </div>

            <form id="reservation-form" action="../controladores/procesar_reserva.php" method="POST">

                <!-- PASO 1: DATOS DEL TOUR -->
                <div class="wizard-panel active" id="panel-1">
                    <div class="wizard-card">
                        <h3><i class="fa-solid fa-map-location-dot"></i> Datos de la Reserva</h3>
                        <div class="form-row">
                            <div class="form-section">
                                <label for="tour">Tour Seleccionado</label>
                                <select id="tour" name="tour_id" required onchange="actualizarResumen()">
                                    <option value="">-- Selecciona un tour --</option>
                                    <?php foreach ($todos_destinos as $d): ?>
                                        <option value="<?= $d['id_destino'] ?>" <?= $tour_id == $d['id_destino'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($d['nombre_destino']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-section">
                                <label for="cantidad">Cantidad de Personas</label>
                                <input type="number" id="cantidad" name="cantidad" min="1" max="10" value="<?= $selectedQuantity ?>" required onchange="actualizarResumen()">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-section">
                                <label for="fecha_salida">Fecha de Salida</label>
                                <input type="date" id="fecha_salida" name="fecha_salida" required onchange="actualizarResumen()">
                            </div>
                            <div class="form-section">
                                <label for="telefono">Teléfono de Contacto</label>
                                <input type="tel" id="telefono" name="telefono" required placeholder="+51 999 999 999">
                            </div>
                        </div>
                        <div class="form-section">
                            <label for="nombre_contacto">Nombre del Contacto Principal</label>
                            <input type="text" id="nombre_contacto" name="nombre_contacto" required placeholder="Nombre completo">
                        </div>
                        <div class="form-section">
                            <label for="guide">Guía Turístico</label>
                            <select id="guide" name="guide_id" required onchange="actualizarGuide()">
                                <option value="0" <?= !$selectedGuide ? 'selected' : '' ?>>Sin guía adicional - Sin cargo</option>
                                <?php foreach ($tourGuides as $guide): ?>
                                    <option value="<?= $guide['id'] ?>" <?= $selectedGuide && $selectedGuide['id'] === $guide['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($guide['nombre']) ?> - <?= htmlspecialchars($guide['especialidad']) ?> (S/. <?= number_format($guide['precio_extra'], 2) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="guide-summary" style="display:<?= $selectedGuide ? 'block' : 'none' ?>">
                            <div class="guide-detail-box">
                                <strong id="guide-name"><?= $selectedGuide ? htmlspecialchars($selectedGuide['nombre']) : '' ?></strong><br>
                                <span id="guide-role"><?= $selectedGuide ? htmlspecialchars($selectedGuide['especialidad']) : '' ?></span> &middot;
                                <span id="guide-experience"><?= $selectedGuide ? htmlspecialchars($selectedGuide['experiencia']) : '' ?></span><br>
                                <span id="guide-languages"><?= $selectedGuide ? htmlspecialchars($selectedGuide['idiomas']) : '' ?></span>
                            </div>
                        </div>
                        <div class="form-section" style="margin-top:20px">
                            <label for="comentarios">Comentarios o Requisitos Especiales</label>
                            <textarea id="comentarios" name="comentarios" placeholder="Cuéntanos si tienes alguna necesidad especial..."></textarea>
                        </div>
                    </div>
                    <div class="wizard-nav">
                        <button type="button" class="btn-next" onclick="irPaso(2)">
                            Siguiente: Pasajeros <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- PASO 2: PASAJEROS -->
                <div class="wizard-panel" id="panel-2">
                    <div class="wizard-card">
                        <h3><i class="fa-solid fa-users"></i> Datos de los Pasajeros</h3>
                        <div class="pasajero-nav">
                            <span id="pasajero-titulo">Pasajero 1</span>
                            <div class="pasajero-dots" id="pasajero-dots"></div>
                        </div>
                        <div id="pasajeros-container"></div>
                        <div class="wizard-nav">
                            <button type="button" class="btn-prev" onclick="irPasajero(-1)">
                                <i class="fa-solid fa-arrow-left"></i> Anterior
                            </button>
                            <button type="button" class="btn-next" id="btn-paso2-next" onclick="siguientePasajeroOPaso()">
                                Siguiente <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    <div class="wizard-nav" style="margin-top:12px">
                        <button type="button" class="btn-prev" onclick="irPaso(1)">
                            <i class="fa-solid fa-arrow-left"></i> Volver al paso 1
                        </button>
                    </div>
                </div>

                <!-- PASO 3: RESUMEN Y CONFIRMAR -->
                <div class="wizard-panel" id="panel-3">
                    <div class="wizard-card">
                        <h3><i class="fa-solid fa-clipboard-check"></i> Resumen de tu Reserva</h3>
                        <div class="summary-item"><span class="summary-item-label">Tour</span><span class="summary-item-value" id="resumen-tour">—</span></div>
                        <div class="summary-item"><span class="summary-item-label">Fecha de salida</span><span class="summary-item-value" id="resumen-fecha">—</span></div>
                        <div class="summary-item"><span class="summary-item-label">Personas</span><span class="summary-item-value" id="resumen-cantidad">—</span></div>
                        <div class="summary-item"><span class="summary-item-label">Guía</span><span class="summary-item-value" id="resumen-guia">—</span></div>
                        <div class="summary-item"><span class="summary-item-label">Recargo guía</span><span class="summary-item-value" id="resumen-guide-price">S/. 0.00</span></div>
                        <div class="summary-item"><span class="summary-item-label">Precio por persona</span><span class="summary-item-value" id="resumen-precio-persona">—</span></div>
                        <div class="total-price"><span>Total a Pagar</span><span id="resumen-total">S/. 0.00</span></div>

                        <div class="pasajeros-resumen">
                            <p style="font-weight:600;color:#333;margin-bottom:10px;"><i class="fa-solid fa-users"></i> Pasajeros registrados</p>
                            <div id="resumen-pasajeros"></div>
                        </div>

                        <button type="button" class="btn-reservation" onclick="enviarReserva()">
                            <i class="fa-solid fa-check-circle"></i> Confirmar Reserva
                        </button>
                        <p style="text-align:center;color:#888;font-size:12px;margin-top:12px;">Al confirmar, aceptas nuestros términos y condiciones</p>
                    </div>
                    <div class="wizard-nav" style="margin-top:12px">
                        <button type="button" class="btn-prev" onclick="irPaso(2)">
                            <i class="fa-solid fa-arrow-left"></i> Volver a Pasajeros
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Tumbes Tours. Todos los derechos reservados.</p>
    </footer>

    <script>
        const tours = {
            <?php foreach ($todos_destinos as $d): ?>
            <?= $d['id_destino'] ?>: { titulo: <?= json_encode($d['nombre_destino']) ?>, precio: <?= (float)$d['precio_referencial'] ?> },
            <?php endforeach; ?>
        };
        const tourGuides = <?= json_encode($tourGuidesMap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const guides = <?= json_encode($guias, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

        let pasoActual = 1;
        let pasajeroActual = 0;

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('fecha_salida').min = new Date().toISOString().split('T')[0];
            const urlParams = new URLSearchParams(window.location.search);
            const tour_id = urlParams.get('tour_id');
            const guide_id = urlParams.get('guide_id');
            if (tour_id) {
                document.getElementById('tour').value = tour_id;
                actualizarGuideOptions();
                if (guide_id) document.getElementById('guide').value = guide_id;
                actualizarGuide();
                actualizarResumen();
            }
            document.getElementById('tour').addEventListener('change', function() {
                actualizarGuideOptions();
                actualizarResumen();
            });
            document.getElementById('cantidad').addEventListener('input', actualizarResumen);
        });

        function irPaso(paso) {
            if (paso === 2 && !validarPaso1()) return;
            if (paso === 3) generarResumenPaso3();
            document.getElementById('panel-' + pasoActual).classList.remove('active');
            document.getElementById('step-ind-' + pasoActual).classList.remove('active');
            if (paso > pasoActual) document.getElementById('step-ind-' + pasoActual).classList.add('done');
            else document.getElementById('step-ind-' + pasoActual).classList.remove('done');
            if (paso === 2) { generarPasajeros(); pasajeroActual = 0; mostrarPasajero(0); }
            pasoActual = paso;
            document.getElementById('panel-' + pasoActual).classList.add('active');
            document.getElementById('step-ind-' + pasoActual).classList.add('active');
            document.getElementById('conn-1').className = 'wizard-connector' + (pasoActual > 1 ? ' done' : '');
            document.getElementById('conn-2').className = 'wizard-connector' + (pasoActual > 2 ? ' done' : '');
            window.scrollTo(0, 0);
        }

        function validarPaso1() {
            const form = document.getElementById('reservation-form');
            if (!form.tour_id.value) { alert('Por favor selecciona un tour'); return false; }
            if (!form.fecha_salida.value) { alert('Por favor selecciona una fecha de salida'); return false; }
            if (!form.nombre_contacto.value.trim()) { alert('Por favor ingresa el nombre del contacto'); return false; }
            if (!form.telefono.value.trim()) { alert('Por favor ingresa tu teléfono'); return false; }
            return true;
        }

        function generarPasajeros() {
            const cantidad = parseInt(document.getElementById('cantidad').value) || 1;
            const container = document.getElementById('pasajeros-container');
            const dots = document.getElementById('pasajero-dots');
            container.innerHTML = '';
            dots.innerHTML = '';
            for (let i = 0; i < cantidad; i++) {
                const panel = document.createElement('div');
                panel.className = 'pasajero-panel' + (i === 0 ? ' active' : '');
                panel.id = 'pasajero-panel-' + i;
                panel.innerHTML = `
                    <div class="form-row">
                        <div class="form-section">
                            <label>Nombres completos *</label>
                            <input type="text" name="pasajeros[${i}][nombres]" required placeholder="Nombres y apellidos">
                        </div>
                        <div class="form-section">
                            <label>Tipo de documento *</label>
                            <select name="pasajeros[${i}][tipo_doc]" required>
                                <option value="DNI">DNI</option>
                                <option value="CE">Carnet de Extranjería</option>
                                <option value="PASSPORT">Pasaporte</option>
                            </select>
                        </div>
                        <div class="form-section">
                            <label>Número de documento *</label>
                            <input type="text" name="pasajeros[${i}][num_doc]" required placeholder="Ej: 12345678">
                        </div>
                        <div class="form-section">
                            <label>Fecha de nacimiento</label>
                            <input type="date" name="pasajeros[${i}][fecha_nac]">
                        </div>
                        <div class="form-section">
                            <label>Contacto de emergencia</label>
                            <input type="text" name="pasajeros[${i}][emergencia_nombre]" placeholder="Nombre">
                        </div>
                        <div class="form-section">
                            <label>Teléfono emergencia</label>
                            <input type="tel" name="pasajeros[${i}][emergencia_tel]" placeholder="Número">
                        </div>
                    </div>`;
                container.appendChild(panel);
                const dot = document.createElement('div');
                dot.className = 'pasajero-dot' + (i === 0 ? ' active' : '');
                dot.dataset.index = i;
                dot.onclick = () => { pasajeroActual = i; mostrarPasajero(i); };
                dots.appendChild(dot);
            }
            actualizarBtnPaso2();
        }

        function mostrarPasajero(idx) {
            const cantidad = parseInt(document.getElementById('cantidad').value) || 1;
            document.querySelectorAll('.pasajero-panel').forEach((p, i) => p.classList.toggle('active', i === idx));
            document.querySelectorAll('.pasajero-dot').forEach((d, i) => {
                d.classList.toggle('active', i === idx);
                d.classList.toggle('done', i < idx);
            });
            document.getElementById('pasajero-titulo').textContent = 'Pasajero ' + (idx + 1) + ' de ' + cantidad;
            actualizarBtnPaso2();
        }

        function actualizarBtnPaso2() {
            const cantidad = parseInt(document.getElementById('cantidad').value) || 1;
            const btn = document.getElementById('btn-paso2-next');
            if (pasajeroActual >= cantidad - 1) {
                btn.innerHTML = 'Ver Resumen <i class="fa-solid fa-arrow-right"></i>';
                btn.onclick = () => { if (validarPasajeroActual()) irPaso(3); };
            } else {
                btn.innerHTML = 'Siguiente <i class="fa-solid fa-arrow-right"></i>';
                btn.onclick = () => siguientePasajeroOPaso();
            }
        }

        function irPasajero(dir) {
            const cantidad = parseInt(document.getElementById('cantidad').value) || 1;
            const nuevo = pasajeroActual + dir;
            if (nuevo < 0) { irPaso(1); return; }
            if (nuevo >= cantidad) return;
            if (dir > 0 && !validarPasajeroActual()) return;
            pasajeroActual = nuevo;
            mostrarPasajero(pasajeroActual);
        }

        function siguientePasajeroOPaso() {
            const cantidad = parseInt(document.getElementById('cantidad').value) || 1;
            if (!validarPasajeroActual()) return;
            if (pasajeroActual < cantidad - 1) {
                pasajeroActual++;
                mostrarPasajero(pasajeroActual);
            } else {
                irPaso(3);
            }
        }

        function validarPasajeroActual() {
            const panel = document.getElementById('pasajero-panel-' + pasajeroActual);
            const nombre = panel.querySelector('input[name*="[nombres]"]');
            const doc = panel.querySelector('input[name*="[num_doc]"]');
            if (!nombre.value.trim()) { alert('Ingresa el nombre del pasajero ' + (pasajeroActual + 1)); nombre.focus(); return false; }
            if (!doc.value.trim()) { alert('Ingresa el documento del pasajero ' + (pasajeroActual + 1)); doc.focus(); return false; }
            return true;
        }

        function generarResumenPaso3() {
            const tourSelect = document.getElementById('tour');
            const cantidad = parseInt(document.getElementById('cantidad').value) || 1;
            const fecha = document.getElementById('fecha_salida').value;
            const guideId = document.getElementById('guide').value;
            const guidePrice = guideId && guides[guideId] ? guides[guideId].precio_extra : 0;
            const tourId = tourSelect.value;
            if (tourId && tours[tourId]) {
                const tour = tours[tourId];
                const total = (tour.precio + guidePrice) * cantidad;
                document.getElementById('resumen-tour').textContent = tour.titulo;
                document.getElementById('resumen-fecha').textContent = fecha || '—';
                document.getElementById('resumen-cantidad').textContent = cantidad;
                document.getElementById('resumen-guia').textContent = guideId && guides[guideId] ? guides[guideId].nombre : 'Sin guía adicional';
                document.getElementById('resumen-guide-price').textContent = 'S/. ' + guidePrice.toFixed(2);
                document.getElementById('resumen-precio-persona').textContent = 'S/. ' + tour.precio.toFixed(2);
                document.getElementById('resumen-total').textContent = 'S/. ' + total.toFixed(2);
            }
            const resumenPasajeros = document.getElementById('resumen-pasajeros');
            resumenPasajeros.innerHTML = '';
            for (let i = 0; i < cantidad; i++) {
                const panel = document.getElementById('pasajero-panel-' + i);
                if (!panel) continue;
                const nombre = panel.querySelector('input[name*="[nombres]"]').value;
                const tipoDoc = panel.querySelector('select[name*="[tipo_doc]"]').value;
                const numDoc = panel.querySelector('input[name*="[num_doc]"]').value;
                const div = document.createElement('div');
                div.className = 'pasajero-resumen-item';
                div.innerHTML = `<i class="fa-solid fa-user"></i> <strong>${nombre}</strong> &nbsp;&middot;&nbsp; ${tipoDoc}: ${numDoc}`;
                resumenPasajeros.appendChild(div);
            }
        }

        function actualizarResumen() {
            const tourId = document.getElementById('tour').value;
            const cantidad = parseInt(document.getElementById('cantidad').value) || 1;
            const guideId = document.getElementById('guide').value;
            const guidePrice = guideId && guides[guideId] ? guides[guideId].precio_extra : 0;
            if (tourId && tours[tourId]) {
                const tour = tours[tourId];
                document.getElementById('resumen-tour').textContent = tour.titulo;
                document.getElementById('resumen-cantidad').textContent = cantidad;
                document.getElementById('resumen-precio-persona').textContent = 'S/. ' + tour.precio.toFixed(2);
                document.getElementById('resumen-total').textContent = 'S/. ' + ((tour.precio + guidePrice) * cantidad).toFixed(2);
            }
        }

        function actualizarGuideOptions() {
            const tourId = document.getElementById('tour').value;
            const guideSelect = document.getElementById('guide');
            guideSelect.innerHTML = '<option value="0">Sin guía adicional - Sin cargo</option>';
            document.getElementById('guide-summary').style.display = 'none';
            if (tourId && tourGuides[tourId]) {
                tourGuides[tourId].forEach(function(guideId) {
                    if (guides[guideId]) {
                        const opt = document.createElement('option');
                        opt.value = guideId;
                        opt.textContent = guides[guideId].nombre + ' - ' + guides[guideId].especialidad + ' (S/. ' + guides[guideId].precio_extra.toFixed(2) + ')';
                        guideSelect.appendChild(opt);
                    }
                });
            }
        }

        function actualizarGuide() {
            const guideId = document.getElementById('guide').value;
            const summary = document.getElementById('guide-summary');
            if (guideId && guides[guideId]) {
                const g = guides[guideId];
                document.getElementById('guide-name').textContent = g.nombre;
                document.getElementById('guide-role').textContent = g.especialidad;
                document.getElementById('guide-experience').textContent = g.experiencia;
                document.getElementById('guide-languages').textContent = g.idiomas;
                document.getElementById('resumen-guia').textContent = g.nombre;
                document.getElementById('resumen-guide-price').textContent = 'S/. ' + g.precio_extra.toFixed(2);
                summary.style.display = 'block';
            } else {
                summary.style.display = 'none';
                document.getElementById('resumen-guia').textContent = 'Sin guía adicional';
                document.getElementById('resumen-guide-price').textContent = 'S/. 0.00';
            }
            actualizarResumen();
        }

        function enviarReserva() {
            document.getElementById('reservation-form').submit();
        }

        function logout() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                window.location.href = '../controladores/cerrar_sesion.php';
            }
        }
    </script>

</body>
</html>
