<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html?redirect=' . urlencode('acerca_de.php'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acerca de - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .acerca-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .acerca-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .acerca-title {
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
        
        .acerca-card {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(162,89,247,0.2);
            margin-bottom: 25px;
        }
        
        .acerca-card h3 {
            color: #a259f7;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-item {
            background: rgba(255,255,255,0.05);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-item:hover {
            transform: translateY(-5px);
            background: rgba(162,89,247,0.1);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #a259f7;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #ccc;
            font-size: 1rem;
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .team-member {
            background: rgba(255,255,255,0.05);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .team-member:hover {
            transform: translateY(-3px);
            background: rgba(162,89,247,0.1);
        }
        
        .team-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(45deg, #a259f7, #7209b7);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 2rem;
            color: white;
            font-weight: bold;
        }
        
        .team-name {
            color: #a259f7;
            font-size: 1.2rem;
            margin-bottom: 5px;
        }
        
        .team-role {
            color: #ccc;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .team-description {
            color: #ccc;
            font-size: 0.85rem;
            line-height: 1.5;
        }
        
        .timeline {
            position: relative;
            margin: 30px 0;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, #a259f7, #7209b7);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 30px;
            padding-left: 60px;
        }
        
        .timeline-dot {
            position: absolute;
            left: 11px;
            top: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #a259f7;
            border: 3px solid #2D1950;
        }
        
        .timeline-content {
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            padding: 20px;
            border-left: 4px solid #a259f7;
        }
        
        .timeline-year {
            color: #a259f7;
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        .timeline-title {
            color: #a259f7;
            font-size: 1rem;
            margin-bottom: 10px;
        }
        
        .timeline-description {
            color: #ccc;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .value-item {
            background: rgba(255,255,255,0.05);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .value-item:hover {
            transform: translateY(-3px);
            background: rgba(162,89,247,0.1);
        }
        
        .value-icon {
            font-size: 2.5rem;
            color: #a259f7;
            margin-bottom: 15px;
        }
        
        .value-title {
            color: #a259f7;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }
        
        .value-description {
            color: #ccc;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .contact-info {
            background: rgba(162,89,247,0.1);
            border: 1px solid rgba(162,89,247,0.3);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
        }
        
        .contact-info h4 {
            color: #a259f7;
            margin-bottom: 15px;
        }
        
        .contact-info p {
            color: #ccc;
            margin-bottom: 10px;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(162,89,247,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #a259f7;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .social-link:hover {
            background: #a259f7;
            color: white;
            transform: translateY(-2px);
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

    <div class="acerca-container">
        <header class="acerca-header">
            <h1 class="acerca-title">Acerca de PartyExpress</h1>
            <a href="configuracion.php" class="btn-volver">
                <i class="fa fa-arrow-left"></i> Volver a configuración
            </a>
        </header>

        <!-- Nuestra Historia -->
        <div class="acerca-card">
            <h3><i class="fa fa-book"></i> Nuestra Historia</h3>
            <p style="color: #ccc; line-height: 1.6; margin-bottom: 20px;">
                PartyExpress nació en 2023 con la visión de conectar a las personas a través de eventos y celebraciones. 
                Nuestro equipo, apasionado por crear experiencias inolvidables, desarrolló esta plataforma para facilitar 
                la organización y descubrimiento de eventos en Paraguay.
            </p>
            
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <div class="timeline-year">2023</div>
                        <div class="timeline-title">Fundación</div>
                        <div class="timeline-description">
                            PartyExpress fue fundada con el objetivo de revolucionar la forma en que las personas 
                            organizan y descubren eventos en Paraguay.
                        </div>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <div class="timeline-year">2024</div>
                        <div class="timeline-title">Lanzamiento Beta</div>
                        <div class="timeline-description">
                            Lanzamos nuestra versión beta con funcionalidades básicas de creación y búsqueda de eventos.
                        </div>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <div class="timeline-year">2024</div>
                        <div class="timeline-title">Expansión</div>
                        <div class="timeline-description">
                            Agregamos nuevas funcionalidades como favoritos, perfiles de usuario y sistema de notificaciones.
                        </div>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <div class="timeline-year">2025</div>
                        <div class="timeline-title">Futuro</div>
                        <div class="timeline-description">
                            Planeamos expandirnos a más ciudades de Paraguay y agregar funcionalidades avanzadas 
                            como pagos en línea y gestión de invitados.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nuestra Misión -->
        <div class="acerca-card">
            <h3><i class="fa fa-bullseye"></i> Nuestra Misión</h3>
            
            <div class="info-card">
                <h4>Conectar Personas</h4>
                <p>
                    Creemos que los eventos tienen el poder de unir a las personas y crear momentos inolvidables. 
                    Nuestra misión es facilitar la organización y descubrimiento de eventos que enriquezcan la vida 
                    social de nuestra comunidad.
                </p>
            </div>
            
            <div class="info-card">
                <h4>Simplificar la Organización</h4>
                <p>
                    Queremos que organizar un evento sea tan fácil como hacer clic en un botón. Nuestra plataforma 
                    proporciona todas las herramientas necesarias para crear, gestionar y promocionar eventos de manera 
                    eficiente y profesional.
                </p>
            </div>
            
            <div class="info-card">
                <h4>Promover la Cultura Paraguaya</h4>
                <p>
                    Estamos comprometidos con promover y preservar la rica cultura paraguaya a través de eventos 
                    que celebren nuestras tradiciones, música, gastronomía y valores.
                </p>
            </div>
        </div>

        <!-- Nuestros Valores -->
        <div class="acerca-card">
            <h3><i class="fa fa-heart"></i> Nuestros Valores</h3>
            
            <div class="values-grid">
                <div class="value-item">
                    <div class="value-icon">
                        <i class="fa fa-users"></i>
                    </div>
                    <div class="value-title">Comunidad</div>
                    <div class="value-description">
                        Construimos una comunidad inclusiva donde todos pueden participar y contribuir 
                        a crear experiencias únicas.
                    </div>
                </div>
                
                <div class="value-item">
                    <div class="value-icon">
                        <i class="fa fa-shield-alt"></i>
                    </div>
                    <div class="value-title">Confianza</div>
                    <div class="value-description">
                        La seguridad y privacidad de nuestros usuarios son fundamentales. 
                        Trabajamos constantemente para proteger su información.
                    </div>
                </div>
                
                <div class="value-item">
                    <div class="value-icon">
                        <i class="fa fa-lightbulb"></i>
                    </div>
                    <div class="value-title">Innovación</div>
                    <div class="value-description">
                        Buscamos constantemente nuevas formas de mejorar la experiencia 
                        de nuestros usuarios y facilitar la organización de eventos.
                    </div>
                </div>
                
                <div class="value-item">
                    <div class="value-icon">
                        <i class="fa fa-star"></i>
                    </div>
                    <div class="value-title">Excelencia</div>
                    <div class="value-description">
                        Nos esforzamos por ofrecer el mejor servicio posible, 
                        desde la atención al cliente hasta la funcionalidad de la plataforma.
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="acerca-card">
            <h3><i class="fa fa-chart-bar"></i> Nuestros Números</h3>
            
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">1,500+</div>
                    <div class="stat-label">Eventos Creados</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-number">5,000+</div>
                    <div class="stat-label">Usuarios Registrados</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Ciudades Cubiertas</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-number">98%</div>
                    <div class="stat-label">Satisfacción del Cliente</div>
                </div>
            </div>
        </div>

        <!-- Nuestro Equipo -->
        <div class="acerca-card">
            <h3><i class="fa fa-users"></i> Nuestro Equipo</h3>
            
            <div class="team-grid">
                <div class="team-member">
                    <div class="team-avatar">M</div>
                    <div class="team-name">María González</div>
                    <div class="team-role">CEO & Fundadora</div>
                    <div class="team-description">
                        Apasionada por la tecnología y los eventos sociales. 
                        Lidera la visión estratégica de PartyExpress.
                    </div>
                </div>
                
                <div class="team-member">
                    <div class="team-avatar">C</div>
                    <div class="team-name">Carlos Rodríguez</div>
                    <div class="team-role">CTO</div>
                    <div class="team-description">
                        Experto en desarrollo web y aplicaciones móviles. 
                        Asegura que nuestra plataforma funcione perfectamente.
                    </div>
                </div>
                
                <div class="team-member">
                    <div class="team-avatar">A</div>
                    <div class="team-name">Ana Martínez</div>
                    <div class="team-role">Diseñadora UX/UI</div>
                    <div class="team-description">
                        Crea experiencias de usuario intuitivas y atractivas 
                        que hacen que organizar eventos sea un placer.
                    </div>
                </div>
                
                <div class="team-member">
                    <div class="team-avatar">D</div>
                    <div class="team-name">David López</div>
                    <div class="team-role">Soporte al Cliente</div>
                    <div class="team-description">
                        Nuestro héroe del soporte, siempre listo para ayudar 
                        a nuestros usuarios con cualquier pregunta o problema.
                    </div>
                </div>
            </div>
        </div>

        <!-- Información de Contacto -->
        <div class="acerca-card">
            <h3><i class="fa fa-envelope"></i> Contáctanos</h3>
            
            <div class="contact-info">
                <h4>¿Tienes preguntas o sugerencias?</h4>
                <p>Nos encantaría escuchar de ti. Nuestro equipo está aquí para ayudarte.</p>
                
                <div style="margin: 20px 0;">
                    <p><i class="fa fa-envelope"></i> info@partyexpress.com.py</p>
                    <p><i class="fa fa-phone"></i> +595 21 123 456</p>
                    <p><i class="fa fa-map-marker-alt"></i> Asunción, Paraguay</p>
                </div>
                
                <div class="social-links">
                    <a href="https://www.facebook.com/partyexpress.py" class="social-link" target="_blank">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://www.instagram.com/partyexpress_py?utm_source=ig_web_button_share_sheet&igsh=MXF6dWcydmt0dTFuNA==" class="social-link" target="_blank">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
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
