<?php
session_start();

// Verificar si el usuario es admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'Admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../config/conexion.php';

// Obtener estadísticas reales de la BD
$stats = [
    'total_reservas' => 0,
    'reservas_pendientes' => 0,
    'ingresos_mes' => 0,
    'tours_activos' => 0
];

// Total de reservas
$query_total = "SELECT COUNT(*) as total FROM reservas";
$result = $conexion->query($query_total);
if ($result) {
    $stats['total_reservas'] = $result->fetch_assoc()['total'];
}

// Reservas pendientes
$query_pendientes = "SELECT COUNT(*) as total FROM reservas WHERE estado_reserva = 'Pendiente'";
$result = $conexion->query($query_pendientes);
if ($result) {
    $stats['reservas_pendientes'] = $result->fetch_assoc()['total'];
}

// Ingresos del mes actual
$query_ingresos = "SELECT COALESCE(SUM(precio_total), 0) as total FROM reservas 
                   WHERE MONTH(fecha_creacion) = MONTH(CURRENT_DATE()) 
                   AND YEAR(fecha_creacion) = YEAR(CURRENT_DATE())
                   AND estado_reserva IN ('Pagada', 'Parcial')";
$result = $conexion->query($query_ingresos);
if ($result) {
    $stats['ingresos_mes'] = $result->fetch_assoc()['total'];
}

// Tours activos
$query_tours = "SELECT COUNT(*) as total FROM paquetes WHERE estado = 'Activo'";
$result = $conexion->query($query_tours);
if ($result) {
    $stats['tours_activos'] = $result->fetch_assoc()['total'];
}

// Obtener reservas recientes
$query_reservas = "SELECT r.codigo_reserva, r.precio_total, r.estado_reserva, r.fecha_creacion,
                          u.nombres, u.apellidos, p.titulo
                   FROM reservas r
                   INNER JOIN usuarios u ON r.id_usuario_titular = u.id_usuario
                   INNER JOIN salidas_operativas s ON r.id_salida = s.id_salida
                   INNER JOIN paquetes p ON s.id_paquete = p.id_paquete
                   ORDER BY r.fecha_creacion DESC
                   LIMIT 5";

$result_reservas = $conexion->query($query_reservas);
$reservas_recientes = [];

