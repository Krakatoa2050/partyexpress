<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="contacto-styles.css">
</head>
<body>
    <div class="contacto-container">
        <header class="contacto-header">
            <a href="index.php" class="contacto-back"><i class="fa fa-arrow-left"></i> Volver al inicio</a>
            <h1>Contacto</h1>
            <?php if (isset($_SESSION['usuario'])): ?>
                <div class="contacto-user">Hola, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></div>
            <?php endif; ?>
        </header>

        <main class="contacto-main">
            <div class="contacto-grid">
                <div class="contacto-info-card">
                    <h2>Información de contacto</h2>
                    <div class="contacto-item">
                        <i class="fa fa-map-marker-alt"></i>
                        <div>
                            <strong>Dirección:</strong><br>
                            Av. España 1234, Asunción, Paraguay
                        </div>
                    </div>
                    <div class="contacto-item">
                        <i class="fa fa-phone"></i>
                        <div>
                            <strong>Teléfono:</strong><br>
                            +595 21 123 456
                        </div>
                    </div>
                    <div class="contacto-item">
                        <i class="fa fa-envelope"></i>
                        <div>
                            <strong>Email:</strong><br>
                            somospartyexpress@gmail.com
                        </div>
                    </div>
                    <div class="contacto-item">
                        <i class="fa fa-clock"></i>
                        <div>
                            <strong>Horarios de atención:</strong><br>
                            Lunes a Viernes: 9:00 - 18:00<br>
                            Sábados: 9:00 - 14:00
                        </div>
                    </div>
                    <div class="contacto-item">
                        <i class="fa fa-comments"></i>
                        <div>
                            <strong>Redes sociales:</strong><br>
                            <a href="https://www.facebook.com/partyexpress.py" class="social-link" target="_blank">Facebook</a> | 
                            <a href="https://www.instagram.com/partyexpress_py?utm_source=ig_web_button_share_sheet&igsh=MXF6dWcydmt0dTFuNA==" class="social-link" target="_blank">Instagram</a> | 
                            <a href="https://wa.me/595981123456" class="social-link" target="_blank">WhatsApp</a>
                        </div>
                    </div>      
                </div>

                <div class="contacto-form-card">
                    <h2>Envíanos un mensaje</h2>
                    <form class="contacto-form" action="contacto_procesar.php" method="POST" novalidate>
                        <div class="form-group">
                            <label for="nombre">Nombre completo</label>
                            <input type="text" id="nombre" name="nombre" placeholder="Tu nombre completo" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Correo electrónico</label>
                            <input type="email" id="email" name="email" placeholder="tu@email.com" required>
                        </div>
                        <div class="form-group">
                            <label for="asunto">Asunto</label>
                            <select id="asunto" name="asunto" required>
                                <option value="" disabled selected>Selecciona un asunto</option>
                                <option value="Consulta general">Consulta general</option>
                                <option value="Soporte técnico">Soporte técnico</option>
                                <option value="Sugerencia">Sugerencia</option>
                                <option value="Reportar problema">Reportar problema</option>
                                <option value="Colaboración">Colaboración</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="mensaje">Mensaje</label>
                            <textarea id="mensaje" name="mensaje" placeholder="Escribe tu mensaje aquí..." rows="5" required></textarea>
                        </div>
                        <button type="submit" class="contacto-btn">Enviar mensaje</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Validación del formulario
        document.querySelector('.contacto-form')?.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                this.classList.remove('form-invalid');
                void this.offsetWidth;
                this.classList.add('form-invalid');
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Enviando...';
            }
        });
    </script>
</body>
</html> 