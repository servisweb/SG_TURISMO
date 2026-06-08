<?php
session_start();
require_once __DIR__ . '/../../config/sesion.php';
verificar_sesion();

if (empty($_SESSION['user_id']) || empty($_SESSION['user_email'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../config/conexion.php';

$userId = (int)$_SESSION['user_id'];
$isAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'Admin';

$flash = '';
if (!empty($_SESSION['flash_profile'])) {
    $flash = $_SESSION['flash_profile'];
    unset($_SESSION['flash_profile']);
}

$errors = [];
$userName = $_SESSION['user_name'] ?? 'Usuario';
$userEmail = $_SESSION['user_email'] ?? '';
$userPhone = $_SESSION['user_phone'] ?? '';

$stmt = $conexion->prepare('SELECT nombres, apellidos, email, telefono FROM usuarios WHERE id_usuario = ? LIMIT 1');
if ($stmt) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $userName = trim($row['nombres'] . ' ' . $row['apellidos']);
        $userEmail = $row['email'];
        $userPhone = $row['telefono'];
        $_SESSION['user_name'] = $userName;
        $_SESSION['user_email'] = $userEmail;
        $_SESSION['user_phone'] = $userPhone;
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    if ($nombre === '') {
        $errors[] = 'El nombre es obligatorio.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Ingresa un correo válido.';
    }

    if (empty($errors)) {
        $userName = $nombre;
        $userEmail = $email;
        $userPhone = $telefono;

        $nameParts = explode(' ', $nombre, 2);
        $nombres = $nameParts[0];
        $apellidos = $nameParts[1] ?? '';

        $update = $conexion->prepare('UPDATE usuarios SET nombres = ?, apellidos = ?, email = ?, telefono = ? WHERE id_usuario = ?');
        if ($update) {
            $update->bind_param('ssssi', $nombres, $apellidos, $email, $telefono, $userId);
            if (!$update->execute()) {
                if ($conexion->errno === 1062) {
                    $errors[] = 'El correo ya está en uso por otro usuario.';
                } else {
                    $errors[] = 'No se pudo actualizar el perfil. Intenta nuevamente.';
                }
            }
            $update->close();
        } else {
            $errors[] = 'No se pudo preparar la actualización del perfil.';
        }

        if (empty($errors)) {
            $_SESSION['user_name'] = $userName;
            $_SESSION['user_email'] = $userEmail;
            $_SESSION['user_phone'] = $userPhone;
            $_SESSION['flash_profile'] = 'Tus datos se han actualizado correctamente.';
            header('Location: perfil_user.php');
            exit;
        }
    }
}

$userName  = htmlspecialchars($userName);
$userEmail = htmlspecialchars($userEmail);
$userPhone = htmlspecialchars($userPhone);

// Reservas del usuario
$reservas_usuario = [];
// Intentar leer reservas desde la tabla `app_reservas` (si existe)
$reservas_usuario = [];
try {
    $stmtR = $conexion->prepare(
        'SELECT ar.codigo_reserva, ar.tour_id, ar.cantidad, ar.total, ar.estado, ar.fecha_creacion, ar.fecha_salida
         FROM app_reservas ar WHERE ar.id_usuario = ? ORDER BY ar.fecha_creacion DESC'
    );
    if ($stmtR) {
        $stmtR->bind_param('i', $userId);
        $stmtR->execute();
        $reservas_usuario = $stmtR->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtR->close();
    }
} catch (mysqli_sql_exception $e) {
    // Tabla `app_reservas` no existe o error en la consulta: evitar fatal
    if ($e->getCode() !== 1146) {
        error_log('perfil_user.php mysqli_sql_exception: ' . $e->getMessage());
    }
    $reservas_usuario = [];
}
// Tambien reservas oficiales migradas
try {
    $stmtRO = $conexion->prepare(
        'SELECT r.codigo_reserva, p.titulo as tour_nombre, r.cantidad_pasajeros as cantidad,
                r.precio_total as total, r.estado_reserva as estado, r.fecha_creacion, NULL as fecha_salida
         FROM reservas r
         LEFT JOIN salidas_operativas so ON so.id_salida = r.id_salida
         LEFT JOIN paquetes p ON p.id_paquete = so.id_paquete
         WHERE r.id_usuario_titular = ? ORDER BY r.fecha_creacion DESC'
    );
    if ($stmtRO) {
        $stmtRO->bind_param('i', $userId);
        $stmtRO->execute();
        $reservas_oficiales = $stmtRO->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtRO->close();
    }
} catch (mysqli_sql_exception $e) {
    error_log('perfil_user.php reservas_oficiales mysqli_sql_exception: ' . $e->getMessage());
    $reservas_oficiales = [];
}

$tourNames = [1=>'Malecón - Puerto Pizarro', 2=>'Balneario de Zorritos', 3=>'Huaca del Sol', 4=>'Punta Sal'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil | Tumbes Tours</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/estyle.css">
    <style>
        body { background:#f7fafc; color:#1f2937; font-family:Inter,system-ui,Arial,sans-serif; margin:0; }
        .profile-shell { max-width: 980px; margin: 0 auto; padding: 28px 20px 40px; }
        .profile-header { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:flex-start; gap:18px; margin-bottom:28px; }
        .profile-header h1 { margin:0; font-size:2rem; letter-spacing:-0.04em; }
        .profile-header p { margin:8px 0 0; color:#4b5563; max-width:620px; }
        .action-buttons { display:flex; flex-wrap:wrap; gap:12px; }
        .action-buttons a, .action-buttons button { display:inline-flex; align-items:center; gap:8px; padding:12px 18px; border-radius:10px; border:none; text-decoration:none; font-weight:600; cursor:pointer; }
        .btn-primary { background:#0f766e; color:#fff; }
        .btn-secondary { background:#f8fafc; color:#334155; border:1px solid #cbd5e1; }
        .btn-primary:hover { background:#115e59; }
        .btn-secondary:hover { background:#e2e8f0; }
        .flash { margin-bottom:20px; padding:18px 20px; border-radius:14px; font-size:0.98rem; }
        .flash-success { background:#d1fae5; color:#115e59; border:1px solid #a7f3d0; }
        .flash-error { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
        .flash ul { margin:0; padding-left:18px; }
        .flash ul li { margin-bottom:8px; }
        .form-item { margin-bottom:18px; }
        .form-item label { display:block; margin-bottom:8px; color:#475569; font-weight:600; }
        .form-item input { width:100%; padding:14px 16px; border:1px solid #d1d5db; border-radius:12px; background:#f8fafc; font-size:1rem; color:#111827; }
        .form-item input:focus { outline:none; border-color:#0f766e; box-shadow:0 0 0 3px rgba(15,118,110,0.12); }
        .form-actions { display:flex; flex-wrap:wrap; gap:12px; align-items:center; margin-top:8px; }
        .profile-grid { display:grid; grid-template-columns:1fr 320px; gap:24px; }
        .card { background:#fff; border:1px solid #e2e8f0; border-radius:20px; padding:28px; box-shadow:0 24px 60px rgba(15,23,42,0.05); }
        .card h2 { margin:0 0 18px; font-size:1.2rem; color:#0f172a; }
        .profile-row { display:flex; justify-content:space-between; align-items:center; padding:16px 0; border-bottom:1px solid #e2e8f0; }
        .profile-row:last-child { border-bottom:0; }
        .profile-label { color:#475569; font-size:0.95rem; }
        .profile-value { color:#111827; font-weight:700; }
        .profile-avatar { width:84px; height:84px; border-radius:18px; display:grid; place-items:center; background:#f8fafc; color:#0f172a; font-size:2rem; font-weight:700; }
        .hint { color:#64748b; font-size:0.94rem; margin-top:14px; }
        .admin-only { margin-top:22px; padding:18px; background:#ecfdf5; border:1px solid #d1fae5; border-radius:16px; }
        .admin-only strong { color:#166534; }
        .reservas-section { margin-top:28px; }
        .reservas-section h2 { font-size:1.2rem; color:#0f172a; margin-bottom:16px; }
        .reserva-card { background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:18px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; }
        .reserva-info strong { display:block; color:#0f172a; font-size:0.97rem; }
        .reserva-info small { color:#64748b; font-size:0.85rem; }
        .badge-r { padding:5px 12px; border-radius:20px; font-size:12px; font-weight:700; }
        .badge-pendiente { background:#fef9c3; color:#854d0e; }
        .badge-pagada    { background:#dcfce7; color:#166534; }
        .badge-cancelada { background:#fee2e2; color:#991b1b; }
        .badge-migrada   { background:#e0f2fe; color:#0369a1; }
        .no-reservas { color:#94a3b8; text-align:center; padding:24px; font-size:0.95rem; }
        @media(max-width:860px) { .profile-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<div class="profile-shell">
    <div class="profile-header">
        <div>
            <h1>Mi perfil</h1>
            <p>Esta es tu zona de perfil. La interfaz es la misma para todos los usuarios, y solo si tienes permisos de administrador verás opciones administrativas adicionales.</p>
        </div>
        <div class="action-buttons">
            <?php if ($isAdmin): ?>
                <a href="../admin/panel_admin.php" class="btn-primary"><i class="fa-solid fa-gauge-high"></i> Panel admin</a>
            <?php endif; ?>
            <a href="../../index.php" class="btn-secondary"><i class="fa-solid fa-house"></i> Inicio</a>
            <a href="../../controladores/cerrar_sesion.php" class="btn-secondary"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="flash flash-success"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="flash flash-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="profile-grid">
        <section class="card">
            <h2>Actualizar perfil</h2>
            <form method="POST" action="perfil_user.php">
                <div class="form-item">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" type="text" value="<?= $userName ?>" required>
                </div>
                <div class="form-item">
                    <label for="email">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="<?= $userEmail ?>" required>
                </div>
                <div class="form-item">
                    <label for="telefono">Teléfono</label>
                    <input id="telefono" name="telefono" type="tel" value="<?= $userPhone ?>">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Guardar cambios</button>
                    <?php if ($isAdmin): ?>
                    <a href="../admin/panel_admin.php" class="btn-secondary" style="text-decoration:none; display:inline-flex; align-items:center;">Volver al panel</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="card">
            <h2>Resumen de cuenta</h2>
            <div class="profile-row">
                <div class="profile-label">Nombre</div>
                <div class="profile-value"><?= $userName ?></div>
            </div>
            <div class="profile-row">
                <div class="profile-label">Correo</div>
                <div class="profile-value"><?= $userEmail ?></div>
            </div>
            <div class="profile-row">
                <div class="profile-label">Teléfono</div>
                <div class="profile-value"><?= $userPhone ?: 'Sin teléfono registrado' ?></div>
            </div>
            <div class="profile-row">
                <div class="profile-label">Último login</div>
                <div class="profile-value"><?= date('d/m/Y H:i') ?></div>
            </div>
            <?php if ($isAdmin): ?>
            <div class="admin-only">
                <strong>Acceso de administrador</strong>
                <p class="hint">Si quieres, regresa al panel administrativo para gestionar contenido y usuarios.</p>
            </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- MIS RESERVAS -->
    <div class="reservas-section">
        <h2><i class="fa-solid fa-calendar-check"></i> Mis Reservas</h2>

        <?php if (empty($reservas_usuario) && empty($reservas_oficiales)): ?>
            <div class="card no-reservas">
                <i class="fa-solid fa-calendar-xmark" style="font-size:2rem;color:#cbd5e1;display:block;margin-bottom:10px;"></i>
                Aún no tienes reservas. <a href="../../index.php" style="color:#0f766e;font-weight:600;">Explora nuestros tours</a>
            </div>
        <?php else: ?>
            <?php foreach ($reservas_oficiales as $r): ?>
            <div class="reserva-card">
                <div class="reserva-info">
                    <strong><?= htmlspecialchars($r['tour_nombre'] ?? 'Tour') ?></strong>
                    <small>Código: <?= htmlspecialchars($r['codigo_reserva']) ?> &middot; <?= $r['cantidad'] ?> persona(s) &middot; Registrada: <?= date('d/m/Y', strtotime($r['fecha_creacion'])) ?></small>
                </div>
                <div style="display:flex;align-items:center;gap:14px;">
                    <span style="font-weight:700;color:#0f766e;">S/. <?= number_format($r['total'], 2) ?></span>
                    <span class="badge-r badge-<?= strtolower($r['estado']) ?>"><?= htmlspecialchars($r['estado']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>

            <?php foreach ($reservas_usuario as $r): ?>
            <div class="reserva-card">
                <div class="reserva-info">
                    <strong><?= htmlspecialchars($tourNames[$r['tour_id']] ?? 'Tour #'.$r['tour_id']) ?></strong>
                    <small>Código: <?= htmlspecialchars($r['codigo_reserva']) ?> &middot; <?= $r['cantidad'] ?> persona(s)
                        <?php if ($r['fecha_salida'] && $r['fecha_salida'] !== '0000-00-00'): ?>
                            &middot; Salida: <?= date('d/m/Y', strtotime($r['fecha_salida'])) ?>
                        <?php endif; ?>
                    </small>
                </div>
                <div style="display:flex;align-items:center;gap:14px;">
                    <span style="font-weight:700;color:#0f766e;">S/. <?= number_format($r['total'], 2) ?></span>
                    <span class="badge-r badge-<?= strtolower($r['estado']) ?>"><?= htmlspecialchars($r['estado']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
