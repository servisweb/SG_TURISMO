<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Tumbes Tours' ?></title>
    <meta name="description" content="<?= isset($page_description) ? htmlspecialchars($page_description) : 'Playas paradisíacas, manglares únicos y experiencias inolvidables en Tumbes te esperan.' ?>">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= isset($base_path) ? $base_path : '' ?>css/estyle.css">
    <link rel="stylesheet" href="<?= isset($base_path) ? $base_path : '' ?>css/mejoras.css">
</head>
<body>

    <!-- HEADER -->
    <header class="site-header">
        <div class="site-header__brand">
            <img src="<?= isset($base_path) ? $base_path : '' ?>assets/pt.jpg" alt="Puerto Pizarro" class="site-header__logo">
            <div class="site-header__titles">
                <h1>Tumbes Tours</h1>
                <p>Descubre el paraíso del norte</p>
            </div>
        </div>
        
        <nav class="site-header__nav" aria-label="Navegación principal">
            <ul>
                <li><a href="<?= isset($base_path) ? $base_path : '' ?>index.php#paquetes">Paquetes</a></li>
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
                <li><a href="<?= isset($base_path) ? $base_path : '' ?>index.php#sobre-nosotros">Sobre Nosotros</a></li>
                <li><a href="<?= isset($base_path) ? $base_path : '' ?>index.php#contacto">Contacto</a></li>
            </ul>
        </nav>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="user-menu">
                <button class="btn btn--user" onclick="toggleUserDropdown()">
                    <i class="fa-solid fa-user-circle"></i> 
                    <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="user-dropdown" id="userDropdown">
                    <?php if ($_SESSION['user_rol'] === 'Admin'): ?>
                        <a href="<?= isset($base_path) ? $base_path : '' ?>views/admin/dashboard.php"><i class="fa-solid fa-gauge"></i> Panel Admin</a>
                    <?php endif; ?>
                    <a href="<?= isset($base_path) ? $base_path : '' ?>views/user/favoritos.php"><i class="fa-solid fa-heart"></i> Mis Favoritos</a>
                    <a href="<?= isset($base_path) ? $base_path : '' ?>views/user/perfil_user.php"><i class="fa-solid fa-user"></i> Mi Perfil</a>
                    <a href="<?= isset($base_path) ? $base_path : '' ?>controladores/cerrar_sesion.php"><i class="fa-solid fa-sign-out-alt"></i> Cerrar Sesión</a>
                </div>
            </div>
        <?php else: ?>
            <button class="btn btn--outline" onclick="window.location.href='<?= isset($base_path) ? $base_path : '' ?>views/login.php'">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Login
            </button>
        <?php endif; ?>
    </header>

    <main>
