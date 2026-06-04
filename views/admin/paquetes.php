<?php
session_start();

// Verificar si el usuario es admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'Admin') {
    header('Location: ../login.php');
    exit;
}

// Datos simulados de paquetes
$paquetes = [
    ['id' => 1, 'codigo' => 'PKG-001', 'titulo' => 'Malecón - Puerto Pizarro', 'destino' => 'Puerto Pizarro', 'precio_base' => 65.00, 'precio_persona' => 65.00, 'precio_grupo' => 260.00, 'estado' => 'Activo'],
    ['id' => 2, 'codigo' => 'PKG-002', 'titulo' => 'Balneario de Zorritos', 'destino' => 'Zorritos', 'precio_base' => 120.00, 'precio_persona' => 120.00, 'precio_grupo' => 480.00, 'estado' => 'Activo'],
    ['id' => 3, 'codigo' => 'PKG-003', 'titulo' => 'Huaca del Sol', 'destino' => 'Cabeza de Vaca', 'precio_base' => 120.00, 'precio_persona' => 120.00, 'precio_grupo' => 480.00, 'estado' => 'Activo'],
    ['id' => 4, 'codigo' => 'PKG-004', 'titulo' => 'Punta Sal', 'destino' => 'Punta Sal', 'precio_base' => 150.00, 'precio_persona' => 150.00, 'precio_grupo' => 600.00, 'estado' => 'Activo'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Paquetes - Panel Administrativo</title>
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

        .topbar-actions {
            display: flex;
            gap: 15px;
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

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
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
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f5f6fa;
        }

        .card-header h3 {
            font-size: 20px;
            color: #2c3e50;
        }

        /* SEARCH BAR */
        .search-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .search-input {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
        }

        .search-input:focus {
            border-color: #3498db;
        }

        .filter-select {
            padding: 12px 20px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            cursor: pointer;
            background: white;
        }

        /* TABLE */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background: #f8f9fa;
        }

        table th {
            padding: 15px 12px;
            text-align: left;
            font-size: 13px;
            color: #7f8c8d;
            font-weight: 600;
            text-transform: uppercase;
        }

        table td {
            padding: 18px 12px;
            border-bottom: 1px solid #f5f6fa;
            font-size: 14px;
            color: #2c3e50;
        }

        table tbody tr:hover {
            background: #f8f9fa;
        }

        /* BADGE */
        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge.success {
            background: #d4edda;
            color: #155724;
        }

        .badge.danger {
            background: #f8d7da;
            color: #721c24;
        }

        /* ACTION BUTTONS */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-icon {
            width: 35px;
            height: 35px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            font-size: 14px;
        }

        .btn-icon.edit {
            background: #e3f2fd;
            color: #1976d2;
        }

        .btn-icon.edit:hover {
            background: #1976d2;
            color: white;
        }

        .btn-icon.delete {
            background: #ffebee;
            color: #c62828;
        }

        .btn-icon.delete:hover {
            background: #c62828;
            color: white;
        }

        .btn-icon.view {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .btn-icon.view:hover {
            background: #7b1fa2;
            color: white;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }

            .topbar {
                flex-direction: column;
                gap: 15px;
            }

            .search-bar {
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
            <h1><i class="fas fa-box"></i> Gestión de Paquetes</h1>
            <div class="topbar-actions">
                <a href="paquetes_crear.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Paquete
                </a>
            </div>
        </div>

        <!-- CARD -->
        <div class="card">
            <div class="card-header">
                <h3>Lista de Paquetes Turísticos</h3>
                <span style="color: #7f8c8d; font-size: 14px;"><?= count($paquetes) ?> paquetes registrados</span>
            </div>

            <!-- SEARCH BAR -->
            <div class="search-bar">
                <input type="text" class="search-input" placeholder="Buscar por código, título o destino...">
                <select class="filter-select">
                    <option value="">Todos los estados</option>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
                <button class="btn btn-secondary">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>

            <!-- TABLE -->
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Título</th>
                            <th>Destino</th>
                            <th>Precio Persona</th>
                            <th>Precio Grupo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paquetes as $paquete): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($paquete['codigo']) ?></strong></td>
                            <td><?= htmlspecialchars($paquete['titulo']) ?></td>
                            <td><?= htmlspecialchars($paquete['destino']) ?></td>
                            <td><strong>S/. <?= number_format($paquete['precio_persona'], 2) ?></strong></td>
                            <td><strong>S/. <?= number_format($paquete['precio_grupo'], 2) ?></strong></td>
                            <td>
                                <span class="badge <?= $paquete['estado'] === 'Activo' ? 'success' : 'danger' ?>">
                                    <?= htmlspecialchars($paquete['estado']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon view" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon delete" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Confirmación de eliminación
        document.querySelectorAll('.btn-icon.delete').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('¿Estás seguro de que deseas eliminar este paquete?')) {
                    alert('Funcionalidad de eliminación pendiente de implementar');
                }
            });
        });
    </script>
</body>
</html>
