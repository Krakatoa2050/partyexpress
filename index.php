<?php
session_start();
require_once 'conexion.php';

// Obtener fiestas destacadas para mostrar en la página principal
$fiestas_destacadas = [];
try {
    $conn = obtenerConexion();
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
            ce.nombre as categoria_nombre,
            ce.icono as categoria_icono,
            ce.color as categoria_color,
            u.nombre as organizador_nombre
        FROM solicitudes_eventos se
        JOIN usuarios u ON se.usuario_id = u.id
        JOIN categorias_eventos ce ON se.categoria_id = ce.id
        WHERE se.estado = "Aprobado" AND se.privacidad = "Público"
        ORDER BY se.fecha_evento ASC
        LIMIT 3
    ');
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $fiestas_destacadas[] = $row;
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    // Si hay error, usar datos de ejemplo
    $fiestas_destacadas = [
        [
            'id' => 1,
            'titulo' => 'Fiesta de Cumpleaños 25',
            'descripcion' => 'Celebración especial con música en vivo y buffet completo',
            'ubicacion' => 'Salón La Casona, Asunción',
            'fecha_evento' => '2024-12-15',
            'hora_evento' => '20:00:00',
            'capacidad' => 150,
            'presupuesto' => 2000000,
            'categoria_nombre' => 'Cumpleaños',
            'categoria_icono' => 'fa-birthday-cake',
            'categoria_color' => '#ff6b6b',
            'organizador_nombre' => 'María González'
        ],
        [
            'id' => 2,
            'titulo' => 'Graduación Universidad',
            'descripcion' => 'Ceremonia de graduación con cena de gala',
            'ubicacion' => 'Centro de Convenciones, Asunción',
            'fecha_evento' => '2024-12-20',
            'hora_evento' => '19:00:00',
            'capacidad' => 300,
            'presupuesto' => 3500000,
            'categoria_nombre' => 'Graduación',
            'categoria_icono' => 'fa-graduation-cap',
            'categoria_color' => '#54a0ff',
            'organizador_nombre' => 'Carlos Rodríguez'
        ],
        [
            'id' => 3,
            'titulo' => 'Boda de Ana y Juan',
            'descripcion' => 'Celebración de amor con ceremonia religiosa y recepción',
            'ubicacion' => 'Hotel Gran Asunción',
            'fecha_evento' => '2024-12-25',
            'hora_evento' => '18:00:00',
            'capacidad' => 200,
            'presupuesto' => 5000000,
            'categoria_nombre' => 'Boda',
            'categoria_icono' => 'fa-heart',
            'categoria_color' => '#ff9ff3',
            'organizador_nombre' => 'Ana Martínez'
        ]
    ];
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tres Bloques</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .fiesta-card {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(162,89,247,0.2);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            display: block;
            min-height: 380px;
            width: auto;
            margin: 0;
        }
        
        .fiesta-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #a259f7 60%, #7209b7 100%);
        }
        
        .fiesta-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 70px rgba(0,0,0,0.4);
            border-color: #a259f7;
        }
        
        .fiesta-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .fiesta-titulo {
            color: #a259f7;
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0 0 12px 0;
            flex: 1;
            line-height: 1.2;
        }
        
        .categoria-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 8px;
        }
        
        .fiesta-descripcion {
            color: #ccc;
            line-height: 1.4;
            margin-bottom: 20px;
            font-size: 0.9rem;
            min-height: 50px;
            max-height: 70px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
        
        .fiesta-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }
        
        .info-icon {
            color: #a259f7;
            width: 16px;
            text-align: center;
        }
        
        .info-label {
            color: #a259f7;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .info-value {
            color: #fff;
            font-size: 0.85rem;
        }
        
        .fiesta-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
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
            font-size: 0.95rem;
        }
        
        .ver-mas-btn {
            background: linear-gradient(90deg, #a259f7 60%, #7209b7 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
            width: 100%;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(162,89,247,0.3);
        }
        
        .ver-mas-btn:hover {
            background: linear-gradient(90deg, #7209b7 60%, #a259f7 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(162,89,247,0.4);
        }
        
        @media (max-width: 768px) {
            .fiesta-info {
                grid-template-columns: 1fr;
            }
            
            .fiesta-footer {
                flex-direction: column;
                align-items: stretch;
            }
        }
        
        /* Estilos del Footer */
        .footer-section {
            background: rgba(45, 25, 80, 0.9);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(162,89,247,0.3);
            padding: 40px 0 20px;
            margin-top: 60px;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .footer-section h3 {
            color: #a259f7;
            font-size: 1.2rem;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .footer-section p {
            color: #ccc;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-section ul li {
            margin-bottom: 8px;
        }
        
        .footer-section ul li a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-section ul li a:hover {
            color: #a259f7;
        }
        
        .redes-sociales {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .red-social {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ccc;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: 1px solid rgba(162,89,247,0.2);
        }
        
        .red-social:hover {
            background: rgba(162,89,247,0.1);
            color: #a259f7;
            border-color: #a259f7;
            transform: translateY(-2px);
        }
        
        .red-social i {
            font-size: 1.2rem;
            color: #a259f7;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(162,89,247,0.2);
            margin-top: 30px;
            padding-top: 20px;
            text-align: center;
        }
        
        .footer-bottom p {
            color: #888;
            font-size: 0.9rem;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }
        .fiestas-lista {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-top: 30px;
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
                <span style="color:#fff;">Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="mis_solicitudes.php" class="menu-item">
                        <i class="fa fa-calendar-check"></i> Mis Solicitudes
                    </a>
                    <form method="POST" action="logout.php" style="margin:0;">
                        <button type="submit" class="logout-btn">Cerrar sesión</button>
                    </form>
                </div>
            <?php else: ?>
                <a href="login.html" class="login-btn">Iniciar sesión</a>
            <?php endif; ?>
        </span>
    </nav>
    
    <section class="buscador-section">
        <h1>Encuentra fiestas y lugares para celebrar</h1>
        <form class="buscador-form" method="GET" action="buscar.php">
            <input type="text" name="q" placeholder="Buscar fiestas, lugares o ciudades..." required>
            <button type="submit">Buscar</button>
        </form>
    </section>
    
    <section class="categorias-section">
        <h2>Categorías populares</h2>
        <div class="categorias-lista">
            <div class="categoria-card">Electrónica</div>
            <div class="categoria-card">Bares</div>
            <div class="categoria-card">Fiestas privadas</div>
            <div class="categoria-card">Conciertos</div>
            <div class="categoria-card">Salones de eventos</div>
        </div>
    </section>
    
    <section class="fiestas-section">
        <h2>Lugares y fiestas destacados en Paraguay</h2>
        <div class="fiestas-lista">
            <?php if (empty($fiestas_destacadas)): ?>
                <div style="text-align: center; padding: 40px; color: #888;">
                    <i class="fa fa-calendar-times" style="font-size: 3rem; color: #a259f7; margin-bottom: 20px; opacity: 0.5;"></i>
                    <h3 style="color: #a259f7; margin-bottom: 10px;">No hay fiestas destacadas</h3>
                    <p>Las fiestas aparecerán aquí cuando sean aprobadas</p>
                </div>
            <?php else: ?>
                <?php foreach ($fiestas_destacadas as $fiesta): ?>
                    <a href="fiesta_detalle.php?id=<?php echo $fiesta['id']; ?>" class="fiesta-card">
                        <div class="fiesta-header">
                            <h3 class="fiesta-titulo"><?php echo htmlspecialchars($fiesta['titulo']); ?></h3>
                            <span class="categoria-badge" style="background: rgba(<?php echo hex2rgb($fiesta['categoria_color']); ?>, 0.2); color: <?php echo $fiesta['categoria_color']; ?>; border: 1px solid rgba(<?php echo hex2rgb($fiesta['categoria_color']); ?>, 0.4);">
                                <i class="fa <?php echo htmlspecialchars($fiesta['categoria_icono']); ?>"></i>
                                <?php echo htmlspecialchars($fiesta['categoria_nombre']); ?>
                            </span>
                        </div>
                        
                        <p class="fiesta-descripcion"><?php echo htmlspecialchars($fiesta['descripcion']); ?></p>
                        
                        <div class="fiesta-info">
                            <div class="info-item">
                                <i class="fa fa-map-marker-alt info-icon"></i>
                                <span class="info-label">Ubicación:</span>
                                <span class="info-value"><?php echo htmlspecialchars($fiesta['ubicacion']); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <i class="fa fa-calendar info-icon"></i>
                                <span class="info-label">Fecha:</span>
                                <span class="info-value"><?php echo formatearFecha($fiesta['fecha_evento']); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <i class="fa fa-clock info-icon"></i>
                                <span class="info-label">Hora:</span>
                                <span class="info-value"><?php echo formatearHora($fiesta['hora_evento']); ?></span>
                            </div>
                            
                            <?php if ($fiesta['capacidad']): ?>
                            <div class="info-item">
                                <i class="fa fa-users info-icon"></i>
                                <span class="info-label">Capacidad:</span>
                                <span class="info-value"><?php echo $fiesta['capacidad']; ?> personas</span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($fiesta['presupuesto']): ?>
                            <div class="info-item">
                                <i class="fa fa-money-bill info-icon"></i>
                                <span class="info-label">Presupuesto:</span>
                                <span class="info-value"><?php echo formatearPresupuesto($fiesta['presupuesto']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="fiesta-footer">
                            <div class="organizador">
                                Organizado por <strong><?php echo htmlspecialchars($fiesta['organizador_nombre']); ?></strong>
                            </div>
                            <div class="fecha-evento">
                                <?php echo formatearFecha($fiesta['fecha_evento']); ?>
                            </div>
                            <div class="ver-mas-btn">
                                <i class="fa fa-eye"></i>
                                Ver detalles
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    
    <section class="organiza-section">
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <a href="organizar.php" class="organiza-btn">+ Organiza tu propia fiesta</a>
        <?php else: ?>
            <a href="login.html?redirect=<?php echo urlencode('organizar.php'); ?>" class="organiza-btn">+ Organiza tu propia fiesta</a>
        <?php endif; ?>
    </section>
    
    <footer class="footer-section">
        <div class="footer-content">
            <div class="footer-section">
                <h3>PartyExpress</h3>
                <p>Tu plataforma para organizar y encontrar los mejores eventos en Paraguay</p>
            </div>
            
            <div class="footer-section">
                <h3>Enlaces Rápidos</h3>
                <ul>
                    <li><a href="fiestas.php">Fiestas</a></li>
                    <li><a href="lugares.php">Lugares</a></li>
                    <li><a href="organizar.php">Organizar Evento</a></li>
                    <li><a href="contacto.php">Contacto</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Síguenos</h3>
                <div class="redes-sociales">
                    <a href="https://www.instagram.com/partyexpress_py?utm_source=ig_web_button_share_sheet&igsh=MXF6dWcydmt0dTFuNA==" target="_blank" class="red-social">
                        <i class="fab fa-instagram"></i>
                        <span>@partyexpress_py</span>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2024 PartyExpress. Todos los derechos reservados.</p>
        </div>
    </footer>
    
    <script>
        const menuToggle = document.getElementById("menuToggle");
        const dropdownMenu = document.getElementById("dropdownMenu");
        
        if (menuToggle && dropdownMenu) {
            menuToggle.onclick = function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
            };
            
            document.addEventListener('click', function(e) {
                if (!dropdownMenu.contains(e.target) && !menuToggle.contains(e.target)) {
                    dropdownMenu.classList.remove('show');
                }
            });
        }
    </script>
</body>
</html>

<?php
function hex2rgb($hex) {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex,0,1).substr($hex,0,1));
        $g = hexdec(substr($hex,1,1).substr($hex,1,1));
        $b = hexdec(substr($hex,2,1).substr($hex,2,1));
    } else {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
    }
    return "$r, $g, $b";
}
?> 