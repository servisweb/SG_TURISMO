<?php
session_start();
require_once __DIR__ . '/../../config/sesion.php';
verificar_sesion();
require_once __DIR__ . '/../../config/conexion.php';

// Proteger ruta: solo Admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Admin') {
    header('Location: ../../views/login.php');
    exit;
}

$adminName = htmlspecialchars($_SESSION['user_name'] ?? 'Administrador');

$summary = [
    'usuarios' => 0,
    'destinos' => 0,
    'reservas' => 0,
    'ingresos' => 0.00,
    'paquetes' => 0,
];

$result = $conexion->query('SELECT COUNT(*) AS total FROM usuarios');
if ($result) {
    $summary['usuarios'] = (int)$result->fetch_assoc()['total'];
}
$result = $conexion->query('SELECT COUNT(*) AS total FROM destinos');
if ($result) {
    $summary['destinos'] = (int)$result->fetch_assoc()['total'];
}
$result = $conexion->query('SELECT COUNT(*) AS total, SUM(precio_total) AS ingresos FROM reservas');
if ($result) {
    $row = $result->fetch_assoc();
    $summary['reservas'] = (int)$row['total'];
    $summary['ingresos'] = (float)($row['ingresos'] ?? 0.00);
}
$result = $conexion->query('SELECT COUNT(*) AS total FROM paquetes');
if ($result) {
    $summary['paquetes'] = (int)$result->fetch_assoc()['total'];
}

$recentReservas = [];
$result = $conexion->query(
    'SELECT r.codigo_reserva, r.precio_total, r.estado_reserva, r.fecha_creacion, '
   . 'u.nombres, u.apellidos '
   . 'FROM reservas r '
   . 'LEFT JOIN usuarios u ON u.id_usuario = r.id_usuario_titular '
   . 'ORDER BY r.fecha_creacion DESC '
   . 'LIMIT 6'
);
if ($result) {
    $recentReservas = $result->fetch_all(MYSQLI_ASSOC);
}

// Reservas pendientes de migrar
$appReservas = [];
try {
    $result = $conexion->query(
        'SELECT ar.id, ar.codigo_reserva, ar.tour_id, ar.cantidad, ar.total, ar.estado, ar.fecha_creacion, '
       . 'u.nombres, u.apellidos '
       . 'FROM app_reservas ar '
       . 'LEFT JOIN usuarios u ON u.id_usuario = ar.id_usuario '
       . "WHERE ar.estado != 'Migrada' "
       . 'ORDER BY ar.fecha_creacion DESC'
    );
    if ($result) {
        $appReservas = $result->fetch_all(MYSQLI_ASSOC);
    }
} catch (mysqli_sql_exception $e) {
    // Si la tabla no existe (código 1146) o hay otro problema, evitar fatal y dejar lista vacía
    if ($e->getCode() !== 1146) {
        error_log('panel_admin.php app_reservas query error: ' . $e->getMessage());
    }
    $appReservas = [];
}

