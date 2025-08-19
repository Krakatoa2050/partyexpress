<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html?redirect=' . urlencode('favoritos.php'));
    exit();
}

// Obtener favoritos del usuario
$favoritos = [];
$total_favoritos = 0;

try {
    $conn = obtenerConexion();
    
    // Por ahora simulamos favoritos, en el futuro esto vendría de una tabla favoritos
    $stmt = $conn->prepare('
        SELECT 
            se.id,
            se.titulo,
            se.descripcion,
            se.ubicacion,
            se.fecha_evento,
            se.hora_evento,
            se.capacidad,
            se.presupuesto,
            se.privacidad,
            se.estado,
            se.fecha_creacion,
            u.nombre as organizador_nombre,
            u.usuario as organizador_usuario,
            ce.nombre as categoria_nombre,
            ce.icono as categoria_icono,
            ce.color as categoria_color
        FROM solicitudes_eventos se
        JOIN usuarios u ON se.usuario_id = u.id
        JOIN categorias_eventos ce ON se.categoria_id = ce.id
        WHERE se.estado = "Aprobado" AND se.privacidad = "Público"
        ORDER BY se.fecha_evento ASC
        LIMIT 10
    ');
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $favoritos[] = $row;
    }
    
    $total_favoritos = count($favoritos);
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    $error = 'Error al cargar favoritos: ' . $e->getMessage();
}

function formatearFecha($fecha) {
    return date('d/m/Y', strtotime($fecha));
}

function formatearHora($hora) {
    return date('H:i', strtotime($hora));
}

function formatearPresupuesto($presupuesto) {
    if (!$presupuesto) return 'No especificado';
    return 'Gs. ' . number_format($presupuesto, 0, ',', '.');
}

