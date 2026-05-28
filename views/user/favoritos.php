<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Mis Favoritos - Tumbes Tours';
$page_description = 'Tus paquetes turísticos favoritos';
$base_path = '../';

require_once __DIR__ . '/../../config/conexion.php';

// Obtener favoritos del usuario (por ahora simulado, luego conectar a BD)
$favoritos = [];

?>
<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="profile-wrapper" style="max-width: 1400px; margin: 40px auto; padding: 20px;">
    <a href="../../index.php" class="btn-back" style="display: inline-flex; align-items: center; gap: 10px; color: var(--color-primary); text-decoration: none; font-weight: 600; padding: 12px 20px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px;">
        <i class="fas fa-arrow-left"></i> Volver al inicio
    </a>
    
    <div class="profile-section" style="background: white; padding: 35px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="color: var(--color-text-main); margin-bottom: 30px; font-size: 28px; display: flex; align-items: center; gap: 12px; padding-bottom: 20px; border-bottom: 2px solid #f0f0f0; font-family: var(--font-heading);">
            <i class="fas fa-heart" style="color: #e74c3c;"></i> Mis Favoritos
        </h2>
        
        <?php if (empty($favoritos)): ?>
            <div style="text-align: center; padding: 60px 20px;">
                <i class="fas fa-heart-broken" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"></i>
                <h3 style="color: #666; margin-bottom: 15px;">No tienes favoritos aún</h3>
                <p style="color: #999; margin-bottom: 30px;">Explora nuestros paquetes y guarda tus favoritos haciendo clic en el corazón</p>
                <a href="../../index.php#paquetes" class="btn btn--primary" style="display: inline-flex; align-items: center; gap: 10px;">
                    <i class="fas fa-search"></i> Explorar Paquetes
                </a>
            </div>
        <?php else: ?>
            <div class="packages__grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 35px;">
                <!-- Aquí se mostrarán los paquetes favoritos -->
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
