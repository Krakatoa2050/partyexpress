<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html?redirect=' . urlencode('privacidad.php'));
    exit();
}

$mensaje_exito = '';
$mensaje_error = '';

// Obtener información del usuario
$usuario = null;
try {
    $conn = obtenerConexion();
    $stmt = $conn->prepare('SELECT id, visibilidad_perfil, permitir_busqueda, compartir_datos FROM usuarios WHERE id = ? AND activo = TRUE');
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

// Procesar cambios de privacidad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'actualizar_privacidad') {
    $visibilidad_perfil = $_POST['visibilidad_perfil'] ?? 'publico';
    $permitir_busqueda = isset($_POST['permitir_busqueda']) ? '1' : '0';
    $compartir_datos = isset($_POST['compartir_datos']) ? '1' : '0';
    
    try {
        $conn = obtenerConexion();
        $stmt = $conn->prepare('UPDATE usuarios SET visibilidad_perfil = ?, permitir_busqueda = ?, compartir_datos = ? WHERE id = ?');
        $stmt->bind_param('sssi', $visibilidad_perfil, $permitir_busqueda, $compartir_datos, $_SESSION['usuario_id']);
        
        if ($stmt->execute()) {
            $mensaje_exito = 'Configuración de privacidad actualizada correctamente';
            $usuario['visibilidad_perfil'] = $visibilidad_perfil;
            $usuario['permitir_busqueda'] = $permitir_busqueda;
            $usuario['compartir_datos'] = $compartir_datos;
        } else {
            $mensaje_error = 'Error al actualizar la privacidad';
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
    <title>Privacidad - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .privacidad-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .privacidad-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .privacidad-title {
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
        
        .privacidad-card {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(162,89,247,0.2);
            margin-bottom: 25px;
        }
        
        .privacidad-card h3 {
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

    <div class="privacidad-container">
        <header class="privacidad-header">
            <h1 class="privacidad-title">Privacidad</h1>
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

        <!-- Configuraciones de Perfil -->
        <div class="privacidad-card">
            <h3><i class="fa fa-user"></i> Visibilidad del Perfil</h3>
            <p style="color: #ccc; margin-bottom: 20px;">Controla quién puede ver tu información personal.</p>
            
            <div class="form-grupo">
                <label class="form-label">Visibilidad del perfil</label>
                <select name="visibilidad_perfil" class="form-select">
                    <option value="publico" <?php echo ($usuario['visibilidad_perfil'] ?? 'publico') === 'publico' ? 'selected' : ''; ?>>Público</option>
                    <option value="amigos" <?php echo ($usuario['visibilidad_perfil'] ?? 'publico') === 'amigos' ? 'selected' : ''; ?>>Amigos</option>
                    <option value="privado" <?php echo ($usuario['visibilidad_perfil'] ?? 'publico') === 'privado' ? 'selected' : ''; ?>>Privado</option>
                </select>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Mostrar nombre completo</h5>
                    <p>Mostrar tu nombre completo en tu perfil público</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Mostrar email</h5>
                    <p>Permitir que otros usuarios vean tu email</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Mostrar foto de perfil</h5>
                    <p>Mostrar tu foto de perfil a otros usuarios</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>

        <!-- Configuraciones de Eventos -->
        <div class="privacidad-card">
            <h3><i class="fa fa-calendar"></i> Privacidad de Eventos</h3>
            <p style="color: #ccc; margin-bottom: 20px;">Controla la visibilidad de tus eventos.</p>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Eventos privados por defecto</h5>
                    <p>Crear eventos privados por defecto</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Permitir búsqueda</h5>
                    <p>Permitir que otros usuarios me encuentren</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="permitir_busqueda" value="1" <?php echo ($usuario['permitir_busqueda'] ?? '1') === '1' ? 'checked' : ''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Mostrar ubicación exacta</h5>
                    <p>Mostrar la ubicación exacta de tus eventos</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>

        <!-- Configuraciones de Datos -->
        <div class="privacidad-card">
            <h3><i class="fa fa-database"></i> Uso de Datos</h3>
            <p style="color: #ccc; margin-bottom: 20px;">Controla cómo se utilizan tus datos.</p>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Compartir datos</h5>
                    <p>Permitir compartir información con terceros</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="compartir_datos" value="1" <?php echo ($usuario['compartir_datos'] ?? '0') === '1' ? 'checked' : ''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Personalización de contenido</h5>
                    <p>Usar tus datos para personalizar el contenido</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Cookies de terceros</h5>
                    <p>Permitir cookies de servicios de terceros</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox">
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>

        <!-- Información sobre Privacidad -->
        <div class="privacidad-card">
            <h3><i class="fa fa-info-circle"></i> Información sobre Privacidad</h3>
            
            <div class="info-card">
                <h4>Tu Privacidad es Importante</h4>
                <p>En PartyExpress respetamos tu privacidad y te damos control total sobre tu información personal. Puedes cambiar estas configuraciones en cualquier momento.</p>
            </div>
            
            <div class="info-card">
                <h4>Datos que Recopilamos</h4>
                <p>• Información de perfil (nombre, email, foto)</p>
                <p>• Eventos que creas y en los que participas</p>
                <p>• Preferencias y configuraciones</p>
                <p>• Datos de uso para mejorar el servicio</p>
            </div>
            
            <div class="info-card">
                <h4>Protección de Datos</h4>
                <p>• Tus datos están protegidos con encriptación SSL</p>
                <p>• No compartimos tu información con terceros sin tu consentimiento</p>
                <p>• Puedes solicitar la eliminación de tus datos en cualquier momento</p>
                <p>• Cumplimos con las leyes de protección de datos vigentes</p>
            </div>
        </div>

        <!-- Botón de Guardar -->
        <div class="privacidad-card">
            <form method="POST" action="privacidad.php">
                <input type="hidden" name="action" value="actualizar_privacidad">
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
