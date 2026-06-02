<?php
// views/admin/admin_destinos.php
// Panel admin: CRUD completo de destinos con gestión de imágenes

session_start();
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../helpers/ImagenHelper.php';

// ── Proteger ruta: solo Admin ────────────────────────────────
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Admin') {
    header('Location: ../../views/login.php');
    exit;
}

$mensaje = '';
$tipoMensaje = '';

// ════════════════════════════════════════════════════════════
// ACCIÓN: GUARDAR (crear o editar)
// ════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    $accion       = $_POST['accion'];
    $id           = isset($_POST['id_destino']) ? (int)$_POST['id_destino'] : 0;
    $nombre       = trim($_POST['nombre_destino'] ?? '');
    $tipo         = $_POST['tipo_destino'] ?? 'Mixto';
    $provincia    = trim($_POST['provincia'] ?? '');
    $distrito     = trim($_POST['distrito'] ?? '');
    $descripcion  = trim($_POST['descripcion'] ?? '');
    $precio       = (float)($_POST['precio_referencial'] ?? 0);
    $estado       = $_POST['estado'] ?? 'Activo';
    $fotoActual   = trim($_POST['foto_actual'] ?? '');

    // Validación mínima
    if (empty($nombre)) {
        $mensaje = 'El nombre del destino es obligatorio.';
        $tipoMensaje = 'error';
    } else {
        // Procesar imagen nueva si se subió
        $nuevaFoto = $fotoActual; // por defecto mantener la actual

        if (!empty($_FILES['foto']['name'])) {
            $resultado = ImagenHelper::subir($_FILES['foto'], UPLOAD_DESTINOS, 'destino');

            if ($resultado['ok']) {
                // Eliminar foto anterior del servidor
                if (!empty($fotoActual)) {
                    ImagenHelper::eliminar($fotoActual);
                }
                $nuevaFoto = $resultado['ruta'];
            } else {
                $mensaje = $resultado['error'];
                $tipoMensaje = 'error';
            }
        }

        if (empty($mensaje)) {
            if ($accion === 'crear') {
                $stmt = $conexion->prepare("
                    INSERT INTO destinos
                        (nombre_destino, tipo_destino, provincia, distrito, descripcion, foto_url, precio_referencial, estado)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param('sssssdss', $nombre, $tipo, $provincia, $distrito, $descripcion, $nuevaFoto, $precio, $estado);
                $stmt->execute();
                $mensaje = "Destino «{$nombre}» creado correctamente.";

            } elseif ($accion === 'editar' && $id > 0) {
                $stmt = $conexion->prepare("
                    UPDATE destinos
                    SET nombre_destino=?, tipo_destino=?, provincia=?, distrito=?,
                        descripcion=?, foto_url=?, precio_referencial=?, estado=?
                    WHERE id_destino=?
                ");
                $stmt->bind_param('sssssdssi', $nombre, $tipo, $provincia, $distrito, $descripcion, $nuevaFoto, $precio, $estado, $id);
                $stmt->execute();
                $mensaje = "Destino actualizado correctamente.";
            }
            $tipoMensaje = 'ok';
        }
    }
}

// ════════════════════════════════════════════════════════════
// ACCIÓN: ELIMINAR
// ════════════════════════════════════════════════════════════
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];

    // Obtener foto antes de borrar para eliminar el archivo
    $row = $conexion->query("SELECT foto_url FROM destinos WHERE id_destino = $id")->fetch_assoc();
    if ($row) {
        ImagenHelper::eliminar($row['foto_url']);
        $conexion->query("DELETE FROM destinos WHERE id_destino = $id");
        $mensaje = 'Destino eliminado.';
        $tipoMensaje = 'ok';
    }
}

// ════════════════════════════════════════════════════════════
// CARGAR destino para editar (si viene ?editar=ID)
// ════════════════════════════════════════════════════════════
$editando = null;
if (isset($_GET['editar'])) {
    $idEditar = (int)$_GET['editar'];
    $editando = $conexion->query("SELECT * FROM destinos WHERE id_destino = $idEditar")->fetch_assoc();
}

