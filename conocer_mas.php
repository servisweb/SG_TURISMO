<?php
session_start();
$logueado = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Tour | Tumbes Tours</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/conocer.css">
    <link rel="stylesheet" href="css/estyle.css">
</head>
<body>

    <header class="site-header">
        <div class="site-header__brand">
            <div class="site-header__titles">
                <h1>Tumbes Tours</h1>
                <p>Descubre el paraíso del norte</p>
            </div>
        </div>
        <nav class="site-header__nav">
            <ul>
                <li><a href="index.php#paquetes">Paquetes</a></li>
                <li><a href="index.php#destinos">Destinos</a></li>
                <li><a href="index.php#sobre-nosotros">Sobre Nosotros</a></li>
                <li><a href="index.php#contacto">Contacto</a></li>
            </ul>
        </nav>
        <?php if ($logueado): ?>
            <a href="controladores/cerrar_sesion.php" class="btn btn--outline">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesión
            </a>
        <?php else: ?>
            <a href="views/login.php" class="btn btn--outline">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Login
            </a>
        <?php endif; ?>
    </header>

    <main class="tour-detail-page">
        <section class="tour-header">
            <div class="tour-header__image">
                <img src="assets/puerto-pizarro.jpg" alt="Malecón Puerto Pizarro">
            </div>
            <div class="tour-header__info">
                <h2>Malecón- Puerto Pizarro</h2>
                <p class="location"><i class="fa-solid fa-location-dot"></i> Puerto Pizarro, Tumbes</p>
                <div class="price">
                    <span class="price-label">Desde</span>
                    <span class="price-amount">S/ 65.00</span>
                </div>
            </div>
        </section>

        <div class="tour-content-grid">
            <section class="tour-info">
                <h3>Descripción del Atractivo</h3>
                <p>El Malecón de Puerto Pizarro es el punto de encuentro y el eje principal de la actividad turística en esta pintoresca caleta de pescadores. Es un espacio público diseñado para caminar frente al mar, disfrutar de la brisa marina y contemplar el paisaje donde el río se une con el océano.</p>

                <h4>¿Qué hacer en el Malecón?</h4>
                <ul class="includes-list">
                    <li><i class="fa-solid fa-store"></i> <strong>Feria de Artesanías:</strong> Compra recuerdos únicos elaborados por artesanos locales con conchas y restos marinos.</li>
                    <li><i class="fa-solid fa-utensils"></i> <strong>Gastronomía Local:</strong> Disfruta de restaurantes rústicos al borde del malecón especializados en ceviche de conchas negras y majarisco.</li>
                    <li><i class="fa-solid fa-camera"></i> <strong>Mirador Turístico:</strong> Sube a las estructuras de madera para obtener las mejores vistas panorámicas y fotografías del área.</li>
                </ul>

                <h4>Recomendaciones para la visita</h4>
                <p>El acceso al malecón es libre durante todo el día. Se recomienda visitarlo por la tarde para disfrutar de los restaurantes locales y ver la puesta de sol desde el mirador principal.</p>
            </section>

            <aside class="tour-booking">
                <div class="booking-card">
                    <h3>Reserva tu lugar</h3>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:6px;margin-bottom:15px;">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <form action="controladores/procesar_reserva.php" method="POST" id="booking-form">
                        <input type="hidden" name="tour_id" value="1">
                        <input type="hidden" name="guide_id" value="0">
                        <div class="form-group">
                            <label for="fecha">Fecha del tour:</label>
                            <input type="date" id="fecha" name="fecha_salida" required>
                        </div>
                        <div class="form-group">
                            <label for="personas">Número de personas:</label>
                            <input type="number" id="personas" name="cantidad" min="1" max="15" value="1" required>
                        </div>
                        <div class="form-group">
                            <label for="horario">Horario de salida:</label>
                            <select id="horario" name="horario" required>
                                <option value="mañana">09:00 AM</option>
                                <option value="tarde">02:00 PM</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nombre_contacto">Nombre de contacto:</label>
                            <input type="text" id="nombre_contacto" name="nombre_contacto" required placeholder="Tu nombre completo">
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono:</label>
                            <input type="tel" id="telefono" name="telefono" required placeholder="+51 999 999 999">
                        </div>
                        <div class="total-price">
                            Total: <span id="precio-total">S/ 65.00</span>
                        </div>
                        <button type="submit" class="btn btn--primary btn-full">Reservar Ahora</button>
                    </form>
                    <p class="booking-note"><i class="fa-solid fa-circle-info"></i> Pago seguro y confirmación inmediata.</p>
                </div>
            </aside>
        </div>
    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Tumbes Tours. Todos los derechos reservados.</p>
    </footer>

    <script src="js/tour-details.js"></script>
    <script>
    (function() {
        document.getElementById('booking-form').addEventListener('submit', function(e) {
            <?php if (!$logueado): ?>
            e.preventDefault();
            alert('Debes iniciar sesión para reservar.');
            window.location.href = 'views/login.php';
            <?php endif; ?>
        });
    })();
    </script>
</body>
</html>
