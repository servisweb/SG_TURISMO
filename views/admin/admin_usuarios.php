<?php
session_start();
require_once __DIR__ . '/../../config/conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Admin') {
    header('Location: ../../views/login.php'); exit;
}

$mensaje = $tipoMensaje = '';

// Cambiar estado o rol
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id_usuario'] ?? 0);
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'estado' && $id > 0) {
        $estado = $_POST['estado'];
        $stmt = $conexion->prepare('UPDATE usuarios SET estado = ? WHERE id_usuario = ?');
        $stmt->bind_param('si', $estado, $id);
        $stmt->execute(); $stmt->close();
        $mensaje = 'Estado actualizado.'; $tipoMensaje = 'ok';

    } elseif ($accion === 'rol' && $id > 0) {
        $rol = $_POST['rol'];
        $stmt = $conexion->prepare('UPDATE usuarios SET rol = ? WHERE id_usuario = ?');
        $stmt->bind_param('si', $rol, $id);
        $stmt->execute(); $stmt->close();
        $mensaje = 'Rol actualizado.'; $tipoMensaje = 'ok';

    } elseif ($accion === 'eliminar' && $id > 0) {
        $stmt = $conexion->prepare('DELETE FROM usuarios WHERE id_usuario = ? AND rol != ?');
        $admin = 'Admin';
        $stmt->bind_param('is', $id, $admin);
        $stmt->execute(); $stmt->close();
        $mensaje = 'Usuario eliminado.'; $tipoMensaje = 'ok';
    }
}

$buscar = trim($_GET['q'] ?? '');
$pagina = max(1, (int)($_GET['pag'] ?? 1));
$porPagina = 10;
$offset = ($pagina - 1) * $porPagina;

