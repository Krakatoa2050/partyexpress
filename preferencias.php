<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html?redirect=' . urlencode('preferencias.php'));
    exit();
}

$mensaje_exito = '';
$mensaje_error = '';

// Obtener información del usuario
$usuario = null;
try {
    $conn = obtenerConexion();
    $stmt = $conn->prepare('SELECT id, nombre, usuario, email, idioma, zona_horaria FROM usuarios WHERE id = ? AND activo = TRUE');
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

// Procesar cambios de preferencias
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'actualizar_preferencias') {
    $idioma = $_POST['idioma'] ?? 'es';
    $zona_horaria = $_POST['zona_horaria'] ?? 'America/Mexico_City';
    
    try {
        $conn = obtenerConexion();
        $stmt = $conn->prepare('UPDATE usuarios SET idioma = ?, zona_horaria = ? WHERE id = ?');
        $stmt->bind_param('ssi', $idioma, $zona_horaria, $_SESSION['usuario_id']);
        
        if ($stmt->execute()) {
            $mensaje_exito = 'Preferencias actualizadas correctamente';
            $usuario['idioma'] = $idioma;
            $usuario['zona_horaria'] = $zona_horaria;
        } else {
            $mensaje_error = 'Error al actualizar las preferencias';
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
    <title>Preferencias - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .preferencias-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .preferencias-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .preferencias-title {
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
        
        .preferencias-card {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(162,89,247,0.2);
            margin-bottom: 25px;
        }
        
        .preferencias-card h3 {
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
        
        .form-select {
            width: 100%;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(162,89,247,0.3);
            border-radius: 10px;
            padding: 12px 15px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-select:focus {
            outline: none;
            border-color: #a259f7;
            background: rgba(162,89,247,0.1);
        }
        

        
        .language-option {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .language-flag {
            width: 24px;
            height: 16px;
            border-radius: 2px;
            background-size: cover;
        }
        
        .flag-es {
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 750 500"><rect width="750" height="500" fill="%23c60b1e"/><rect width="750" height="250" y="125" fill="%23ffc400"/></svg>');
        }
        
        .flag-en {
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 750 500"><rect width="750" height="500" fill="%23012169"/><rect width="750" height="38.5" y="38.5" fill="%23ffffff"/><rect width="750" height="38.5" y="115.5" fill="%23ffffff"/><rect width="750" height="38.5" y="192.5" fill="%23ffffff"/><rect width="750" height="38.5" y="269.5" fill="%23ffffff"/><rect width="750" height="38.5" y="346.5" fill="%23ffffff"/><rect width="750" height="38.5" y="423.5" fill="%23ffffff"/><rect width="300" height="269.5" fill="%23c8102e"/></svg>');
        }
        
        .flag-pt {
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 750 500"><rect width="750" height="500" fill="%23009262"/><rect width="750" height="250" y="125" fill="%23ffed00"/><circle cx="375" cy="250" r="100" fill="%23002776"/></svg>');
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

    <div class="preferencias-container">
        <header class="preferencias-header">
            <h1 class="preferencias-title">Preferencias</h1>
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

        <!-- Apariencia -->
        <div class="preferencias-card">
            <h3><i class="fa fa-palette"></i> Apariencia</h3>
            <p style="color: #ccc; margin-bottom: 20px;">Personaliza la apariencia de PartyExpress según tus preferencias.</p>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Animaciones</h5>
                    <p>Mostrar animaciones y transiciones suaves</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Efectos de hover</h5>
                    <p>Mostrar efectos al pasar el mouse sobre elementos</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>

        <!-- Idioma y Región -->
        <div class="preferencias-card">
            <h3><i class="fa fa-globe"></i> Idioma y Región</h3>
            <p style="color: #ccc; margin-bottom: 20px;">Configura el idioma y la región para personalizar tu experiencia.</p>
            
            <div class="form-grupo">
                <label class="form-label">Idioma</label>
                <div class="language-option">
                    <input type="radio" name="idioma" id="idioma-es" value="es" <?php echo ($usuario['idioma'] ?? 'es') === 'es' ? 'checked' : ''; ?>>
                    <div class="language-flag flag-es"></div>
                    <label for="idioma-es">Español</label>
                </div>
                <div class="language-option">
                    <input type="radio" name="idioma" id="idioma-en" value="en" <?php echo ($usuario['idioma'] ?? 'es') === 'en' ? 'checked' : ''; ?>>
                    <div class="language-flag flag-en"></div>
                    <label for="idioma-en">English</label>
                </div>
                <div class="language-option">
                    <input type="radio" name="idioma" id="idioma-fr" value="fr" <?php echo ($usuario['idioma'] ?? 'es') === 'fr' ? 'checked' : ''; ?>>
                    <div class="language-flag flag-fr"></div>
                    <label for="idioma-fr">Français</label>
                </div>
            </div>
            
            <div class="form-grupo">
                <label class="form-label">Zona Horaria</label>
                <select name="zona_horaria" class="form-select">
                    <option value="America/Mexico_City" <?php echo ($usuario['zona_horaria'] ?? 'America/Mexico_City') === 'America/Mexico_City' ? 'selected' : ''; ?>>México (GMT-6)</option>
                    <option value="America/New_York" <?php echo ($usuario['zona_horaria'] ?? 'America/Mexico_City') === 'America/New_York' ? 'selected' : ''; ?>>Nueva York (GMT-5)</option>
                    <option value="Europe/Madrid" <?php echo ($usuario['zona_horaria'] ?? 'America/Mexico_City') === 'Europe/Madrid' ? 'selected' : ''; ?>>Madrid (GMT+1)</option>
                    <option value="America/Los_Angeles" <?php echo ($usuario['zona_horaria'] ?? 'America/Mexico_City') === 'America/Los_Angeles' ? 'selected' : ''; ?>>Los Ángeles (GMT-8)</option>
                </select>
            </div>
            
            <div class="form-grupo">
                <label class="form-label">Formato de Fecha</label>
                <select class="form-select">
                    <option value="dd/mm/yyyy" selected>DD/MM/YYYY</option>
                    <option value="mm/dd/yyyy">MM/DD/YYYY</option>
                    <option value="yyyy-mm-dd">YYYY-MM-DD</option>
                </select>
            </div>
        </div>

        <!-- Contenido -->
        <div class="preferencias-card">
            <h3><i class="fa fa-filter"></i> Contenido</h3>
            <p style="color: #ccc; margin-bottom: 20px;">Personaliza qué contenido quieres ver en la plataforma.</p>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Mostrar eventos privados</h5>
                    <p>Incluir eventos privados en las búsquedas (si tienes acceso)</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Filtro de edad</h5>
                    <p>Mostrar solo eventos apropiados para tu edad</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Contenido personalizado</h5>
                    <p>Mostrar eventos basados en tus intereses y actividad</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="form-grupo">
                <label class="form-label">Categorías de Interés</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                    <label style="display: flex; align-items: center; gap: 8px; color: #ccc;">
                        <input type="checkbox" checked> Cumpleaños
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; color: #ccc;">
                        <input type="checkbox" checked> Bodas
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; color: #ccc;">
                        <input type="checkbox" checked> Graduaciones
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; color: #ccc;">
                        <input type="checkbox" checked> Eventos Corporativos
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; color: #ccc;">
                        <input type="checkbox" checked> Fiestas Temáticas
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; color: #ccc;">
                        <input type="checkbox" checked> Baby Showers
                    </label>
                </div>
            </div>
        </div>

        <!-- Accesibilidad -->
        <div class="preferencias-card">
            <h3><i class="fa fa-universal-access"></i> Accesibilidad</h3>
            <p style="color: #ccc; margin-bottom: 20px;">Configuraciones para mejorar la accesibilidad de la plataforma.</p>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Alto contraste</h5>
                    <p>Usar colores de alto contraste para mejor visibilidad</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Texto más grande</h5>
                    <p>Aumentar el tamaño del texto en toda la plataforma</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Navegación por teclado</h5>
                    <p>Mejorar la navegación usando solo el teclado</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Descripciones de audio</h5>
                    <p>Proporcionar descripciones de audio para imágenes</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox">
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>

        <!-- Rendimiento -->
        <div class="preferencias-card">
            <h3><i class="fa fa-tachometer-alt"></i> Rendimiento</h3>
            <p style="color: #ccc; margin-bottom: 20px;">Optimiza el rendimiento de la plataforma según tu conexión.</p>
            
            <div class="form-grupo">
                <label class="form-label">Calidad de Imágenes</label>
                <select class="form-select">
                    <option value="auto" selected>Automática (Recomendado)</option>
                    <option value="high">Alta calidad</option>
                    <option value="medium">Calidad media</option>
                    <option value="low">Calidad baja (Conexión lenta)</option>
                </select>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Carga automática</h5>
                    <p>Cargar más contenido automáticamente al hacer scroll</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Precarga de imágenes</h5>
                    <p>Precargar imágenes para una experiencia más fluida</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>

        <!-- Información -->
        <div class="preferencias-card">
            <h3><i class="fa fa-info-circle"></i> Información</h3>
            
            <div class="info-card">
                <h4>¿Cómo funcionan las preferencias?</h4>
                <p>Las preferencias te permiten personalizar tu experiencia en PartyExpress. Los cambios se guardan automáticamente y se aplican inmediatamente. Puedes cambiar estas configuraciones en cualquier momento.</p>
            </div>
            
            <div class="info-card">
                <h4>Preferencias Recomendadas</h4>
                <p>• <strong>Animaciones:</strong> Mejoran la experiencia visual</p>
                <p>• <strong>Contenido personalizado:</strong> Te muestra eventos más relevantes</p>
                <p>• <strong>Calidad automática:</strong> Se adapta a tu conexión</p>
                <p>• <strong>Idioma local:</strong> Mejor experiencia en tu idioma preferido</p>
            </div>
        </div>

        <!-- Botón de Guardar -->
        <div class="preferencias-card">
            <form method="POST" action="preferencias.php">
                <input type="hidden" name="action" value="actualizar_preferencias">
                <button type="submit" class="btn-guardar">
                    <i class="fa fa-save"></i> Guardar Preferencias
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
