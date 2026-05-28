<?php 
$page_title = 'Detalles del Tour - Tumbes Tours';
$page_description = 'Descubre todos los detalles de nuestro tour';
$base_path = '';
include __DIR__ . '/controladores/detalles_tour.php';
if ($tour) {
    $page_title = htmlspecialchars($tour['titulo']) . ' | Tumbes Tours';
    $page_description = htmlspecialchars($tour['descripcion']);
}
include __DIR__ . '/includes/header.php';

// Reseñas simuladas
$resenas = [
    [ 'estrellas' => 5, 'nombre' => 'Lucía M.', 'fecha' => '2026-03-12', 'texto' => 'Excelente experiencia, el guía conocía muy bien la fauna y los horarios se cumplieron.' ],
    [ 'estrellas' => 4, 'nombre' => 'Andrés P.', 'fecha' => '2026-04-02', 'texto' => 'Buen tour y paisajes hermosos. Recomendable para familias.' ],
    [ 'estrellas' => 5, 'nombre' => 'María R.', 'fecha' => '2026-05-10', 'texto' => 'El servicio del guía privado fue excepcional.' ]
];
?>
        <?php if ($tour): ?>
            <!-- HERO DEL TOUR -->
            <section class="tour-hero">
                
                <div class="tour-hero__overlay">
                    <div class="tour-hero__content">
                        <h1><?= htmlspecialchars($tour['titulo']) ?></h1>
                        <p><?= htmlspecialchars($tour['descripcion']) ?></p>
                    </div>
                </div>
            </section>

            <!-- CONTENIDO PRINCIPAL -->
            <section class="tour-details">
                <div class="tour-details__container">
                    <!-- INFORMACIÓN PRINCIPAL -->
                    <div class="tour-details__main">
                        <h2>Descripción del Tour</h2>
                        <p><?= nl2br(htmlspecialchars($tour['descripcion_completa'])) ?></p>

                        <!-- INFORMACIÓN DEL TOUR -->
                        <div class="tour-info-grid">
                            <div class="tour-info-item">
                                <i class="fa-solid fa-map-pin"></i>
                                <h3>Ubicación</h3>
                                <p><?= htmlspecialchars($tour['ubicacion']) ?></p>
                            </div>
                            <div class="tour-info-item">
                                <i class="fa-solid fa-clock"></i>
                                <h3>Duración</h3>
                                <p><?= htmlspecialchars($tour['duracion']) ?></p>
                            </div>
                            <div class="tour-info-item">
                                <i class="fa-solid fa-users"></i>
                                <h3>Grupo</h3>
                                <p><?= htmlspecialchars($tour['grupo']) ?></p>
                            </div>
                            <div class="tour-info-item">
                                <i class="fa-solid fa-sun"></i>
                                <h3>Horario</h3>
                                <p><?= htmlspecialchars($tour['horario_salida']) ?> - <?= htmlspecialchars($tour['horario_retorno']) ?></p>
                            </div>
                        </div>                         

                        <!-- QUÉ INCLUYE -->
                        <div class="tour-includes">
                            <h3>¿Qué Incluye?</h3>
                            <ul>
                                <?php foreach ($tour['incluye'] as $item): ?>
                                    <?php $displayItem = ($item === 'Guía especializado') ? 'Guía local general (Grupo compartido)' : $item; ?>
                                    <li><i class="fa-solid fa-check"></i> <?= htmlspecialchars($displayItem) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <!---reseñas--->
                        <div class="reviews-list">
                            <h3>Reseñas de Viajeros</h3>
                            <?php foreach ($resenas as $r): ?>
                            <div class="review-card">
                            <div class="review-meta">
                            <div class="review-stars">
                            <?php for ($i=0;$i<$r['estrellas'];$i++): ?>
                            <i class="fa-solid fa-star"></i>
                            <?php endfor; ?>
                            </div>
                            <div class="review-author"><?= htmlspecialchars($r['nombre']) ?> · <span class="review-date"><?= htmlspecialchars($r['fecha']) ?></span></div>
                            </div>
                            <p class="review-text"><?= htmlspecialchars($r['texto']) ?></p>
                            </div>
                            <?php endforeach; ?>
                            </div>

                    </div>
                    

                    <!-- PANEL DE PRECIOS Y RESERVA -->
                    <aside class="tour-sidebar">
                        <div class="tour-booking-card">
                            <h3>Información de Precios</h3>
                            
                            <div class="booking-price">
                                <div class="price-section">
                                    <span class="price-label">Por Persona</span>
                                    <span class="price-amount" id="base-price">S/. <?= number_format($tour['precio_persona'], 2) ?></span>
                                </div>
                                <div class="price-section">
                                    <span class="price-label">Grupo (4 personas)</span>
                                    <span class="price-amount">S/. <?= number_format($tour['precio_grupo'], 2) ?></span>
                                </div>
                            </div>

                            <div class="quantity-selector">
                                <label for="personas_count">Cantidad de personas</label>
                                <input type="number" id="personas_count" value="4" min="1" max="10" step="1" aria-label="Cantidad de personas">
                                <div class="capacity-note">Capacidad máxima: 10 personas</div>
                                <div id="quantity-warning" class="quantity-warning" style="display:none;">Has alcanzado el límite máximo de personas.</div>
                            </div>

                            <div class="booking-total">
                                <span class="price-label">Total a Pagar</span>
                                <span class="price-amount" id="calculated-total">S/. <?= number_format($tour['precio_persona'] * 4, 2) ?></span>
                            </div>

                            <div class="tour-booking-details">
                                <h4>Más información</h4>
                                <ul>
                                    <li><span>Duración</span><span><?= htmlspecialchars($tour['duracion']) ?></span></li>
                                    <li><span>Grupo</span><span><?= htmlspecialchars($tour['grupo']) ?></span></li>
                                    <li><span>Salida</span><span><?= htmlspecialchars($tour['horario_salida']) ?></span></li>
                                    <li><span>Retorno</span><span><?= htmlspecialchars($tour['horario_retorno']) ?></span></li>
                                </ul>
                            </div>

                            <div class="booking-guides">
                                <h3>Elige tu guía <button id="guide-info-btn" class="btn-info" title="Detalles del servicio"><i class="fa-solid fa-circle-info"></i></button></h3>
                                <div class="guide-options">
                                    <label class="guide-card guide-card--no-choice">
                                        <input type="radio" name="guide_id" value="0" data-price="0" checked onchange="updateReserveLink()">
                                        <div class="guide-card__content">
                                            <div>
                                                <h4>Prefiero no elegir guía</h4>
                                                <p class="guide-specialty">Reservar solo el paquete sin guía adicional.</p>
                                                <p class="guide-availability">Sin recargo de guía.</p>
                                            </div>
                                        </div>
                                    </label>

                                    <?php if (!empty($tourGuias)): ?>
                                        <?php foreach ($tourGuias as $guide): ?>
                                            <label class="guide-card">
                                                <input type="radio" name="guide_id" value="<?= $guide['id'] ?>" data-price="<?= number_format($guide['precio_extra'], 2, '.', '') ?>" onchange="updateReserveLink()">
                                                <div class="guide-card__content">
                                                    <img src="<?= htmlspecialchars($guide['foto']) ?>" alt="<?= htmlspecialchars($guide['nombre']) ?>" class="guide-card__image">
                                                    <div>
                                                        <h4><?= htmlspecialchars($guide['nombre']) ?></h4>
                                                        <p class="guide-specialty"><?= htmlspecialchars($guide['especialidad']) ?></p>
                                                        <p class="guide-experience"><strong><?= htmlspecialchars($guide['experiencia']) ?></strong> · <?= htmlspecialchars($guide['idiomas']) ?></p>
                                                        <p class="guide-price">+ S/. <?= number_format($guide['precio_extra'], 2) ?> (Tarifa fija)</p>
                                                        <p class="guide-availability">Disponibilidad: <?= htmlspecialchars($guide['disponibilidad']) ?></p>
                                                    </div>
                                                </div>
                                    
                                            </label>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p>No hay guías disponibles para este tour.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                             <a id="reserve-link" class="btn btn--primary btn-reserve" href="views/reservar.php?tour_id=<?= $tour['id'] ?>&guide_id=0">
                                <i class="fa-solid fa-calendar-check"></i> Reservar Ahora
                            </a>

                            <!-- Modal: Servicios Guía Privado -->
                            <div id="guide-modal" class="modal" aria-hidden="true">
                                <div class="modal-overlay" id="modal-overlay"></div>
                                <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modal-title">
                                    <button class="modal-close" id="modal-close"><i class="fa-solid fa-xmark"></i></button>
                                    <h4 id="modal-title">Servicios del Guía Privado</h4>
                                    <ul>
                                        <li>Atención 100% personalizada y guía exclusivo para tu grupo.</li>
                                        <li>Flexibilidad de paradas y tiempos según preferencia del grupo.</li>
                                        <li>Asistencia fotográfica básica durante el tour.</li>
                                        <li>No incluye alimentación del guía (a cargo del turista o el guía).</li>
                                        <li>Coordinación previa para rutas y requerimientos especiales.</li>
                                    </ul>
                                </div>
                            </div>
                            
                    </div>
                </div>


                            <p class="booking-notice">Para completar tu reserva, necesitarás iniciar sesión o registrarte.</p>
                        </div>
                    </aside>
                </div>
            </section>

        <?php else: ?>
            <section class="error-section">
                <div class="error-content">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <h2>Tour no encontrado</h2>
                    <p>Lo sentimos, el tour que buscas no está disponible.</p>
                    <a href="index.php" class="btn btn--primary">Volver a Paquetes</a>
                </div>
            </section>
        <?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>

    <script>
        const basePrice = <?= number_format($tour['precio_persona'], 2, '.', '') ?>;
        const groupPrice = <?= number_format($tour['precio_grupo'], 2, '.', '') ?>;
        const maxPeople = 10;

        function getSelectedGuideExtra() {
            const selectedGuide = document.querySelector('input[name="guide_id"]:checked');
            return selectedGuide ? parseFloat(selectedGuide.dataset.price || '0') : 0;
        }

        function getSelectedGuideId() {
            const selectedGuide = document.querySelector('input[name="guide_id"]:checked');
            return selectedGuide ? selectedGuide.value : '0';
        }

        function getPersonCount() {
            const input = document.getElementById('personas_count');
            let count = parseInt(input.value, 10);
            if (Number.isNaN(count) || count < 1) {
                count = 1;
                input.value = 1;
            }
            if (count > maxPeople) {
                count = maxPeople;
                input.value = maxPeople;
            }
            return count;
        }

        function updateQuantityWarning(count) {
            const warning = document.getElementById('quantity-warning');
            if (warning) {
                if (count >= maxPeople) {
                    warning.style.display = 'block';
                } else {
                    warning.style.display = 'none';
                }
            }
        }

        function calculateTourTotal(count, pricePersona, priceGrupo, guideExtra) {
            const groupPackages = Math.floor(count / 4);
            const individuals = count % 4;
            const tourCost = (groupPackages * priceGrupo) + (individuals * pricePersona);
            const guideCost = guideExtra;
            return tourCost + guideCost;
        }

        function updatePriceSummary() {
            const count = getPersonCount();
            const guideExtra = getSelectedGuideExtra();
            const total = calculateTourTotal(count, basePrice, groupPrice, guideExtra);
            const totalElement = document.getElementById('calculated-total');
            if (totalElement) {
                totalElement.textContent = 'S/. ' + total.toFixed(2);
            }
            updateQuantityWarning(count);
        }

        function updateReserveLink() {
            const reserveLink = document.getElementById('reserve-link');
            if (!reserveLink) {
                return;
            }

            const guideId = getSelectedGuideId();
            const count = getPersonCount();
            const baseUrl = 'views/reservar.php?tour_id=<?= $tour['id'] ?>';
            reserveLink.href = baseUrl + '&guide_id=' + guideId + '&cantidad=' + count;
            updatePriceSummary();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const quantityInput = document.getElementById('personas_count');
            if (quantityInput) {
                quantityInput.addEventListener('input', updateReserveLink);
                quantityInput.addEventListener('change', updateReserveLink);
            }
            const guideInputs = document.querySelectorAll('input[name="guide_id"]');
            guideInputs.forEach(input => input.addEventListener('change', updateReserveLink));
            updateReserveLink();

            const guideInfoBtn = document.getElementById('guide-info-btn');
            const guideModal = document.getElementById('guide-modal');
            const modalClose = document.getElementById('modal-close');
            const modalOverlay = document.getElementById('modal-overlay');

            if (guideInfoBtn && guideModal) {
                guideInfoBtn.addEventListener('click', function() {
                    guideModal.setAttribute('aria-hidden', 'false');
                    guideModal.classList.add('open');
                });
            }
            if (modalClose) {
                modalClose.addEventListener('click', function() {
                    guideModal.setAttribute('aria-hidden', 'true');
                    guideModal.classList.remove('open');
                });
            }
            if (modalOverlay) {
                modalOverlay.addEventListener('click', function() {
                    guideModal.setAttribute('aria-hidden', 'true');
                    guideModal.classList.remove('open');
                });
            }
        });
    </script>