$sqlBase = 'FROM usuarios';
if ($buscar) {
    $like = '%' . $conexion->real_escape_string($buscar) . '%';
    $sqlBase .= " WHERE nombres LIKE '$like' OR apellidos LIKE '$like' OR email LIKE '$like'";
}
$total = (int)$conexion->query("SELECT COUNT(*) $sqlBase")->fetch_row()[0];
$totalPaginas = max(1, ceil($total / $porPagina));
$sql = "SELECT id_usuario, nombres, apellidos, email, telefono, rol, estado, created_at $sqlBase ORDER BY created_at DESC LIMIT $porPagina OFFSET $offset";
$usuarios = $conexion->query($sql)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/estyle.css">
    <style>
        body { background:#f6f7f9; font-family:Inter,system-ui,Arial,sans-serif; }
        .shell { max-width:1100px; margin:30px auto; padding:0 20px 40px; }
        .top-bar { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; margin-bottom:24px; }
        .top-bar h2 { margin:0; font-size:1.6rem; }
        .top-bar a { display:inline-flex; align-items:center; gap:8px; padding:10px 16px; background:#31735a; color:#fff; border-radius:8px; text-decoration:none; font-weight:600; }
        .search-box { display:flex; gap:8px; margin-bottom:20px; }
        .search-box input { flex:1; padding:10px 14px; border:1px solid #ddd; border-radius:8px; font-size:14px; outline:none; }
        .search-box button { padding:10px 18px; background:#31735a; color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:600; }
        .card { background:#fff; border:1px solid #e4e7ed; border-radius:14px; overflow:hidden; }
        table { width:100%; border-collapse:collapse; min-width:700px; }
        th { background:#f8fafc; padding:12px 16px; text-align:left; font-size:13px; color:#555; font-weight:600; }
        td { padding:12px 16px; border-top:1px solid #f0f2f5; font-size:14px; vertical-align:middle; }
        tr:hover td { background:#fafbfc; }
        .badge { padding:4px 10px; border-radius:20px; font-size:12px; font-weight:700; }
        .badge-activo   { background:#dcfce7; color:#166534; }
        .badge-inactivo { background:#fee2e2; color:#991b1b; }
        .badge-admin    { background:#e0f2fe; color:#0369a1; }
        .badge-cliente  { background:#f3f4f6; color:#374151; }
        select.inline { padding:5px 8px; border:1px solid #ddd; border-radius:6px; font-size:13px; }
        .btn-sm { padding:6px 12px; border:none; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer; }
        .btn-save { background:#31735a; color:#fff; }
        .btn-del  { background:#fee2e2; color:#991b1b; }
        .btn-save:hover { background:#265f4d; }
        .btn-del:hover  { background:#fecaca; }
        .msg { padding:12px 16px; border-radius:8px; margin-bottom:18px; font-size:14px; font-weight:600; }
        .msg-ok    { background:#dcfce7; color:#166534; }
        .msg-error { background:#fee2e2; color:#991b1b; }
        .overflow { overflow-x:auto; }
        .paginacion { display:flex; gap:6px; justify-content:center; padding:18px; flex-wrap:wrap; }
        .paginacion a, .paginacion span { padding:7px 13px; border-radius:7px; font-size:13px; font-weight:600; text-decoration:none; border:1px solid #e2e8f0; color:#334155; }
        .paginacion a:hover { background:#f1f5f9; }
        .paginacion .activa { background:#31735a; color:#fff; border-color:#31735a; }
    </style>
</head>
<body>
<div class="shell">
    <div class="top-bar">
        <h2><i class="fa-solid fa-users"></i> Gestión de Usuarios</h2>
        <a href="panel_admin.php"><i class="fa-solid fa-arrow-left"></i> Volver al panel</a>
    </div>

    <?php if ($mensaje): ?>
        <div class="msg msg-<?= $tipoMensaje === 'ok' ? 'ok' : 'error' ?>"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <form class="search-box" method="GET">
        <input type="text" name="q" value="<?= htmlspecialchars($buscar) ?>" placeholder="Buscar por nombre o email...">
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
    </form>

    <div class="card">
        <div class="overflow">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($usuarios)): ?>
                <tr><td colspan="8" style="text-align:center;color:#999;padding:30px;">No se encontraron usuarios.</td></tr>
            <?php endif; ?>
            <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= $u['id_usuario'] ?></td>
                <td><?= htmlspecialchars(trim($u['nombres'] . ' ' . $u['apellidos'])) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['telefono'] ?? '—') ?></td>
                <td>
                    <form method="POST" style="display:inline-flex;gap:6px;align-items:center;">
                        <input type="hidden" name="accion" value="rol">
                        <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                        <select name="rol" class="inline">
                            <option value="Cliente" <?= $u['rol'] === 'Cliente' ? 'selected' : '' ?>>Cliente</option>
                            <option value="Admin"   <?= $u['rol'] === 'Admin'   ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <button type="submit" class="btn-sm btn-save" title="Guardar rol"><i class="fa-solid fa-check"></i></button>
                    </form>
                </td>
                <td>
                    <form method="POST" style="display:inline-flex;gap:6px;align-items:center;">
                        <input type="hidden" name="accion" value="estado">
                        <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                        <select name="estado" class="inline">
                            <option value="Activo"   <?= $u['estado'] === 'Activo'   ? 'selected' : '' ?>>Activo</option>
                            <option value="Inactivo" <?= $u['estado'] === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                        <button type="submit" class="btn-sm btn-save" title="Guardar estado"><i class="fa-solid fa-check"></i></button>
                    </form>
                </td>
                <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                <td>
                    <?php if ($u['rol'] !== 'Admin'): ?>
                    <form method="POST" onsubmit="return confirm('¿Eliminar este usuario?')">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                        <button type="submit" class="btn-sm btn-del"><i class="fa-solid fa-trash"></i></button>
                    </form>
                    <?php else: ?>
                        <span style="color:#aaa;font-size:12px;">—</span>
                    <?php endif; ?>
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
            <?php $params = http_build_query(['q' => $buscar, 'pag' => $i]); ?>
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
</html>
