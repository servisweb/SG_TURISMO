<?php
session_start();

// Verificar si hay una reserva confirmada
if (!isset($_SESSION['ultima_reserva'])) {
    header('Location: ../html/index.php');
    exit;
}

$reserva = $_SESSION['ultima_reserva'];

// Nombres de los tours
$tours = [
    1 => "Malecón - Puerto Pizarro",
    2 => "Balneario de Zorritos",
    3 => "Huaca del Sol – Cabeza de Vaca",
    4 => "Punta Sal"
];

$nombre_tour = isset($tours[$reserva['tour_id']]) ? $tours[$reserva['tour_id']] : 'Tour desconocido';
$nombre_guia = isset($reserva['guide_name']) ? $reserva['guide_name'] : 'Prefiero no elegir guía';
$precio_guia = isset($reserva['precio_guia']) ? $reserva['precio_guia'] : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Reserva | Tumbes Tours</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/estyle.css">
    <style>
        .confirmation-container {
            max-width: 700px;
            margin: 60px auto;
            padding: 20px;
        }

        .confirmation-card {
            background: white;
            padding: 50px;
            border-radius: 12px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
            text-align: center;
        }

        .confirmation-icon {
            font-size: 80px;
            color: #4caf50;
            margin-bottom: 20px;
            animation: bounce 0.6s;
        }

        @keyframes bounce {
            0%, 100% { transform: scale(0); }
            50% { transform: scale(1); }
        }

        .confirmation-card h2 {
            font-size: 32px;
            color: #111;
            margin-bottom: 10px;
        }

        .confirmation-card p {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }

        .confirmation-details {
            background: #f5f5f5;
            padding: 25px;
            border-radius: 8px;
            margin: 30px 0;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #ddd;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #666;
            font-weight: 600;
        }

        .detail-value {
            color: #111;
            font-weight: 600;
        }

        .confirmation-id {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }

        .confirmation-id strong {
            color: #1976d2;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-confirmation {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary-conf {
            background-color: #31735a;
            color: white;
        }

        .btn-primary-conf:hover {
            background-color: #236c5b;
        }

        .btn-secondary-conf {
            background-color: #f0f0f0;
            color: #333;
            border: 1px solid #ddd;
        }

        .btn-secondary-conf:hover {
            background-color: #e0e0e0;
        }

        .next-steps {
            background: #fff3e0;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            border-left: 4px solid #ff9800;
        }

        .next-steps h4 {
            color: #e65100;
            margin-bottom: 10px;
        }

        .next-steps ol {
            margin-left: 20px;
            color: #666;
        }

        .next-steps li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <header class="site-header">
        <div class="site-header__brand">
            <img src="https://via.placeholder.com/50" alt="Logotipo de Tumbes Tours" class="site-header__logo">
            <div class="site-header__titles">
                <h1>Tumbes Tours</h1>
                <p>Descubre el paraíso del norte</p>
            </div>
        </div>
        
        <nav class="site-header__nav" aria-label="Navegación principal">
            <ul>
                <li><a href="../html/index.php">Inicio</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="confirmation-container">
            <div class="confirmation-card">
                <div class="confirmation-icon">
                    <i class="fa-solid fa-check-circle"></i>
                </div>

                <h2>¡Reserva Confirmada!</h2>
                <p>Tu reserva ha sido procesada exitosamente. Nos pondremos en contacto contigo pronto.</p>

                <div class="confirmation-id">
                    <strong>Número de Reserva:</strong> <span><?= htmlspecialchars($reserva['reserva_id']) ?></span>
                </div>

                <div class="confirmation-details">
                    <div class="detail-row">
                        <span class="detail-label">Tour:</span>
                        <span class="detail-value"><?= htmlspecialchars($nombre_tour) ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Guía asignado:</span>
                        <span class="detail-value"><?= htmlspecialchars($nombre_guia) ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Recargo guía por persona:</span>
                        <span class="detail-value">S/. <?= number_format($precio_guia, 2) ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Cantidad de Personas:</span>
                        <span class="detail-value"><?= $reserva['cantidad'] ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Fecha de Salida:</span>
                        <span class="detail-value">
                            <?php
                            $fecha = new DateTime($reserva['fecha_salida']);
                            echo $fecha->format('d/m/Y');
                            ?>
                        </span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Nombre de Contacto:</span>
                        <span class="detail-value"><?= htmlspecialchars($reserva['nombre_contacto']) ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Teléfono:</span>
                        <span class="detail-value"><?= htmlspecialchars($reserva['telefono']) ?></span>
                    </div>

                    <div class="detail-row" style="border-bottom: 2px solid #31735a; padding-top: 15px; margin-top: 15px;">
                        <span class="detail-label" style="font-size: 18px;">Total a Pagar:</span>
                        <span class="detail-value" style="font-size: 18px; color: #31735a;">S/. <?= number_format($reserva['total'], 2) ?></span>
                    </div>
                </div>

                <div class="next-steps">
                    <h4><i class="fa-solid fa-clipboard-list"></i> Próximos Pasos:</h4>
                    <ol>
                        <li>Recibirás un email de confirmación en los próximos 5 minutos</li>
                        <li>Te contactaremos vía WhatsApp o teléfono para confirmar detalles</li>
                        <li>Realiza el pago según las indicaciones que te enviaremos</li>
                        <li>Una vez confirmado el pago, tu reserva estará completamente asegurada</li>
                    </ol>
                </div>

                <div class="action-buttons">
                    <button class="btn-confirmation btn-primary-conf" onclick="descargarRecibo()">
                        <i class="fa-solid fa-download"></i> Descargar Recibo
                    </button>
                    <button class="btn-confirmation btn-secondary-conf" onclick="window.location.href='../html/index.php'">
                        <i class="fa-solid fa-home"></i> Volver al Inicio
                    </button>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Tumbes Tours. Todos los derechos reservados.</p>
    </footer>

    <script>
        function descargarRecibo() {
            alert('La funcionalidad de descarga de recibo estará disponible pronto.');
            // Aquí se implementaría la lógica para generar un PDF
        }
    </script>

</body>
</html>
