<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$userLogged = !empty($_SESSION['user_name']);
$isAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'Admin';
?>
<header class="site-header">
        <div class="site-header__brand">
            <img src="assets/img/logo.png" alt="Logotipo de Tumbes Tours" class="site-header__logo">
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
        
        <?php if ($userLogged): ?>
            <div class="site-header__actions">
                <button class="btn btn--outline" onclick="window.location.href='/views/user/perfil_user.php'" aria-label="Mi perfil">
                    <i class="fa-solid fa-user"></i> Mi perfil
                </button>
                <?php if ($isAdmin): ?>
                    <button class="btn btn--outline" onclick="window.location.href='/views/admin/panel_admin.php'" aria-label="Panel admin">
                        <i class="fa-solid fa-gauge-high"></i> Admin
                    </button>
                <?php endif; ?>
                <button class="btn btn--outline" onclick="window.location.href='/controladores/cerrar_sesion.php'" aria-label="Cerrar sesión">
                    <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Cerrar sesión
                </button>
            </div>
        <?php else: ?>
            <button class="btn btn--outline" onclick="window.location.href='/views/login.php'" aria-label="Iniciar sesión">
                <i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i> Login
            </button>
        <?php endif; ?>
    </header>