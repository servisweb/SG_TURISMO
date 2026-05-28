    </main>

    <!-- FOOTER -->
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-section">
                <h4>🏖️ Tumbes Tours</h4>
                <p>Tu compañía de confianza para explorar los destinos más hermosos del norte peruano.</p>
                <div class="footer-social">
                    <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fa-brands fa-twitter"></i></a>
                    <a href="https://wa.me/51942123456" target="_blank" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>🔗 Enlaces Rápidos</h4>
                <ul class="footer-links">
                    <li><a href="<?= isset($base_path) ? $base_path : '' ?>index.php#paquetes">Paquetes Turísticos</a></li>
                    <li><a href="<?= isset($base_path) ? $base_path : '' ?>index.php#sobre-nosotros">Sobre Nosotros</a></li>
                    <li><a href="<?= isset($base_path) ? $base_path : '' ?>index.php#contacto">Contacto</a></li>
                    <li><a href="<?= isset($base_path) ? $base_path : '' ?>views/login.php">Iniciar Sesión</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>📞 Contacto</h4>
                <ul class="footer-contact">
                    <li><i class="fa-solid fa-map-marker-alt"></i> Jr. Bolívar 234, Tumbes</li>
                    <li><i class="fa-solid fa-phone"></i> +51 942 123 456</li>
                    <li><i class="fa-solid fa-envelope"></i> info@tumbestours.com</li>
                    <li><i class="fa-solid fa-clock"></i> Lun - Dom: 8:00 AM - 6:00 PM</li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2024 Tumbes Tours. Todos los derechos reservados.</p>
            <p>Desarrollado con ❤️ para el turismo en Tumbes</p>
        </div>
    </footer>

    <!-- BOTÓN FLOTANTE DE WHATSAPP -->
    <a href="https://wa.me/51942123456?text=Hola%20Tumbes%20Tours,%20me%20gustaría%20obtener%20más%20información" 
       class="whatsapp-float" 
       target="_blank" 
       aria-label="Contactar por WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>

    <script src="<?= isset($base_path) ? $base_path : '' ?>js/main.js"></script>
    <script>
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        // Cerrar dropdown al hacer clic fuera
        window.onclick = function(event) {
            if (!event.target.matches('.btn--user') && !event.target.closest('.btn--user')) {
                const dropdown = document.getElementById('userDropdown');
                if (dropdown && dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        }
    </script>
</body>
</html>
