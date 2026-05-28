<?php include __DIR__ . '/../controladores/cards-destinos.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tumbes Tours | Descubre el paraíso del norte</title>
    <meta name="description" content="Playas paradisíacas, manglares únicos y experiencias inolvidables en Tumbes te esperan.">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/estyle.css">
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
                <li><a href="#paquetes">Paquetes</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" id="destinos-link">Destinos <i class="fa-solid fa-chevron-down"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="#" class="filter-destino" data-target="corrales">Corrales</a></li>
                        <li><a href="#" class="filter-destino" data-target="tumbes">Tumbes</a></li>
                        <li><a href="#" class="filter-destino" data-target="punta-sal">Punta Sal</a></li>
                        <li><a href="#" class="filter-destino" data-target="zorritos">Zorritos</a></li>
                        <li><a href="#" class="filter-destino" data-target="todos">Ver todos</a></li>
                    </ul>
                </li>
                <li><a href="#sobre-nosotros">Sobre Nosotros</a></li>
                <li><a href="#contacto">Contacto</a></li>
            </ul>
        </nav>
        
        <button class="btn btn--outline">
            <i class="fa-solid fa-arrow-right-to-bracket"></i> Login
        </button>
    </header>

    <main>
        <!-- HERO -->
        <section class="hero" aria-labelledby="hero-title">
            <div class="hero-overlay"></div>
            <div class="hero__content">
                <h2 id="hero-title">Explora Tumbes</h2>
                <p>Playas paradisíacas, manglares únicos y experiencias inolvidables te esperan</p>
            </div>
        </section>

        <!-- BUSCADOR Y FILTROS -->
        <search class="search-section">
            <form class="search-section__form" role="search" action="#" method="GET">
                <input type="search" placeholder="Buscar paquetes o zonas..." aria-label="Buscar paquetes">
                <select aria-label="Seleccionar duración">
                    <option value="">Duración</option>
                    <option value="1">1 Día</option>
                    <option value="2">2 a 3 Días</option>
                </select>
            </form>
            
            <div class="search-section__filters">
                <button class="btn-filter is-active" data-filter="todos">Todos</button>
                <button class="btn-filter" data-filter="playa">Playa</button>
                <button class="btn-filter" data-filter="naturaleza">Naturaleza</button>
                <button class="btn-filter" data-filter="cultura">Cultura</button>
            </div>
        </search>

        <!-- PAQUETES DINÁMICOS -->
        <section class="packages" id="paquetes">
            <header class="packages__header text-center">
                <h3 class="title-merriweather">Nuestros Paquetes</h3>
            </header>
            
            <div class="packages__grid">
                
                <?php foreach ($tours as $tour): ?>
                <article class="package-card" data-categoria="<?= htmlspecialchars($tour['categoria']) ?>">
                    <button class="package-card__favorite">
                        <i class="fa-regular fa-heart"></i>
                    </button>
                    
                    <figure class="package-card__figure">
                        <img src="<?= htmlspecialchars($tour['imagen']) ?>" 
                            alt="<?= htmlspecialchars($tour['titulo']) ?>" loading="lazy">
                    </figure>
                    
                    <div class="package-card__body">
                        <div class="package-card__header">
                            <h4><?= htmlspecialchars($tour['titulo']) ?></h4>
                            <div class="package-card__rating">
                                <?php
                                $full = floor($tour['rating']);
                                for($i = 1; $i <= 5; $i++) {
                                    echo ($i <= $full) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                                }
                                ?>
                            </div>
                        </div>

                        <p class="package-card__description"><?= htmlspecialchars($tour['descripcion']) ?></p>

                        <ul class="tour-meta">
                            <li><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($tour['ubicacion']) ?></li>
                            <li><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($tour['duracion']) ?></li>
                            <li><i class="fa-solid fa-user-group"></i> <?= htmlspecialchars($tour['grupo']) ?></li>
                        </ul>

                        <div class="package-pricing">
                            <div class="price-box">
                                <span class="label">Desde</span>
                                <span class="price-main blue">S/. <?= $tour['precio_persona'] ?></span>
                                <span class="small">por persona</span>
                            </div>
                            <div class="price-box">
                                <span class="label">Grupo</span>
                                <span class="price-main">S/. <?= $tour['precio_grupo'] ?></span>
                                <span class="small">4 personas</span>
                            </div>
                        </div>
                    </div>
                    
                    <footer class="package-card__footer">
                        <a href="detalles_tour.php?id=<?= $tour['id'] ?>" class="btn btn--primary">Conocer más</a>
                    </footer>
                </article>
                <?php endforeach; ?>

            </div>
        </section>

        <!-- Aquí puedes pegar el resto de tu contenido (Sobre Nosotros, Contacto, Footer) -->

        <!-- SOBRE NOSOTROS -->
        <section class="info-section" id="sobre-nosotros">
            <h3>Sobre Nosotros</h3>
            <p>Somos Tumbes Tours, tu compañía de confianza para explorar los destinos más hermosos del norte peruano. Con más de 10 años de experiencia en el turismo, nos dedicamos a ofrecer experiencias inolvidables en las playas paradisíacas y atractivos naturales de Tumbes.</p>
            
            <div class="about-features">
                <ul class="feature-list">
                    <li><i class="fa-solid fa-check feature-icon"></i><strong>Experiencia:</strong> Más de 10 años llevando turistas a los mejores destinos de Tumbes.</li>
                    <li><i class="fa-solid fa-check feature-icon"></i><strong>Profesionalismo:</strong> Guías especializados y preparados para brindarte el mejor servicio.</li>
                    <li><i class="fa-solid fa-check feature-icon"></i><strong>Seguridad:</strong> Contamos con todos los permisos y seguros necesarios para tu tranquilidad.</li>
                    <li><i class="fa-solid fa-check feature-icon"></i><strong>Atención personalizada:</strong> Diseñamos paquetes ajustados a tus necesidades y presupuesto.</li>
                    <li><i class="fa-solid fa-check feature-icon"></i><strong>Destinos únicos:</strong> Playas, manglares, acantilados y mucho más para explorar.</li>
                </ul>
            </div>
        </section>

        <!-- CONTACTO -->
        <section class="info-section" id="contacto">
            <h3>Contacto</h3>
            <div class="contact-grid">
                <div class="contact-info">
                    <h4>Información de Contacto</h4>
                    <ul class="contact-list">
                        <li>
                            <i class="fa-solid fa-map-pin contact-icon"></i>
                            <span><strong>Dirección:</strong> Jr. Bolívar 234, Tumbes - Perú</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-phone contact-icon"></i>
                            <span><strong>Teléfono:</strong> +51 942 123 456</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-envelope contact-icon"></i>
                            <span><strong>Email:</strong> info@tumbestours.com</span>
                        </li>
                        <li>
                            <i class="fa-brands fa-whatsapp contact-icon"></i>
                            <span><strong>WhatsApp:</strong> +51 942 123 456</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-clock contact-icon"></i>
                            <span><strong>Horario:</strong> Lunes a Domingo, 8:00 AM - 6:00 PM</span>
                        </li>
                    </ul>
                    <a href="https://wa.me/51942123456?text=Hola%20Tumbes%20Tours,%20me%20gustaría%20conocer%20más%20sobre%20sus%20paquetes" 
                       target="_blank" class="btn-whatsapp">
                        <i class="fa-brands fa-whatsapp"></i> Contactar por WhatsApp
                    </a>
                </div>
                
                <div class="contact-form-container">
                    <h4>Envíanos tu consulta</h4>
                    <form class="contact-form">
                        <input type="text" placeholder="Tu nombre" required>
                        <input type="email" placeholder="Tu correo electrónico" required>
                        <input type="tel" placeholder="Tu teléfono" required>
                        <textarea placeholder="Tu mensaje o consulta..." rows="5" required></textarea>
                        <button type="submit" class="btn btn--primary">Enviar Consulta</button>
                    </form>
                </div>
            </div>
        </section>

    </main>

    <script src="../js/main.js"></script>
</body>
</html>