if ($result_reservas && $result_reservas->num_rows > 0) {
    while ($row = $result_reservas->fetch_assoc()) {
        $reservas_recientes[] = [
            'id' => $row['codigo_reserva'],
            'cliente' => $row['nombres'] . ' ' . $row['apellidos'],
            'tour' => $row['titulo'],
            'fecha' => $row['fecha_creacion'],
            'monto' => $row['precio_total'],
            'estado' => $row['estado_reserva']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Panel Administrativo</title>
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
.sidebar {
    overflow-y: auto;
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

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .topbar-user img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 2px solid #3498db;
        }

        .topbar-user-info h4 {
            font-size: 14px;
            color: #2c3e50;
        }

        .topbar-user-info p {
            font-size: 12px;
            color: #7f8c8d;
        }

        /* STATS CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .stat-card-info h3 {
            font-size: 32px;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-card-info p {
            color: #7f8c8d;
            font-size: 14px;
        }

        .stat-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .stat-card-icon.blue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-card-icon.green {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .stat-card-icon.orange {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .stat-card-icon.purple {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        /* CONTENT GRID */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        /* CARD */
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f5f6fa;
        }

        .card-header h3 {
            font-size: 20px;
            color: #2c3e50;
        }

        .card-header a {
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .card-header a:hover {
            color: #2980b9;
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
            padding: 12px;
            text-align: left;
            font-size: 13px;
            color: #7f8c8d;
            font-weight: 600;
            text-transform: uppercase;
        }

        table td {
            padding: 15px 12px;
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
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge.success {
            background: #d4edda;
            color: #155724;
        }

        .badge.warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge.info {
            background: #d1ecf1;
            color: #0c5460;
        }

        /* QUICK ACTIONS */
        .quick-actions {
            display: grid;
            gap: 15px;
        }

        .quick-action-btn {
            display: flex;
            align-items: center;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            transition: transform 0.3s, box-shadow 0.3s;
            font-weight: 500;
        }

        .quick-action-btn:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .quick-action-btn i {
            margin-right: 12px;
            font-size: 20px;
        }

        .quick-action-btn:nth-child(2) {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .quick-action-btn:nth-child(3) {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .quick-action-btn:nth-child(4) {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
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
            <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="paquetes.php"><i class="fas fa-box"></i> Paquetes</a></li>
            <li><a href="destinos.php"><i class="fas fa-map-marked-alt"></i> Destinos</a></li>
            <li><a href="reservas.php"><i class="fas fa-calendar-check"></i> Reservas</a></li>
            <li><a href="usuarios.php"><i class="fas fa-users"></i> Usuarios</a></li>
            <li><a href="guias.php"><i class="fas fa-user-tie"></i> Guías</a></li>
            <li><a href="proveedores.php"><i class="fas fa-handshake"></i> Proveedores</a></li>
            <li><a href="movilidades.php"><i class="fas fa-bus"></i> Movilidades</a></li>
            <li><a href="reportes.php"><i class="fas fa-chart-bar"></i> Reportes</a></li>
            <li><a href="../../index.php"><i class="fas fa-globe"></i> Ver Sitio Web</a></li>
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
            <h1>📊 Dashboard</h1>
            <div class="topbar-user">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name'] ?? 'Admin') ?>&background=3498db&color=fff" alt="Usuario">
                <div class="topbar-user-info">
                    <h4><?= htmlspecialchars($_SESSION['user_name'] ?? 'Administrador') ?></h4>
                    <p>🔑 Administrador</p>
                </div>
            </div>
        </div>

        <!-- STATS CARDS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-info">
                    <h3><?= $stats['total_reservas'] ?></h3>
                    <p>Total Reservas</p>
                </div>
                <div class="stat-card-icon blue">
                    <i class="fas fa-ticket-alt"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-info">
                    <h3><?= $stats['reservas_pendientes'] ?></h3>
                    <p>Reservas Pendientes</p>
                </div>
                <div class="stat-card-icon green">
                    <i class="fas fa-clock"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-info">
                    <h3>S/. <?= number_format($stats['ingresos_mes'], 2) ?></h3>
                    <p>Ingresos del Mes</p>
                </div>
                <div class="stat-card-icon orange">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-info">
                    <h3><?= $stats['tours_activos'] ?></h3>
                    <p>Tours Activos</p>
                </div>
                <div class="stat-card-icon purple">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
            </div>
        </div>

        <!-- CONTENT GRID -->
        <div class="content-grid">
            <!-- RESERVAS RECIENTES -->
            <div class="card">
                <div class="card-header">
                    <h3>Reservas Recientes</h3>
                    <a href="reservas.php">Ver todas <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Tour</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reservas_recientes)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 30px; color: #7f8c8d;">
                                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                                    No hay reservas registradas aún
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($reservas_recientes as $reserva): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($reserva['id']) ?></strong></td>
                                <td><?= htmlspecialchars($reserva['cliente']) ?></td>
                                <td><?= htmlspecialchars($reserva['tour']) ?></td>
                                <td><?= date('d/m/Y', strtotime($reserva['fecha'])) ?></td>
                                <td><strong>S/. <?= number_format($reserva['monto'], 2) ?></strong></td>
                                <td>
                                    <?php
                                    $badgeClass = 'info';
                                    if ($reserva['estado'] === 'Confirmada') $badgeClass = 'success';
                                    if ($reserva['estado'] === 'Pendiente') $badgeClass = 'warning';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($reserva['estado']) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ACCIONES RÁPIDAS -->
            <div class="card">
                <div class="card-header">
                    <h3>Acciones Rápidas</h3>
                </div>
                <div class="quick-actions">
                    <a href="paquetes.php?action=create" class="quick-action-btn">
                        <i class="fas fa-plus-circle"></i> Crear Nuevo Paquete
                    </a>
                    <a href="reservas.php?action=create" class="quick-action-btn">
                        <i class="fas fa-calendar-plus"></i> Nueva Reserva
                    </a>
                    <a href="destinos.php?action=create" class="quick-action-btn">
                        <i class="fas fa-map-pin"></i> Agregar Destino
                    </a>
                    <a href="reportes.php" class="quick-action-btn">
                        <i class="fas fa-file-download"></i> Generar Reporte
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
