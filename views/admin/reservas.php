<?php
session_start();

// Verificar si el usuario es admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'Admin') {
    header('Location: ../login.php');
    exit;
}

// Datos simulados de reservas
$reservas = [
    ['id' => 'RES-001', 'codigo' => 'RES-2024-001', 'cliente' => 'Juan Pérez García', 'email' => 'juan@email.com', 'tour' => 'Puerto Pizarro', 'fecha_salida' => '2024-01-20', 'personas' => 4, 'total' => 260.00, 'estado' => 'Confirmada', 'fecha_reserva' => '2024-01-10'],
    ['id' => 'RES-002', 'codigo' => 'RES-2024-002', 'cliente' => 'María García López', 'email' => 'maria@email.com', 'tour' => 'Punta Sal', 'fecha_salida' => '2024-01-22', 'personas' => 2, 'total' => 300.00, 'estado' => 'Pendiente', 'fecha_reserva' => '2024-01-11'],
    ['id' => 'RES-003', 'codigo' => 'RES-2024-003', 'cliente' => 'Carlos López Martínez', 'email' => 'carlos@email.com', 'tour' => 'Zorritos', 'fecha_salida' => '2024-01-25', 'personas' => 6, 'total' => 600.00, 'estado' => 'Pagada', 'fecha_reserva' => '2024-01-12'],
    ['id' => 'RES-004', 'codigo' => 'RES-2024-004', 'cliente' => 'Ana Martínez Ruiz', 'email' => 'ana@email.com', 'tour' => 'Huaca del Sol', 'fecha_salida' => '2024-01-28', 'personas' => 3, 'total' => 360.00, 'estado' => 'Confirmada', 'fecha_reserva' => '2024-01-13'],
    ['id' => 'RES-005', 'codigo' => 'RES-2024-005', 'cliente' => 'Pedro Sánchez Torres', 'email' => 'pedro@email.com', 'tour' => 'Puerto Pizarro', 'fecha_salida' => '2024-02-01', 'personas' => 5, 'total' => 325.00, 'estado' => 'Pendiente', 'fecha_reserva' => '2024-01-14'],
    ['id' => 'RES-006', 'codigo' => 'RES-2024-006', 'cliente' => 'Laura Fernández Díaz', 'email' => 'laura@email.com', 'tour' => 'Manglares', 'fecha_salida' => '2024-02-05', 'personas' => 2, 'total' => 180.00, 'estado' => 'Cancelada', 'fecha_reserva' => '2024-01-15'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reservas - Panel Administrativo</title>
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

        /* STATS MINI */
        .stats-mini {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-mini-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-mini-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .stat-mini-icon.blue { background: #e3f2fd; color: #1976d2; }
        .stat-mini-icon.orange { background: #fff3e0; color: #f57c00; }
        .stat-mini-icon.green { background: #e8f5e9; color: #388e3c; }
        .stat-mini-icon.red { background: #ffebee; color: #d32f2f; }

        .stat-mini-info h4 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 3px;
        }

        .stat-mini-info p {
            font-size: 13px;
            color: #7f8c8d;
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

        /* FILTERS */
        .filters {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 15px;
            margin-bottom: 25px;
        }

        .filter-input {
            padding: 12px 20px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
        }

        .filter-input:focus {
            border-color: #3498db;
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

        .badge.success { background: #d4edda; color: #155724; }
        .badge.warning { background: #fff3cd; color: #856404; }
        .badge.info { background: #d1ecf1; color: #0c5460; }
        .badge.danger { background: #f8d7da; color: #721c24; }

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

        .btn-icon.view { background: #e3f2fd; color: #1976d2; }
        .btn-icon.view:hover { background: #1976d2; color: white; }

        .btn-icon.edit { background: #f3e5f5; color: #7b1fa2; }
        .btn-icon.edit:hover { background: #7b1fa2; color: white; }

        .btn-icon.delete { background: #ffebee; color: #c62828; }
        .btn-icon.delete:hover { background: #c62828; color: white; }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }

            .filters {
                grid-template-columns: 1fr;
            }

            .stats-mini {
                grid-template-columns: 1fr;
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
            <li><a href="paquetes.php"><i class="fas fa-box"></i> Paquetes</a></li>
            <li><a href="destinos.php"><i class="fas fa-map-marked-alt"></i> Destinos</a></li>
            <li><a href="reservas.php" class="active"><i class="fas fa-calendar-check"></i> Reservas</a></li>
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
            <h1><i class="fas fa-calendar-check"></i> Gestión de Reservas</h1>
            <button class="btn btn-primary">
                <i class="fas fa-file-export"></i> Exportar Excel
            </button>
        </div>

        <!-- STATS MINI -->
        <div class="stats-mini">
            <div class="stat-mini-card">
                <div class="stat-mini-icon blue">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-mini-info">
                    <h4><?= count($reservas) ?></h4>
                    <p>Total Reservas</p>
                </div>
            </div>

            <div class="stat-mini-card">
                <div class="stat-mini-icon orange">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-mini-info">
                    <h4><?= count(array_filter($reservas, fn($r) => $r['estado'] === 'Pendiente')) ?></h4>
                    <p>Pendientes</p>
                </div>
            </div>

            <div class="stat-mini-card">
                <div class="stat-mini-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-mini-info">
                    <h4><?= count(array_filter($reservas, fn($r) => $r['estado'] === 'Confirmada' || $r['estado'] === 'Pagada')) ?></h4>
                    <p>Confirmadas</p>
                </div>
            </div>

            <div class="stat-mini-card">
                <div class="stat-mini-icon red">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-mini-info">
                    <h4><?= count(array_filter($reservas, fn($r) => $r['estado'] === 'Cancelada')) ?></h4>
                    <p>Canceladas</p>
                </div>
            </div>
        </div>

        <!-- CARD -->
        <div class="card">
            <div class="card-header">
                <h3>Lista de Reservas</h3>
            </div>

            <!-- FILTERS -->
            <div class="filters">
                <input type="text" class="filter-input" placeholder="Buscar por código, cliente o email...">
                <select class="filter-input">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="confirmada">Confirmada</option>
                    <option value="pagada">Pagada</option>
                    <option value="cancelada">Cancelada</option>
                </select>
                <input type="date" class="filter-input">
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
                            <th>Cliente</th>
                            <th>Tour</th>
                            <th>Fecha Salida</th>
                            <th>Personas</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservas as $reserva): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($reserva['codigo']) ?></strong></td>
                            <td>
                                <div><?= htmlspecialchars($reserva['cliente']) ?></div>
                                <small style="color: #7f8c8d;"><?= htmlspecialchars($reserva['email']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($reserva['tour']) ?></td>
                            <td><?= date('d/m/Y', strtotime($reserva['fecha_salida'])) ?></td>
                            <td><strong><?= $reserva['personas'] ?></strong></td>
                            <td><strong>S/. <?= number_format($reserva['total'], 2) ?></strong></td>
                            <td>
                                <?php
                                $badgeClass = 'info';
                                if ($reserva['estado'] === 'Confirmada' || $reserva['estado'] === 'Pagada') $badgeClass = 'success';
                                if ($reserva['estado'] === 'Pendiente') $badgeClass = 'warning';
                                if ($reserva['estado'] === 'Cancelada') $badgeClass = 'danger';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($reserva['estado']) ?></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon view" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon delete" title="Cancelar">
                                        <i class="fas fa-ban"></i>
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
</body>
</html>
