<?php
session_start();
require_once __DIR__ . '/../../config/conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Admin') {
    header('Location: ../../views/login.php'); exit;
}

$mensaje = $tipoMensaje = '';

// Cambiar estado de reserva
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id_reserva'] ?? 0);
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'estado' && $id > 0) {
        $estado = $_POST['estado_reserva'];
        $stmt = $conexion->prepare('UPDATE reservas SET estado_reserva = ? WHERE id_reserva = ?');
        $stmt->bind_param('si', $estado, $id);
        $stmt->execute(); $stmt->close();
        $mensaje = 'Estado de reserva actualizado.'; $tipoMensaje = 'ok';

    } elseif ($accion === 'eliminar' && $id > 0) {
        $stmt = $conexion->prepare('DELETE FROM reservas WHERE id_reserva = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute(); $stmt->close();
        $mensaje = 'Reserva eliminada.'; $tipoMensaje = 'ok';
    }
}

$filtroEstado = $_GET['estado'] ?? '';
$buscar = trim($_GET['q'] ?? '');
$pagina = max(1, (int)($_GET['pag'] ?? 1));
$porPagina = 10;
$offset = ($pagina - 1) * $porPagina;

$where = 'WHERE 1=1';
if ($filtroEstado) $where .= " AND r.estado_reserva = '" . $conexion->real_escape_string($filtroEstado) . "'";
if ($buscar) {
    $like = '%' . $conexion->real_escape_string($buscar) . '%';
    $where .= " AND (u.nombres LIKE '$like' OR u.apellidos LIKE '$like' OR r.codigo_reserva LIKE '$like')";
}

$sqlBase = 'FROM reservas r
        LEFT JOIN usuarios u ON u.id_usuario = r.id_usuario_titular
        LEFT JOIN salidas_operativas so ON so.id_salida = r.id_salida
        LEFT JOIN paquetes p ON p.id_paquete = so.id_paquete ' . $where;

$total = (int)$conexion->query("SELECT COUNT(*) $sqlBase")->fetch_row()[0];
$totalPaginas = max(1, ceil($total / $porPagina));

$sql = "SELECT r.id_reserva, r.codigo_reserva, r.cantidad_pasajeros, r.precio_total,
               r.estado_reserva, r.fecha_creacion,
               u.nombres, u.apellidos, u.email,
               p.titulo as paquete
        $sqlBase ORDER BY r.fecha_creacion DESC LIMIT $porPagina OFFSET $offset";
$reservas = $conexion->query($sql)->fetch_all(MYSQLI_ASSOC);

