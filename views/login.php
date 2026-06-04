<?php
session_start();

$redirectTourId = isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : 0;
$redirectGuideId = isset($_GET['guide_id']) ? (int)$_GET['guide_id'] : 0;
$redirectCantidad = isset($_GET['cantidad']) ? (int)$_GET['cantidad'] : 1;

// Si ya está logueado, redirigir al tour o a reserva
if (isset($_SESSION['user_id'])) {
    $query = '';
    if ($redirectTourId > 0) {
        $query = '?tour_id=' . $redirectTourId . '&guide_id=' . $redirectGuideId . '&cantidad=' . $redirectCantidad;
    }
    header('Location: ../views/reservar.php' . $query);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Tumbes Tours</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/estyle.css">
    <style>
        .auth-container {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(
                rgba(0,0,0,.45),
                rgba(0,0,0,.45)
            ),
            url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .auth-form-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .auth-form {
            background: white;
            padding: 50px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
        }

        .auth-form h2 {
            text-align: center;
            margin-bottom: 10px;
            color: #111;
            font-size: 32px;
        }

        .auth-form p {
            text-align: center;
            color: #777;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            border-color: #31735a;
            box-shadow: 0 0 0 3px rgba(49, 115, 90, 0.1);
        }

        .btn-auth {
            width: 100%;
            padding: 14px;
            background-color: #31735a;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        .btn-auth:hover {
            background-color: #236c5b;
        }

        .auth-toggle {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }

        .auth-toggle a {
            color: #31735a;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-toggle a:hover {
            text-decoration: underline;
        }

        .back-link {
            text-align: center;
            margin-bottom: 20px;
        }

        .back-link a {
            color: #31735a;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-password {
            text-align: right;
            margin-top: -15px;
            margin-bottom: 20px;
        }

        .forgot-password a {
            color: #31735a;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .separator {
            display: flex;
            align-items: center;
            margin: 25px 0;
        }

        .separator::before,
        .separator::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #ddd;
        }

        .separator span {
            margin: 0 15px;
            color: #888;
            font-size: 14px;
        }

        .btn-google {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px;
            border-radius: 8px;
            text-decoration: none;
            background: #fff;
            border: 1px solid #ddd;
            color: #444;
            font-weight: 600;
            transition: .3s;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-google:hover {
            background: #f5f5f5;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        @media (max-width: 768px) {
            .auth-form {
                padding: 30px;
            }

            .auth-form h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-form-wrapper">
        <div class="auth-form" id="login-form">
            <div class="back-link">
                <a href="..\index.php"><i class="fa-solid fa-arrow-left"></i> Volver</a>
            </div>

            <h2>Iniciar Sesión</h2>
            <p>Accede a tu cuenta de Tumbes Tours</p>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?php
                    $error = htmlspecialchars($_GET['error']);
                    if ($error == 'invalid') {
                        echo "Email o contraseña incorrectos.";
                    } elseif ($error == 'not_found') {
                        echo "La cuenta no existe. Por favor, regístrate.";
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="success-message">
                    <?php
                    $success = htmlspecialchars($_GET['success']);
                    if ($success == 'registered') {
                        echo "¡Registro exitoso! Por favor, inicia sesión.";
                    }
                    ?>
                </div>
            <?php endif; ?>

            <form action="../controladores/procesar_login.php" method="POST">
                <input type="hidden" name="tour_id" value="<?= $redirectTourId ?>">
                <input type="hidden" name="guide_id" value="<?= $redirectGuideId ?>">
                <input type="hidden" name="cantidad" value="<?= $redirectCantidad ?>">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="tu@email.com">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>

                <div class="forgot-password">
                    <a href="#">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="btn-auth">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Iniciar Sesión
                </button>
            </form>

            <div class="separator">
                <span>o continúa con</span>
            </div>

           <a href="../controladores/google-login.php" class="btn-google">
            <i class="fab fa-google"></i>
            Iniciar sesión con Google
            </a>

            <div class="auth-toggle">
                ¿No tienes cuenta? <a href="#" onclick="toggleForms()">Regístrate aquí</a>
            </div>
        </div>

        <div class="auth-form" id="register-form" style="display: none;">
            <div class="back-link">
                <a href="..\index.php"><i class="fa-solid fa-arrow-left"></i> Volver</a>
            </div>

            <h2>Crear Cuenta</h2>
            <p>Únete a Tumbes Tours</p>

            <form action="../controladores/procesar_registro.php" method="POST">
                <input type="hidden" name="tour_id" value="<?= $redirectTourId ?>">
                <input type="hidden" name="guide_id" value="<?= $redirectGuideId ?>">
                <input type="hidden" name="cantidad" value="<?= $redirectCantidad ?>">
                <div class="form-group">
                    <label for="nombres">Nombres</label>
                    <input type="text" id="nombres" name="nombres" required placeholder="Juan">
                </div>

                <div class="form-group">
                    <label for="apellidos">Apellidos</label>
                    <input type="text" id="apellidos" name="apellidos" required placeholder="Pérez García">
                </div>

                <div class="form-group">
                    <label for="email_reg">Email</label>
                    <input type="email" id="email_reg" name="email" required placeholder="tu@email.com">
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" placeholder="+51 999 999 999">
                </div>

                <div class="form-group">
                    <label for="password_reg">Contraseña</label>
                    <input type="password" id="password_reg" name="password" required placeholder="••••••••" minlength="6">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="••••••••" minlength="6">
                </div>

                <button type="submit" class="btn-auth">
                    <i class="fa-solid fa-user-plus"></i> Registrarse
                </button>
            </form>

            <div class="auth-toggle">
                ¿Ya tienes cuenta? <a href="#" onclick="toggleForms()">Inicia sesión aquí</a>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleForms() {
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        
        if (loginForm.style.display === 'none') {
            loginForm.style.display = 'block';
            registerForm.style.display = 'none';
        } else {
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
        }
    }
</script>

</body>
</html>