function obtenerClaseCategoria($categoria) {
    switch ($categoria) {
        case 'Cumpleaños': return 'categoria-cumpleanos';
        case 'Boda': return 'categoria-boda';
        case 'Graduación': return 'categoria-graduacion';
        case 'Aniversario': return 'categoria-aniversario';
        case 'Evento Corporativo': return 'categoria-corporativo';
        case 'Fiesta Temática': return 'categoria-tematica';
        case 'Baby Shower': return 'categoria-babyshower';
        case 'Despedida': return 'categoria-despedida';
        default: return 'categoria-otro';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Favoritos - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .favoritos-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .favoritos-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .favoritos-title {
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
        
        .favoritos-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .favoritos-count {
            color: #a259f7;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .favoritos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .favorito-card {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(162,89,247,0.2);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .favorito-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #a259f7 60%, #7209b7 100%);
        }
        
        .favorito-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 70px rgba(0,0,0,0.4);
            border-color: #a259f7;
        }
        
        .favorito-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .favorito-titulo {
            color: #a259f7;
            font-size: 1.4rem;
            font-weight: 600;
            margin: 0;
            flex: 1;
        }
        
        .categoria-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 10px;
        }
        
        .categoria-cumpleanos {
            background: rgba(255, 107, 107, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(255, 107, 107, 0.4);
        }
        
        .categoria-boda {
            background: rgba(255, 159, 243, 0.2);
            color: #ff9ff3;
            border: 1px solid rgba(255, 159, 243, 0.4);
        }
        
        .categoria-graduacion {
            background: rgba(84, 160, 255, 0.2);
            color: #54a0ff;
            border: 1px solid rgba(84, 160, 255, 0.4);
        }
        
        .categoria-aniversario {
            background: rgba(95, 39, 205, 0.2);
            color: #5f27cd;
            border: 1px solid rgba(95, 39, 205, 0.4);
        }
        
        .categoria-corporativo {
            background: rgba(0, 210, 211, 0.2);
            color: #00d2d3;
            border: 1px solid rgba(0, 210, 211, 0.4);
        }
        
        .categoria-tematica {
            background: rgba(255, 159, 67, 0.2);
            color: #ff9f43;
            border: 1px solid rgba(255, 159, 67, 0.4);
        }
        
        .categoria-babyshower {
            background: rgba(165, 94, 234, 0.2);
            color: #a55eea;
            border: 1px solid rgba(165, 94, 234, 0.4);
        }
        
        .categoria-despedida {
            background: rgba(38, 222, 129, 0.2);
            color: #26de81;
            border: 1px solid rgba(38, 222, 129, 0.4);
        }
        
        .categoria-otro {
            background: rgba(162, 89, 247, 0.2);
            color: #a259f7;
            border: 1px solid rgba(162, 89, 247, 0.4);
        }
        
        .favorito-descripcion {
            color: #ccc;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        
        .favorito-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-icon {
            color: #a259f7;
            width: 18px;
            text-align: center;
        }
        
        .info-label {
            color: #a259f7;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .info-value {
            color: #fff;
            font-size: 0.9rem;
        }
        
        .favorito-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .organizador {
            color: #888;
            font-size: 0.85rem;
        }
        
        .organizador strong {
            color: #a259f7;
        }
        
        .fecha-evento {
            color: #28a745;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .ver-mas-btn {
            background: linear-gradient(90deg, #a259f7 60%, #7209b7 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
        }
        
        .ver-mas-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(162,89,247,0.3);
        }
        
        .sin-favoritos {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }
        
        .sin-favoritos i {
            font-size: 4rem;
            color: #a259f7;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .sin-favoritos h3 {
            color: #a259f7;
            margin-bottom: 10px;
        }
        
        .btn-explorar {
            background: linear-gradient(90deg, #a259f7 60%, #7209b7 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
        }
        
        .btn-explorar:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(162,89,247,0.3);
        }
        
        @media (max-width: 768px) {
            .favoritos-info {
                flex-direction: column;
                align-items: stretch;
            }
            
            .favoritos-grid {
                grid-template-columns: 1fr;
            }
            
            .favorito-info {
                grid-template-columns: 1fr;
            }
            
            .favorito-footer {
                flex-direction: column;
                align-items: stretch;
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
                                <span class="menu-stat-number">3</span>
                                <span class="menu-stat-label">Eventos</span>
                            </div>
                            <div class="menu-stat">
                                <span class="menu-stat-number">12</span>
                                <span class="menu-stat-label">Días</span>
                            </div>
                            <div class="menu-stat">
                                <span class="menu-stat-number"><?php echo $total_favoritos; ?></span>
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
                        <span class="menu-item-badge">3</span>
                    </a>
                    <a href="perfil.php" class="menu-item">
                        <i class="fa fa-user"></i> Mi Perfil
                    </a>
                    <a href="favoritos.php" class="menu-item">
                        <i class="fa fa-heart"></i> Favoritos
                        <span class="menu-item-badge"><?php echo $total_favoritos; ?></span>
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

    <div class="favoritos-container">
        <header class="favoritos-header">
            <h1 class="favoritos-title">Mis Favoritos</h1>
            <a href="index.php" class="btn-volver">
                <i class="fa fa-home"></i> Volver al inicio
            </a>
        </header>

        <div class="favoritos-info">
            <div class="favoritos-count">
                <?php if ($total_favoritos > 0): ?>
                    Tienes <?php echo $total_favoritos; ?> evento<?php echo $total_favoritos !== 1 ? 's' : ''; ?> favorito<?php echo $total_favoritos !== 1 ? 's' : ''; ?>
                <?php else: ?>
                    No tienes favoritos aún
                <?php endif; ?>
            </div>
        </div>

        <?php if ($total_favoritos > 0): ?>
            <div class="favoritos-grid">
                <?php foreach ($favoritos as $favorito): ?>
                    <a href="fiesta_detalle.php?id=<?php echo $favorito['id']; ?>" class="favorito-card">
                        <div class="favorito-header">
                            <h3 class="favorito-titulo"><?php echo htmlspecialchars($favorito['titulo']); ?></h3>
                            <span class="categoria-badge <?php echo obtenerClaseCategoria($favorito['categoria_nombre']); ?>">
                                <i class="fa <?php echo htmlspecialchars($favorito['categoria_icono']); ?>"></i>
                                <?php echo htmlspecialchars($favorito['categoria_nombre']); ?>
                            </span>
                        </div>
                        
                        <p class="favorito-descripcion"><?php echo htmlspecialchars($favorito['descripcion']); ?></p>
                        
                        <div class="favorito-info">
                            <div class="info-item">
                                <i class="fa fa-map-marker-alt info-icon"></i>
                                <span class="info-label">Ubicación:</span>
                                <span class="info-value"><?php echo htmlspecialchars($favorito['ubicacion']); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <i class="fa fa-calendar info-icon"></i>
                                <span class="info-label">Fecha:</span>
                                <span class="info-value"><?php echo formatearFecha($favorito['fecha_evento']); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <i class="fa fa-clock info-icon"></i>
                                <span class="info-label">Hora:</span>
                                <span class="info-value"><?php echo formatearHora($favorito['hora_evento']); ?></span>
                            </div>
                            
                            <?php if ($favorito['capacidad']): ?>
                            <div class="info-item">
                                <i class="fa fa-users info-icon"></i>
                                <span class="info-label">Capacidad:</span>
                                <span class="info-value"><?php echo $favorito['capacidad']; ?> personas</span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($favorito['presupuesto']): ?>
                            <div class="info-item">
                                <i class="fa fa-money-bill info-icon"></i>
                                <span class="info-label">Presupuesto:</span>
                                <span class="info-value"><?php echo formatearPresupuesto($favorito['presupuesto']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="favorito-footer">
                            <div class="organizador">
                                Organizado por: <strong><?php echo htmlspecialchars($favorito['organizador_nombre']); ?></strong>
                            </div>
                            
                            <div class="fecha-evento">
                                <i class="fa fa-calendar-check"></i>
                                <?php echo formatearFecha($favorito['fecha_evento']); ?>
                            </div>
                        </div>
                        
                        <div class="ver-mas-btn">
                            <i class="fa fa-eye"></i>
                            Ver detalles
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="sin-favoritos">
                <i class="fa fa-heart"></i>
                <h3>No tienes favoritos aún</h3>
                <p>Explora eventos y agrega tus favoritos para verlos aquí</p>
                <a href="index.php" class="btn-explorar">
                    <i class="fa fa-search"></i>
                    Explorar eventos
                </a>
            </div>
        <?php endif; ?>
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
    </script>
</body>
</html>
