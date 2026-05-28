<?php
session_start();

// Verificar si el usuario es admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'Admin') {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Paquete - Panel Administrativo</title>
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
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
        }

        .sidebar-footer a {
            display: flex;
            align-items: center;
            padding: 12px;
            background: rgba(231, 76, 60, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .sidebar-footer a:hover {
            background: #e74c3c;
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

        /* FORM CARD */
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 35px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            max-width: 900px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group label .required {
            color: #e74c3c;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #3498db;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        select.form-control {
            cursor: pointer;
        }

        .form-help {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }

        /* FILE UPLOAD */
        .file-upload {
            border: 2px dashed #ecf0f1;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-upload:hover {
            border-color: #3498db;
            background: #f8f9fa;
        }

        .file-upload i {
            font-size: 48px;
            color: #bdc3c7;
            margin-bottom: 15px;
        }

        .file-upload p {
            color: #7f8c8d;
            margin-bottom: 10px;
        }

        .file-upload input[type="file"] {
            display: none;
        }

        /* FORM ACTIONS */
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            padding-top: 25px;
            border-top: 2px solid #f5f6fa;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        /* SECTION TITLE */
        .section-title {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f5f6fa;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #3498db;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-mountain"></i> Tumbes Tours</h2>
            <p>Panel Administrativo</p>
        </div>

        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="paquetes.php" class="active"><i class="fas fa-box"></i> Paquetes</a></li>
            <li><a href="destinos.php"><i class="fas fa-map-marked-alt"></i> Destinos</a></li>
            <li><a href="reservas.php"><i class="fas fa-calendar-check"></i> Reservas</a></li>
            <li><a href="usuarios.php"><i class="fas fa-users"></i> Usuarios</a></li>
            <li><a href="guias.php"><i class="fas fa-user-tie"></i> Guías</a></li>
            <li><a href="proveedores.php"><i class="fas fa-handshake"></i> Proveedores</a></li>
            <li><a href="movilidades.php"><i class="fas fa-bus"></i> Movilidades</a></li>
            <li><a href="reportes.php"><i class="fas fa-chart-bar"></i> Reportes</a></li>
            <li><a href="configuracion.php"><i class="fas fa-cog"></i> Configuración</a></li>
        </ul>

        <div class="sidebar-footer">
            <a href="../../controladores/cerrar_sesion.php">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <h1><i class="fas fa-plus-circle"></i> Crear Nuevo Paquete</h1>
            <a href="paquetes.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>

        <!-- FORM CARD -->
        <div class="form-card">
            <form id="paquete-form" method="POST" enctype="multipart/form-data">
                
                <!-- INFORMACIÓN BÁSICA -->
                <div class="section-title">
                    <i class="fas fa-info-circle"></i>
                    <span>Información Básica</span>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Código del Paquete <span class="required">*</span></label>
                        <input type="text" name="codigo_paquete" class="form-control" placeholder="PKG-001" required>
                        <small class="form-help">Código único para identificar el paquete</small>
                    </div>

                    <div class="form-group">
                        <label>Estado <span class="required">*</span></label>
                        <select name="estado" class="form-control" required>
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label>Título del Paquete <span class="required">*</span></label>
                    <input type="text" name="titulo" class="form-control" placeholder="Ej: Tour Mágico a Puerto Pizarro" required>
                </div>

                <div class="form-group full-width">
                    <label>Descripción General <span class="required">*</span></label>
                    <textarea name="descripcion_general" class="form-control" placeholder="Describe el paquete turístico..." required></textarea>
                </div>

                <!-- DESTINO -->
                <div class="section-title">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Destino</span>
                </div>

                <div class="form-group">
                    <label>Seleccionar Destino <span class="required">*</span></label>
                    <select name="id_destino" class="form-control" required>
                        <option value="">-- Selecciona un destino --</option>
                        <option value="1">Puerto Pizarro</option>
                        <option value="2">Zorritos</option>
                        <option value="3">Punta Sal</option>
                        <option value="4">Cabeza de Vaca</option>
                        <option value="5">Manglares de Tumbes</option>
                    </select>
                </div>

                <!-- PRECIOS -->
                <div class="section-title">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Precios</span>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Precio Base <span class="required">*</span></label>
                        <input type="number" name="precio_base" class="form-control" placeholder="0.00" step="0.01" min="0" required>
                        <small class="form-help">Precio de referencia</small>
                    </div>

                    <div class="form-group">
                        <label>Precio por Persona <span class="required">*</span></label>
                        <input type="number" name="precio_persona" class="form-control" placeholder="0.00" step="0.01" min="0" required>
                        <small class="form-help">Precio individual</small>
                    </div>

                    <div class="form-group">
                        <label>Precio por Grupo (4 personas) <span class="required">*</span></label>
                        <input type="number" name="precio_grupo" class="form-control" placeholder="0.00" step="0.01" min="0" required>
                        <small class="form-help">Precio para grupo de 4</small>
                    </div>

                    <div class="form-group">
                        <label>Duración (días)</label>
                        <input type="number" name="duracion_dias" class="form-control" placeholder="1" min="1" value="1">
                    </div>
                </div>

                <!-- CUPOS -->
                <div class="section-title">
                    <i class="fas fa-users"></i>
                    <span>Capacidad</span>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Cupo Mínimo <span class="required">*</span></label>
                        <input type="number" name="cupo_minimo" class="form-control" placeholder="1" min="1" value="1" required>
                    </div>

                    <div class="form-group">
                        <label>Cupo Máximo <span class="required">*</span></label>
                        <input type="number" name="cupo_maximo" class="form-control" placeholder="10" min="1" value="10" required>
                    </div>
                </div>

                <!-- IMAGEN -->
                <div class="section-title">
                    <i class="fas fa-image"></i>
                    <span>Imagen de Portada</span>
                </div>

                <div class="form-group full-width">
                    <label for="file-input" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p><strong>Haz clic para subir</strong> o arrastra una imagen aquí</p>
                        <small class="form-help">PNG, JPG o JPEG (máx. 5MB)</small>
                        <input type="file" id="file-input" name="foto_portada" accept="image/*">
                    </label>
                </div>

                <!-- FORM ACTIONS -->
                <div class="form-actions">
                    <button type="button" class="btn btn-danger" onclick="resetForm()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Paquete
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Preview de imagen
        document.getElementById('file-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    alert('Imagen seleccionada: ' + file.name);
                }
                reader.readAsDataURL(file);
            }
        });

        // Reset form
        function resetForm() {
            if (confirm('¿Estás seguro de que deseas cancelar? Se perderán todos los cambios.')) {
                document.getElementById('paquete-form').reset();
            }
        }

        // Submit form
        document.getElementById('paquete-form').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Funcionalidad de guardado pendiente de implementar con la base de datos');
        });
    </script>
</body>
</html>
