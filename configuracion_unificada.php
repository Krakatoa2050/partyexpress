<?php
session_start();
require_once 'conexion.php';
require_once 'actualizar_db_configuracion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html?redirect=' . urlencode('configuracion_unificada.php'));
    exit();
}

$mensaje_exito = '';
$mensaje_error = '';

// Actualizar automáticamente la base de datos si es necesario
$columnas_agregadas = actualizarBaseDatosConfiguracion();
if ($columnas_agregadas !== false && !empty($columnas_agregadas)) {
    $mensaje_exito = '✅ Base de datos actualizada automáticamente. Se agregaron las columnas: ' . implode(', ', $columnas_agregadas);
}

// Obtener información del usuario
$usuario = null;
try {
    $conn = obtenerConexion();
    $stmt = $conn->prepare('SELECT id, nombre, usuario, email, foto_perfil, fecha_registro, 
                           idioma, zona_horaria, visibilidad_perfil, permitir_busqueda, 
                           compartir_datos, email_notificaciones, push_notificaciones, 
                           recordatorios_eventos, nuevos_favoritos 
                           FROM usuarios WHERE id = ? AND activo = TRUE');
    $stmt->bind_param('i', $_SESSION['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
    }
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $error = 'Error al cargar datos del usuario: ' . $e->getMessage();
}

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'actualizar_perfil':
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
                    } else {
                        $mensaje_error = 'Error al actualizar el perfil';
                    }
                    $stmt->close();
                    $conn->close();
                } catch (Exception $e) {
                    $mensaje_error = 'Error: ' . $e->getMessage();
                }
            } else {
                $mensaje_error = 'Por favor complete todos los campos';
            }
            break;
            
        case 'cambiar_password':
            $password_actual = $_POST['password_actual'] ?? '';
            $password_nuevo = $_POST['password_nuevo'] ?? '';
            $password_confirmar = $_POST['password_confirmar'] ?? '';
            
            if (!empty($password_actual) && !empty($password_nuevo) && !empty($password_confirmar)) {
                if ($password_nuevo === $password_confirmar) {
                    if (strlen($password_nuevo) >= 6) {
                        try {
                            $conn = obtenerConexion();
                            $stmt = $conn->prepare('SELECT contrasena FROM usuarios WHERE id = ?');
                            $stmt->bind_param('i', $_SESSION['usuario_id']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                $usuario_db = $result->fetch_assoc();
                                
                                if (password_verify($password_actual, $usuario_db['contrasena'])) {
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
                            $mensaje_error = 'Error: ' . $e->getMessage();
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
            break;
            
        case 'actualizar_preferencias':
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
            break;
            
        case 'actualizar_privacidad':
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
            break;
            
        case 'actualizar_notificaciones':
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
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .config-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            min-height: 100vh;
        }
        
        .config-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding: 20px 0;
            border-bottom: 2px solid rgba(162,89,247,0.2);
        }
        
        .config-title {
            color: #a259f7;
            font-size: 2.5rem;
            margin: 0;
            font-weight: 700;
        }
        
        .btn-volver {
            background: linear-gradient(135deg, #a259f7 0%, #7209b7 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(162,89,247,0.3);
        }
        
        .config-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }
        
        .config-sidebar {
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            border-radius: 25px;
            padding: 30px;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(162,89,247,0.2);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .config-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .config-nav-item {
            margin-bottom: 10px;
        }
        
        .config-nav-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            border-radius: 15px;
            text-decoration: none;
            color: #ccc;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .config-nav-link:hover,
        .config-nav-link.active {
            background: linear-gradient(135deg, #a259f7 0%, #7209b7 100%);
            color: white;
            transform: translateX(5px);
        }
        
        .config-content {
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            border-radius: 25px;
            padding: 40px;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(162,89,247,0.2);
            min-height: 600px;
        }
        
        .config-section {
            display: none;
        }
        
        .config-section.active {
            display: block;
        }
        
        .section-title {
            color: #a259f7;
            font-size: 2rem;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            color: #a259f7;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .form-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid rgba(162,89,247,0.3);
            border-radius: 15px;
            background: rgba(255,255,255,0.1);
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #a259f7;
            box-shadow: 0 0 20px rgba(162,89,247,0.3);
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #a259f7 0%, #7209b7 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid rgba(40, 167, 69, 0.4);
            color: #28a745;
        }
        
        .alert-error {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.4);
            color: #dc3545;
        }
        
        .profile-info {
            background: rgba(162,89,247,0.1);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(162,89,247,0.2);
        }
        
        .info-label {
            color: #a259f7;
            font-weight: 600;
        }
        
        .info-value {
            color: #fff;
        }
        
        .security-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .security-card {
            background: rgba(162,89,247,0.1);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(162,89,247,0.2);
        }
        
        .security-card h4 {
            color: #a259f7;
            margin-bottom: 15px;
        }
        
        .form-input[type="checkbox"] {
            width: auto;
            margin-right: 15px;
            transform: scale(1.3);
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(162,89,247,0.5);
            border-radius: 4px;
            background: rgba(255,255,255,0.1);
            position: relative;
            transition: all 0.3s ease;
        }
        
        .form-input[type="checkbox"]:checked {
            background: linear-gradient(135deg, #a259f7 0%, #7209b7 100%);
            border-color: #a259f7;
        }
        
        /* Checkboxes en tema claro */
        .tema-claro .form-input[type="checkbox"] {
            border: 2px solid rgba(0,0,0,0.3);
            background: rgba(255,255,255,0.8);
        }
        
        .tema-claro .form-input[type="checkbox"]:checked {
            background: linear-gradient(135deg, #a259f7 0%, #7209b7 100%);
            border-color: #a259f7;
        }
        
        .tema-claro .form-input[type="checkbox"]:hover {
            border-color: #a259f7;
            box-shadow: 0 0 10px rgba(162,89,247,0.3);
        }
        
        /* Checkboxes en tema oscuro */
        .tema-oscuro .form-input[type="checkbox"] {
            border: 2px solid rgba(162,89,247,0.5);
            background: rgba(255,255,255,0.1);
        }
        
        .tema-oscuro .form-input[type="checkbox"]:checked {
            background: linear-gradient(135deg, #a259f7 0%, #7209b7 100%);
            border-color: #a259f7;
        }
        
        .tema-oscuro .form-input[type="checkbox"]:hover {
            border-color: #a259f7;
            box-shadow: 0 0 10px rgba(162,89,247,0.3);
        }
        
        .form-input[type="checkbox"]:checked::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
        
        .form-input[type="checkbox"]:hover {
            border-color: #a259f7;
            box-shadow: 0 0 10px rgba(162,89,247,0.3);
        }
        
        .form-input[type="checkbox"] + label {
            color: #ccc;
            cursor: pointer;
            font-size: 1rem;
            line-height: 1.5;
            user-select: none;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid rgba(162,89,247,0.1);
        }
        
        .checkbox-group:last-child {
            border-bottom: none;
        }
        
        .checkbox-label {
            flex: 1;
        }
        
        .checkbox-title {
            color: #a259f7;
            font-weight: 600;
            margin-bottom: 5px;
            display: block;
        }
        
        .checkbox-description {
            color: #999;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .settings-container {
            background: rgba(162,89,247,0.05);
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
            border: 1px solid rgba(162,89,247,0.1);
        }
        
        .settings-section {
            margin-bottom: 30px;
        }
        
        .settings-section:last-child {
            margin-bottom: 0;
        }
        

        
        /* Notificaciones flotantes */
        .notificacion-flotante {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #a259f7 0%, #7209b7 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(162,89,247,0.3);
            z-index: 10000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }
        
        .notificacion-flotante.mostrar {
            transform: translateX(0);
        }
        
        .notificacion-flotante i {
            font-size: 1.2rem;
        }
        
        .notificacion-error {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            box-shadow: 0 4px 20px rgba(220,53,69,0.3);
        }
        
        .form-input[type="select"] {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23a259f7' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
        
        /* Estilos para las opciones del select */
        .form-input option {
            background-color: #1a1a1a;
            color: #fff;
            padding: 10px;
            border: none;
        }
        
        .form-input option:hover {
            background-color: #a259f7;
            color: white;
        }
        
        .form-input option:checked {
            background-color: #a259f7;
            color: white;
        }
        
        /* Estilos específicos para el select */
        select.form-input {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
            border: 2px solid rgba(162,89,247,0.3);
            border-radius: 15px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        select.form-input:focus {
            outline: none;
            border-color: #a259f7;
            box-shadow: 0 0 20px rgba(162,89,247,0.3);
        }
        
        select.form-input:hover {
            border-color: #a259f7;
            background-color: rgba(255,255,255,0.15);
        }
        
        /* Estilos para el dropdown abierto */
        select.form-input:focus option {
            background-color: #2a2a2a;
            color: #fff;
            padding: 12px 15px;
            margin: 2px 0;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        select.form-input:focus option:hover {
            background-color: #a259f7;
            color: white;
            transform: translateX(5px);
        }
        
        select.form-input:focus option:checked {
            background: linear-gradient(135deg, #a259f7 0%, #7209b7 100%);
            color: white;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .config-layout {
                grid-template-columns: 1fr;
            }
            
            .config-sidebar {
                position: static;
            }
            
            .config-nav {
                display: flex;
                overflow-x: auto;
                gap: 10px;
            }
            
            .config-nav-item {
                margin-bottom: 0;
                flex-shrink: 0;
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

    <div class="config-container">
        <header class="config-header">
            <h1 class="config-title">
                <i class="fa fa-cogs"></i> Configuración
            </h1>
            <a href="index.php" class="btn-volver">
                <i class="fa fa-home"></i> Volver al inicio
            </a>
        </header>

        <div class="config-layout">
            <aside class="config-sidebar">
                <nav class="config-nav">
                    <div class="config-nav-item">
                        <a href="#cuenta" class="config-nav-link active" data-section="cuenta">
                            <i class="fa fa-user-circle"></i>
                            <span>Cuenta</span>
                        </a>
                    </div>
                    <div class="config-nav-item">
                        <a href="#seguridad" class="config-nav-link" data-section="seguridad">
                            <i class="fa fa-shield-alt"></i>
                            <span>Seguridad</span>
                        </a>
                    </div>
                    <div class="config-nav-item">
                        <a href="#privacidad" class="config-nav-link" data-section="privacidad">
                            <i class="fa fa-lock"></i>
                            <span>Privacidad</span>
                        </a>
                    </div>
                    <div class="config-nav-item">
                        <a href="#notificaciones" class="config-nav-link" data-section="notificaciones">
                            <i class="fa fa-bell"></i>
                            <span>Notificaciones</span>
                        </a>
                    </div>
                    <div class="config-nav-item">
                        <a href="#preferencias" class="config-nav-link" data-section="preferencias">
                            <i class="fa fa-sliders-h"></i>
                            <span>Preferencias</span>
                        </a>
                    </div>
                    <div class="config-nav-item">
                        <a href="#ayuda" class="config-nav-link" data-section="ayuda">
                            <i class="fa fa-question-circle"></i>
                            <span>Ayuda</span>
                        </a>
                    </div>
                    <div class="config-nav-item">
                        <a href="#soporte" class="config-nav-link" data-section="soporte">
                            <i class="fa fa-headset"></i>
                            <span>Soporte</span>
                        </a>
                    </div>
                    <div class="config-nav-item">
                        <a href="#acerca" class="config-nav-link" data-section="acerca">
                            <i class="fa fa-info-circle"></i>
                            <span>Acerca de</span>
                        </a>
                    </div>
                </nav>
            </aside>

            <main class="config-content">
                <?php if ($mensaje_exito): ?>
                    <div class="alert alert-success">
                        <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($mensaje_exito); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($mensaje_error): ?>
                    <div class="alert alert-error">
                        <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($mensaje_error); ?>
                    </div>
                <?php endif; ?>



                <!-- Sección Cuenta -->
                <section id="cuenta" class="config-section active">
                    <h2 class="section-title">
                        <i class="fa fa-user-circle"></i> Mi Cuenta
                    </h2>
                    
                    <div class="profile-info">
                        <div class="info-item">
                            <span class="info-label">Usuario:</span>
                            <span class="info-value"><?php echo htmlspecialchars($usuario['usuario'] ?? ''); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($usuario['email'] ?? ''); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Fecha de registro:</span>
                            <span class="info-value"><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'] ?? '')); ?></span>
                        </div>
                    </div>

                    <form method="POST" action="">
                        <input type="hidden" name="action" value="actualizar_perfil">
                        
                        <div class="form-group">
                            <label for="nombre" class="form-label">Nombre completo</label>
                            <input type="text" id="nombre" name="nombre" class="form-input" 
                                   value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-input" 
                                   value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" required>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <i class="fa fa-save"></i> Actualizar perfil
                        </button>
                    </form>
                </section>

                <!-- Sección Seguridad -->
                <section id="seguridad" class="config-section">
                    <h2 class="section-title">
                        <i class="fa fa-shield-alt"></i> Seguridad
                    </h2>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="cambiar_password">
                        
                        <div class="form-group">
                            <label for="password_actual" class="form-label">Contraseña actual</label>
                            <input type="password" id="password_actual" name="password_actual" class="form-input" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password_nuevo" class="form-label">Nueva contraseña</label>
                            <input type="password" id="password_nuevo" name="password_nuevo" class="form-input" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirmar" class="form-label">Confirmar nueva contraseña</label>
                            <input type="password" id="password_confirmar" name="password_confirmar" class="form-input" required>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <i class="fa fa-key"></i> Cambiar contraseña
                        </button>
                    </form>

                    <div class="security-options">
                        <div class="security-card">
                            <h4><i class="fa fa-mobile-alt"></i> Autenticación de dos factores</h4>
                            <p>Activa la verificación en dos pasos para mayor seguridad</p>
                            <a href="two_factor_auth.php" class="btn-submit" style="text-decoration: none; display: inline-block;">
                                <i class="fa fa-shield"></i> Configurar 2FA
                            </a>
                        </div>
                        
                        <div class="security-card">
                            <h4><i class="fa fa-history"></i> Historial de sesiones</h4>
                            <p>Revisa y gestiona las sesiones activas</p>
                            <button class="btn-submit">
                                <i class="fa fa-eye"></i> Ver historial
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Sección Privacidad -->
                <section id="privacidad" class="config-section">
                    <h2 class="section-title">
                        <i class="fa fa-lock"></i> Privacidad
                    </h2>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="actualizar_privacidad">
                        
                        <div class="settings-container">
                            <div class="settings-section">
                                <div class="form-group">
                                    <label for="visibilidad_perfil" class="form-label">Visibilidad del perfil</label>
                                    <select id="visibilidad_perfil" name="visibilidad_perfil" class="form-input">
                                        <option value="publico" <?php echo ($usuario['visibilidad_perfil'] ?? '') === 'publico' ? 'selected' : ''; ?>>Público</option>
                                        <option value="amigos" <?php echo ($usuario['visibilidad_perfil'] ?? '') === 'amigos' ? 'selected' : ''; ?>>Amigos</option>
                                        <option value="privado" <?php echo ($usuario['visibilidad_perfil'] ?? '') === 'privado' ? 'selected' : ''; ?>>Privado</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="settings-section">
                                <div class="checkbox-group">
                            <input type="checkbox" id="permitir_busqueda" name="permitir_busqueda" value="1" 
                                   <?php echo ($usuario['permitir_busqueda'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <div class="checkbox-label">
                                <span class="checkbox-title">Permitir búsqueda</span>
                                <span class="checkbox-description">Permitir que otros usuarios me encuentren</span>
                            </div>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="compartir_datos" name="compartir_datos" value="1" 
                                   <?php echo ($usuario['compartir_datos'] ?? '0') === '1' ? 'checked' : ''; ?>>
                            <div class="checkbox-label">
                                <span class="checkbox-title">Compartir datos</span>
                                <span class="checkbox-description">Permitir compartir información con terceros</span>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <i class="fa fa-save"></i> Guardar configuración de privacidad
                        </button>
                    </form>
                </section>

                <!-- Sección Notificaciones -->
                <section id="notificaciones" class="config-section">
                    <h2 class="section-title">
                        <i class="fa fa-bell"></i> Notificaciones
                    </h2>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="actualizar_notificaciones">
                        
                        <div class="settings-container">
                            <div class="checkbox-group">
                            <input type="checkbox" id="email_notificaciones" name="email_notificaciones" value="1" 
                                   <?php echo ($usuario['email_notificaciones'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <div class="checkbox-label">
                                <span class="checkbox-title">Notificaciones por email</span>
                                <span class="checkbox-description">Recibir notificaciones por correo electrónico</span>
                            </div>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="push_notificaciones" name="push_notificaciones" value="1" 
                                   <?php echo ($usuario['push_notificaciones'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <div class="checkbox-label">
                                <span class="checkbox-title">Notificaciones push</span>
                                <span class="checkbox-description">Recibir notificaciones en tiempo real</span>
                            </div>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="recordatorios_eventos" name="recordatorios_eventos" value="1" 
                                   <?php echo ($usuario['recordatorios_eventos'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <div class="checkbox-label">
                                <span class="checkbox-title">Recordatorios de eventos</span>
                                <span class="checkbox-description">Notificaciones sobre eventos próximos</span>
                            </div>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="nuevos_favoritos" name="nuevos_favoritos" value="1" 
                                   <?php echo ($usuario['nuevos_favoritos'] ?? '0') === '1' ? 'checked' : ''; ?>>
                            <div class="checkbox-label">
                                <span class="checkbox-title">Nuevos favoritos</span>
                                <span class="checkbox-description">Notificaciones sobre nuevos lugares y eventos</span>
                            </div>
                        </div>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <i class="fa fa-save"></i> Guardar configuración de notificaciones
                        </button>
                    </form>
                </section>

                <!-- Sección Preferencias -->
                <section id="preferencias" class="config-section">
                    <h2 class="section-title">
                        <i class="fa fa-sliders-h"></i> Preferencias
                    </h2>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="actualizar_preferencias">
                        
                        <div class="form-group">
                            <label for="idioma" class="form-label">Idioma</label>
                            <select id="idioma" name="idioma" class="form-input">
                                <option value="es" <?php echo ($usuario['idioma'] ?? 'es') === 'es' ? 'selected' : ''; ?>>Español</option>
                                <option value="en" <?php echo ($usuario['idioma'] ?? 'es') === 'en' ? 'selected' : ''; ?>>English</option>
                                <option value="fr" <?php echo ($usuario['idioma'] ?? 'es') === 'fr' ? 'selected' : ''; ?>>Français</option>
                            </select>
                        </div>
                        

                        
                        <div class="form-group">
                            <label for="zona_horaria" class="form-label">Zona horaria</label>
                            <select id="zona_horaria" name="zona_horaria" class="form-input">
                                <option value="America/Mexico_City" <?php echo ($usuario['zona_horaria'] ?? 'America/Mexico_City') === 'America/Mexico_City' ? 'selected' : ''; ?>>México (GMT-6)</option>
                                <option value="America/New_York" <?php echo ($usuario['zona_horaria'] ?? 'America/Mexico_City') === 'America/New_York' ? 'selected' : ''; ?>>Nueva York (GMT-5)</option>
                                <option value="Europe/Madrid" <?php echo ($usuario['zona_horaria'] ?? 'America/Mexico_City') === 'Europe/Madrid' ? 'selected' : ''; ?>>Madrid (GMT+1)</option>
                                <option value="America/Los_Angeles" <?php echo ($usuario['zona_horaria'] ?? 'America/Mexico_City') === 'America/Los_Angeles' ? 'selected' : ''; ?>>Los Ángeles (GMT-8)</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <i class="fa fa-save"></i> Guardar preferencias
                        </button>
                    </form>
                </section>

                <!-- Sección Ayuda -->
                <section id="ayuda" class="config-section">
                    <h2 class="section-title">
                        <i class="fa fa-question-circle"></i> Ayuda
                    </h2>
                    
                    <div class="security-options">
                        <div class="security-card">
                            <h4><i class="fa fa-book"></i> Guía de usuario</h4>
                            <p>Aprende a usar todas las funciones de PartyExpress</p>
                            <button class="btn-submit" onclick="window.location.href='ayuda.php'">
                                <i class="fa fa-arrow-right"></i> Ver ayuda
                            </button>
                        </div>
                        
                        <div class="security-card">
                            <h4><i class="fa fa-question"></i> Preguntas frecuentes</h4>
                            <p>Encuentra respuestas a las dudas más comunes</p>
                            <button class="btn-submit">
                                <i class="fa fa-search"></i> Ver FAQ
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Sección Soporte -->
                <section id="soporte" class="config-section">
                    <h2 class="section-title">
                        <i class="fa fa-headset"></i> Soporte
                    </h2>
                    
                    <div class="security-options">
                        <div class="security-card">
                            <h4><i class="fa fa-comments"></i> Chat en vivo</h4>
                            <p>Habla directamente con nuestro equipo de soporte</p>
                            <button class="btn-submit" onclick="window.location.href='soporte.php'">
                                <i class="fa fa-arrow-right"></i> Contactar soporte
                            </button>
                        </div>
                        
                        <div class="security-card">
                            <h4><i class="fa fa-envelope"></i> Contacto por email</h4>
                            <p>Envía un mensaje a nuestro equipo de soporte</p>
                            <button class="btn-submit">
                                <i class="fa fa-paper-plane"></i> Enviar email
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Sección Acerca de -->
                <section id="acerca" class="config-section">
                    <h2 class="section-title">
                        <i class="fa fa-info-circle"></i> Acerca de
                    </h2>
                    
                    <div class="profile-info">
                        <div class="info-item">
                            <span class="info-label">Versión:</span>
                            <span class="info-value">1.0.0</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Desarrollado por:</span>
                            <span class="info-value">PartyExpress Team</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Año:</span>
                            <span class="info-value">2024</span>
                        </div>
                    </div>
                    
                    <div class="security-options">
                        <div class="security-card">
                            <h4><i class="fa fa-file-text"></i> Información legal</h4>
                            <p>Lee nuestros términos y políticas</p>
                            <button class="btn-submit" onclick="window.location.href='acerca_de.php'">
                                <i class="fa fa-arrow-right"></i> Más información
                            </button>
                        </div>
                        
                        <div class="security-card">
                            <h4><i class="fa fa-code"></i> Código abierto</h4>
                            <p>Contribuye al desarrollo de PartyExpress</p>
                            <button class="btn-submit">
                                <i class="fa fa-github"></i> Ver en GitHub
                            </button>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script>
        // Función para aplicar cambios en tiempo real
        function aplicarCambiosEnTiempoReal(datos) {
            // Aplicar idioma
            if (datos.idioma) {
                aplicarIdioma(datos.idioma);
            }
            
            // Aplicar zona horaria
            if (datos.zona_horaria) {
                aplicarZonaHoraria(datos.zona_horaria);
            }
            
            // Mostrar notificación de éxito
            mostrarNotificacion('Cambios aplicados correctamente', 'success');
        }
        
        // Función para aplicar idioma
        function aplicarIdioma(idioma) {
            // Guardar en localStorage
            localStorage.setItem('idioma', idioma);
            
            // Aquí podrías implementar la traducción dinámica
            console.log('Idioma cambiado a:', idioma);
        }
        
        // Función para aplicar zona horaria
        function aplicarZonaHoraria(zonaHoraria) {
            // Guardar en localStorage
            localStorage.setItem('zona_horaria', zonaHoraria);
            
            // Actualizar fechas en la página si las hay
            actualizarFechasEnPagina(zonaHoraria);
        }
        
        // Función para actualizar fechas en la página
        function actualizarFechasEnPagina(zonaHoraria) {
            const fechas = document.querySelectorAll('[data-fecha]');
            fechas.forEach(elemento => {
                const fechaOriginal = elemento.getAttribute('data-fecha');
                if (fechaOriginal) {
                    const fecha = new Date(fechaOriginal);
                    const opciones = { 
                        timeZone: zonaHoraria,
                        year: 'numeric', 
                        month: '2-digit', 
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit'
                    };
                    elemento.textContent = fecha.toLocaleString('es-ES', opciones);
                }
            });
        }
        
        // Función para mostrar notificaciones
        function mostrarNotificacion(mensaje, tipo = 'success') {
            // Remover notificaciones anteriores
            const notificacionesAnteriores = document.querySelectorAll('.notificacion-flotante');
            notificacionesAnteriores.forEach(notif => notif.remove());
            
            // Crear nueva notificación
            const notificacion = document.createElement('div');
            notificacion.className = `notificacion-flotante notificacion-${tipo}`;
            notificacion.innerHTML = `
                <i class="fa fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${mensaje}</span>
            `;
            
            // Agregar al body
            document.body.appendChild(notificacion);
            
            // Mostrar con animación
            setTimeout(() => {
                notificacion.classList.add('mostrar');
            }, 100);
            
            // Ocultar después de 3 segundos
            setTimeout(() => {
                notificacion.classList.remove('mostrar');
                setTimeout(() => {
                    notificacion.remove();
                }, 300);
            }, 3000);
        }
        
        // Navegación del menú
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
        }

        // Navegación entre secciones
        const navLinks = document.querySelectorAll('.config-nav-link');
        const sections = document.querySelectorAll('.config-section');

        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remover clase active de todos los enlaces y secciones
                navLinks.forEach(l => l.classList.remove('active'));
                sections.forEach(s => s.classList.remove('active'));
                
                // Agregar clase active al enlace clickeado
                this.classList.add('active');
                
                // Mostrar la sección correspondiente
                const targetSection = this.getAttribute('data-section');
                document.getElementById(targetSection).classList.add('active');
            });
        });

        // Validación de contraseñas en tiempo real
        const passwordNuevo = document.getElementById('password_nuevo');
        const passwordConfirmar = document.getElementById('password_confirmar');
        
        if (passwordNuevo && passwordConfirmar) {
            function validarContraseñas() {
                if (passwordNuevo.value && passwordConfirmar.value) {
                    if (passwordNuevo.value === passwordConfirmar.value) {
                        passwordConfirmar.style.borderColor = '#28a745';
                        passwordConfirmar.style.boxShadow = '0 0 10px rgba(40, 167, 69, 0.3)';
                    } else {
                        passwordConfirmar.style.borderColor = '#dc3545';
                        passwordConfirmar.style.boxShadow = '0 0 10px rgba(220, 53, 69, 0.3)';
                    }
                }
            }
            
            passwordNuevo.addEventListener('input', validarContraseñas);
            passwordConfirmar.addEventListener('input', validarContraseñas);
        }
        
        // Notificación para cambio de contraseña
        const seguridadForm = document.querySelector('form[action*="cambiar_password"]');
        if (seguridadForm) {
            seguridadForm.addEventListener('submit', function(e) {
                // La validación se hace en el servidor, pero podemos mostrar una notificación de envío
                mostrarNotificacion('Procesando cambio de contraseña...', 'success');
            });
        }

        // Auto-guardar preferencias cuando cambien
        const preferenciasForm = document.querySelector('form[action*="actualizar_preferencias"]');
        if (preferenciasForm) {
            const inputs = preferenciasForm.querySelectorAll('select');
            inputs.forEach(input => {
                input.addEventListener('change', function() {
                    // Recopilar datos del formulario
                    const formData = new FormData(preferenciasForm);
                    const datos = {
                        idioma: formData.get('idioma'),
                        tema: formData.get('tema'),
                        zona_horaria: formData.get('zona_horaria')
                    };
                    
                    // Aplicar cambios inmediatamente
                    aplicarCambiosEnTiempoReal(datos);
                    
                    // Enviar formulario después de un breve delay
                    setTimeout(() => {
                        preferenciasForm.submit();
                    }, 500);
                });
            });
        }
        
        // Mejorar experiencia de checkboxes
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Agregar efecto visual
                if (this.checked) {
                    this.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 150);
                }
            });
        });
        
        // Auto-guardar notificaciones y privacidad cuando cambien
        const notificacionesForm = document.querySelector('form[action*="actualizar_notificaciones"]');
        const privacidadForm = document.querySelector('form[action*="actualizar_privacidad"]');
        
        if (notificacionesForm) {
            const checkboxes = notificacionesForm.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Mostrar notificación de cambio
                    const nombreOpcion = this.id.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    mostrarNotificacion(`Configuración de ${nombreOpcion} actualizada`, 'success');
                    
                    setTimeout(() => {
                        notificacionesForm.submit();
                    }, 300);
                });
            });
        }
        
        if (privacidadForm) {
            const checkboxes = privacidadForm.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Mostrar notificación de cambio
                    const nombreOpcion = this.id.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    mostrarNotificacion(`Configuración de ${nombreOpcion} actualizada`, 'success');
                    
                    setTimeout(() => {
                        privacidadForm.submit();
                    }, 300);
                });
            });
            
            // Auto-guardar cuando cambie el select de visibilidad
            const visibilidadSelect = privacidadForm.querySelector('select[name="visibilidad_perfil"]');
            if (visibilidadSelect) {
                visibilidadSelect.addEventListener('change', function() {
                    // Mostrar notificación de cambio
                    const opcionSeleccionada = this.options[this.selectedIndex].text;
                    mostrarNotificacion(`Visibilidad del perfil cambiada a: ${opcionSeleccionada}`, 'success');
                    
                    setTimeout(() => {
                        privacidadForm.submit();
                    }, 300);
                });
            }
        }
        
        // Mejorar experiencia de los selects
        document.querySelectorAll('select.form-input').forEach(select => {
            select.addEventListener('change', function() {
                // Agregar efecto visual
                this.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            });
        });
        
        // Cargar configuración guardada al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar idioma
            const idiomaGuardado = localStorage.getItem('idioma');
            if (idiomaGuardado) {
                aplicarIdioma(idiomaGuardado);
            }
            
            // Cargar zona horaria
            const zonaHorariaGuardada = localStorage.getItem('zona_horaria');
            if (zonaHorariaGuardada) {
                aplicarZonaHoraria(zonaHorariaGuardada);
            }
        });
    </script>
</body>
</html>
