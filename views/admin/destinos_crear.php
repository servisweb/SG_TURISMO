<?php
session_start();

// Verificar si el usuario es admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'Admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../config/conexion.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_destino = trim($_POST['nombre_destino'] ?? '');
    $tipo_destino = trim($_POST['tipo_destino'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $provincia = trim($_POST['provincia'] ?? '');
    $distrito = trim($_POST['distrito'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $foto_url = trim($_POST['foto_url'] ?? '');
    $horario_apertura = trim($_POST['horario_apertura'] ?? '');
    $horario_cierre = trim($_POST['horario_cierre'] ?? '');
    $precio_referencial = trim($_POST['precio_referencial'] ?? '');
    $estado = trim($_POST['estado'] ?? 'Activo');

    // Validaciones
    if (empty($nombre_destino)) {
        $mensaje = 'El nombre del destino es requerido';
        $tipo_mensaje = 'error';
    } elseif (empty($tipo_destino)) {
        $mensaje = 'El tipo de destino es requerido';
        $tipo_mensaje = 'error';
    } elseif (strlen($nombre_destino) < 3) {
        $mensaje = 'El nombre debe tener al menos 3 caracteres';
        $tipo_mensaje = 'error';
    } else {
        // Preparar datos para insertar
        $precio_referencial = !empty($precio_referencial) ? floatval($precio_referencial) : NULL;

        $query = "INSERT INTO destinos 
                  (nombre_destino, tipo_destino, region, provincia, distrito, descripcion, 
                   foto_url, horario_apertura, horario_cierre, precio_referencial, estado, created_at, updated_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $stmt = $conexion->prepare($query);
        $stmt->bind_param(
            'sssssssssds',
            $nombre_destino,
            $tipo_destino,
            $region,
            $provincia,
            $distrito,
            $descripcion,
            $foto_url,
            $horario_apertura,
            $horario_cierre,
            $precio_referencial,
            $estado
        );

        if ($stmt->execute()) {
            $mensaje = '✓ Destino creado exitosamente';
            $tipo_mensaje = 'success';
            // Limpiar formulario
            $_POST = [];
        } else {
            $mensaje = 'Error al crear el destino: ' . $conexion->error;
            $tipo_mensaje = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Destino - Panel Administrativo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            color: #2c3e50;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%);
            padding: 20px 0;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            scrollbar-width: none; /* Firefox */
        }

        .sidebar::-webkit-scrollbar {
            display: none; /* Chrome, Edge, Safari */
        }

        .sidebar-header {
            padding: 0 20px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            color: white;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            color: rgba(255,255,255,0.7);
            font-size: 13px;
        }

        .sidebar-menu {
            list-style: none;
            flex: 1;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 15px;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left: 4px solid #3498db;
        }

        .sidebar-menu a i {
            width: 25px;
            margin-right: 12px;
            font-size: 18px;
        }

        .sidebar-footer {
            position: relative;
            bottom: auto;
            left: auto;
            right: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 15px 20px;
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-footer a {
            display: flex;
            align-items: center;
            padding: 12px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            font-size: 14px;
        }

        .sidebar-footer a.btn-logout {
            background: rgba(231, 76, 60, 0.2);
        }

        .sidebar-footer a.btn-logout:hover {
            background: #e74c3c;
        }

        .sidebar-footer a.btn-website {
            background: rgba(52, 152, 219, 0.2);
        }

        .sidebar-footer a.btn-website:hover {
            background: #3498db;
        }

        .sidebar-footer a i {
            margin-right: 10px;
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 260px;
            padding: 30px;
            min-height: 100vh;
        }

        /* TOPBAR */
        .topbar {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .topbar h1 {
            font-size: 28px;
            color: #2c3e50;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary {
            background: #ecf0f1;
            color: #2c3e50;
        }

        .btn-secondary:hover {
            background: #bdc3c7;
        }

        /* CARD */
        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        /* FORM */
        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="time"],
        input[type="url"],
        select,
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="number"]:focus,
        input[type="time"]:focus,
        input[type="url"]:focus,
        select:focus,
        textarea:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        .required {
            color: #e74c3c;
        }

        /* BUTTONS */
        .form-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 40px;
            font-size: 15px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-cancel {
            background: #ecf0f1;
            color: #2c3e50;
            padding: 14px 40px;
            font-size: 15px;
        }

        .btn-cancel:hover {
            background: #bdc3c7;
        }

        /* MENSAJE */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>🏞️ Tumbes Tours</h2>
            <p>Panel Administrativo</p>
        </div>

        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="paquetes.php"><i class="fas fa-box"></i> Paquetes</a></li>
            <li><a href="destinos.php" class="active"><i class="fas fa-map-marked-alt"></i> Destinos</a></li>
            <li><a href="reservas.php"><i class="fas fa-calendar-check"></i> Reservas</a></li>
            <li><a href="usuarios.php"><i class="fas fa-users"></i> Usuarios</a></li>
            <li><a href="guias.php"><i class="fas fa-user-tie"></i> Guías</a></li>
            <li><a href="proveedores.php"><i class="fas fa-handshake"></i> Proveedores</a></li>
            <li><a href="movilidades.php"><i class="fas fa-bus"></i> Movilidades</a></li>
            <li><a href="reportes.php"><i class="fas fa-chart-bar"></i> Reportes</a></li>
        </ul>

        <div class="sidebar-footer">
            <a href="../../index.php" class="btn-website">
                <i class="fas fa-globe"></i> Ver Sitio Web
            </a>
            <a href="../../controladores/cerrar_sesion.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <h1><i class="fas fa-map-marked-alt"></i> Crear Nuevo Destino</h1>
            <a href="destinos.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>

        <!-- CARD -->
        <div class="card">
            <?php if (!empty($mensaje)): ?>
                <div class="alert <?= $tipo_mensaje ?>">
                    <i class="fas fa-<?= $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                    <span><?= htmlspecialchars($mensaje) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre_destino">Nombre del Destino <span class="required">*</span></label>
                        <input type="text" id="nombre_destino" name="nombre_destino" 
                               value="<?= htmlspecialchars($_POST['nombre_destino'] ?? '') ?>" 
                               placeholder="Ej: Puerto Pizarro" required>
                    </div>

                    <div class="form-group">
                        <label for="tipo_destino">Tipo de Destino <span class="required">*</span></label>
                        <select id="tipo_destino" name="tipo_destino" required>
                            <option value="">Seleccionar tipo...</option>
                            <option value="Playa" <?= ($_POST['tipo_destino'] ?? '') === 'Playa' ? 'selected' : '' ?>>Playa</option>
                            <option value="Naturaleza" <?= ($_POST['tipo_destino'] ?? '') === 'Naturaleza' ? 'selected' : '' ?>>Naturaleza</option>
                            <option value="Cultura" <?= ($_POST['tipo_destino'] ?? '') === 'Cultura' ? 'selected' : '' ?>>Cultura</option>
                            <option value="Mixto" <?= ($_POST['tipo_destino'] ?? '') === 'Mixto' ? 'selected' : '' ?>>Mixto</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="region">Región <span class="required">*</span></label>
                        <input type="text" id="region" name="region" 
                               value="<?= htmlspecialchars($_POST['region'] ?? '') ?>" 
                               placeholder="Ej: Tumbes" required>
                    </div>

                    <div class="form-group">
                        <label for="provincia">Provincia</label>
                        <input type="text" id="provincia" name="provincia" 
                               value="<?= htmlspecialchars($_POST['provincia'] ?? '') ?>" 
                               placeholder="Ej: Tumbes">
                    </div>

                    <div class="form-group">
                        <label for="distrito">Distrito</label>
                        <input type="text" id="distrito" name="distrito" 
                               value="<?= htmlspecialchars($_POST['distrito'] ?? '') ?>" 
                               placeholder="Ej: Tumbes">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="horario_apertura">Horario de Apertura</label>
                        <input type="time" id="horario_apertura" name="horario_apertura" 
                               value="<?= htmlspecialchars($_POST['horario_apertura'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="horario_cierre">Horario de Cierre</label>
                        <input type="time" id="horario_cierre" name="horario_cierre" 
                               value="<?= htmlspecialchars($_POST['horario_cierre'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="precio_referencial">Precio Referencial</label>
                        <input type="number" id="precio_referencial" name="precio_referencial" 
                               value="<?= htmlspecialchars($_POST['precio_referencial'] ?? '') ?>" 
                               placeholder="Ej: 150.00" step="0.01">
                    </div>
                </div>

                <div class="form-group">
                    <label for="foto_url">URL de Foto</label>
                    <input type="url" id="foto_url" name="foto_url" 
                           value="<?= htmlspecialchars($_POST['foto_url'] ?? '') ?>" 
                           placeholder="https://ejemplo.com/foto.jpg">
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" 
                              placeholder="Describe el destino..."><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="Activo" <?= ($_POST['estado'] ?? 'Activo') === 'Activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= ($_POST['estado'] ?? '') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn btn-submit">
                        <i class="fas fa-save"></i> Guardar Destino
                    </button>
                    <a href="destinos.php" class="btn btn-cancel">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Auto-ocultar mensaje después de 5 segundos
        const alert = document.querySelector('.alert');
        if (alert) {
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>