// ════════════════════════════════════════════════════════════
// LISTAR todos los destinos
// ════════════════════════════════════════════════════════════
$destinos = $conexion->query("
    SELECT id_destino, nombre_destino, tipo_destino, provincia, foto_url,
           precio_referencial, estado
    FROM destinos
    ORDER BY nombre_destino ASC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Destinos | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/estyle.css">
    <style>
        .admin-container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .admin-grid { display: grid; grid-template-columns: 380px 1fr; gap: 30px; align-items: start; }

        /* Formulario */
        .form-card { background:#fff; border:1px solid #eee; border-radius:12px; padding:28px; }
        .form-card h3 { margin:0 0 20px; font-size:18px; color:#111; }
        .form-group { margin-bottom:16px; }
        .form-group label { display:block; font-weight:600; font-size:13px; color:#444; margin-bottom:6px; }
        .form-group input, .form-group select, .form-group textarea {
            width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:6px;
            font-size:14px; font-family:inherit; outline:none; transition:border-color .2s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color:#31735a;
        }
        .form-group textarea { resize:vertical; min-height:80px; }

        /* Preview imagen */
        .img-preview-wrap { position:relative; display:inline-block; }
        .img-preview { width:100%; max-height:160px; object-fit:cover; border-radius:8px;
                       border:1px solid #eee; margin-top:8px; display:none; }
        .img-preview.visible { display:block; }
        .btn-quitar-foto { position:absolute; top:12px; right:4px; background:#d32f2f;
                           color:#fff; border:none; border-radius:50%; width:26px; height:26px;
                           cursor:pointer; font-size:13px; display:none; }
        .img-preview.visible + .btn-quitar-foto { display:flex; align-items:center; justify-content:center; }

        /* Botones */
        .btn-guardar { width:100%; padding:12px; background:#31735a; color:#fff;
                       border:none; border-radius:6px; font-size:15px; font-weight:600;
                       cursor:pointer; transition:background .2s; }
        .btn-guardar:hover { background:#236c5b; }
        .btn-cancelar { display:block; text-align:center; margin-top:10px; color:#666;
                        font-size:13px; cursor:pointer; }

        /* Tabla */
        .tabla-card { background:#fff; border:1px solid #eee; border-radius:12px; overflow:hidden; }
        .tabla-card table { width:100%; border-collapse:collapse; }
        .tabla-card th { background:#f5f5f5; padding:12px 16px; text-align:left;
                         font-size:13px; color:#666; font-weight:600; }
        .tabla-card td { padding:12px 16px; border-top:1px solid #f0f0f0; font-size:14px; vertical-align:middle; }
        .tabla-card tr:hover td { background:#fafafa; }
        .thumb { width:52px; height:40px; object-fit:cover; border-radius:6px; }
        .badge { padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
        .badge-activo   { background:#e8f5e9; color:#2e7d32; }
        .badge-inactivo { background:#fce4ec; color:#c62828; }
        .btn-edit { color:#1976d2; background:none; border:none; cursor:pointer; font-size:16px; margin-right:8px; }
        .btn-del  { color:#d32f2f; background:none; border:none; cursor:pointer; font-size:16px; }

        /* Mensajes */
        .msg { padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:14px; }
        .msg-ok    { background:#e8f5e9; color:#2e7d32; border:1px solid #c8e6c9; }
        .msg-error { background:#fce4ec; color:#c62828; border:1px solid #f8bbd0; }

        @media(max-width:768px) { .admin-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<div class="admin-container">

    <h2 style="margin-bottom:24px;">
        <i class="fa-solid fa-map-location-dot"></i> Gestión de Destinos
    </h2>

    <?php if ($mensaje): ?>
        <div class="msg msg-<?= $tipoMensaje === 'ok' ? 'ok' : 'error' ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <div class="admin-grid">

        <!-- ── FORMULARIO ── -->
        <div class="form-card">
            <h3>
                <?= $editando ? '✏️ Editar destino' : '➕ Nuevo destino' ?>
            </h3>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion"      value="<?= $editando ? 'editar' : 'crear' ?>">
                <input type="hidden" name="id_destino"  value="<?= $editando['id_destino'] ?? '' ?>">
                <input type="hidden" name="foto_actual" value="<?= htmlspecialchars($editando['foto_url'] ?? '') ?>" id="foto-actual">

                <div class="form-group">
                    <label>Nombre del destino *</label>
                    <input type="text" name="nombre_destino" required
                           value="<?= htmlspecialchars($editando['nombre_destino'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Tipo</label>
                    <select name="tipo_destino">
                        <?php foreach (['Playa','Naturaleza','Cultura','Mixto'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($editando['tipo_destino'] ?? '') === $t ? 'selected' : '' ?>>
                                <?= $t ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Provincia</label>
                    <input type="text" name="provincia"
                           value="<?= htmlspecialchars($editando['provincia'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Distrito</label>
                    <input type="text" name="distrito"
                           value="<?= htmlspecialchars($editando['distrito'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion"><?= htmlspecialchars($editando['descripcion'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Precio referencial (S/.)</label>
                    <input type="number" name="precio_referencial" step="0.01" min="0"
                           value="<?= $editando['precio_referencial'] ?? '0.00' ?>">
                </div>

                <!-- Imagen -->
                <div class="form-group">
                    <label>Imagen</label>
                    <input type="file" name="foto" id="input-foto" accept="image/jpeg,image/png,image/webp"
                           onchange="previewImagen(this)">

                    <div class="img-preview-wrap">
                        <img id="img-preview"
                             src="<?= $editando && $editando['foto_url'] ? ImagenHelper::url($editando['foto_url']) : '../../assets/pt.jpg' ?>"
                             class="img-preview <?= $editando && $editando['foto_url'] ? 'visible' : '' ?>"
                             alt="Vista previa">
                        <button type="button" class="btn-quitar-foto" onclick="quitarFoto()">×</button>
                    </div>

                    <small style="color:#999; font-size:12px;">
                        JPG, PNG o WebP · Máx. 5 MB · Se redimensiona a 1200×900 px
                    </small>
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <select name="estado">
                        <option value="Activo"   <?= ($editando['estado'] ?? 'Activo') === 'Activo'   ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= ($editando['estado'] ?? '')        === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>

                <button type="submit" class="btn-guardar">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <?= $editando ? 'Guardar cambios' : 'Crear destino' ?>
                </button>

                <?php if ($editando): ?>
                    <a href="admin_destinos.php" class="btn-cancelar">Cancelar edición</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- ── TABLA ── -->
        <div class="tabla-card">
            <table>
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($destinos as $d): ?>
                    <tr>
                        <td>
                            <img src="<?= ImagenHelper::url($d['foto_url']) ?>"
                                 alt="<?= htmlspecialchars($d['nombre_destino']) ?>"
                                 class="thumb">
                        </td>
                        <td><?= htmlspecialchars($d['nombre_destino']) ?></td>
                        <td><?= $d['tipo_destino'] ?></td>
                        <td>S/. <?= number_format($d['precio_referencial'], 2) ?></td>
                        <td>
                            <span class="badge badge-<?= strtolower($d['estado']) ?>">
                                <?= $d['estado'] ?>
                            </span>
                        </td>
                        <td>
                            <a href="?editar=<?= $d['id_destino'] ?>" class="btn-edit" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <button class="btn-del" title="Eliminar"
                                onclick="confirmarEliminar(<?= $d['id_destino'] ?>, '<?= htmlspecialchars($d['nombre_destino'], ENT_QUOTES) ?>')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($destinos)): ?>
                        <tr><td colspan="6" style="text-align:center; color:#999; padding:30px;">
                            No hay destinos registrados aún.
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div><!-- /admin-grid -->
</div>

<script>
function previewImagen(input) {
    const preview = document.getElementById('img-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.classList.add('visible');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function quitarFoto() {
    document.getElementById('img-preview').classList.remove('visible');
    document.getElementById('img-preview').src = '';
    document.getElementById('input-foto').value = '';
    document.getElementById('foto-actual').value = '';
}

function confirmarEliminar(id, nombre) {
    if (confirm(`¿Eliminar el destino "${nombre}"?\nTambién se borrará su imagen del servidor.`)) {
        window.location.href = `?eliminar=${id}`;
    }
}
</script>
</body>
</html>