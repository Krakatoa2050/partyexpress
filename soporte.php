<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html?redirect=' . urlencode('soporte.php'));
    exit();
}

$mensaje_exito = '';
$mensaje_error = '';

// Procesar formulario de soporte
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'enviar_soporte') {
    $asunto = $_POST['asunto'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $prioridad = $_POST['prioridad'] ?? 'media';
    
    if (!empty($asunto) && !empty($categoria) && !empty($descripcion)) {
        try {
            $conn = obtenerConexion();
            
            // Aquí se procesaría el envío del ticket de soporte
            // Por ahora solo simulamos el éxito
            $mensaje_exito = 'Tu solicitud de soporte ha sido enviada correctamente. Te responderemos en las próximas 24 horas.';
            
            $conn->close();
        } catch (Exception $e) {
            $mensaje_error = 'Error al enviar la solicitud: ' . $e->getMessage();
        }
    } else {
        $mensaje_error = 'Por favor completa todos los campos requeridos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soporte - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .soporte-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .soporte-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .soporte-title {
            color: #a259f7;
            font-size: 2rem;
            margin: 0;
        }
        
        .btn-volver {
            background: rgba(162,89,247,0.2);
            color: #a259f7;
            border: 1px solid rgba(162,89,247,0.4);
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .soporte-card {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(162,89,247,0.2);
            margin-bottom: 25px;
        }
        
        .soporte-card h3 {
            color: #a259f7;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .mensaje {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .mensaje-exito {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .mensaje-error {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .form-grupo {
            margin-bottom: 20px;
        }
        
        .form-label {
            color: #a259f7;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            font-size: 0.95rem;
        }
        
        .form-input, .form-select, .form-textarea {
            width: 100%;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(162,89,247,0.3);
            border-radius: 10px;
            padding: 12px 15px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #a259f7;
            background: rgba(162,89,247,0.1);
        }
        
        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn-enviar {
            background: linear-gradient(90deg, #a259f7 60%, #7209b7 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .btn-enviar:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(162,89,247,0.3);
        }
        
        .info-card {
            background: rgba(162,89,247,0.1);
            border: 1px solid rgba(162,89,247,0.3);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .info-card h4 {
            color: #a259f7;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .info-card p {
            color: #ccc;
            line-height: 1.6;
            margin: 0;
        }
        
        .contacto-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .contacto-item {
            background: rgba(255,255,255,0.05);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .contacto-item:hover {
            transform: translateY(-2px);
            background: rgba(162,89,247,0.1);
        }
        
        .contacto-item i {
            font-size: 2rem;
            color: #a259f7;
            margin-bottom: 10px;
        }
        
        .contacto-item h4 {
            color: #a259f7;
            margin: 0 0 5px 0;
        }
        
        .contacto-item p {
            color: #ccc;
            margin: 0;
            font-size: 0.9rem;
        }
        
        .prioridad-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .prioridad-baja {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .prioridad-media {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .prioridad-alta {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .horarios-soporte {
            background: rgba(162,89,247,0.1);
            border: 1px solid rgba(162,89,247,0.3);
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .horarios-soporte h4 {
            color: #a259f7;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .horario-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .horario-item:last-child {
            border-bottom: none;
        }
        
        .horario-dia {
            color: #a259f7;
            font-weight: 600;
        }
        
        .horario-tiempo {
            color: #ccc;
        }
    </style>
</head>
<body>
    <nav class="catalogo">
        <div class="logo-nombre">
            <img src="img/logo.jpg" alt="Logo PartyExpress" class="logo-img">
            <span class="logo-text">PartyExpress</span>
        </div>
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="fiestas.php">Fiestas</a></li>
            <li><a href="lugares.php">Lugares</a></li>
            <li><a href="organizar.php">Organizar fiesta</a></li>
            <li><a href="contacto.php">Contacto</a></li>
        </ul>
        <span class="usuario-menu-container">
            <?php if (isset($_SESSION['usuario'])): ?>
                <div class="usuario-bienvenida">
                    <div class="usuario-avatar">
                        <?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?>
                    </div>
                    <span>Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                </div>
                <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="mis_solicitudes.php" class="menu-item">
                        <i class="fa fa-calendar-check"></i> Mis Solicitudes
                    </a>
                    <a href="perfil.php" class="menu-item">
                        <i class="fa fa-user"></i> Mi Perfil
                    </a>
                    <a href="favoritos.php" class="menu-item">
                        <i class="fa fa-heart"></i> Favoritos
                    </a>
                    <a href="configuracion.php" class="menu-item">
                        <i class="fa fa-cog"></i> Configuración
                    </a>
                    
                    <form method="POST" action="logout.php" style="margin:0;">
                        <button type="submit" class="logout-btn">
                            <i class="fa fa-sign-out-alt"></i> Cerrar sesión
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <a href="login.html" class="login-btn">Iniciar sesión</a>
            <?php endif; ?>
        </span>
    </nav>

    <div class="soporte-container">
        <header class="soporte-header">
            <h1 class="soporte-title">Soporte Técnico</h1>
            <a href="configuracion.php" class="btn-volver">
                <i class="fa fa-arrow-left"></i> Volver a configuración
            </a>
        </header>

        <?php if ($mensaje_exito): ?>
            <div class="mensaje mensaje-exito">
                <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($mensaje_exito); ?>
            </div>
        <?php endif; ?>

        <?php if ($mensaje_error): ?>
            <div class="mensaje mensaje-error">
                <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($mensaje_error); ?>
            </div>
        <?php endif; ?>

        <!-- Información de Contacto -->
        <div class="soporte-card">
            <h3><i class="fa fa-headset"></i> Información de Contacto</h3>
            
            <div class="contacto-grid">
                <div class="contacto-item">
                    <i class="fa fa-envelope"></i>
                    <h4>Email</h4>
                    <p>soporte@partyexpress.com.py</p>
                </div>
                
                <div class="contacto-item">
                    <i class="fa fa-phone"></i>
                    <h4>Teléfono</h4>
                    <p>+595 21 123 456</p>
                </div>
                
                <div class="contacto-item">
                    <i class="fa fa-whatsapp"></i>
                    <h4>WhatsApp</h4>
                    <p>+595 981 123 456</p>
                </div>
                
                <div class="contacto-item">
                    <i class="fa fa-clock"></i>
                    <h4>Horarios</h4>
                    <p>Lun - Vie: 8:00 - 18:00</p>
                </div>
            </div>
            
            <div class="horarios-soporte">
                <h4>Horarios de Atención</h4>
                <div class="horario-item">
                    <span class="horario-dia">Lunes - Viernes</span>
                    <span class="horario-tiempo">8:00 AM - 6:00 PM</span>
                </div>
                <div class="horario-item">
                    <span class="horario-dia">Sábados</span>
                    <span class="horario-tiempo">9:00 AM - 2:00 PM</span>
                </div>
                <div class="horario-item">
                    <span class="horario-dia">Domingos</span>
                    <span class="horario-tiempo">Cerrado</span>
                </div>
            </div>
        </div>

        <!-- Formulario de Soporte -->
        <div class="soporte-card">
            <h3><i class="fa fa-ticket-alt"></i> Crear Ticket de Soporte</h3>
            <p style="color: #ccc; margin-bottom: 20px;">Completa el formulario y nuestro equipo te responderá lo antes posible.</p>
            
            <form method="POST" action="soporte.php">
                <input type="hidden" name="action" value="enviar_soporte">
                
                <div class="form-grupo">
                    <label class="form-label">Asunto *</label>
                    <input type="text" name="asunto" class="form-input" placeholder="Describe brevemente tu problema" required>
                </div>
                
                <div class="form-grupo">
                    <label class="form-label">Categoría *</label>
                    <select name="categoria" class="form-select" required>
                        <option value="">Selecciona una categoría</option>
                        <option value="tecnico">Problema Técnico</option>
                        <option value="cuenta">Problema con mi Cuenta</option>
                        <option value="evento">Problema con Eventos</option>
                        <option value="pago">Problema de Pagos</option>
                        <option value="sugerencia">Sugerencia</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                
                <div class="form-grupo">
                    <label class="form-label">Prioridad</label>
                    <select name="prioridad" class="form-select">
                        <option value="baja">Baja</option>
                        <option value="media" selected>Media</option>
                        <option value="alta">Alta</option>
                    </select>
                </div>
                
                <div class="form-grupo">
                    <label class="form-label">Descripción del Problema *</label>
                    <textarea name="descripcion" class="form-textarea" placeholder="Describe detalladamente tu problema, incluye pasos para reproducirlo y cualquier información adicional que consideres relevante..." required></textarea>
                </div>
                
                <button type="submit" class="btn-enviar">
                    <i class="fa fa-paper-plane"></i> Enviar Ticket
                </button>
            </form>
        </div>

        <!-- Información Adicional -->
        <div class="soporte-card">
            <h3><i class="fa fa-info-circle"></i> Información Adicional</h3>
            
            <div class="info-card">
                <h4>Tiempo de Respuesta</h4>
                <p>• <strong>Prioridad Alta:</strong> 2-4 horas</p>
                <p>• <strong>Prioridad Media:</strong> 24 horas</p>
                <p>• <strong>Prioridad Baja:</strong> 48-72 horas</p>
            </div>
            
            <div class="info-card">
                <h4>Antes de Contactar</h4>
                <p>• Revisa nuestra sección de <a href="ayuda.php" style="color: #a259f7;">Ayuda</a> para ver si tu pregunta ya tiene respuesta</p>
                <p>• Asegúrate de incluir toda la información necesaria en tu descripción</p>
                <p>• Si es un problema técnico, incluye capturas de pantalla si es posible</p>
                <p>• Proporciona tu nombre de usuario y detalles del navegador que usas</p>
            </div>
            
            <div class="info-card">
                <h4>Problemas Comunes</h4>
                <p>• <strong>No puedo iniciar sesión:</strong> Verifica tu email y contraseña, o usa "Olvidé mi contraseña"</p>
                <p>• <strong>No puedo crear un evento:</strong> Asegúrate de estar logueado y completar todos los campos requeridos</p>
                <p>• <strong>No se carga la página:</strong> Intenta refrescar la página o usar un navegador diferente</p>
                <p>• <strong>Problema con fotos:</strong> Verifica que el archivo sea JPG, PNG o GIF y no exceda 5MB</p>
            </div>
        </div>

        <!-- Enlaces Útiles -->
        <div class="soporte-card">
            <h3><i class="fa fa-link"></i> Enlaces Útiles</h3>
            
            <div class="contacto-grid">
                <a href="ayuda.php" class="contacto-item" style="text-decoration: none;">
                    <i class="fa fa-question-circle"></i>
                    <h4>Centro de Ayuda</h4>
                    <p>Encuentra respuestas rápidas</p>
                </a>
                
                <a href="configuracion.php" class="contacto-item" style="text-decoration: none;">
                    <i class="fa fa-cog"></i>
                    <h4>Configuración</h4>
                    <p>Gestiona tu cuenta</p>
                </a>
                
                <a href="index.php" class="contacto-item" style="text-decoration: none;">
                    <i class="fa fa-home"></i>
                    <h4>Página Principal</h4>
                    <p>Volver al inicio</p>
                </a>
            </div>
        </div>
    </div>

    <script>
        const menuToggle = document.getElementById("menuToggle");
        const dropdownMenu = document.getElementById("dropdownMenu");
        
        if (menuToggle && dropdownMenu) {
            menuToggle.onclick = function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
                menuToggle.classList.toggle('active');
            };
            
            document.addEventListener('click', function(e) {
                if (!dropdownMenu.contains(e.target) && !menuToggle.contains(e.target)) {
                    dropdownMenu.classList.remove('show');
                    menuToggle.classList.remove('active');
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && dropdownMenu.classList.contains('show')) {
                    dropdownMenu.classList.remove('show');
                    menuToggle.classList.remove('active');
                }
            });
        }
    </script>
</body>
</html>
