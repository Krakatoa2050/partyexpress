<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html?redirect=' . urlencode('notificaciones.php'));
    exit();
}

$mensaje_exito = '';
$mensaje_error = '';

// Obtener información del usuario
$usuario = null;
try {
    $conn = obtenerConexion();
    $stmt = $conn->prepare('SELECT id, email_notificaciones, push_notificaciones, recordatorios_eventos, nuevos_favoritos FROM usuarios WHERE id = ? AND activo = TRUE');
    $stmt->bind_param('i', $_SESSION['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
    }
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $mensaje_error = 'Error al cargar datos del usuario: ' . $e->getMessage();
}

// Procesar cambios de notificaciones
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'actualizar_notificaciones') {
    $email_notificaciones = isset($_POST['email_notificaciones']) ? '1' : '0';
    $push_notificaciones = isset($_POST['push_notificaciones']) ? '1' : '0';
    $recordatorios_eventos = isset($_POST['recordatorios_eventos']) ? '1' : '0';
    $nuevos_favoritos = isset($_POST['nuevos_favoritos']) ? '1' : '0';
    
    try {
        $conn = obtenerConexion();
        $stmt = $conn->prepare('UPDATE usuarios SET email_notificaciones = ?, push_notificaciones = ?, recordatorios_eventos = ?, nuevos_favoritos = ? WHERE id = ?');
        $stmt->bind_param('ssssi', $email_notificaciones, $push_notificaciones, $recordatorios_eventos, $nuevos_favoritos, $_SESSION['usuario_id']);
        
        if ($stmt->execute()) {
            $mensaje_exito = 'Configuración de notificaciones actualizada correctamente';
            $usuario['email_notificaciones'] = $email_notificaciones;
            $usuario['push_notificaciones'] = $push_notificaciones;
            $usuario['recordatorios_eventos'] = $recordatorios_eventos;
            $usuario['nuevos_favoritos'] = $nuevos_favoritos;
        } else {
            $mensaje_error = 'Error al actualizar las notificaciones';
        }
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        $mensaje_error = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .notificaciones-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .notificaciones-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .notificaciones-title {
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
        
        .notificaciones-card {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(162,89,247,0.2);
            margin-bottom: 25px;
        }
        
        .notificaciones-card h3 {
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
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255,255,255,0.2);
            transition: .4s;
            border-radius: 34px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider {
            background-color: #a259f7;
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }
        
        .configuracion-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .configuracion-item:last-child {
            border-bottom: none;
        }
        
        .configuracion-item-info h5 {
            color: #a259f7;
            margin: 0 0 5px 0;
            font-size: 1rem;
        }
        
        .configuracion-item-info p {
            color: #ccc;
            margin: 0;
            font-size: 0.9rem;
        }
        
        .btn-guardar {
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
        
        .btn-guardar:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(162,89,247,0.3);
        }
        
        .notification-preview {
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            padding: 15px;
            margin-top: 10px;
            border-left: 4px solid #a259f7;
        }
        
        .notification-preview h6 {
            color: #a259f7;
            margin: 0 0 5px 0;
            font-size: 0.9rem;
        }
        
        .notification-preview p {
            color: #ccc;
            margin: 0;
            font-size: 0.8rem;
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

    <div class="notificaciones-container">
        <header class="notificaciones-header">
            <h1 class="notificaciones-title">Notificaciones</h1>
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

        <!-- Notificaciones por Email -->
        <div class="notificaciones-card">
            <h3><i class="fa fa-envelope"></i> Notificaciones por Email</h3>
            <p style="color: #ccc; margin-bottom: 20px;">Recibe notificaciones importantes directamente en tu email.</p>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Notificaciones por email</h5>
                    <p>Recibir notificaciones importantes por email</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="email_notificaciones" value="1" <?php echo ($usuario['email_notificaciones'] ?? '1') === '1' ? 'checked' : ''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Recordatorios de eventos</h5>
                    <p>Recordatorios antes de tus eventos programados</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="recordatorios_eventos" value="1" <?php echo ($usuario['recordatorios_eventos'] ?? '1') === '1' ? 'checked' : ''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>

        <!-- Notificaciones Push -->
        <div class="notificaciones-card">
            <h3><i class="fa fa-bell"></i> Notificaciones Push</h3>
            <p style="color: #ccc; margin-bottom: 20px;">Recibe notificaciones instantáneas en tu navegador.</p>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Notificaciones push</h5>
                    <p>Recibir notificaciones en el navegador</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="push_notificaciones" value="1" <?php echo ($usuario['push_notificaciones'] ?? '1') === '1' ? 'checked' : ''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Sonidos de notificación</h5>
                    <p>Reproducir sonidos al recibir notificaciones</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox">
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>

        <!-- Tipos de Notificaciones -->
        <div class="notificaciones-card">
            <h3><i class="fa fa-list"></i> Tipos de Notificaciones</h3>
            <p style="color: #ccc; margin-bottom: 20px;">Selecciona qué tipos de notificaciones quieres recibir.</p>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Nuevos eventos</h5>
                    <p>Notificaciones sobre nuevos eventos en tu área</p>
                    <div class="notification-preview">
                        <h6>🎉 Nuevo evento cerca de ti</h6>
                        <p>Fiesta de Cumpleaños - A 2km de tu ubicación</p>
                    </div>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Actualizaciones de eventos</h5>
                    <p>Cambios en eventos que sigues o en los que participas</p>
                    <div class="notification-preview">
                        <h6>📅 Evento actualizado</h6>
                        <p>La fecha de "Fiesta de Graduación" ha cambiado</p>
                    </div>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Nuevos favoritos</h5>
                    <p>Notificaciones sobre nuevos lugares y eventos</p>
                    <div class="notification-preview">
                        <h6>⭐ Nuevo lugar favorito</h6>
                        <p>Se agregó un nuevo lugar cerca de ti</p>
                    </div>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="nuevos_favoritos" value="1" <?php echo ($usuario['nuevos_favoritos'] ?? '0') === '1' ? 'checked' : ''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Promociones y ofertas</h5>
                    <p>Ofertas especiales y promociones de lugares</p>
                    <div class="notification-preview">
                        <h6>🎁 Oferta especial</h6>
                        <p>20% de descuento en Parque Acuático Aqualandia</p>
                    </div>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Noticias y actualizaciones</h5>
                    <p>Novedades de la plataforma y mejoras</p>
                    <div class="notification-preview">
                        <h6>🆕 Nueva funcionalidad</h6>
                        <p>Ya puedes agregar fotos a tus eventos</p>
                    </div>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>

        <!-- Configuraciones Avanzadas -->
        <div class="notificaciones-card">
            <h3><i class="fa fa-cog"></i> Configuraciones Avanzadas</h3>
            <p style="color: #ccc; margin-bottom: 20px;">Configuraciones adicionales para personalizar tu experiencia.</p>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Notificaciones silenciosas</h5>
                    <p>Recibir notificaciones sin sonido ni vibración</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Horario de notificaciones</h5>
                    <p>Recibir notificaciones solo en horario de día (8:00 - 22:00)</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Notificaciones de seguimiento</h5>
                    <p>Notificaciones cuando alguien siga tus eventos</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>

        <!-- Información sobre Notificaciones -->
        <div class="notificaciones-card">
            <h3><i class="fa fa-info-circle"></i> Información sobre Notificaciones</h3>
            
            <div class="info-card">
                <h4>¿Por qué recibir notificaciones?</h4>
                <p>Las notificaciones te ayudan a estar al día con los eventos más relevantes, mantenerte conectado con otros usuarios y no perderte de las mejores ofertas y promociones.</p>
            </div>
            
            <div class="info-card">
                <h4>Tipos de Notificaciones</h4>
                <p>• <strong>Email:</strong> Notificaciones importantes y resúmenes</p>
                <p>• <strong>Push:</strong> Notificaciones instantáneas en tiempo real</p>
                <p>• <strong>In-app:</strong> Notificaciones dentro de la aplicación</p>
            </div>
            
            <div class="info-card">
                <h4>Control Total</h4>
                <p>• Puedes activar o desactivar cada tipo de notificación</p>
                <p>• Configurar horarios para recibir notificaciones</p>
                <p>• Personalizar qué eventos te interesan</p>
                <p>• Cambiar configuraciones en cualquier momento</p>
            </div>
        </div>

        <!-- Botón de Guardar -->
        <div class="notificaciones-card">
            <form method="POST" action="notificaciones.php">
                <input type="hidden" name="action" value="actualizar_notificaciones">
                <button type="submit" class="btn-guardar">
                    <i class="fa fa-save"></i> Guardar Configuraciones
                </button>
            </form>
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
