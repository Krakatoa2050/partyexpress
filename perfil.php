<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html?redirect=' . urlencode('perfil.php'));
    exit();
}

// Obtener información del usuario
$usuario = null;
$solicitudes_count = 0;
$favoritos_count = 0;

try {
    $conn = obtenerConexion();
    
    // Obtener datos del usuario - ahora incluyendo foto_perfil
    $stmt = $conn->prepare('SELECT id, nombre, usuario, email, foto_perfil, fecha_registro, ultimo_acceso FROM usuarios WHERE id = ? AND activo = TRUE');
    $stmt->bind_param('i', $_SESSION['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
    }
    
    $stmt->close();
    
    // Contar solicitudes del usuario
    $stmt = $conn->prepare('SELECT COUNT(*) as total FROM solicitudes_eventos WHERE usuario_id = ?');
    $stmt->bind_param('i', $_SESSION['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $solicitudes_count = $result->fetch_assoc()['total'];
    $stmt->close();
    
    // Contar favoritos (simulado por ahora)
    $favoritos_count = 5;
    
    $conn->close();
} catch (Exception $e) {
    $error = 'Error al cargar el perfil: ' . $e->getMessage();
    // Debug: mostrar información de la sesión
    error_log('Error en perfil.php: ' . $e->getMessage());
    error_log('Usuario ID en sesión: ' . ($_SESSION['usuario_id'] ?? 'No definido'));
}

// Procesar cambio de foto de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cambiar_foto') {
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $archivo = $_FILES['foto_perfil'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($extension, $extensiones_permitidas)) {
            $nombre_archivo = 'perfil_' . $_SESSION['usuario_id'] . '_' . time() . '.' . $extension;
            $ruta_destino = 'uploads/perfiles/' . $nombre_archivo;
            
            // Crear directorio si no existe
            if (!is_dir('uploads/perfiles')) {
                mkdir('uploads/perfiles', 0777, true);
            }
            
            if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                try {
                    $conn = obtenerConexion();
                    $stmt = $conn->prepare('UPDATE usuarios SET foto_perfil = ? WHERE id = ?');
                    $stmt->bind_param('si', $nombre_archivo, $_SESSION['usuario_id']);
                    
                    if ($stmt->execute()) {
                        $mensaje_exito = 'Foto de perfil actualizada correctamente';
                        $usuario['foto_perfil'] = $nombre_archivo;
                    } else {
                        $mensaje_error = 'Error al actualizar la foto en la base de datos';
                    }
                    
                    $stmt->close();
                    $conn->close();
                } catch (Exception $e) {
                    $mensaje_error = 'Error al actualizar la foto: ' . $e->getMessage();
                }
            } else {
                $mensaje_error = 'Error al subir el archivo';
            }
        } else {
            $mensaje_error = 'Formato de archivo no permitido. Use JPG, PNG o GIF';
        }
    } else {
        $mensaje_error = 'Por favor seleccione una imagen';
    }
}

// Procesar actualización de información personal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'actualizar_perfil') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if (!empty($nombre) && !empty($email)) {
        try {
            $conn = obtenerConexion();
            $stmt = $conn->prepare('UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?');
            $stmt->bind_param('ssi', $nombre, $email, $_SESSION['usuario_id']);
            
            if ($stmt->execute()) {
                $mensaje_exito = 'Perfil actualizado correctamente';
                $usuario['nombre'] = $nombre;
                $usuario['email'] = $email;
                $_SESSION['nombre'] = $nombre;
            } else {
                $mensaje_error = 'Error al actualizar el perfil';
            }
            
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            $mensaje_error = 'Error al actualizar el perfil: ' . $e->getMessage();
        }
    } else {
        $mensaje_error = 'Por favor complete todos los campos';
    }
}

