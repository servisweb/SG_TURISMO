<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../config/conexion.php';

// Obtener datos del usuario desde la BD
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM usuarios WHERE id_usuario = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    header('Location: ../../controladores/cerrar_sesion.php');
    exit;
}

$mensaje = '';
$tipo_mensaje = '';

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil'])) {
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    
    // Validar email único (excepto el propio)
    $check_email = "SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?";
    $stmt_check = $conexion->prepare($check_email);
    $stmt_check->bind_param("si", $email, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        $mensaje = 'El email ya está registrado por otro usuario';
        $tipo_mensaje = 'error';
    } else {
        // Actualizar datos
        $update_query = "UPDATE usuarios SET nombres = ?, apellidos = ?, telefono = ?, email = ? WHERE id_usuario = ?";
        $stmt_update = $conexion->prepare($update_query);
        $stmt_update->bind_param("ssssi", $nombres, $apellidos, $telefono, $email, $user_id);
        
        if ($stmt_update->execute()) {
            // Actualizar sesión
            $_SESSION['user_name'] = $nombres . ' ' . $apellidos;
            $_SESSION['user_email'] = $email;
            
            // Recargar datos
            $stmt->execute();
            $result = $stmt->get_result();
            $usuario = $result->fetch_assoc();
            
            $mensaje = '✅ Perfil actualizado correctamente';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = '❌ Error al actualizar el perfil';
            $tipo_mensaje = 'error';
        }
    }
}

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
    $password_actual = $_POST['password_actual'];
    $password_nueva = $_POST['password_nueva'];
    $password_confirmar = $_POST['password_confirmar'];
    
    // Verificar contraseña actual
    if (password_verify($password_actual, $usuario['password_hash'])) {
        if ($password_nueva === $password_confirmar) {
            if (strlen($password_nueva) >= 6) {
                $nuevo_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
                $update_pass = "UPDATE usuarios SET password_hash = ? WHERE id_usuario = ?";
                $stmt_pass = $conexion->prepare($update_pass);
                $stmt_pass->bind_param("si", $nuevo_hash, $user_id);
                
                if ($stmt_pass->execute()) {
                    $mensaje = '✅ Contraseña actualizada correctamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = '❌ Error al actualizar la contraseña';
                    $tipo_mensaje = 'error';
                }
            } else {
                $mensaje = '❌ La contraseña debe tener al menos 6 caracteres';
                $tipo_mensaje = 'error';
            }
        } else {
            $mensaje = '❌ Las contraseñas nuevas no coinciden';
            $tipo_mensaje = 'error';
        }
    } else {
        $mensaje = '❌ La contraseña actual es incorrecta';
        $tipo_mensaje = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Tumbes Tours</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/estyle.css">
    <link rel="stylesheet" href="../../css/mejoras.css">
    <style>
        body {
            background-color: var(--color-bg-body);
            padding: 20px 0;
        }
        
        .profile-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 25px;
        }
        
        .btn-back {
            grid-column: 1 / -1;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 600;
            padding: 12px 20px;
            background: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            width: fit-content;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            transform: translateX(-5px);
            box-shadow: var(--shadow-md);
        }
        
        /* SIDEBAR */
        .profile-sidebar-wrapper {
            position: sticky;
            top: 20px;
            align-self: start;
        }
        
        .profile-card-user {
            display: none;
        }
        
        .profile-cover {
            display: none;
        }
        
        .profile-avatar-section {
            display: none;
        }
        
        .profile-stats {
            display: none;
        }
        
        /* CARD DE PERFIL EN DATOS PERSONALES */
        .profile-header-card {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            border-radius: var(--radius-lg);
            padding: 30px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 25px;
            box-shadow: var(--shadow-md);
        }
        
        .profile-header-card .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            flex-shrink: 0;
        }
        
        .profile-header-info {
            flex: 1;
            color: white;
        }
        
        .profile-header-info h2 {
            font-size: 28px;
            margin-bottom: 8px;
            color: white;
            font-family: var(--font-heading);
        }
        
        .profile-header-info p {
            font-size: 16px;
            opacity: 0.95;
            margin-bottom: 12px;
        }
        
        .profile-header-info .profile-badge {
            display: inline-block;
            padding: 6px 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: var(--radius-pill);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .profile-header-stats {
            display: flex;
            gap: 30px;
        }
        
        .profile-header-stats .stat-item {
            text-align: center;
            background: rgba(255, 255, 255, 0.15);
            padding: 15px 25px;
            border-radius: var(--radius-md);
            backdrop-filter: blur(10px);
        }
        
        .profile-header-stats .stat-item .number {
            display: block;
            font-size: 28px;
            font-weight: 700;
            color: white;
            margin-bottom: 5px;
        }
        
        .profile-header-stats .stat-item .label {
            display: block;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.9);
            text-transform: uppercase;
        }
        
        /* MENU NAVEGACION */
        .profile-sidebar {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 2px solid var(--color-primary);
        }
        
        .sidebar-header {
            padding: 20px;
            background: #fafafa;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .sidebar-header h3 {
            color: var(--color-text-main);
            font-size: 16px;
            font-weight: 700;
            font-family: var(--font-heading);
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 10px;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            color: var(--color-text-main);
            text-decoration: none;
            border-radius: var(--radius-md);
            transition: all 0.3s;
            font-weight: 500;
            font-size: 15px;
        }
        
        .sidebar-menu a:hover {
            background: #fafafa;
            color: var(--color-primary);
            transform: translateX(5px);
        }
        
        .sidebar-menu a.active {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(49, 115, 90, 0.3);
        }
        
        .sidebar-menu a i {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }
        
        /* CONTENT AREA */
        .profile-content {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        
        .alert {
            grid-column: 1 / -1;
            padding: 16px 20px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .profile-section {
            background: white;
            padding: 35px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            scroll-margin-top: 20px;
        }
        
        .profile-section h2 {
            color: var(--color-text-main);
            margin-bottom: 30px;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
            font-family: var(--font-heading);
        }
        
        .profile-section h2 i {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            color: white;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        
        .form-group {
            margin-bottom: 22px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--color-text-main);
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group label i {
            color: var(--color-primary);
            margin-right: 6px;
            width: 16px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: var(--radius-md);
            font-size: 15px;
            transition: all 0.3s;
            background: #fafafa;
            font-family: var(--font-body);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--color-primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(49, 115, 90, 0.1);
        }
        
        .form-group input:disabled {
            background: #f0f0f0;
            cursor: not-allowed;
            color: var(--color-text-muted);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: var(--radius-md);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(49, 115, 90, 0.3);
            font-family: var(--font-body);
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(49, 115, 90, 0.4);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 0;
        }
        
        .info-card {
            background: #fafafa;
            padding: 20px;
            border-radius: var(--radius-md);
            border-left: 4px solid var(--color-primary);
            transition: all 0.3s;
        }
        
        .info-card:hover {
            background: #f5f5f5;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .info-card .label {
            font-size: 12px;
            color: var(--color-text-muted);
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }
        
        .info-card .value {
            font-size: 16px;
            color: var(--color-text-main);
            font-weight: 600;
        }
        
        @media (max-width: 968px) {
            .profile-wrapper {
                grid-template-columns: 1fr;
            }
            
            .profile-sidebar-wrapper {
                position: static;
            }
            
            .sidebar-menu {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
            }
            
            .sidebar-menu a {
                flex-direction: column;
                text-align: center;
                padding: 12px 8px;
                font-size: 13px;
            }
            
            .profile-header-card {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-header-stats {
                flex-direction: column;
                gap: 15px;
                width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .profile-wrapper {
                padding: 10px;
            }
            
            .sidebar-menu {
                grid-template-columns: 1fr;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-section {
                padding: 20px;
            }
            
            .profile-header-card {
                padding: 20px;
            }
            
            .profile-header-info h2 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="profile-wrapper">
        <a href="../../index.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Volver al inicio
        </a>
        
        <?php if ($mensaje): ?>
            <div class="alert <?= $tipo_mensaje ?>">
                <i class="fas fa-<?= $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= $mensaje ?>
            </div>
        <?php endif; ?>
        
        <!-- SIDEBAR COMPLETO -->
        <div class="profile-sidebar-wrapper">
            <!-- CARD DE PERFIL -->
            <div class="profile-card-user">
                <div class="profile-cover"></div>
                <div class="profile-avatar-section">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($usuario['nombres'] . ' ' . $usuario['apellidos']) ?>&size=100&background=31735a&color=fff&bold=true" 
                         alt="Avatar" class="profile-avatar">
                    <h2><?= htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']) ?></h2>
                    <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($usuario['email']) ?></p>
                    <span class="profile-badge"><?= htmlspecialchars($usuario['rol']) ?></span>
                </div>
                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="number">0</span>
                        <span class="label">Reservas</span>
                    </div>
                    <div class="stat-item">
                        <span class="number"><?= htmlspecialchars($usuario['estado']) === 'Activo' ? '✓' : '✗' ?></span>
                        <span class="label">Estado</span>
                    </div>
                </div>
            </div>
            
            <!-- MENU NAVEGACION -->
            <nav class="profile-sidebar">
                <div class="sidebar-header">
                    <h3>📋 Menú de Perfil</h3>
                </div>
                <ul class="sidebar-menu">
                    <li><a href="#datos-personales" class="active"><i class="fas fa-user-edit"></i> Datos Personales</a></li>
                    <li><a href="#cambiar-password"><i class="fas fa-lock"></i> Cambiar Contraseña</a></li>
                    <li><a href="#info-cuenta"><i class="fas fa-info-circle"></i> Info. de Cuenta</a></li>
                </ul>
            </nav>
        </div>
        
        <!-- CONTENT AREA -->
        <div class="profile-content">
            <!-- DATOS PERSONALES -->
            <section class="profile-section" id="datos-personales">
                <!-- CARD DE PERFIL -->
                <div class="profile-header-card">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($usuario['nombres'] . ' ' . $usuario['apellidos']) ?>&size=100&background=ffffff&color=31735a&bold=true" 
                         alt="Avatar" class="profile-avatar">
                    <div class="profile-header-info">
                        <h2><?= htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']) ?></h2>
                        <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($usuario['email']) ?></p>
                        <span class="profile-badge"><?= htmlspecialchars($usuario['rol']) ?></span>
                    </div>
                    <div class="profile-header-stats">
                        <div class="stat-item">
                            <span class="number">0</span>
                            <span class="label">Reservas</span>
                        </div>
                        <div class="stat-item">
                            <span class="number"><?= htmlspecialchars($usuario['estado']) === 'Activo' ? '✓' : '✗' ?></span>
                            <span class="label">Estado</span>
                        </div>
                    </div>
                </div>
                
                <h2><i class="fas fa-user-edit"></i> Datos Personales</h2>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-id-card"></i> Tipo de Documento</label>
                            <input type="text" value="<?= htmlspecialchars($usuario['tipo_documento']) ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-hashtag"></i> Número de Documento</label>
                            <input type="text" value="<?= htmlspecialchars($usuario['numero_documento']) ?>" disabled>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Nombres *</label>
                            <input type="text" name="nombres" value="<?= htmlspecialchars($usuario['nombres']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Apellidos *</label>
                            <input type="text" name="apellidos" value="<?= htmlspecialchars($usuario['apellidos']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Email *</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Teléfono</label>
                            <input type="tel" name="telefono" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>" placeholder="+51 999 999 999">
                        </div>
                    </div>
                    
                    <button type="submit" name="actualizar_perfil" class="btn-submit">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </form>
            </section>
            
            <!-- CAMBIAR CONTRASEÑA -->
            <section class="profile-section" id="cambiar-password">
                <h2><i class="fas fa-lock"></i> Cambiar Contraseña</h2>
                <form method="POST">
                    <div class="form-group">
                        <label><i class="fas fa-key"></i> Contraseña Actual *</label>
                        <input type="password" name="password_actual" placeholder="Ingresa tu contraseña actual" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Nueva Contraseña *</label>
                            <input type="password" name="password_nueva" placeholder="Mínimo 6 caracteres" minlength="6" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Confirmar Contraseña *</label>
                            <input type="password" name="password_confirmar" placeholder="Repite la contraseña" minlength="6" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="cambiar_password" class="btn-submit">
                        <i class="fas fa-key"></i> Actualizar Contraseña
                    </button>
                </form>
            </section>
            
            <!-- INFORMACIÓN DE CUENTA -->
            <section class="profile-section" id="info-cuenta">
                <h2><i class="fas fa-info-circle"></i> Información de Cuenta</h2>
                <div class="info-grid">
                    <div class="info-card">
                        <span class="label">Estado de Cuenta</span>
                        <span class="value"><?= htmlspecialchars($usuario['estado']) ?></span>
                    </div>
                    <div class="info-card">
                        <span class="label">Rol de Usuario</span>
                        <span class="value"><?= htmlspecialchars($usuario['rol']) ?></span>
                    </div>
                    <div class="info-card">
                        <span class="label">Fecha de Registro</span>
                        <span class="value"><?= date('d/m/Y H:i', strtotime($usuario['created_at'])) ?></span>
                    </div>
                    <div class="info-card">
                        <span class="label">Última Actualización</span>
                        <span class="value"><?= date('d/m/Y H:i', strtotime($usuario['updated_at'])) ?></span>
                    </div>
                </div>
            </section>
        </div>
    </div>
    
    <script>
        // Smooth scroll y activación de menú
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remover clase active de todos
                document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
                
                // Agregar clase active al clickeado
                this.classList.add('active');
                
                // Scroll suave a la sección
                const targetId = this.getAttribute('href');
                const targetSection = document.querySelector(targetId);
                
                if (targetSection) {
                    targetSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Detectar sección visible y actualizar menú
        const sections = document.querySelectorAll('.profile-section');
        const menuLinks = document.querySelectorAll('.sidebar-menu a');
        
        window.addEventListener('scroll', () => {
            let current = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (pageYOffset >= (sectionTop - 150)) {
                    current = section.getAttribute('id');
                }
            });
            
            menuLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