$estados = ['Pendiente', 'Parcial', 'Pagada', 'Cancelada'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reservas | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/estyle.css">
    <style>
        body { background:#f6f7f9; font-family:Inter,system-ui,Arial,sans-serif; }
        .shell { max-width:1200px; margin:30px auto; padding:0 20px 40px; }
        .top-bar { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; margin-bottom:24px; }
        .top-bar h2 { margin:0; font-size:1.6rem; }
        .top-bar a { display:inline-flex; align-items:center; gap:8px; padding:10px 16px; background:#31735a; color:#fff; border-radius:8px; text-decoration:none; font-weight:600; }
        .filters { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:18px; }
        .filters input { padding:9px 14px; border:1px solid #ddd; border-radius:8px; font-size:14px; outline:none; min-width:220px; }
        .filters select { padding:9px 12px; border:1px solid #ddd; border-radius:8px; font-size:14px; outline:none; }
        .filters button { padding:9px 16px; background:#31735a; color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:600; }
        .card { background:#fff; border:1px solid #e4e7ed; border-radius:14px; overflow:hidden; }
        .overflow { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; min-width:900px; }
        th { background:#f8fafc; padding:12px 16px; text-align:left; font-size:13px; color:#555; font-weight:600; }
        td { padding:12px 16px; border-top:1px solid #f0f2f5; font-size:14px; vertical-align:middle; }
        tr:hover td { background:#fafbfc; }
        .badge { padding:4px 10px; border-radius:20px; font-size:12px; font-weight:700; }
        .badge-pendiente { background:#fef9c3; color:#854d0e; }
        .badge-parcial   { background:#e0f2fe; color:#0369a1; }
        .badge-pagada    { background:#dcfce7; color:#166534; }
        .badge-cancelada { background:#fee2e2; color:#991b1b; }
        select.inline { padding:5px 8px; border:1px solid #ddd; border-radius:6px; font-size:13px; }
        .btn-sm { padding:6px 12px; border:none; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer; }
        .btn-save { background:#31735a; color:#fff; }
        .btn-del  { background:#fee2e2; color:#991b1b; }
        .btn-save:hover { background:#265f4d; }
        .btn-del:hover  { background:#fecaca; }
        .msg { padding:12px 16px; border-radius:8px; margin-bottom:18px; font-size:14px; font-weight:600; }
        .msg-ok    { background:#dcfce7; color:#166534; }
        .msg-error { background:#fee2e2; color:#991b1b; }
        .paginacion { display:flex; gap:6px; justify-content:center; padding:18px; flex-wrap:wrap; }
        .paginacion a, .paginacion span { padding:7px 13px; border-radius:7px; font-size:13px; font-weight:600; text-decoration:none; border:1px solid #e2e8f0; color:#334155; }
        .paginacion a:hover { background:#f1f5f9; }
        .paginacion .activa { background:#31735a; color:#fff; border-color:#31735a; }
        #toast-estado { position:fixed; bottom:24px; right:24px; padding:14px 20px; border-radius:10px; font-weight:600; font-size:14px; display:none; opacity:1; transition:opacity 0.4s; z-index:9999; box-shadow:0 4px 20px rgba(0,0,0,0.12); }
    </style>
</head>
<body>
<div id="toast-estado"></div>
<div class="shell">
    <div class="top-bar">
        <h2><i class="fa-solid fa-calendar-check"></i> Gestión de Reservas</h2>
        <a href="panel_admin.php"><i class="fa-solid fa-arrow-left"></i> Volver al panel</a>
    </div>

    <?php if ($mensaje): ?>
        <div class="msg msg-<?= $tipoMensaje === 'ok' ? 'ok' : 'error' ?>"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <form class="filters" method="GET">
        <input type="text" name="q" value="<?= htmlspecialchars($buscar) ?>" placeholder="Buscar por cliente o código...">
        <select name="estado">
            <option value="">Todos los estados</option>
            <?php foreach ($estados as $e): ?>
                <option value="<?= $e ?>" <?= $filtroEstado === $e ? 'selected' : '' ?>><?= $e ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Filtrar</button>
    </form>

    <div class="card">
        <div class="overflow">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Paquete</th>
                    <th>Personas</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($reservas)): ?>
                <tr><td colspan="8" style="text-align:center;color:#999;padding:30px;">No se encontraron reservas.</td></tr>
            <?php endif; ?>
            <?php foreach ($reservas as $r): ?>
            <tr>
                <td><small><?= htmlspecialchars($r['codigo_reserva']) ?></small></td>
                <td>
                    <?= htmlspecialchars(trim($r['nombres'] . ' ' . $r['apellidos'])) ?>
                    <br><small style="color:#888;"><?= htmlspecialchars($r['email']) ?></small>
                </td>
                <td><?= htmlspecialchars($r['paquete'] ?? '—') ?></td>
                <td><?= $r['cantidad_pasajeros'] ?></td>
                <td>S/. <?= number_format($r['precio_total'], 2) ?></td>
                <td>
                    <form onsubmit="cambiarEstado(event, <?= $r['id_reserva'] ?>)" style="display:inline-flex;gap:6px;align-items:center;">
                        <input type="hidden" name="accion" value="estado">
                        <input type="hidden" name="id_reserva" value="<?= $r['id_reserva'] ?>">
                        <select name="estado_reserva" class="inline" id="sel-<?= $r['id_reserva'] ?>">
                            <?php foreach ($estados as $e): ?>
                                <option value="<?= $e ?>" <?= $r['estado_reserva'] === $e ? 'selected' : '' ?>><?= $e ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn-sm btn-save"><i class="fa-solid fa-check"></i></button>
                    </form>
                </td>
                <td><?= date('d/m/Y', strtotime($r['fecha_creacion'])) ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('¿Eliminar esta reserva?')">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id_reserva" value="<?= $r['id_reserva'] ?>">
                        <button type="submit" class="btn-sm btn-del"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <?php if ($totalPaginas > 1): ?>
    <div class="paginacion">
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <?php $params = http_build_query(['q' => $buscar, 'estado' => $filtroEstado, 'pag' => $i]); ?>
            <?php if ($i === $pagina): ?>
                <span class="activa"><?= $i ?></span>
            <?php else: ?>
                <a href="?<?= $params ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

</div>
</body>
<script>
const badgeClasses = {
    'Pendiente': 'badge-pendiente',
    'Parcial':   'badge-parcial',
    'Pagada':    'badge-pagada',
    'Cancelada': 'badge-cancelada'
};

const toastEl = document.getElementById('toast-estado');

function mostrarToast(msg, ok) {
    toastEl.textContent = msg;
    toastEl.style.background = ok ? '#dcfce7' : '#fee2e2';
    toastEl.style.color      = ok ? '#166534' : '#991b1b';
    toastEl.style.display    = 'block';
    toastEl.style.opacity    = '1';
    clearTimeout(toastEl._t);
    toastEl._t = setTimeout(() => {
        toastEl.style.opacity = '0';
        setTimeout(() => toastEl.style.display = 'none', 400);
    }, 3000);
}

function cambiarEstado(e, idReserva) {
    e.preventDefault();
    const form   = e.target;
    const select = form.querySelector('select[name="estado_reserva"]');
    const btn    = form.querySelector('button');
    const estado = select.value;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    fetch('admin_reservas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'accion=estado&id_reserva=' + idReserva + '&estado_reserva=' + encodeURIComponent(estado)
    })
    .then(r => r.text())
    .then(() => {
        // Actualizar badge en la fila
        const row   = form.closest('tr');
        const badge = row.querySelector('.badge-estado');
        if (badge) {
            badge.className = 'badge badge-estado ' + (badgeClasses[estado] || '');
            badge.textContent = estado;
        }
        mostrarToast('Estado actualizado a "' + estado + '"', true);
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check"></i>';
    })
    .catch(() => {
        mostrarToast('Error al actualizar. Intenta de nuevo.', false);
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check"></i>';
    });
}
</script>
</html>