// Salidas operativas disponibles
$salidas = [];
$result = $conexion->query(
    'SELECT so.id_salida, so.fecha_hora_salida, p.titulo '
   . 'FROM salidas_operativas so '
   . 'JOIN paquetes p ON p.id_paquete = so.id_paquete '
   . "WHERE so.estado IN ('Programada','Confirmada') "
   . 'ORDER BY so.fecha_hora_salida ASC'
);
if ($result) {
    $salidas = $result->fetch_all(MYSQLI_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo | Tumbes Tours</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/estyle.css">
    <style>
        body { background:#f6f7f9; color:#222; font-family:Inter,system-ui,Arial,sans-serif; }
        .admin-shell { max-width: 1180px; margin: 30px auto; padding: 0 20px 40px; }
        .admin-head { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:18px; margin-bottom:26px; }
        .admin-head h1 { margin:0; font-size:2.1rem; letter-spacing:-0.03em; }
        .admin-head p { margin:4px 0 0; color:#555; }
        .admin-actions { display:flex; gap:12px; flex-wrap:wrap; }
        .admin-actions a { display:inline-flex; align-items:center; gap:8px; padding:12px 18px; border-radius:10px; text-decoration:none; background:#31735a; color:#fff; font-weight:600; transition:transform .15s ease,background .15s ease; }
        .admin-actions a:hover { transform:translateY(-1px); background:#265f4d; }
        .grid-summary { display:grid; grid-template-columns:repeat(4,minmax(180px,1fr)); gap:18px; margin-bottom:26px; }
        .summary-card { background:#fff; border:1px solid #e4e7ed; border-radius:18px; padding:24px; box-shadow:0 12px 40px rgba(15,23,42,.04); }
        .summary-card h2 { margin:0 0 10px; font-size:1.2rem; color:#111; }
        .summary-card p { margin:0; font-size:2rem; font-weight:700; color:#0f172a; }
        .summary-card small { color:#64748b; display:block; margin-top:10px; }
        .section { background:#fff; border:1px solid #e4e7ed; border-radius:20px; padding:24px; margin-bottom:22px; }
        .section h2 { margin-top:0; font-size:1.3rem; }
        .section-grid { display:grid; grid-template-columns:2fr 1fr; gap:24px; }
        .widget { display:grid; gap:14px; }
        .widget-card { display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:18px; }
        .widget-card strong { font-size:1rem; color:#111827; }
        .widget-card span { color:#475569; }
        .table-responsive { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; min-width:720px; }
        th, td { padding:14px 16px; text-align:left; border-bottom:1px solid #eff2f7; }
        th { color:#475569; font-size:0.92rem; text-transform:uppercase; letter-spacing:0.04em; }
        td { color:#334155; font-size:0.95rem; }
        .badge { display:inline-flex; align-items:center; justify-content:center; padding:6px 10px; border-radius:999px; font-size:0.78rem; font-weight:700; }
        .badge-pendiente { background:#f8fafc; color:#334155; }
        .badge-pagada { background:#dcfce7; color:#166534; }
        .badge-cancelada { background:#fee2e2; color:#991b1b; }
        .badge-migrada { background:#e0f2fe; color:#0369a1; }
        .recent-empty { padding:24px 0; color:#64748b; }
        .btn-migrar { padding:7px 14px; background:#31735a; color:#fff; border:none; border-radius:8px; font-size:0.85rem; font-weight:600; cursor:pointer; }
        .btn-migrar:hover { background:#265f4d; }
        .btn-migrar:disabled { background:#aaa; cursor:not-allowed; }
        select.salida-select { padding:6px 10px; border:1px solid #ddd; border-radius:6px; font-size:0.85rem; max-width:220px; }
        .tour-names { font-size:0.82rem; color:#64748b; }
        #migrar-msg { padding:12px 16px; border-radius:10px; margin-bottom:16px; display:none; font-weight:600; }
        @media(max-width:1024px) { .grid-summary { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media(max-width:720px) { .admin-head, .section-grid { flex-direction:column; } .section-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<div class="admin-shell">
    <div class="admin-head">
        <div>
            <h1>Panel Administrativo</h1>
            <p>Bienvenido, <?= $adminName ?>. Gestiona destinos, reservas y usuarios desde este panel.</p>
        </div>
        <div class="admin-actions">
            <a href="../user/perfil_user.php"><i class="fa-solid fa-user"></i> Mi perfil</a>
            <a href="admin_destinos.php"><i class="fa-solid fa-map-location-dot"></i> Gestionar destinos</a>
            <a href="../../views/login.php"><i class="fa-solid fa-arrow-right-to-bracket"></i> Cambiar cuenta</a>
            <a href="../../controladores/cerrar_sesion.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a>
        </div>
    </div>

    <div class="grid-summary">
        <div class="summary-card">
            <h2>Usuarios registrados</h2>
            <p><?= number_format($summary['usuarios']) ?></p>
            <small>Total de cuentas</small>
        </div>
        <div class="summary-card">
            <h2>Destinos</h2>
            <p><?= number_format($summary['destinos']) ?></p>
            <small>Ofertas activas</small>
        </div>
        <div class="summary-card">
            <h2>Reservas</h2>
            <p><?= number_format($summary['reservas']) ?></p>
            <small>Reservas totales</small>
        </div>
        <div class="summary-card">
            <h2>Ingresos</h2>
            <p>S/. <?= number_format($summary['ingresos'], 2, '.', ',') ?></p>
            <small>Precio total estimado</small>
        </div>
    </div>

    <div class="section-grid">
        <section class="section">
            <h2>Atajos administrativos</h2>
            <div class="widget">
                <div class="widget-card">
                    <div>
                        <strong>Gestión de destinos</strong>
                        <span>Crear, editar y eliminar destinos</span>
                    </div>
                    <a href="admin_destinos.php" class="btn btn--outline" style="font-size:0.88rem;">Abrir</a>
                </div>
                <div class="widget-card">
                    <div>
                        <strong>Ver reservas</strong>
                        <span>Revisa las últimas reservas confirmadas</span>
                    </div>
                    <a href="admin_reservas.php" class="btn btn--outline" style="font-size:0.88rem;">Abrir</a>
                </div>
                <div class="widget-card">
                    <div>
                        <strong>Usuarios registrados</strong>
                        <span>Monitorea el crecimiento de clientes</span>
                    </div>
                    <a href="admin_usuarios.php" class="btn btn--outline" style="font-size:0.88rem;">Abrir</a>
                </div>
                <div class="widget-card">
                    <div>
                        <strong>Paquetes disponibles</strong>
                        <span>Ofertas activas en la plataforma</span>
                    </div>
                    <a href="admin_paquetes.php" class="btn btn--outline" style="font-size:0.88rem;">Abrir</a>
                </div>
            </div>
        </section>

        <section class="section">
            <h2>Reservas recientes</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Cliente</th>
                            <th>Importe</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentReservas)): ?>
                        <tr>
                            <td colspan="5" class="recent-empty">No se encontraron reservas recientes.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($recentReservas as $reserva): ?>
                            <tr>
                                <td><?= htmlspecialchars($reserva['codigo_reserva']) ?></td>
                                <td><?= htmlspecialchars(trim($reserva['nombres'] . ' ' . $reserva['apellidos'])) ?></td>
                                <td>S/. <?= number_format($reserva['precio_total'], 2, '.', ',') ?></td>
                                <td>
                                    <?php
                                        $estado = $reserva['estado_reserva'];
                                        $class = 'badge-pendiente';
                                        if ($estado === 'Pagada') { $class = 'badge-pagada'; }
                                        if ($estado === 'Cancelada') { $class = 'badge-cancelada'; }
                                    ?>
                                    <span class="badge <?= $class ?>"><?= htmlspecialchars($estado) ?></span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($reserva['fecha_creacion'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<!-- SECCIÓN RESERVAS PENDIENTES DE MIGRAR -->
<div class="admin-shell" style="margin-top:0;padding-top:0;">
    <section class="section">
        <h2><i class="fa-solid fa-arrow-right-arrow-left"></i> Reservas web pendientes de migrar</h2>
        <p style="color:#64748b;margin-top:-10px;margin-bottom:16px;">Estas reservas fueron hechas desde el sitio web. Asígnales una salida operativa y confírmalas en el sistema oficial.</p>

        <div id="migrar-msg"></div>

        <?php if (empty($appReservas)): ?>
            <p style="color:#64748b;">No hay reservas web pendientes de migrar.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cliente</th>
                        <th>Tour</th>
                        <th>Personas</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Asignar salida</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $tourNames = [1=>'Malecón - Puerto Pizarro', 2=>'Zorritos', 3=>'Huaca del Sol', 4=>'Punta Sal'];
                foreach ($appReservas as $ar):
                    $tnombre = $tourNames[$ar['tour_id']] ?? 'Tour '.$ar['tour_id'];
                ?>
                <tr id="row-<?= $ar['id'] ?>">
                    <td><?= htmlspecialchars($ar['codigo_reserva']) ?></td>
                    <td><?= htmlspecialchars(trim($ar['nombres'].' '.$ar['apellidos'])) ?></td>
                    <td class="tour-names"><?= htmlspecialchars($tnombre) ?></td>
                    <td><?= $ar['cantidad'] ?></td>
                    <td>S/. <?= number_format($ar['total'], 2) ?></td>
                    <td><span class="badge badge-<?= strtolower($ar['estado']) ?>"><?= $ar['estado'] ?></span></td>
                    <td>
                        <select class="salida-select" id="salida-<?= $ar['id'] ?>">
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($salidas as $s): ?>
                            <option value="<?= $s['id_salida'] ?>">
                                <?= htmlspecialchars($s['titulo']) ?> — <?= date('d/m/Y', strtotime($s['fecha_hora_salida'])) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <button class="btn-migrar" onclick="migrar(<?= $ar['id'] ?>)">
                            <i class="fa-solid fa-check"></i> Migrar
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>
</div>

<script>
function migrar(id) {
    const salidaSelect = document.getElementById('salida-' + id);
    const idSalida = salidaSelect.value;
    if (!idSalida) { alert('Selecciona una salida operativa primero'); return; }
    const btn = document.querySelector('#row-' + id + ' .btn-migrar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Migrando...';
    fetch('../../controladores/migrar_reserva.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_app_reserva=' + id + '&id_salida=' + idSalida
    })
    .then(r => r.json())
    .then(data => {
        const msg = document.getElementById('migrar-msg');
        msg.style.display = 'block';
        if (data.ok) {
            msg.style.background = '#dcfce7'; msg.style.color = '#166534';
            msg.textContent = '✓ ' + data.msg + ' (ID oficial: ' + data.id_reserva + ')';
            const row = document.getElementById('row-' + id);
            row.style.transition = 'opacity 0.4s';
            row.style.opacity = '0';
            setTimeout(() => {
                row.remove();
                const tbody = document.querySelector('.table-responsive tbody');
                if (tbody && tbody.querySelectorAll('tr').length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" style="color:#64748b;padding:20px;">No hay reservas pendientes de migrar.</td></tr>';
                }
            }, 400);
        } else {
            msg.style.background = '#fee2e2'; msg.style.color = '#991b1b';
            msg.textContent = '✗ ' + data.msg;
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Migrar';
        }
        setTimeout(() => msg.style.display = 'none', 5000);
    });
}
</script>
</body>
</html>
