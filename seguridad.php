<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html?redirect=' . urlencode('seguridad.php'));
    exit();
}

$mensaje_exito = '';
$mensaje_error = '';

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cambiar_password') {
    $password_actual = $_POST['password_actual'] ?? '';
    $password_nuevo = $_POST['password_nuevo'] ?? '';
    $password_confirmar = $_POST['password_confirmar'] ?? '';
    
    if (!empty($password_actual) && !empty($password_nuevo) && !empty($password_confirmar)) {
        if ($password_nuevo === $password_confirmar) {
            if (strlen($password_nuevo) >= 6) {
                try {
                    $conn = obtenerConexion();
                    
                    // Verificar contraseña actual
                    $stmt = $conn->prepare('SELECT contrasena FROM usuarios WHERE id = ?');
                    $stmt->bind_param('i', $_SESSION['usuario_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $usuario_db = $result->fetch_assoc();
                        
                        if (password_verify($password_actual, $usuario_db['contrasena'])) {
                            // Actualizar contraseña
                            $password_hash = password_hash($password_nuevo, PASSWORD_DEFAULT);
                            $stmt = $conn->prepare('UPDATE usuarios SET contrasena = ? WHERE id = ?');
                            $stmt->bind_param('si', $password_hash, $_SESSION['usuario_id']);
                            
                            if ($stmt->execute()) {
                                $mensaje_exito = 'Contraseña actualizada correctamente';
                            } else {
                                $mensaje_error = 'Error al actualizar la contraseña';
                            }
                        } else {
                            $mensaje_error = 'La contraseña actual es incorrecta';
                        }
                    }
                    
                    $stmt->close();
                    $conn->close();
                } catch (Exception $e) {
                    $mensaje_error = 'Error al cambiar la contraseña: ' . $e->getMessage();
                }
            } else {
                $mensaje_error = 'La nueva contraseña debe tener al menos 6 caracteres';
            }
        } else {
            $mensaje_error = 'Las contraseñas nuevas no coinciden';
        }
    } else {
        $mensaje_error = 'Por favor complete todos los campos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguridad - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .seguridad-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .seguridad-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .seguridad-title {
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
        
        .seguridad-card {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(162,89,247,0.2);
            margin-bottom: 25px;
        }
        
        .seguridad-card h3 {
            color: #a259f7;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        .form-input {
            width: 100%;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(162,89,247,0.3);
            border-radius: 10px;
            padding: 12px 15px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #a259f7;
            background: rgba(162,89,247,0.1);
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

    <div class="seguridad-container">
        <header class="seguridad-header">
            <h1 class="seguridad-title">Seguridad</h1>
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

        <!-- Cambio de Contraseña -->
        <div class="seguridad-card">
            <h3><i class="fa fa-key"></i> Cambiar Contraseña</h3>
            <p style="color: #ccc; margin-bottom: 20px;">Actualiza tu contraseña para mantener tu cuenta segura.</p>
            
            <form method="POST" action="seguridad.php">
                <input type="hidden" name="action" value="cambiar_password">
                
                <div class="form-grupo">
                    <label class="form-label">Contraseña actual</label>
                    <input type="password" name="password_actual" class="form-input" required>
                </div>
                
                <div class="form-grupo">
                    <label class="form-label">Nueva contraseña</label>
                    <input type="password" name="password_nuevo" class="form-input" required>
                </div>
                
                <div class="form-grupo">
                    <label class="form-label">Confirmar nueva contraseña</label>
                    <input type="password" name="password_confirmar" class="form-input" required>
                </div>
                
                <button type="submit" class="btn-guardar">
                    <i class="fa fa-save"></i> Cambiar contraseña
                </button>
            </form>
        </div>

        <!-- Configuraciones de Seguridad -->
        <div class="seguridad-card">
            <h3><i class="fa fa-shield-alt"></i> Configuraciones de Seguridad</h3>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Autenticación de dos factores</h5>
                    <p>Agrega una capa extra de seguridad a tu cuenta</p>
                </div>
                <a href="two_factor_auth.php" class="btn-submit" style="text-decoration: none; display: inline-block; padding: 8px 16px; font-size: 0.9rem;">
                    <i class="fa fa-shield"></i> Configurar 2FA
                </a>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Notificaciones de inicio de sesión</h5>
                    <p>Recibe alertas cuando inicies sesión desde un nuevo dispositivo</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Sesión automática</h5>
                    <p>Mantener sesión iniciada en este dispositivo</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <div class="configuracion-item">
                <div class="configuracion-item-info">
                    <h5>Verificación por email</h5>
                    <p>Confirmar cambios importantes por email</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>

        <!-- Recomendaciones de Seguridad -->
        <div class="seguridad-card">
            <h3><i class="fa fa-lightbulb"></i> Recomendaciones de Seguridad</h3>
            
            <div class="info-card">
                <h4>Contraseña Fuerte</h4>
                <p>• Usa al menos 8 caracteres</p>
                <p>• Incluye letras mayúsculas, minúsculas, números y símbolos</p>
                <p>• No uses información personal como fecha de nacimiento</p>
                <p>• Cambia tu contraseña regularmente</p>
            </div>
            
            <div class="info-card">
                <h4>Protección de Cuenta</h4>
                <p>• No compartas tu contraseña con nadie</p>
                <p>• Cierra sesión en dispositivos públicos</p>
                <p>• Mantén tu información de contacto actualizada</p>
                <p>• Revisa regularmente la actividad de tu cuenta</p>
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
