<?php
session_start();
require_once __DIR__ . '/../../config/conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Admin') {
    header('Location: ../../views/login.php'); exit;
}

$mensaje = $tipoMensaje = '';

// Destinos para el select
$destinos = $conexion->query('SELECT id_destino, nombre_destino FROM destinos WHERE estado="Activo" ORDER BY nombre_destino ASC')->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion   = $_POST['accion'] ?? '';
    $id       = (int)($_POST['id_paquete'] ?? 0);
    $titulo   = trim($_POST['titulo'] ?? '');
    $id_dest  = (int)($_POST['id_destino'] ?? 0);
    $codigo   = trim($_POST['codigo_paquete'] ?? '');
    $desc     = trim($_POST['descripcion_general'] ?? '');
    $cat      = $_POST['categoria'] ?? 'Mixto';
    $duracion = (int)($_POST['duracion_horas'] ?? 8);
    $precio   = (float)($_POST['precio_base'] ?? 0);
    $precio_g = (float)($_POST['precio_grupo'] ?? 0);
    $cupo_min = (int)($_POST['cupo_minimo'] ?? 1);
    $cupo_max = (int)($_POST['cupo_maximo'] ?? 20);
    $estado   = $_POST['estado'] ?? 'Activo';

    if (empty($titulo) || $id_dest <= 0) {
        $mensaje = 'El título y el destino son obligatorios.'; $tipoMensaje = 'error';
    } else {
        if ($accion === 'crear') {
            if (empty($codigo)) $codigo = 'PKG-' . strtoupper(uniqid());
            $stmt = $conexion->prepare(
                'INSERT INTO paquetes (id_destino, codigo_paquete, titulo, descripcion_general, categoria, duracion_horas, precio_base, precio_grupo, cupo_minimo, cupo_maximo, estado)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param('issssiiddis', $id_dest, $codigo, $titulo, $desc, $cat, $duracion, $precio, $precio_g, $cupo_min, $cupo_max, $estado);
            if ($stmt->execute()) { $mensaje = "Paquete «{$titulo}» creado."; $tipoMensaje = 'ok'; }
            else { $mensaje = 'Error: ' . $conexion->error; $tipoMensaje = 'error'; }
            $stmt->close();

        } elseif ($accion === 'editar' && $id > 0) {
            $stmt = $conexion->prepare(
                'UPDATE paquetes SET id_destino=?, titulo=?, descripcion_general=?, categoria=?, duracion_horas=?, precio_base=?, precio_grupo=?, cupo_minimo=?, cupo_maximo=?, estado=?
                 WHERE id_paquete=?'
            );
            $stmt->bind_param('isssiddissi', $id_dest, $titulo, $desc, $cat, $duracion, $precio, $precio_g, $cupo_min, $cupo_max, $estado, $id);
            $stmt->execute(); $stmt->close();
            $mensaje = 'Paquete actualizado.'; $tipoMensaje = 'ok';

        } elseif ($accion === 'eliminar' && $id > 0) {
            $stmt = $conexion->prepare('DELETE FROM paquetes WHERE id_paquete = ?');
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) { $mensaje = 'Paquete eliminado.'; $tipoMensaje = 'ok'; }
            else { $mensaje = 'No se puede eliminar, tiene registros asociados.'; $tipoMensaje = 'error'; }
            $stmt->close();
        }
    }
}

$editando = null;
if (isset($_GET['editar'])) {
    $editando = $conexion->query('SELECT * FROM paquetes WHERE id_paquete=' . (int)$_GET['editar'])->fetch_assoc();
}

$pagina = max(1, (int)($_GET['pag'] ?? 1));
$porPagina = 10;
$offset = ($pagina - 1) * $porPagina;
$total = (int)$conexion->query('SELECT COUNT(*) FROM paquetes')->fetch_row()[0];
$totalPaginas = max(1, ceil($total / $porPagina));