function formatearFecha($fecha) {
    return date('d/m/Y H:i', strtotime($fecha));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .perfil-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .perfil-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .perfil-title {
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
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-volver:hover {
            background: rgba(162,89,247,0.3);
            transform: translateY(-2px);
        }
        
        .perfil-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .perfil-sidebar {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(162,89,247,0.2);
            height: fit-content;
        }
        
        .perfil-foto-container {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .perfil-foto {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(162,89,247,0.3);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin-bottom: 15px;
            background: linear-gradient(135deg, #a259f7 0%, #7209b7 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            font-weight: bold;
            margin: 0 auto 15px auto;
        }
        
        .perfil-foto img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .btn-cambiar-foto {
            background: linear-gradient(90deg, #a259f7 60%, #7209b7 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-cambiar-foto:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(162,89,247,0.3);
        }
        
        .perfil-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .perfil-stat {
            text-align: center;
            padding: 15px;
            background: rgba(162,89,247,0.1);
            border-radius: 15px;
            border: 1px solid rgba(162,89,247,0.2);
        }
        
        .perfil-stat-number {
            color: #a259f7;
            font-size: 1.5rem;
            font-weight: bold;
            display: block;
        }
        
        .perfil-stat-label {
            color: rgba(255,255,255,0.7);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .perfil-info {
            color: #ccc;
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        .perfil-info strong {
            color: #a259f7;
        }
        
        .perfil-main {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(162,89,247,0.2);
        }
        
        .seccion-titulo {
            color: #a259f7;
            font-size: 1.5rem;
            margin-bottom: 20px;
            font-weight: 600;
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
        
        .form-input:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-actualizar {
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
        
        .btn-actualizar:hover {
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
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: linear-gradient(135deg, #2D1950 0%, #231942 100%);
            margin: 5% auto;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            border: 1px solid rgba(162,89,247,0.3);
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-title {
            color: #a259f7;
            font-size: 1.3rem;
            margin: 0;
        }
        
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .close:hover {
            color: #a259f7;
        }
        
        .file-input-container {
            position: relative;
            margin-bottom: 20px;
        }
        
        .file-input {
            width: 100%;
            padding: 12px;
            border: 2px dashed rgba(162,89,247,0.3);
            border-radius: 10px;
            background: rgba(162,89,247,0.1);
            color: #fff;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .file-input:hover {
            border-color: #a259f7;
            background: rgba(162,89,247,0.2);
        }
        
        .file-input:focus {
            outline: none;
            border-color: #a259f7;
        }
        
        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .btn-cancelar {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-cancelar:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .btn-subir {
            background: linear-gradient(90deg, #a259f7 60%, #7209b7 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-subir:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(162,89,247,0.3);
        }
        
        @media (max-width: 768px) {
            .perfil-grid {
                grid-template-columns: 1fr;
            }
            
            .perfil-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .perfil-stats {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                margin: 10% auto;
                width: 95%;
                padding: 20px;
            }
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
                        <?php 
                        // Obtener foto de perfil del usuario
                        $foto_perfil = null;
                        try {
                            $conn = obtenerConexion();
                            $stmt = $conn->prepare('SELECT foto_perfil FROM usuarios WHERE id = ?');
                            $stmt->bind_param('i', $_SESSION['usuario_id']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result->num_rows > 0) {
                                $foto_perfil = $result->fetch_assoc()['foto_perfil'];
                            }
                            $stmt->close();
                            $conn->close();
                        } catch (Exception $e) {
                            // Silenciar errores
                        }
                        
                        if ($foto_perfil): ?>
                            <img src="uploads/perfiles/<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de perfil">
                        <?php else: ?>
                            <?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <span>Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                </div>
                <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
                <div class="dropdown-menu" id="dropdownMenu">
                    <!-- Header con información del usuario -->
                    <div class="menu-header">
                        <div class="menu-user-info">
                            <div class="menu-user-avatar">
                                <?php if ($foto_perfil): ?>
                                    <img src="uploads/perfiles/<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de perfil">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <div class="menu-user-details">
                                <h4><?php echo htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['usuario']); ?></h4>
                                <p><?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
                            </div>
                        </div>
                        <div class="menu-stats">
                            <div class="menu-stat">
                                <span class="menu-stat-number"><?php echo $solicitudes_count; ?></span>
                                <span class="menu-stat-label">Eventos</span>
                            </div>
                            <div class="menu-stat">
                                <span class="menu-stat-number">12</span>
                                <span class="menu-stat-label">Días</span>
                            </div>
                            <div class="menu-stat">
                                <span class="menu-stat-number"><?php echo $favoritos_count; ?></span>
                                <span class="menu-stat-label">Favoritos</span>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de notificaciones -->
                    <div class="menu-notifications">
                        <div class="menu-notifications-header">
                            <h5 class="menu-notifications-title">Notificaciones</h5>
                            <div class="notification-badge">2</div>
                        </div>
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class="fa fa-bell"></i>
                            </div>
                            <div class="notification-content">
                                <h5>Nueva solicitud aprobada</h5>
                                <p>Tu evento "Fiesta de Cumpleaños" fue aprobado</p>
                            </div>
                        </div>
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class="fa fa-heart"></i>
                            </div>
                            <div class="notification-content">
                                <h5>Nuevo lugar disponible</h5>
                                <p>Parque Acuático Aqualandia ahora disponible</p>
                            </div>
                        </div>
                    </div>

                    <!-- Enlaces del menú -->
                    <a href="mis_solicitudes.php" class="menu-item">
                        <i class="fa fa-calendar-check"></i> Mis Solicitudes
                        <span class="menu-item-badge"><?php echo $solicitudes_count; ?></span>
                    </a>
                    <a href="perfil.php" class="menu-item">
                        <i class="fa fa-user"></i> Mi Perfil
                    </a>
                    <a href="favoritos.php" class="menu-item">
                        <i class="fa fa-heart"></i> Favoritos
                        <span class="menu-item-badge"><?php echo $favoritos_count; ?></span>
                    </a>
                    <a href="configuracion.php" class="menu-item">
                        <i class="fa fa-cog"></i> Configuración
                    </a>
                    
                    <!-- Botón de cerrar sesión -->
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

    <div class="perfil-container">
        <header class="perfil-header">
            <h1 class="perfil-title">Mi Perfil</h1>
            <a href="index.php" class="btn-volver">
                <i class="fa fa-home"></i> Volver al inicio
            </a>
        </header>

        <?php if (isset($mensaje_exito)): ?>
            <div class="mensaje mensaje-exito">
                <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($mensaje_exito); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($mensaje_error)): ?>
            <div class="mensaje mensaje-error">
                <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($mensaje_error); ?>
            </div>
        <?php endif; ?>

        <?php if ($usuario): ?>
            <div class="perfil-grid">
                <!-- Sidebar con foto y estadísticas -->
                <div class="perfil-sidebar">
                    <div class="perfil-foto-container">
                        <div class="perfil-foto">
                            <?php if ($usuario['foto_perfil']): ?>
                                <img src="uploads/perfiles/<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Foto de perfil">
                            <?php else: ?>
                                <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <button class="btn-cambiar-foto" onclick="abrirModalFoto()">
                            <i class="fa fa-camera"></i> Cambiar foto
                        </button>
                    </div>

                    <div class="perfil-stats">
                        <div class="perfil-stat">
                            <span class="perfil-stat-number"><?php echo $solicitudes_count; ?></span>
                            <span class="perfil-stat-label">Eventos</span>
                        </div>
                        <div class="perfil-stat">
                            <span class="perfil-stat-number"><?php echo $favoritos_count; ?></span>
                            <span class="perfil-stat-label">Favoritos</span>
                        </div>
                    </div>

                    <div class="perfil-info">
                        <p><strong>Usuario:</strong> <?php echo htmlspecialchars($usuario['usuario']); ?></p>
                        <p><strong>Miembro desde:</strong> <?php echo formatearFecha($usuario['fecha_registro']); ?></p>
                        <p><strong>Último acceso:</strong> <?php echo formatearFecha($usuario['ultimo_acceso']); ?></p>
                    </div>
                </div>

                <!-- Contenido principal -->
                <div class="perfil-main">
                    <h2 class="seccion-titulo">Información Personal</h2>
                    
                    <form method="POST" action="perfil.php">
                        <input type="hidden" name="action" value="actualizar_perfil">
                        
                        <div class="form-grupo">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" name="nombre" class="form-input" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                        </div>
                        
                        <div class="form-grupo">
                            <label class="form-label">Nombre de usuario</label>
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($usuario['usuario']); ?>" disabled>
                        </div>
                        
                        <div class="form-grupo">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                        </div>
                        
                        <button type="submit" class="btn-actualizar">
                            <i class="fa fa-save"></i> Actualizar perfil
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="mensaje mensaje-error">
                <i class="fa fa-exclamation-circle"></i> No se pudo cargar la información del perfil
                <?php if (isset($error)): ?>
                    <br><small>Error: <?php echo htmlspecialchars($error); ?></small>
                <?php endif; ?>
                <br><small>Usuario ID: <?php echo htmlspecialchars($_SESSION['usuario_id'] ?? 'No definido'); ?></small>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para cambiar foto de perfil -->
    <div id="modalFoto" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Cambiar foto de perfil</h3>
                <span class="close" onclick="cerrarModalFoto()">&times;</span>
            </div>
            
            <form method="POST" action="perfil.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="cambiar_foto">
                
                <div class="file-input-container">
                    <input type="file" name="foto_perfil" class="file-input" accept="image/*" required>
                </div>
                
                <p style="color: #ccc; font-size: 0.9rem; margin-bottom: 20px;">
                    <i class="fa fa-info-circle"></i> Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB
                </p>
                
                <div class="modal-buttons">
                    <button type="button" class="btn-cancelar" onclick="cerrarModalFoto()">Cancelar</button>
                    <button type="submit" class="btn-subir">
                        <i class="fa fa-upload"></i> Subir foto
                    </button>
                </div>
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

            // Cerrar menú con tecla Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && dropdownMenu.classList.contains('show')) {
                    dropdownMenu.classList.remove('show');
                    menuToggle.classList.remove('active');
                }
            });

            // Animación suave al hacer scroll
            let scrollTimeout;
            window.addEventListener('scroll', function() {
                if (dropdownMenu.classList.contains('show')) {
                    clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(function() {
                        dropdownMenu.classList.remove('show');
                        menuToggle.classList.remove('active');
                    }, 100);
                }
            });
        }

        // Funciones para el modal de foto
        function abrirModalFoto() {
            document.getElementById('modalFoto').style.display = 'block';
        }

        function cerrarModalFoto() {
            document.getElementById('modalFoto').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('modalFoto');
            if (event.target === modal) {
                cerrarModalFoto();
            }
        }

        // Cerrar modal con Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModalFoto();
            }
        });
    </script>
</body>
</html>