$paquetes = $conexion->query(
    "SELECT p.id_paquete, p.titulo, p.categoria, p.precio_base, p.precio_grupo, p.cupo_maximo, p.estado, d.nombre_destino
     FROM paquetes p LEFT JOIN destinos d ON d.id_destino = p.id_destino
     ORDER BY p.id_paquete DESC LIMIT $porPagina OFFSET $offset"
)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Paquetes | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/estyle.css">
    <style>
        body { background:#f6f7f9; font-family:Inter,system-ui,Arial,sans-serif; }
        .shell { max-width:1150px; margin:30px auto; padding:0 20px 40px; }
        .top-bar { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; margin-bottom:24px; }
        .top-bar h2 { margin:0; font-size:1.6rem; }
        .top-bar a { display:inline-flex; align-items:center; gap:8px; padding:10px 16px; background:#31735a; color:#fff; border-radius:8px; text-decoration:none; font-weight:600; }
        .admin-grid { display:grid; grid-template-columns:360px 1fr; gap:28px; align-items:start; }
        .form-card { background:#fff; border:1px solid #e4e7ed; border-radius:14px; padding:26px; }
        .form-card h3 { margin:0 0 20px; font-size:17px; }
        .form-group { margin-bottom:15px; }
        .form-group label { display:block; font-weight:600; font-size:13px; color:#444; margin-bottom:5px; }
        .form-group input, .form-group select, .form-group textarea {
            width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:8px;
            font-size:14px; font-family:inherit; outline:none; box-sizing:border-box;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color:#31735a; }
        .form-group textarea { resize:vertical; min-height:70px; }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .btn-guardar { width:100%; padding:12px; background:#31735a; color:#fff; border:none; border-radius:8px; font-size:15px; font-weight:600; cursor:pointer; }
        .btn-guardar:hover { background:#265f4d; }
        .btn-cancelar { display:block; text-align:center; margin-top:10px; color:#666; font-size:13px; cursor:pointer; text-decoration:none; }
        .card { background:#fff; border:1px solid #e4e7ed; border-radius:14px; overflow:hidden; }
        .overflow { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; min-width:700px; }
        th { background:#f8fafc; padding:12px 16px; text-align:left; font-size:13px; color:#555; font-weight:600; }
        td { padding:12px 16px; border-top:1px solid #f0f2f5; font-size:14px; vertical-align:middle; }
        tr:hover td { background:#fafbfc; }
        .badge { padding:4px 10px; border-radius:20px; font-size:12px; font-weight:700; }
        .badge-activo   { background:#dcfce7; color:#166534; }
        .badge-inactivo { background:#fee2e2; color:#991b1b; }
        .btn-edit { color:#1976d2; background:none; border:none; cursor:pointer; font-size:15px; margin-right:8px; }
        .btn-del  { color:#d32f2f; background:none; border:none; cursor:pointer; font-size:15px; }
        .msg { padding:12px 16px; border-radius:8px; margin-bottom:18px; font-size:14px; font-weight:600; }
        .msg-ok    { background:#dcfce7; color:#166534; }
        .msg-error { background:#fee2e2; color:#991b1b; }
        .paginacion { display:flex; gap:6px; justify-content:center; padding:18px; flex-wrap:wrap; }
        .paginacion a, .paginacion span { padding:7px 13px; border-radius:7px; font-size:13px; font-weight:600; text-decoration:none; border:1px solid #e2e8f0; color:#334155; }
        .paginacion a:hover { background:#f1f5f9; }
        .paginacion .activa { background:#31735a; color:#fff; border-color:#31735a; }
        @media(max-width:900px) { .admin-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<div class="shell">
    <div class="top-bar">
        <h2><i class="fa-solid fa-box-open"></i> Gestión de Paquetes</h2>
        <a href="panel_admin.php"><i class="fa-solid fa-arrow-left"></i> Volver al panel</a>
    </div>

    <?php if ($mensaje): ?>
        <div class="msg msg-<?= $tipoMensaje === 'ok' ? 'ok' : 'error' ?>"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="admin-grid">
        <!-- FORMULARIO -->
        <div class="form-card">
            <h3><?= $editando ? '✏️ Editar paquete' : '➕ Nuevo paquete' ?></h3>
            <form method="POST">
                <input type="hidden" name="accion" value="<?= $editando ? 'editar' : 'crear' ?>">
                <input type="hidden" name="id_paquete" value="<?= $editando['id_paquete'] ?? '' ?>">

                <div class="form-group">
                    <label>Título *</label>
                    <input type="text" name="titulo" required value="<?= htmlspecialchars($editando['titulo'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Destino *</label>
                    <select name="id_destino" required>
                        <option value="">-- Selecciona --</option>
                        <?php foreach ($destinos as $d): ?>
                            <option value="<?= $d['id_destino'] ?>" <?= ($editando['id_destino'] ?? '') == $d['id_destino'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['nombre_destino']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Categoría</label>
                    <select name="categoria">
                        <?php foreach (['Playa','Naturaleza','Cultura','Mixto'] as $c): ?>
                            <option value="<?= $c ?>" <?= ($editando['categoria'] ?? 'Mixto') === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion_general"><?= htmlspecialchars($editando['descripcion_general'] ?? '') ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Precio base (S/.)</label>
                        <input type="number" name="precio_base" step="0.01" min="0" value="<?= $editando['precio_base'] ?? '0.00' ?>">
                    </div>
                    <div class="form-group">
                        <label>Precio grupo (S/.)</label>
                        <input type="number" name="precio_grupo" step="0.01" min="0" value="<?= $editando['precio_grupo'] ?? '0.00' ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Duración (horas)</label>
                        <input type="number" name="duracion_horas" min="1" value="<?= $editando['duracion_horas'] ?? '8' ?>">
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado">
                            <option value="Activo"   <?= ($editando['estado'] ?? 'Activo') === 'Activo'   ? 'selected' : '' ?>>Activo</option>
                            <option value="Inactivo" <?= ($editando['estado'] ?? '') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Cupo mínimo</label>
                        <input type="number" name="cupo_minimo" min="1" value="<?= $editando['cupo_minimo'] ?? '1' ?>">
                    </div>
                    <div class="form-group">
                        <label>Cupo máximo</label>
                        <input type="number" name="cupo_maximo" min="1" value="<?= $editando['cupo_maximo'] ?? '20' ?>">
                    </div>
                </div>
                <button type="submit" class="btn-guardar">
                    <i class="fa-solid fa-floppy-disk"></i> <?= $editando ? 'Guardar cambios' : 'Crear paquete' ?>
                </button>
                <?php if ($editando): ?>
                    <a href="admin_paquetes.php" class="btn-cancelar">Cancelar edición</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- TABLA -->
        <div class="card">
            <div class="overflow">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Título</th>
                        <th>Destino</th>
                        <th>Categoría</th>
                        <th>Precio base</th>
                        <th>Cupo máx.</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($paquetes)): ?>
                    <tr><td colspan="8" style="text-align:center;color:#999;padding:30px;">No hay paquetes registrados.</td></tr>
                <?php endif; ?>
                <?php foreach ($paquetes as $p): ?>
                <tr>
                    <td><?= $p['id_paquete'] ?></td>
                    <td><?= htmlspecialchars($p['titulo']) ?></td>
                    <td><?= htmlspecialchars($p['nombre_destino'] ?? '—') ?></td>
                    <td><?= $p['categoria'] ?></td>
                    <td>S/. <?= number_format($p['precio_base'], 2) ?></td>
                    <td><?= $p['cupo_maximo'] ?></td>
                    <td><span class="badge badge-<?= strtolower($p['estado']) ?>"><?= $p['estado'] ?></span></td>
                    <td>
                        <a href="?editar=<?= $p['id_paquete'] ?>" class="btn-edit" title="Editar"><i class="fa-solid fa-pen"></i></a>
                        <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar este paquete?')">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id_paquete" value="<?= $p['id_paquete'] ?>">
                            <button type="submit" class="btn-del" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <?php if ($totalPaginas > 1): ?>
    <div class="paginacion">
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <?php if ($i === $pagina): ?>
                <span class="activa"><?= $i ?></span>
            <?php else: ?>
                <a href="?pag=<?= $i ?><?= $editando ? '&editar='.$editando['id_paquete'] : '' ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

</div>
</body>
</html>
